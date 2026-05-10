<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $quote;

    public string $oldStatus;

    public string $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $quote, string $oldStatus, string $newStatus)
    {
        $this->quote = $quote;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}
