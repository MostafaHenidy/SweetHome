<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('filter_type');
            $table->string('filter_value');
            $table->string('color_hex')->nullable();
            $table->timestamps();

            $table->index('product_id');
            $table->index('filter_type');
            $table->unique(['product_id', 'filter_type', 'filter_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_filters');
    }
};
