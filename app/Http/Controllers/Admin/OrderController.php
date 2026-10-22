<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusChangedMail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return view('admin.orders.index', compact('orders', 'sortBy', 'sortDir', 'statusFilter', 'paymentStatusFilter'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'transaction_id' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_carrier' => 'nullable|string|max:100',
        ]);

        $order = Order::with('items.product')->findOrFail($id);
        $oldStatus = $order->status;

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
            Mail::to($order->user->email)->queue(new OrderStatusChangedMail($order, $oldStatus));
        }

        $message = 'Order updated successfully.';
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $message = 'Order cancelled and stock restored.';
        }

        return redirect()->route('admin.orders.show', $id)->with('success', $message);
    }

    public function invoice($id)
    {
        $order = Order::with('items.product')->findOrFail($id);

        $pdf = Pdf::loadView('user.orders.invoice', compact('order'))
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

        Mail::to($toEmail)->queue(new OrderConfirmationMail($order->load('items.product')));

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
