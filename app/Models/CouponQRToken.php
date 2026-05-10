<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponQRToken extends Model
{
    use HasFactory;

    protected $table = 'coupon_qr_tokens';

    protected $fillable = [
        'coupon_id',
        'token',
        'user_id',
        'redeemed_at',
        'expires_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the coupon that this token belongs to.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who redeemed this token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is valid and can be redeemed.
     */
    public function isValid(): bool
    {
        // Token must not be expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Token must not be already redeemed
        if ($this->redeemed_at !== null) {
            return false;
        }

        // Coupon must be active and valid
        if (! $this->coupon || $this->coupon->status !== 'active') {
            return false;
        }

        return true;
    }

    /**
     * Check if the token has been redeemed.
     */
    public function isRedeemed(): bool
    {
        return $this->redeemed_at !== null;
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope for valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('redeemed_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for redeemed tokens.
     */
    public function scopeRedeemed($query)
    {
        return $query->whereNotNull('redeemed_at');
    }

    /**
     * Scope for expired tokens.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }
}
