<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add order type column
            $table->enum('type', ['direct', 'quote_request'])
                ->default('direct')
                ->after('order_number');

            // Update status enum to support quote workflow
            $table->dropColumn('status');
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'awaiting_payment',
                'paid',
                'quote_sent',
                'quote_accepted',
                'quote_rejected',
                'cancelled',
                'completed',
            ])->default('pending')->after('total');

            // Add quote-specific fields
            $table->timestamp('quote_expires_at')->nullable()->after('notes');
            $table->string('quote_pdf_path')->nullable()->after('quote_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['type', 'quote_expires_at', 'quote_pdf_path']);

            // Revert status enum to original
            $table->dropColumn('status');
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'completed',
                'cancelled',
            ])->default('pending');
        });
    }
};
