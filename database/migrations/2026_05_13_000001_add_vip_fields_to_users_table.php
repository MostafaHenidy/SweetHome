<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_vip')->default(false)->after('phone');
            $table->enum('vip_type', ['manual', 'automatic'])->nullable()->after('is_vip');
            $table->timestamp('vip_assigned_at')->nullable()->after('vip_type');
            $table->foreignId('vip_assigned_by')->nullable()->after('vip_assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_vip', 'vip_type', 'vip_assigned_at', 'vip_assigned_by']);
        });
    }
};