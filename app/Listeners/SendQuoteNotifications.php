<?php

namespace App\Listeners;

use App\Events\QuoteRequestCreated;
use App\Events\QuoteStatusChanged;
use App\Models\User;
use App\Notifications\QuoteRequestNotification;
use App\Notifications\QuoteStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendQuoteNotifications implements ShouldQueue
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
     * Handle quote request created event.
     */
    public function handleQuoteRequestCreated(QuoteRequestCreated $event): void
    {
        $quote = $event->quote;

        try {
            // Notify the customer (if user exists)
            if ($quote->user_id) {
                $user = User::find($quote->user_id);
                if ($user) {
                    $user->notify(new QuoteRequestNotification($quote));
                }
            }

            // Notify customer via email (even if not a registered user)
            if ($quote->customer_email) {
                Notification::route('mail', $quote->customer_email)
                    ->notify(new QuoteRequestNotification($quote));
            }

            // Notify admins
            $adminEmails = config('app.admin_emails', []);
            foreach ($adminEmails as $adminEmail) {
                Notification::route('mail', $adminEmail)
                    ->notify(new QuoteRequestNotification($quote));
            }

            Log::info("Quote request notifications sent for quote #{$quote->order_number}");

        } catch (\Exception $e) {
            Log::error('Failed to send quote request notifications: '.$e->getMessage(), [
                'quote_id' => $quote->id,
                'quote_number' => $quote->order_number,
            ]);
        }
    }

    /**
     * Handle quote status changed event.
     */
    public function handleQuoteStatusChanged(QuoteStatusChanged $event): void
    {
        $quote = $event->quote;

        try {
            // Notify the customer (if user exists)
            if ($quote->user_id) {
                $user = User::find($quote->user_id);
                if ($user) {
                    $user->notify(new QuoteStatusChangedNotification(
                        $quote,
                        $event->oldStatus,
                        $event->newStatus
                    ));
                }
            }

            // Notify customer via email (even if not a registered user)
            if ($quote->customer_email) {
                Notification::route('mail', $quote->customer_email)
                    ->notify(new QuoteStatusChangedNotification(
                        $quote,
                        $event->oldStatus,
                        $event->newStatus
                    ));
            }

            // Notify admins about significant quote status changes
            $significantStatuses = ['quote_sent', 'accepted', 'rejected'];
            if (in_array($event->newStatus, $significantStatuses)) {
                $adminEmails = config('app.admin_emails', []);
                foreach ($adminEmails as $adminEmail) {
                    Notification::route('mail', $adminEmail)
                        ->notify(new QuoteStatusChangedNotification(
                            $quote,
                            $event->oldStatus,
                            $event->newStatus
                        ));
                }
            }

            Log::info("Quote status changed notifications sent for quote #{$quote->order_number}");

        } catch (\Exception $e) {
            Log::error('Failed to send quote status changed notifications: '.$e->getMessage(), [
                'quote_id' => $quote->id,
                'quote_number' => $quote->order_number,
            ]);
        }
    }

    /**
     * Handle the event - supports both QuoteRequestCreated and QuoteStatusChanged.
     */
    public function handle($event): void
    {
        if ($event instanceof QuoteRequestCreated) {
            $this->handleQuoteRequestCreated($event);
        } elseif ($event instanceof QuoteStatusChanged) {
            $this->handleQuoteStatusChanged($event);
        }
    }
}
