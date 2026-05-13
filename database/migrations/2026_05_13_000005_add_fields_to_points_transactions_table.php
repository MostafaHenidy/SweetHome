<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('points_transactions', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('points_transactions', 'balance_before')) {
                $table->decimal('balance_before', 10, 2)->default(0)->after('points');
            }
            if (!Schema::hasColumn('points_transactions', 'balance_after')) {
                $table->decimal('balance_after', 10, 2)->default(0)->after('balance_before');
            }
            if (!Schema::hasColumn('points_transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('points_transactions', function (Blueprint $table) {
            $table->dropColumn(['balance_before', 'balance_after', 'metadata']);
        });
    }
};