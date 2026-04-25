<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        $statusLabels = [
            'pending' => 'In Attesa',
            'processing' => 'In Lavorazione',
            'shipped' => 'Spedito',
            'completed' => 'Completato',
            'cancelled' => 'Annullato',
        ];

        return new Envelope(
            subject: 'SweetHome Milano — Ordine #' . $this->order->order_number . ' ' . ($statusLabels[$this->newStatus] ?? $this->newStatus),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-changed',
        );
    }
}
