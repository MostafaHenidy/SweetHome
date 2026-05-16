<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_debt',
        'debt_limit',
        'last_review_date',
        'notes',
    ];

    protected $casts = [
        'current_debt' => 'decimal:2',
        'debt_limit' => 'decimal:2',
        'last_review_date' => 'date',
    ];

    /**
     * Get the user associated with this debt record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the effective debt limit for this customer
     * Uses custom limit if set, otherwise falls back to global limit
     */
    public function getEffectiveDebtLimit(): float
    {
        if ($this->debt_limit !== null) {
            return (float) $this->debt_limit;
        }

        // Get global debt limit from settings
        $settings = PaymentMethodConfig::getInstance();
        return (float) $settings->global_debt_limit;
    }

    /**
     * Calculate remaining available credit
     */
    public function getRemainingCredit(): float
    {
        return $this->getEffectiveDebtLimit() - (float) $this->current_debt;
    }

    /**
     * Check if customer can place an order of specified amount
     */
    public function canPlaceOrder(float $orderAmount): bool
    {
        $settings = PaymentMethodConfig::getInstance();

        // If debt limits are disabled, always allow
        if (!$settings->enable_debt_limits) {
            return true;
        }

        return $orderAmount <= $this->getRemainingCredit();
    }

    /**
     * Add debt to customer account
     */
    public function addDebt(float $amount, string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $this->current_debt = (float) $this->current_debt + $amount;
        $this->last_review_date = now();

        if ($description) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') .
                          now()->format('Y-m-d H:i') . ': +' . number_format($amount, 2) . ' - ' . $description;
        }

        return $this->save();
    }

    /**
     * Remove debt from customer account (payment received)
     */
    public function removeDebt(float $amount, string $description = null): bool
    {
        if ($amount <= 0) {
            return false;
        }

        $newDebt = (float) $this->current_debt - $amount;

        // Don't allow negative debt (overpayment should be handled separately)
        if ($newDebt < 0) {
            $newDebt = 0;
        }

        $this->current_debt = $newDebt;
        $this->last_review_date = now();

        if ($description) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') .
                          now()->format('Y-m-d H:i') . ': -' . number_format($amount, 2) . ' - ' . $description;
        }

        return $this->save();
    }

    /**
     * Update customer-specific debt limit
     */
    public function setCustomDebtLimit(float $limit): bool
    {
        if ($limit <= 0) {
            return false;
        }

        $this->debt_limit = $limit;
        return $this->save();
    }

    /**
     * Reset to use global debt limit
     */
    public function resetToGlobalLimit(): bool
    {
        $this->debt_limit = null;
        return $this->save();
    }

    /**
     * Check if customer is over their debt limit
     */
    public function isOverDebtLimit(): bool
    {
        $settings = PaymentMethodConfig::getInstance();

        // If debt limits are disabled, never over limit
        if (!$settings->enable_debt_limits) {
            return false;
        }

        return (float) $this->current_debt > $this->getEffectiveDebtLimit();
    }

    /**
     * Get debt utilization percentage
     */
    public function getDebtUtilization(): float
    {
        $limit = $this->getEffectiveDebtLimit();
        if ($limit <= 0) {
            return 0;
        }

        return min(100, ((float) $this->current_debt / $limit) * 100);
    }

    /**
     * Get debt status (low, medium, high, over)
     */
    public function getDebtStatus(): string
    {
        $utilization = $this->getDebtUtilization();

        if ($utilization >= 100) {
            return 'over';
        } elseif ($utilization >= 80) {
            return 'high';
        } elseif ($utilization >= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Create or get debt record for a user
     */
    public static function forUser(User $user): self
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            [
                'current_debt' => 0.00,
                'last_review_date' => now(),
            ]
        );
    }

    /**
     * Get customers with highest debt
     */
    public static function getHighestDebt(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('user')
            ->orderBy('current_debt', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get customers approaching debt limit
     */
    public static function getCustomersNearLimit(float $thresholdPercentage = 80): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('user')
            ->whereHas('user', function ($query) {
                $query->where('override_global_debt_limit', false);
            })
            ->get()
            ->filter(function ($debt) use ($thresholdPercentage) {
                return $debt->getDebtUtilization() >= $thresholdPercentage;
            });
    }

    /**
     * Reset debt for a user (use with caution)
     */
    public static function resetUserDebt(User $user): bool
    {
        $debt = static::forUser($user);
        $debt->current_debt = 0.00;
        $debt->notes = ($debt->notes ? $debt->notes . "\n" : '') .
                      now()->format('Y-m-d H:i') . ': Debt reset to 0.00';
        return $debt->save();
    }
}