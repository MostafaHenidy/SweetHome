<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Add VIP configuration settings to the settings table
        Setting::set('vip_minimum_order_threshold', 1000, 'vip');
        Setting::set('vip_time_period_days', 365, 'vip');
        Setting::set('vip_auto_qualify_enabled', true, 'vip');
        Setting::set('points_per_euro_rate', 1.00, 'vip');
    }

    public function down(): void
    {
        // Remove VIP configuration settings
        Setting::where('key', 'LIKE', 'vip_%')->delete();
        Setting::where('key', 'points_per_euro_rate')->delete();
    }
};