<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public ?string $oldStatus = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Status Update - #'.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order-status-changed',
            with: ['order' => $this->order, 'oldStatus' => $this->oldStatus],
        );
    }
}
