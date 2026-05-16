<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add payment method configurations to settings table
        Schema::table('settings', function (Blueprint $table) {
            // Payment method enable/disable settings
            $table->boolean('payment_fawaterak_enabled')->default(true);
            $table->boolean('payment_stripe_enabled')->default(true);
            $table->boolean('payment_paypal_enabled')->default(true);
            $table->boolean('payment_bank_transfer_enabled')->default(true);
            $table->boolean('payment_cash_on_delivery_enabled')->default(true);

            // Payment gateway credentials (encrypted)
            $table->text('stripe_secret_key')->nullable();
            $table->text('stripe_publishable_key')->nullable();
            $table->text('stripe_webhook_secret')->nullable();

            $table->text('paypal_client_id')->nullable();
            $table->text('paypal_client_secret')->nullable();
            $table->text('paypal_mode')->default('sandbox');

            $table->text('fawaterak_api_key')->nullable();
            $table->text('fawaterak_secret_key')->nullable();
            $table->text('fawaterak_base_url')->nullable();

            // Customer debt settings
            $table->decimal('global_debt_limit', 10, 2)->default(10000.00);
            $table->boolean('enable_debt_limits')->default(true);
            $table->integer('debt_review_period_days')->default(30);

            // Installment policy settings
            $table->integer('max_installments')->default(12);
            $table->decimal('min_installment_amount', 10, 2)->default(50.00);
            $table->decimal('installment_interest_rate', 5, 2)->default(0.00);
            $table->boolean('enable_installments')->default(true);

            // Display order for payment methods
            $table->json('payment_method_order')->nullable();
        });

        // Create table for customer-specific debt limits
        Schema::create('customer_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_debt', 10, 2)->default(0.00);
            $table->decimal('debt_limit', 10, 2)->nullable(); // Nullable to use global limit
            $table->date('last_review_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('current_debt');
        });

        // Create table for installment policies
        Schema::create('installment_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_installments');
            $table->decimal('min_order_amount', 10, 2);
            $table->decimal('interest_rate', 5, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->json('applicable_categories')->nullable(); // Category IDs this policy applies to
            $table->json('excluded_products')->nullable(); // Product IDs excluded from this policy
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        // Add debt relationship to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('custom_debt_limit', 10, 2)->nullable()->after('country');
            $table->boolean('override_global_debt_limit')->default(false)->after('custom_debt_limit');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });

        Schema::dropIfExists('customer_debts');
        Schema::dropIfExists('installment_policies');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['custom_debt_limit', 'override_global_debt_limit']);
        });
    }
};