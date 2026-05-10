<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected string $serverKey;

    protected string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('firebase.fcm.server_key');
    }

    /**
     * Send push notification to a single device
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = []): array
    {
        if (! $this->serverKey) {
            Log::warning('Firebase server key not configured');

            return ['success' => false, 'message' => 'Firebase server key not configured'];
        }

        $payload = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => config('firebase.notification.sound', 'default'),
            ],
            'data' => $data,
            'priority' => config('firebase.notification.priority', 'high'),
            'time_to_live' => config('firebase.notification.time_to_live', 86400),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.$this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'FCM request failed',
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Firebase notification error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send push notification to multiple devices
     */
    public function sendToMultipleDevices(array $deviceTokens, string $title, string $body, array $data = []): array
    {
        if (! $this->serverKey) {
            Log::warning('Firebase server key not configured');

            return ['success' => false, 'message' => 'Firebase server key not configured'];
        }

        $payload = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => config('firebase.notification.sound', 'default'),
            ],
            'data' => $data,
            'priority' => config('firebase.notification.priority', 'high'),
            'time_to_live' => config('firebase.notification.time_to_live', 86400),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.$this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'FCM request failed',
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Firebase notification error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification to topic
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): array
    {
        if (! $this->serverKey) {
            Log::warning('Firebase server key not configured');

            return ['success' => false, 'message' => 'Firebase server key not configured'];
        }

        $payload = [
            'to' => '/topics/'.$topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => config('firebase.notification.sound', 'default'),
            ],
            'data' => $data,
            'priority' => config('firebase.notification.priority', 'high'),
            'time_to_live' => config('firebase.notification.time_to_live', 86400),
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key='.$this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'response' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'FCM request failed',
                'response' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Firebase topic notification error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get admin topic name
     */
    public function getAdminTopic(): string
    {
        return 'admin';
    }

    /**
     * Get user specific topic name
     */
    public function getUserTopic(int $userId): string
    {
        return 'user_'.$userId;
    }
}
