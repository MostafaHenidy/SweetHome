<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendOrderNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle order created event.
     */
    public function handleOrderCreated(OrderCreated $event): void
    {
        $order = $event->order;

        try {
            // Notify the customer (if user exists)
            if ($order->user_id) {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->notify(new OrderCreatedNotification($order));
                }
            }

            // Notify admins
            $adminEmails = config('app.admin_emails', []);
            foreach ($adminEmails as $adminEmail) {
                Notification::route('mail', $adminEmail)
                    ->notify(new OrderCreatedNotification($order));
            }

            Log::info("Order created notifications sent for order #{$order->order_number}");

        } catch (\Exception $e) {
            Log::error('Failed to send order created notifications: '.$e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        }
    }

    /**
     * Handle order status changed event.
     */
    public function handleOrderStatusChanged(OrderStatusChanged $event): void
    {
        $order = $event->order;

        try {
            // Notify the customer (if user exists)
            if ($order->user_id) {
                $user = User::find($order->user_id);
                if ($user) {
                    $user->notify(new OrderStatusChangedNotification(
                        $order,
                        $event->oldStatus,
                        $event->newStatus
                    ));
                }
            }

            // Notify admins about significant status changes
            $significantStatuses = ['shipped', 'completed', 'cancelled'];
            if (in_array($event->newStatus, $significantStatuses)) {
                $adminEmails = config('app.admin_emails', []);
                foreach ($adminEmails as $adminEmail) {
                    Notification::route('mail', $adminEmail)
                        ->notify(new OrderStatusChangedNotification(
                            $order,
                            $event->oldStatus,
                            $event->newStatus
                        ));
                }
            }

            Log::info("Order status changed notifications sent for order #{$order->order_number}");

        } catch (\Exception $e) {
            Log::error('Failed to send order status changed notifications: '.$e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        }
    }

    /**
     * Handle the event - supports both OrderCreated and OrderStatusChanged.
     */
    public function handle($event): void
    {
        if ($event instanceof OrderCreated) {
            $this->handleOrderCreated($event);
        } elseif ($event instanceof OrderStatusChanged) {
            $this->handleOrderStatusChanged($event);
        }
    }
}
