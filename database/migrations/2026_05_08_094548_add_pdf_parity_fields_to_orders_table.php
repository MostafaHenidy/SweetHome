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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_company_name')->nullable()->after('customer_phone');
            $table->string('customer_vat_number', 32)->nullable()->after('customer_company_name');
            $table->string('customer_tax_code', 32)->nullable()->after('customer_vat_number');
            $table->unsignedInteger('quote_document_number')->nullable()->after('quote_pdf_path');
            $table->unsignedSmallInteger('quote_document_year')->nullable()->after('quote_document_number');
            $table->unsignedInteger('invoice_document_number')->nullable()->after('order_confirmation_pdf_path');
            $table->unsignedSmallInteger('invoice_document_year')->nullable()->after('invoice_document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_company_name',
                'customer_vat_number',
                'customer_tax_code',
                'quote_document_number',
                'quote_document_year',
                'invoice_document_number',
                'invoice_document_year',
            ]);
        });
    }
};
