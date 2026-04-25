<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_configurations', function (Blueprint $table) {
            $table->id();
            $table->decimal('points_per_euro', 10, 2)->default(100);
            $table->decimal('points_per_order_percentage', 5, 2)->default(5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_configurations');
    }
};
