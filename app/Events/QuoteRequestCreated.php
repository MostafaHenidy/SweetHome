<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $quote;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $quote)
    {
        $this->quote = $quote;
    }
}
