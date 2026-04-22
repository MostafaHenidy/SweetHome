<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('option_name');
            $table->string('option_value');
            $table->timestamps();

            $table->index('variant_id');
            $table->unique(['variant_id', 'option_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_options');
    }
};
