<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the rate limits for various actions in your application.
    | These limits are used by Laravel's built-in rate limiting system.
    |
    */

    'login' => [
        'attempts' => 5,
        'decay' => 60 * 15, // 15 minutes in seconds
    ],

    'password_reset' => [
        'attempts' => 3,
        'decay' => 60 * 60, // 1 hour in seconds
    ],

    'registration' => [
        'attempts' => 3,
        'decay' => 60 * 60, // 1 hour in seconds
    ],

    'api' => [
        'attempts' => 60,
        'decay' => 60, // 1 minute in seconds
    ],
];
