<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirebaseToken extends Model
{
    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
        'device_name',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns this device token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
