<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add shipping method field
            $table->enum('shipping_method', ['delivery', 'pickup'])->nullable()->after('customer_phone');

            // Add service type field
            $table->enum('service_type', ['shipping_only', 'shipping_and_construction'])->nullable()->after('shipping_method');

            // Add payment plan type field
            $table->enum('payment_plan_type', ['50-50', '50-30-20'])->nullable()->after('service_type');

            // Add deposit amount field
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('payment_plan_type');

            // Update status enum to include new stages
            $table->dropColumn('status');
            $table->enum('status', [
                'pending',
                'pending_deposit',
                'deposit_paid',
                'processing',
                'shipped',
                'delivered',
                'construction_scheduled',
                'construction_in_progress',
                'completed',
                'cancelled'
            ])->default('pending')->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_method', 'service_type', 'payment_plan_type', 'deposit_amount']);
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending')->after('payment_method');
        });
    }
};