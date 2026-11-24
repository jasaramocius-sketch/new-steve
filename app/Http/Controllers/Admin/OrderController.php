<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use App\Services\InvoiceSettingsService;
use App\Services\Couriers\CourierManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'order_number', 'total_amount', 'total', 'status', 'payment_status', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $query = Order::with('user');

        $statusFilter = $request->status;
        if (in_array($statusFilter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $query->where('status', $statusFilter);
        }

        $paymentStatusFilter = $request->payment_status;
        if (in_array($paymentStatusFilter, ['unpaid', 'paid', 'refunded'], true)) {
            $query->where('payment_status', $paymentStatusFilter);
        }

        $orders = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        $orders->appends($request->query())->onEachSide(1);

        return view('backend.orders.index', compact('orders', 'sortBy', 'sortDir', 'statusFilter', 'paymentStatusFilter'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);

        return view('backend.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () use ($request, $id) {
                    $order = Order::find($id);

                    if (! $order) {
                        return false;
                    }

                    $effectivePaymentStatus = $request->has('payment_status')
                        ? $request->input('payment_status')
                        : ($order->payment_status ?? 'unpaid');

                    if ($effectivePaymentStatus !== 'paid') {
                        return false;
                    }

                    $details = $order->payment_details;
                    $details = is_array($details) ? $details : (is_string($details) ? json_decode($details, true) : []);
                    $details = is_array($details) ? $details : [];

                    return empty($details['transaction_id'] ?? '');
                }),
            ],
            'tracking_number' => 'nullable|string|max:255',
            'tracking_carrier' => 'nullable|string|max:100',
        ], [
            'transaction_id.required' => 'Transaction ID is required when payment status is Paid.',
        ]);

        $order = Order::with('items.product')->findOrFail($id);
        $oldStatus = $order->status;
        $requestedStatus = $validatedData['status'] ?? null;
        $statusRanks = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];

        if ($requestedStatus !== null && $requestedStatus !== $oldStatus) {
            if ($oldStatus === 'cancelled') {
                return back()
                    ->withErrors(['status' => 'Cancelled orders cannot be changed. Use a return/refund workflow if needed.'])
                    ->withInput();
            }

            if ($requestedStatus === 'cancelled' && in_array($oldStatus, ['shipped', 'delivered'], true)) {
                return back()
                    ->withErrors(['status' => $oldStatus === 'delivered'
                        ? 'Delivered orders cannot be cancelled. Use Return/Refund instead.'
                        : 'Shipped orders cannot be cancelled. Use Return/Refund instead.'])
                    ->withInput();
            }

            $isMovingStatusBackward = isset($statusRanks[$oldStatus], $statusRanks[$requestedStatus])
                && $statusRanks[$requestedStatus] < $statusRanks[$oldStatus];

            if ($isMovingStatusBackward) {
                return back()
                    ->withErrors(['status' => 'Order status cannot be moved backward.'])
                    ->withInput();
            }
        }

        $requestedPaymentStatus = $validatedData['payment_status'] ?? null;
        $oldPaymentStatus = $order->payment_status ?? 'unpaid';
        $paymentStatusRanks = ['unpaid' => 0, 'paid' => 1, 'refunded' => 2];

        if (
            $requestedPaymentStatus !== null
            && $requestedPaymentStatus !== $oldPaymentStatus
            && isset($paymentStatusRanks[$oldPaymentStatus], $paymentStatusRanks[$requestedPaymentStatus])
            && $paymentStatusRanks[$requestedPaymentStatus] < $paymentStatusRanks[$oldPaymentStatus]
        ) {
            return back()
                ->withErrors(['payment_status' => 'Payment status cannot be moved backward.'])
                ->withInput();
        }

        $newPaymentStatus = $request->has('payment_status') && $request->filled('payment_status')
            ? $request->payment_status
            : $order->payment_status;

        $rawDetails = $order->payment_details;
        $details = is_array($rawDetails) ? $rawDetails : (is_string($rawDetails) ? json_decode($rawDetails, true) : null);
        $details = is_array($details) ? $details : [];
        $existingTxnId = $details['transaction_id'] ?? '';

        $update = [];
        if ($request->has('status') && $request->filled('status')) {
            $update['status'] = $request->status;
        }
        if ($request->has('payment_status') && $request->filled('payment_status')) {
            $update['payment_status'] = $request->payment_status;
        }
        if ($request->has('tracking_number')) {
            $update['tracking_number'] = trim((string) $request->tracking_number) ?: null;
        }
        if ($request->has('tracking_carrier')) {
            $update['tracking_carrier'] = trim((string) $request->tracking_carrier) ?: null;
        }

        // Transaction ID only makes sense when the payment is Paid.
        $paidOrder = $newPaymentStatus === 'paid' || ($order->payment_status === 'paid' && ! $request->has('payment_status'));

        if ($paidOrder && $request->has('transaction_id')) {
            $transactionId = trim((string) $request->transaction_id);

            if ($transactionId === '') {
                // No-op: an existing transaction ID is never cleared.
            } elseif ($existingTxnId === $transactionId) {
                // Same value re-submitted — nothing to do.
            } elseif ($existingTxnId !== '') {
                // Already set — cannot be changed (one-time).
                return back()->withErrors(['transaction_id' => 'Transaction ID is already set for this order and can only be set once.'])->withInput();
            } else {
                // Check uniqueness across all other orders.
                $duplicate = Order::where('id', '!=', $order->id)
                    ->where('payment_details->transaction_id', $transactionId)
                    ->exists();

                if ($duplicate) {
                    return back()->withErrors(['transaction_id' => 'This Transaction ID is already used by another order.'])->withInput();
                }

                $details['transaction_id'] = $transactionId;
                $update['payment_details'] = json_encode($details);
            }
        }

        // Refunds for Stripe/card payments must go through Stripe API, not just a label.
        if ($requestedPaymentStatus === 'refunded' && $oldPaymentStatus !== 'refunded') {
            $paymentMethod = strtolower((string) ($order->payment_method ?? ''));
            $isStripePayment = in_array($paymentMethod, ['card', 'stripe'], true)
                || ! empty($details['stripe_session_id'])
                || str_starts_with((string) ($details['transaction_id'] ?? ''), 'pi_')
                || str_starts_with((string) ($details['transaction_id'] ?? ''), 'cs_')
                || str_starts_with((string) ($details['transaction_id'] ?? ''), 'ch_');

            if ($isStripePayment) {
                $paymentReference = (string) ($details['transaction_id'] ?? $details['stripe_session_id'] ?? '');
                try {
                    $refund = app(\App\Services\StripeCheckoutService::class)->createRefund($paymentReference, null, [
                        'order_id' => (string) $order->id,
                        'order_number' => (string) $order->order_number,
                    ]);
                    $details['stripe_refund_id'] = $refund->id ?? null;
                    $details['stripe_refund_status'] = $refund->status ?? null;
                    $details['refunded_at'] = now()->toIso8601String();
                    $update['payment_details'] = json_encode($details);
                } catch (\Throwable $e) {
                    return back()
                        ->withErrors(['payment_status' => 'Stripe refund failed: '.$e->getMessage()])
                        ->withInput();
                }
            } else {
                $details['refunded_at'] = $details['refunded_at'] ?? now()->toIso8601String();
                $details['refund_note'] = $details['refund_note'] ?? 'Manual refund recorded by admin.';
                $update['payment_details'] = json_encode($details);
            }
        }

        // A paid order should not stay Pending. COD is different: payment is
        // collected on delivery, so marking it Paid means it is Delivered.
        // Never reopen a cancelled order or override an explicit cancellation.
        if ($paidOrder && $oldStatus !== 'cancelled') {
            $statusAfterInput = $update['status'] ?? $oldStatus;
            $isCod = strtolower((string) ($order->payment_method ?? '')) === 'cod';

            if ($statusAfterInput !== 'cancelled') {
                if ($isCod) {
                    if ($statusAfterInput !== 'delivered') {
                        $update['status'] = 'delivered';
                    }
                } elseif ($statusAfterInput === 'pending') {
                    $update['status'] = 'processing';
                }
            }
        }

        $newStatus = $update['status'] ?? $order->status;

        DB::transaction(function () use ($order, $update, $oldStatus, $newStatus) {
            if (! empty($update)) {
                $order->update($update);
            }

            if ($newStatus !== $oldStatus) {
                $order->recordStatusChange($newStatus);
            }

            // Stock management on status transitions
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled' && $order->stock_deducted) {
                $order->restoreStock();
                $order->update(['stock_deducted' => false]);
            } elseif ($newStatus !== 'cancelled' && $oldStatus === 'cancelled' && ! $order->stock_deducted) {
                foreach ($order->items as $item) {
                    if ($item->product && (int) $item->product->stock < (int) $item->qty) {
                        throw new \RuntimeException("Not enough stock to reactivate order #{$order->order_number}.");
                    }
                }
                $order->deductStock();
                $order->update(['stock_deducted' => true]);
            }
        });

        NotificationHelper::orderStatusChanged($order, $oldStatus);

        if ($order->user_id && $order->user?->email && $newStatus !== $oldStatus) {
            try {
                Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, $oldStatus));
            } catch (Throwable $exception) {
                logger()->warning('Order status email failed: '.$exception->getMessage());
            }
        }

        $message = 'Order updated successfully.';
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $message = 'Order cancelled and stock restored.';
        }

        $finalPaymentStatus = $update['payment_status'] ?? $order->payment_status;
        $finalPaymentMethod = strtolower((string) ($order->payment_method ?? ''));
        $warning = null;
        if (in_array($newStatus, ['shipped', 'delivered'], true)
            && $finalPaymentStatus !== 'paid'
            && $finalPaymentMethod !== 'cod') {
            $warning = 'Warning: this non-COD order is '.$newStatus.' but payment is still '.$finalPaymentStatus.'. Please verify payment before fulfilment.';
        }

        $redirect = redirect()->route('admin.orders.show', $id)->with('success', $message);
        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function shipViaApi($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        try {
            $manager = app(CourierManager::class);
            $result = $manager->ship($order);

            if (!$result['success']) {
                return back()->with('error', $result['message'] ?? 'Failed to create shipment via API.');
            }

            $order->refresh();
            NotificationHelper::orderStatusChanged($order, 'pending');

            if ($order->user_id && $order->user?->email) {
                try {
                    Mail::to($order->user->email)->send(new OrderStatusChangedMail($order, 'pending'));
                } catch (\Throwable $e) {
                    // ignore mail errors
                }
            }

            return back()->with('success', 'Shipment created via API. Tracking: ' . ($result['awb'] ?? 'N/A') . ' (' . ($result['carrier'] ?? '') . ')');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function invoice($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        $invoiceData = app(InvoiceSettingsService::class)->forOrder($order);

        $pdf = Pdf::loadView('user.orders.invoice', ['order' => $order] + $invoiceData)
            ->setPaper('a4');

        return $pdf->download('invoice-'.$order->order_number.'.pdf');
    }

    public function emailCustomer($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        if ($order->user_id && $order->user?->email) {
            $toEmail = $order->user->email;
        } else {
            $billing = json_decode((string) $order->shipping_details, true) ?: [];
            $toEmail = $billing['email'] ?? null;
        }

        if (! $toEmail) {
            return back()->with('error', 'No customer email is available for this order.');
        }

        try {
            Mail::to($toEmail)->send(new OrderConfirmationMail($order->load('items.product')));
        } catch (Throwable $exception) {
            logger()->warning('Order confirmation email failed: '.$exception->getMessage());

            return back()->with('error', 'Mail delivery failed. Check the SMTP settings and try again.');
        }

        return back()->with('success', "Order details emailed to {$toEmail}.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Order::with(['user', 'items.product']);

        $statusFilter = $request->status;
        if (in_array($statusFilter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $query->where('status', $statusFilter);
        }
        $paymentStatusFilter = $request->payment_status;
        if (in_array($paymentStatusFilter, ['unpaid', 'paid', 'refunded'], true)) {
            $query->where('payment_status', $paymentStatusFilter);
        }

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order #', 'Date', 'Customer', 'Email', 'Status', 'Payment Status', 'Payment Method', 'Total', 'Shipping Fee', 'Tax', 'Coupon', 'Coupon Discount', 'Items']);

            $query->orderBy('created_at', 'desc')->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    $billing = json_decode((string) $order->shipping_details, true) ?: [];
                    $customer = $billing['name'] ?? ($order->user?->name ?? 'Guest');
                    $email = $billing['email'] ?? ($order->user?->email ?? '');
                    $items = $order->items->map(fn ($i) => ($i->product->name ?? 'Product #'.$i->product_id).' x'.$i->qty)->implode('; ');

                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at?->format('Y-m-d H:i:s'),
                        $customer,
                        $email,
                        $order->status,
                        $order->payment_status,
                        $order->payment_method,
                        $order->total_amount,
                        $order->shipping_fee,
                        $order->tax,
                        $order->coupon_code ?: '',
                        $order->coupon_discount,
                        $items,
                    ]);
                }
            });

            fclose($handle);
        };

        $filename = 'orders-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }
}
