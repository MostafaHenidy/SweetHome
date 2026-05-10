<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Firebase settings for push notifications.
    | We're using HTTP client to interact with Firebase Cloud Messaging API
    | to avoid dependency conflicts with other packages.
    |
    */

    'fcm' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
        'sender_id' => env('FIREBASE_SENDER_ID'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | If you're using Firebase Realtime Database or Firestore, configure the URL here.
    |
    */

    'database_url' => env('FIREBASE_DATABASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for Firebase notifications.
    |
    */

    'notification' => [
        'sound' => 'default',
        'priority' => 'high',
        'time_to_live' => 86400, // 24 hours
    ],
];
