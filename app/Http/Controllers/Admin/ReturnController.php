<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TemplatedMail;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user', 'product']);

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected', 'refunded'], true)) {
            $query->where('status', $request->status);
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('backend.returns.index', compact('returns'));
    }

    public function show($id)
    {
        $return = ReturnRequest::with(['order', 'user', 'product'])->findOrFail($id);

        return view('backend.returns.show', compact('return'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,refunded',
            'refund_amount' => 'nullable|numeric|min:0',
            'admin_note' => 'nullable|string|max:5000',
        ], [], [
            'refund_amount' => 'refund amount',
        ]);

        $return = ReturnRequest::with(['product', 'order'])->findOrFail($id);
        $oldStatus = $return->status;

        $paidAmount = (float) ($return->order->total_amount ?? 0);
        if ($request->status !== 'rejected' && $request->filled('refund_amount')) {
            $requestedRefund = (float) $request->refund_amount;
            if ($requestedRefund - $paidAmount > 0.0001) {
                return back()
                    ->withErrors(['refund_amount' => 'Refund amount cannot exceed the amount paid ('.currency_format($paidAmount).').'])
                    ->withInput();
            }
        }

        $update = [
            'status' => $request->status,
            'admin_note' => $request->input('admin_note') ?: null,
        ];

        // Rejected return → no refund happens, so refund amount must be cleared.
        // Approved/Refunded → refund amount may be set (defaulted to order total in UI).
        if ($request->status === 'rejected') {
            $update['refund_amount'] = null;
        } elseif ($request->filled('refund_amount')) {
            $update['refund_amount'] = $request->refund_amount;
        } elseif ($request->status === 'refunded') {
            $update['refund_amount'] = $return->order->total_amount ?? $return->refund_amount;
        }

        if ($request->status === 'refunded' && $oldStatus !== 'refunded') {
            $order = $return->order;
            if ($order) {
                $rawDetails = $order->payment_details;
                $details = is_array($rawDetails) ? $rawDetails : (is_string($rawDetails) ? json_decode($rawDetails, true) : []);
                $details = is_array($details) ? $details : [];
                $paymentMethod = strtolower((string) ($order->payment_method ?? ''));
                $isStripePayment = in_array($paymentMethod, ['card', 'stripe'], true)
                    || ! empty($details['stripe_session_id'])
                    || str_starts_with((string) ($details['transaction_id'] ?? ''), 'pi_')
                    || str_starts_with((string) ($details['transaction_id'] ?? ''), 'cs_')
                    || str_starts_with((string) ($details['transaction_id'] ?? ''), 'ch_');

                $refundAmount = $request->filled('refund_amount')
                    ? (float) $request->refund_amount
                    : (float) ($order->total_amount ?? 0);

                if ($isStripePayment) {
                    $paymentReference = (string) ($details['transaction_id'] ?? $details['stripe_session_id'] ?? '');
                    $amountMinor = null;
                    if ($refundAmount > 0) {
                        $stripe = app(\App\Services\StripeCheckoutService::class);
                        $amountMinor = $stripe->toMinorUnits($refundAmount);
                    }

                    try {
                        $stripe = $stripe ?? app(\App\Services\StripeCheckoutService::class);
                        $refund = $stripe->createRefund($paymentReference, $amountMinor, [
                            'order_id' => (string) $order->id,
                            'order_number' => (string) $order->order_number,
                            'return_request_id' => (string) $return->id,
                        ]);
                        $details['stripe_refund_id'] = $refund->id ?? ($details['stripe_refund_id'] ?? null);
                        $details['stripe_refund_status'] = $refund->status ?? null;
                        $details['refund_amount'] = $refundAmount;
                        $details['refund_return_request_id'] = $return->id;
                        $details['refunded_at'] = now()->toIso8601String();
                        $order->update([
                            'payment_status' => 'refunded',
                            'payment_details' => json_encode($details),
                        ]);
                    } catch (\Throwable $e) {
                        return back()->withErrors(['status' => 'Stripe refund failed: '.$e->getMessage()])->withInput();
                    }
                } else {
                    $details['refund_amount'] = $refundAmount;
                    $details['refund_return_request_id'] = $return->id;
                    $details['refunded_at'] = now()->toIso8601String();
                    $order->update([
                        'payment_status' => 'refunded',
                        'payment_details' => json_encode($details),
                    ]);
                }
            }
        }

        DB::transaction(function () use ($return, $update, $oldStatus, $request) {
            $return->update($update);

            // Restore stock when the product is returned to us (refunded).
            if ($request->status === 'refunded' && $oldStatus !== 'refunded' && $return->product_id) {
                $return->product?->increment('vendor_stock', $return->qty);
            }
        });

        $return->loadMissing(['user', 'order']);
        $order = $return->order;
        $orderEmail = $return->user?->email ?: ($order?->user?->email);
        $billing = is_array($order?->shipping_details) ? $order->shipping_details : (is_string($order?->shipping_details) ? json_decode($order->shipping_details, true) : []);
        $billing = is_array($billing) ? $billing : [];
        $customerName = $billing['name'] ?? ($return->user->name ?? $order?->user?->name ?? 'there');
        $orderNumber = $order->order_number ?? $order?->id ?? 'N/A';
        $orderUrl = $order ? route('user.orders.show', $order->id) : url('/');

        if ($orderEmail && $request->status !== $oldStatus) {
            try {
                Mail::to($orderEmail)->send(new TemplatedMail('return_status', [
                    'customer_name' => $customerName,
                    'order_number' => $orderNumber,
                    'return_status' => ucwords(str_replace('_', ' ', $request->status)),
                    'return_reason' => (string) ($return->reason ?? 'N/A'),
                    'admin_note' => (string) ($return->admin_note ?? ''),
                    'admin_note_block' => $return->admin_note ? '<p style="font-size:14px;line-height:1.6;margin:0 0 20px;"><strong>Admin note:</strong><br>' . nl2br(e($return->admin_note)) . '</p>' : '',
                    'order_url' => $orderUrl,
                ], 'Return Request Update - #' . $orderNumber));
            } catch (Throwable $e) {
                logger()->warning('Return status email failed: ' . $e->getMessage());
            }
        }

        if ($orderEmail && $request->status === 'refunded' && $oldStatus !== 'refunded') {
            $details = is_array($order?->payment_details) ? $order->payment_details : (is_string($order?->payment_details) ? json_decode($order->payment_details, true) : []);
            $details = is_array($details) ? $details : [];
            try {
                Mail::to($orderEmail)->send(new TemplatedMail('refund_processed', [
                    'customer_name' => $customerName,
                    'order_number' => $orderNumber,
                    'refund_amount' => '$' . number_format((float) ($details['refund_amount'] ?? $return->refund_amount ?? $order?->total_amount ?? 0), 2),
                    'refund_method' => ucwords(str_replace('_', ' ', (string) ($order?->payment_method ?? 'N/A'))),
                    'refund_reference' => (string) ($details['stripe_refund_id'] ?? $details['transaction_id'] ?? 'N/A'),
                    'order_url' => $orderUrl,
                ], 'Refund Processed - #' . $orderNumber));
            } catch (Throwable $e) {
                logger()->warning('Refund email failed: ' . $e->getMessage());
            }
        }

        $message = 'Return request updated.';
        if ($request->status === 'refunded') {
            $message = 'Return approved and refunded. Stock restored.';
        }

        return redirect()->route('admin.returns.show', $return->id)->with('success', $message);
    }
}
