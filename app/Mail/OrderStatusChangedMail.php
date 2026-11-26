<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\EmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $rendered = null;

    public function __construct(public Order $order, public ?string $oldStatus = null) {}

    protected function templateKey(): ?string
    {
        return match ((string) $this->order->status) {
            'shipped' => 'order_shipped',
            'delivered' => 'order_delivered',
            default => null,
        };
    }

    protected function rendered(): ?array
    {
        if ($this->rendered !== null) {
            return $this->rendered ?: null;
        }

        $key = $this->templateKey();
        if (!$key) {
            $this->rendered = [];
            return null;
        }

        $order = $this->order;
        $billing = json_decode($order->shipping_details, true) ?? [];
        $trackingNumber = (string) ($order->tracking_number ?? '');
        $trackingCarrier = (string) ($order->tracking_carrier ?? '');
        $trackingUrl = (string) ($order->tracking_url ?? '');
        $trackingUrlBlock = $trackingUrl !== ''
            ? '<p style="font-size:14px;line-height:1.6;margin:0 0 20px;"><a href="' . e($trackingUrl) . '" style="color:#1d3557;">Track your shipment</a></p>'
            : '';

        $this->rendered = EmailTemplates::render($key, [
            'customer_name' => $billing['name'] ?? ($order->user->name ?? 'there'),
            'order_number' => $order->order_number ?? $order->id,
            'tracking_number' => $trackingNumber ?: 'N/A',
            'tracking_carrier' => $trackingCarrier ?: 'N/A',
            'tracking_url' => $trackingUrl,
            'tracking_url_block' => $trackingUrlBlock,
            'order_url' => route('user.orders.show', $order->id),
        ]) ?: [];

        return $this->rendered ?: null;
    }

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();
        $statusLabels = [
            'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped',
            'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
        ];
        $label = $statusLabels[$this->order->status] ?? ucfirst((string) $this->order->status);

        return new Envelope(
            subject: $rendered['subject'] ?? ('Order Status Update - #' . $this->order->order_number . ' (' . $label . ')'),
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();
        if ($rendered) {
            return new Content(htmlString: $rendered['body_html']);
        }

        return new Content(
            view: 'mail.order-status-changed',
            with: ['order' => $this->order, 'oldStatus' => $this->oldStatus],
        );
    }
}
