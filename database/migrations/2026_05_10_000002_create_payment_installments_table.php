<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 10, 2);
            $table->integer('percentage');
            $table->enum('due_stage', ['deposit', 'on_delivery', 'on_construction']);
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate installments per order
            $table->unique(['order_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};