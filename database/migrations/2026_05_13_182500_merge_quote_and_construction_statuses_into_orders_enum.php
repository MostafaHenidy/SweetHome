<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 2026_05_10 shipping/service migration replaced `orders.status` with an enum that omitted
     * quote workflow values. MySQL then rejects updates such as `quote_sent` from QuoteService::generatePdf().
     */
    public function up(): void
    {
        if (! Schema::hasTable('orders') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM(
                'pending',
                'pending_deposit',
                'deposit_paid',
                'processing',
                'shipped',
                'delivered',
                'construction_scheduled',
                'construction_in_progress',
                'construction_completed',
                'completed',
                'cancelled',
                'awaiting_payment',
                'paid',
                'quote_sent',
                'quote_accepted',
                'quote_rejected'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM(
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
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
