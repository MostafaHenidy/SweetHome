<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add role field if not exists
            if (! Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['customer', 'admin'])->default('customer')->after('email');
            }

            // Add status field if not exists
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            }

            // Add last login timestamp if not exists
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            }

            // Add points balance if not exists
            if (! Schema::hasColumn('users', 'points_balance')) {
                $table->integer('points_balance')->default(0)->after('last_login_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'role')) {
                $columnsToDrop[] = 'role';
            }
            if (Schema::hasColumn('users', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $columnsToDrop[] = 'last_login_at';
            }
            if (Schema::hasColumn('users', 'points_balance')) {
                $columnsToDrop[] = 'points_balance';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
