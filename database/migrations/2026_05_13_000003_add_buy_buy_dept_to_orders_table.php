<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_vip_order')->default(false)->after('service_type');
            $table->decimal('points_earned', 10, 2)->default(0)->after('is_vip_order');
            $table->decimal('points_redeemed', 10, 2)->default(0)->after('points_earned');
            $table->string('buy_buy_dept')->nullable()->after('points_redeemed');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_vip_order', 'points_earned', 'points_redeemed', 'buy_buy_dept']);
        });
    }
};