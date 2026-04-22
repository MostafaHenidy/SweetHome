<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'unisex', 'kids'])->nullable()->after('brand_id');
            $table->enum('product_type', ['simple', 'variable', 'grouped', 'external'])->default('simple')->after('gender');
            $table->string('meta_title')->nullable()->after('product_type');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('focus_keyword')->nullable()->after('meta_description');
            $table->decimal('weight', 8, 2)->nullable()->after('focus_keyword');
            $table->decimal('length', 8, 2)->nullable()->after('weight');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            $table->string('shipping_class')->nullable()->after('height');
            $table->boolean('free_shipping')->default(false)->after('shipping_class');
            $table->boolean('enable_reviews')->default(true)->after('free_shipping');
            $table->boolean('is_taxable')->default(true)->after('enable_reviews');
            $table->boolean('is_featured')->default(false)->after('is_taxable');
            $table->timestamp('published_at')->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'product_type',
                'meta_title',
                'meta_description',
                'focus_keyword',
                'weight',
                'length',
                'width',
                'height',
                'shipping_class',
                'free_shipping',
                'enable_reviews',
                'is_taxable',
                'is_featured',
                'published_at',
            ]);
        });
    }
};
