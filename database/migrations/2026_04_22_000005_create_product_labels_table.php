<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('label_type', ['new_arrival', 'best_seller', 'featured', 'on_sale', 'limited', 'exclusive']);
            $table->timestamps();

            $table->index('product_id');
            $table->unique(['product_id', 'label_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_labels');
    }
};
