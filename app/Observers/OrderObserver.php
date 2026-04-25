<?php

namespace App\Observers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusChanged;
use App\Models\Order;
use App\Services\PointsService;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function created(Order $order): void
    {
        Mail::to($order->customer_email)->send(new OrderConfirmation($order));
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            Mail::to($order->customer_email)->send(
                new OrderStatusChanged($order, $order->getOriginal('status'), $order->status)
            );
        }

        if ($order->status === 'completed' && $order->getOriginal('status') !== 'completed') {
            app(PointsService::class)->awardPointsForOrder($order);
        }
    }
}
