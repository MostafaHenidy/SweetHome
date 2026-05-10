<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'pending',
                'processing',
                'shipped',
                'awaiting_payment',
                'paid',
                'quote_sent',
                'quote_accepted',
                'quote_rejected',
                'cancelled',
                'completed'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE orders
            MODIFY status ENUM(
                'pending',
                'awaiting_payment',
                'paid',
                'quote_sent',
                'quote_accepted',
                'quote_rejected',
                'cancelled',
                'completed'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
