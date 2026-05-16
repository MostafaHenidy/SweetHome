<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentMethodConfig extends Model
{
    use HasFactory;

    protected $table = 'settings'; // Use the settings table

    protected $fillable = [
        'payment_fawaterak_enabled',
        'payment_stripe_enabled',
        'payment_paypal_enabled',
        'payment_bank_transfer_enabled',
        'payment_cash_on_delivery_enabled',
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_webhook_secret',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_mode',
        'fawaterak_api_key',
        'fawaterak_secret_key',
        'fawaterak_base_url',
        'global_debt_limit',
        'enable_debt_limits',
        'debt_review_period_days',
        'max_installments',
        'min_installment_amount',
        'installment_interest_rate',
        'enable_installments',
        'payment_method_order',
    ];

    protected $casts = [
        'payment_fawaterak_enabled' => 'boolean',
        'payment_stripe_enabled' => 'boolean',
        'payment_paypal_enabled' => 'boolean',
        'payment_bank_transfer_enabled' => 'boolean',
        'payment_cash_on_delivery_enabled' => 'boolean',
        'enable_debt_limits' => 'boolean',
        'enable_installments' => 'boolean',
        'global_debt_limit' => 'decimal:2',
        'min_installment_amount' => 'decimal:2',
        'installment_interest_rate' => 'decimal:2',
        'payment_method_order' => 'array',
    ];

    // Cache key for payment configurations
    private const CACHE_KEY = 'payment_method_config';

    /**
     * Get the single instance of payment configuration
     */
    public static function getInstance(): self
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            // Since we're using the settings table, we need to get the first record
            // or create one if it doesn't exist
            $instance = self::first();
            if (!$instance) {
                // Create default settings
                $instance = self::create([
                    'payment_fawaterak_enabled' => true,
                    'payment_stripe_enabled' => true,
                    'payment_paypal_enabled' => true,
                    'payment_bank_transfer_enabled' => true,
                    'payment_cash_on_delivery_enabled' => true,
                    'global_debt_limit' => 10000.00,
                    'enable_debt_limits' => true,
                    'debt_review_period_days' => 30,
                    'max_installments' => 12,
                    'min_installment_amount' => 50.00,
                    'installment_interest_rate' => 0.00,
                    'enable_installments' => true,
                ]);
            }
            return $instance;
        });
    }

    /**
     * Clear the payment configuration cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get all enabled payment methods
     */
    public function getEnabledPaymentMethods(): array
    {
        $methods = [];

        if ($this->payment_fawaterak_enabled) {
            $methods[] = 'fawaterak';
        }
        if ($this->payment_stripe_enabled) {
            $methods[] = 'stripe';
        }
        if ($this->payment_paypal_enabled) {
            $methods[] = 'paypal';
        }
        if ($this->payment_bank_transfer_enabled) {
            $methods[] = 'bank_transfer';
        }
        if ($this->payment_cash_on_delivery_enabled) {
            $methods[] = 'cash_on_delivery';
        }

        return $methods;
    }

    /**
     * Check if a specific payment method is enabled
     */
    public function isMethodEnabled(string $method): bool
    {
        $method = str_replace('-', '_', $method);

        return match ($method) {
            'fawaterak' => $this->payment_fawaterak_enabled,
            'stripe' => $this->payment_stripe_enabled,
            'paypal' => $this->payment_paypal_enabled,
            'bank_transfer' => $this->payment_bank_transfer_enabled,
            'cash_on_delivery' => $this->payment_cash_on_delivery_enabled,
            default => false,
        };
    }

    /**
     * Enable a payment method
     */
    public function enableMethod(string $method): bool
    {
        $field = "payment_{$method}_enabled";
        if (in_array($field, $this->fillable)) {
            $this->$field = true;
            $result = $this->save();
            self::clearCache();
            return $result;
        }
        return false;
    }

    /**
     * Disable a payment method
     */
    public function disableMethod(string $method): bool
    {
        $field = "payment_{$method}_enabled";
        if (in_array($field, $this->fillable)) {
            $this->$field = false;
            $result = $this->save();
            self::clearCache();
            return $result;
        }
        return false;
    }

    /**
     * Get payment credentials for a specific method
     */
    public function getCredentials(string $method): array
    {
        $credentials = [];

        switch ($method) {
            case 'stripe':
                $credentials = [
                    'secret_key' => $this->stripe_secret_key,
                    'publishable_key' => $this->stripe_publishable_key,
                    'webhook_secret' => $this->stripe_webhook_secret,
                ];
                break;

            case 'paypal':
                $credentials = [
                    'client_id' => $this->paypal_client_id,
                    'client_secret' => $this->paypal_client_secret,
                    'mode' => $this->paypal_mode,
                ];
                break;

            case 'fawaterak':
                $credentials = [
                    'api_key' => $this->fawaterak_api_key,
                    'secret_key' => $this->fawaterak_secret_key,
                    'base_url' => $this->fawaterak_base_url,
                ];
                break;
        }

        // Filter out null values
        return array_filter($credentials, fn($value) => !is_null($value));
    }

    /**
     * Check if payment credentials are configured for a method
     */
    public function hasCredentials(string $method): bool
    {
        $credentials = $this->getCredentials($method);
        return !empty($credentials);
    }

    /**
     * Get the default payment method (first available enabled method)
     */
    public function getDefaultPaymentMethod(): ?string
    {
        $enabledMethods = $this->getEnabledPaymentMethods();

        // If no methods are enabled, return cash on delivery as fallback
        if (empty($enabledMethods)) {
            return 'cash_on_delivery';
        }

        return $enabledMethods[0] ?? null;
    }

    /**
     * Get payment method order for display
     */
    public function getPaymentMethodOrder(): array
    {
        if ($this->payment_method_order) {
            return $this->payment_method_order;
        }

        // Default order
        return ['fawaterak', 'stripe', 'paypal', 'bank_transfer', 'cash_on_delivery'];
    }

    /**
     * Set payment method order
     */
    public function setPaymentMethodOrder(array $order): bool
    {
        $this->payment_method_order = $order;
        $result = $this->save();
        self::clearCache();
        return $result;
    }

    /**
     * Validate payment settings
     */
    public function validateSettings(): array
    {
        $errors = [];

        // Check if at least one payment method is enabled
        if (empty($this->getEnabledPaymentMethods())) {
            $errors[] = 'At least one payment method must be enabled';
        }

        // Validate debt limits
        if ($this->global_debt_limit <= 0) {
            $errors[] = 'Global debt limit must be greater than 0';
        }

        // Validate installment settings
        if ($this->max_installments <= 0) {
            $errors[] = 'Max installments must be greater than 0';
        }

        if ($this->min_installment_amount <= 0) {
            $errors[] = 'Minimum installment amount must be greater than 0';
        }

        return $errors;
    }

    /**
     * Save with cache clearing
     */
    public function save(array $options = []): bool
    {
        $result = parent::save($options);
        if ($result) {
            self::clearCache();
        }
        return $result;
    }
}