<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstallmentPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_installments',
        'min_order_amount',
        'interest_rate',
        'is_active',
        'applicable_categories',
        'excluded_products',
        'sort_order',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'applicable_categories' => 'array',
        'excluded_products' => 'array',
    ];

    /**
     * Get active policies ordered by sort order
     */
    public static function getActivePolicies(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_order_amount')
            ->get();
    }

    /**
     * Get the best applicable policy for an order amount
     */
    public static function getApplicablePolicy(float $orderAmount, array $categoryIds = [], array $productIds = []): ?self
    {
        $policies = static::getActivePolicies();

        foreach ($policies as $policy) {
            if ($policy->appliesTo($orderAmount, $categoryIds, $productIds)) {
                return $policy;
            }
        }

        return null;
    }

    /**
     * Check if this policy applies to the given order
     */
    public function appliesTo(float $orderAmount, array $categoryIds = [], array $productIds = []): bool
    {
        // Check minimum order amount
        if ($orderAmount < (float) $this->min_order_amount) {
            return false;
        }

        // Check if any products are excluded
        if (!empty($this->excluded_products) && !empty($productIds)) {
            $excluded = array_intersect($productIds, $this->excluded_products);
            if (!empty($excluded)) {
                return false;
            }
        }

        // Check if policy applies to specific categories
        if (!empty($this->applicable_categories)) {
            if (empty($categoryIds)) {
                return false;
            }
            $matchingCategories = array_intersect($categoryIds, $this->applicable_categories);
            if (empty($matchingCategories)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate installment details for an order
     */
    public function calculateInstallments(float $orderAmount, int $numberOfInstallments = null): array
    {
        $maxInstallments = $this->max_installments;

        // If specific number of installments requested, validate it
        if ($numberOfInstallments !== null) {
            if ($numberOfInstallments > $maxInstallments) {
                $numberOfInstallments = $maxInstallments;
            }
            if ($numberOfInstallments < 1) {
                $numberOfInstallments = 1;
            }
        } else {
            $numberOfInstallments = min(3, $maxInstallments); // Default to 3 installments
        }

        // Calculate installment amount
        $interestRate = (float) $this->interest_rate;
        $totalWithInterest = $orderAmount * (1 + ($interestRate / 100));
        $installmentAmount = $totalWithInterest / $numberOfInstallments;

        return [
            'policy_id' => $this->id,
            'policy_name' => $this->name,
            'order_amount' => $orderAmount,
            'total_with_interest' => $totalWithInterest,
            'installment_count' => $numberOfInstallments,
            'installment_amount' => $installmentAmount,
            'total_interest' => $totalWithInterest - $orderAmount,
            'interest_rate' => $interestRate,
        ];
    }

    /**
     * Get applicable policies for a user based on their debt status
     */
    public static function getPoliciesForUser(User $user, float $orderAmount): \Illuminate\Database\Eloquent\Collection
    {
        $userDebt = CustomerDebt::forUser($user);
        $debtUtilization = $userDebt->getDebtUtilization();

        $policies = static::getActivePolicies();

        // Filter policies based on user's debt status
        return $policies->filter(function ($policy) use ($orderAmount, $debtUtilization, $user) {
            // Check if order amount meets minimum
            if ($orderAmount < (float) $policy->min_order_amount) {
                return false;
            }

            // Apply stricter policies for users with high debt utilization
            if ($debtUtilization > 80) {
                // Users with high debt get fewer installment options
                return $policy->max_installments <= 3;
            } elseif ($debtUtilization > 50) {
                // Users with medium debt get moderate installment options
                return $policy->max_installments <= 6;
            }

            return true;
        })->sortBy('sort_order');
    }

    /**
     * Validate if installments are enabled globally
     */
    public static function areInstallmentsEnabled(): bool
    {
        $settings = PaymentMethodConfig::getInstance();
        return $settings->enable_installments;
    }

    /**
     * Get the global maximum installments from settings
     */
    public static function getGlobalMaxInstallments(): int
    {
        $settings = PaymentMethodConfig::getInstance();
        return (int) $settings->max_installments;
    }

    /**
     * Get the global minimum installment amount from settings
     */
    public static function getGlobalMinInstallmentAmount(): float
    {
        $settings = PaymentMethodConfig::getInstance();
        return (float) $settings->min_installment_amount;
    }

    /**
     * Check if order qualifies for installments
     */
    public static function qualifiesForInstallments(float $orderAmount, User $user = null): bool
    {
        if (!self::areInstallmentsEnabled()) {
            return false;
        }

        if ($orderAmount < self::getGlobalMinInstallmentAmount()) {
            return false;
        }

        // Check user debt limits if user provided
        if ($user) {
            $userDebt = CustomerDebt::forUser($user);
            if (!$userDebt->canPlaceOrder($orderAmount)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the best installment plan for an order
     */
    public static function getBestInstallmentPlan(float $orderAmount, User $user = null, int $preferredInstallments = null): ?array
    {
        if (!self::qualifiesForInstallments($orderAmount, $user)) {
            return null;
        }

        // Get user's categories and products (if applicable)
        $categoryIds = [];
        $productIds = [];

        // Find applicable policy
        $policy = static::getApplicablePolicy($orderAmount, $categoryIds, $productIds);

        if (!$policy) {
            // Use global settings as fallback
            $policy = new static([
                'name' => 'Global Policy',
                'max_installments' => self::getGlobalMaxInstallments(),
                'min_order_amount' => self::getGlobalMinInstallmentAmount(),
                'interest_rate' => (float) PaymentMethodConfig::getInstance()->installment_interest_rate,
            ]);
        }

        return $policy->calculateInstallments($orderAmount, $preferredInstallments);
    }

    /**
     * Create a standard policy quickly
     */
    public static function createStandardPolicy(
        string $name,
        int $maxInstallments,
        float $minOrderAmount,
        float $interestRate = 0.0,
        string $description = null
    ): self {
        return static::create([
            'name' => $name,
            'description' => $description ?? "Standard {$name} policy",
            'max_installments' => $maxInstallments,
            'min_order_amount' => $minOrderAmount,
            'interest_rate' => $interestRate,
            'is_active' => true,
        ]);
    }

    /**
     * Scope for active policies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for policies applicable to order amount
     */
    public function scopeForOrderAmount($query, float $amount)
    {
        return $query->where('min_order_amount', '<=', $amount);
    }
}