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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Email notifications
            $table->boolean('email_order_created')->default(true);
            $table->boolean('email_order_status_changed')->default(true);
            $table->boolean('email_quote_created')->default(true);
            $table->boolean('email_quote_status_changed')->default(true);

            // Firebase push notifications
            $table->boolean('firebase_order_created')->default(true);
            $table->boolean('firebase_order_status_changed')->default(true);
            $table->boolean('firebase_quote_created')->default(true);
            $table->boolean('firebase_quote_status_changed')->default(true);

            // Database notifications (in-app)
            $table->boolean('database_order_created')->default(true);
            $table->boolean('database_order_status_changed')->default(true);
            $table->boolean('database_quote_created')->default(true);
            $table->boolean('database_quote_status_changed')->default(true);

            $table->timestamps();

            // Unique constraint per user
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
