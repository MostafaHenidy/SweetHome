<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\PointsService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'province',
        'postal_code',
        'country',
        'points_balance',
        'is_vip',
        'vip_type',
        'vip_assigned_at',
        'vip_assigned_by',
        'role',
        'status',
        'custom_debt_limit',
        'override_global_debt_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points_balance' => 'decimal:2',
            'is_vip' => 'boolean',
            'vip_assigned_at' => 'datetime',
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the debt record for this user
     */
    public function debt(): HasOne
    {
        return $this->hasOne(CustomerDebt::class);
    }

    /**
     * Get the user's current debt amount
     */
    public function getCurrentDebtAttribute(): float
    {
        return $this->debt?->current_debt ?? 0.00;
    }

    /**
     * Get the user's debt limit (custom or global)
     */
    public function getDebtLimitAttribute(): float
    {
        if ($this->override_global_debt_limit && $this->custom_debt_limit !== null) {
            return (float) $this->custom_debt_limit;
        }

        if ($this->debt) {
            return $this->debt->getEffectiveDebtLimit();
        }

        // Fall back to global setting
        $settings = \App\Models\PaymentMethodConfig::getInstance();
        return (float) $settings->global_debt_limit;
    }

    /**
     * Get remaining credit available
     */
    public function getRemainingCreditAttribute(): float
    {
        return $this->debtLimit - $this->currentDebt;
    }

    /**
     * Check if user can place an order of specified amount
     */
    public function canPlaceOrder(float $orderAmount): bool
    {
        if (!$this->debt) {
            return true;
        }

        return $this->debt->canPlaceOrder($orderAmount);
    }

    /**
     * Get debt utilization percentage
     */
    public function getDebtUtilizationAttribute(): float
    {
        if (!$this->debt) {
            return 0;
        }

        return $this->debt->getDebtUtilization();
    }
    

    public function pointsTransactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class);
    }

    public function couponUsages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function notificationPreferences(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function firebaseTokens(): HasMany
    {
        return $this->hasMany(FirebaseToken::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(UserInquiry::class);
    }

    public function vipAssignments(): HasMany
    {
        return $this->hasMany(VipAssignment::class, 'user_id');
    }

    public function vipAssigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vip_assigned_by');
    }

    public function getPointsValueInEurosAttribute(): float
    {
        return app(PointsService::class)->convertPointsToEuros($this->points_balance ?? 0);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return isset($this->role) && $this->role === 'admin';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return ! isset($this->role) || $this->role === 'customer';
    }

    /**
     * Check if user has specific status
     */
    public function hasStatus(string $status): bool
    {
        return isset($this->status) && $this->status === $status;
    }

    /**
     * Get user's display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? 'User';
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->province,
            $this->postal_code,
            $this->country,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * VIP Status Methods
     */
    public function isVip(): bool
    {
        return (bool) $this->is_vip;
    }

    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    public function scopeNonVip($query)
    {
        return $query->where('is_vip', false);
    }

    public function becomeVip(string $type = 'manual', ?User $assignedBy = null): bool
    {
        $this->is_vip = true;
        $this->vip_type = $type;
        $this->vip_assigned_at = now();
        $this->vip_assigned_by = $assignedBy?->id;

        return $this->save();
    }

    public function loseVipStatus(): bool
    {
        $this->is_vip = false;
        $this->vip_type = null;
        $this->vip_assigned_at = null;
        $this->vip_assigned_by = null;

        return $this->save();
    }

    /**
     * Calculate lifetime spend for VIP qualification
     */
    public function calculateLifetimeSpend(int $days = null): float
    {
        $query = $this->orders()
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled');

        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        return (float) $query->sum('total');
    }
}
