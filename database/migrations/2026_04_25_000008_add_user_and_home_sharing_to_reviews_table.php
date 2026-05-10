<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('product_id')->index();
            $table->boolean('show_on_home')->default(false)->after('status')->index();
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['show_on_home']);
            $table->dropColumn(['user_id', 'show_on_home']);
        });
    }
};
