<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseChannel
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        // Check if the notification has a toFirebase method
        if (! method_exists($notification, 'toFirebase')) {
            return;
        }

        // Get Firebase notification data
        $firebaseNotification = $notification->toFirebase($notifiable);

        // Get device tokens for the notifiable
        $deviceTokens = $this->getDeviceTokens($notifiable);

        if (empty($deviceTokens)) {
            Log::info('No device tokens found for notifiable', [
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ]);

            return;
        }

        // Send to single device or multiple devices
        if (count($deviceTokens) === 1) {
            $result = $this->firebaseService->sendToDevice(
                $deviceTokens[0],
                $firebaseNotification['title'],
                $firebaseNotification['body'],
                $firebaseNotification['data'] ?? []
            );
        } else {
            $result = $this->firebaseService->sendToMultipleDevices(
                $deviceTokens,
                $firebaseNotification['title'],
                $firebaseNotification['body'],
                $firebaseNotification['data'] ?? []
            );
        }

        // Log the result
        if (! $result['success']) {
            Log::warning('Failed to send Firebase notification', [
                'error' => $result['message'] ?? 'Unknown error',
                'notifiable_type' => get_class($notifiable),
                'notifiable_id' => $notifiable->id ?? null,
            ]);
        }
    }

    /**
     * Get device tokens for the notifiable.
     *
     * @return array<string>
     */
    protected function getDeviceTokens($notifiable): array
    {
        // If notifiable is a User model, get their device tokens
        if ($notifiable instanceof User) {
            return $notifiable->firebaseTokens()
                ->where('last_used_at', '>', now()->subDays(30))
                ->pluck('device_token')
                ->toArray();
        }

        // If notifiable has a routeNotificationForFirebase method
        if (method_exists($notifiable, 'routeNotificationForFirebase')) {
            $tokens = $notifiable->routeNotificationForFirebase();

            return is_array($tokens) ? $tokens : [$tokens];
        }

        // If notifiable has an email property, check for admin notifications
        if (isset($notifiable->email)) {
            $adminEmails = config('app.admin_emails', []);
            if (in_array($notifiable->email, $adminEmails)) {
                // For admin email notifications, we could use a topic or specific admin tokens
                // For now, return empty - admin notifications via email work differently
                return [];
            }
        }

        return [];
    }
}
