<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_order_created',
        'email_order_status_changed',
        'email_quote_created',
        'email_quote_status_changed',
        'firebase_order_created',
        'firebase_order_status_changed',
        'firebase_quote_created',
        'firebase_quote_status_changed',
        'database_order_created',
        'database_order_status_changed',
        'database_quote_created',
        'database_quote_status_changed',
    ];

    protected $casts = [
        'email_order_created' => 'boolean',
        'email_order_status_changed' => 'boolean',
        'email_quote_created' => 'boolean',
        'email_quote_status_changed' => 'boolean',
        'firebase_order_created' => 'boolean',
        'firebase_order_status_changed' => 'boolean',
        'firebase_quote_created' => 'boolean',
        'firebase_quote_status_changed' => 'boolean',
        'database_order_created' => 'boolean',
        'database_order_status_changed' => 'boolean',
        'database_quote_created' => 'boolean',
        'database_quote_status_changed' => 'boolean',
    ];

    /**
     * Get the user that owns these preferences.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
