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
        Schema::table('products', function (Blueprint $table) {
            $table->text('quote_line_text')->nullable()->after('short_description');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('is_taxable');
            $table->string('unit_of_measure', 20)->nullable()->after('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['quote_line_text', 'vat_rate', 'unit_of_measure']);
        });
    }
};
