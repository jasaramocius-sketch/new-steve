<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\EmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $rendered = null;

    public function __construct(public Order $order) {}

    protected function rendered(): ?array
    {
        if ($this->rendered !== null) {
            return $this->rendered ?: null;
        }

        $order = $this->order->loadMissing('items.product');
        $billing = json_decode($order->shipping_details, true) ?? [];
        $items = $order->items;
        $subtotal = $items->sum(fn ($i) => $i->price * $i->qty);
        $shipping = (float) $order->shipping_fee;
        $couponDiscount = (float) $order->coupon_discount;

        $rows = '';
        foreach ($items as $item) {
            $name = e($item->product->name ?? 'Product #'.$item->product_id);
            $qty = (int) $item->qty;
            $line = number_format($item->price * $item->qty, 2);
            $rows .= '<tr>'
                . '<td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;">' . $name . '</td>'
                . '<td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;">' . $qty . '</td>'
                . '<td style="padding:10px 16px;border-bottom:1px solid #f4f4f4;font-size:13px;text-align:right;">$' . $line . '</td>'
                . '</tr>';
        }
        $itemsHtml = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eee;border-radius:6px;margin-bottom:20px;">'
            . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Product</td><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;">Qty</td><td style="padding:12px 16px;border-bottom:1px solid #eee;font-size:12px;color:#666;text-align:right;">Price</td></tr>'
            . $rows
            . '<tr><td colspan="2" style="padding:10px 16px;font-size:13px;">Subtotal</td><td style="padding:10px 16px;font-size:13px;text-align:right;">$' . number_format($subtotal, 2) . '</td></tr>'
            . '<tr><td colspan="2" style="padding:10px 16px;font-size:13px;">Shipping</td><td style="padding:10px 16px;font-size:13px;text-align:right;">$' . number_format($shipping, 2) . '</td></tr>'
            . ($couponDiscount > 0 ? '<tr><td colspan="2" style="padding:10px 16px;font-size:13px;">Coupon (' . e($order->coupon_code) . ')</td><td style="padding:10px 16px;font-size:13px;text-align:right;">-$' . number_format($couponDiscount, 2) . '</td></tr>' : '')
            . '<tr><td colspan="2" style="padding:12px 16px;font-size:14px;font-weight:bold;">Total</td><td style="padding:12px 16px;font-size:14px;font-weight:bold;text-align:right;">$' . number_format($order->total_amount, 2) . '</td></tr>'
            . '</table>';

        $shippingAddress = implode(', ', array_filter([
            $billing['address'] ?? null,
            $billing['city'] ?? null,
            $billing['state'] ?? null,
            $billing['zip_code'] ?? null,
            $billing['country'] ?? null,
        ]));

        $this->rendered = EmailTemplates::render('order_confirmation', [
            'customer_name' => $billing['name'] ?? ($order->user->name ?? 'there'),
            'order_number' => $order->order_number ?? $order->id,
            'order_date' => optional($order->created_at)->format('d M, Y'),
            'order_total' => '$' . number_format((float) $order->total_amount, 2),
            'payment_method' => ucwords(str_replace('_', ' ', (string) ($order->payment_method ?? 'N/A'))),
            'shipping_address' => e($shippingAddress ?: 'N/A'),
            'order_items_html' => $itemsHtml,
            'order_url' => route('user.orders.show', $order->id),
        ]) ?: [];

        return $this->rendered ?: null;
    }

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();
        return new Envelope(
            subject: $rendered['subject'] ?? ('Order Confirmation - #' . $this->order->order_number),
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();
        if ($rendered) {
            return new Content(htmlString: $rendered['body_html']);
        }

        return new Content(
            view: 'mail.order-confirmation',
            with: ['order' => $this->order],
        );
    }
}
