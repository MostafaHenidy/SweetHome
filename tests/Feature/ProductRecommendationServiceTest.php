<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prefers_shared_categories_then_explicit_related_then_featured(): void
    {
        $category = Category::create([
            'name' => 'Implants',
            'slug' => 'implants',
            'level' => 1,
            'active' => true,
        ]);

        $current = $this->product('Current Implant');
        $explicit = $this->product('Explicit Related');
        $shared = $this->product('Shared Category');
        $unrelated = $this->product('Unrelated Featured', ['is_featured' => true]);

        $current->categories()->attach($category);
        $shared->categories()->attach($category);
        $current->relatedProducts()->attach($explicit);

        $recommendations = app(ProductRecommendationService::class)->forProduct($current, 3);

        $this->assertSame(
            [$shared->id, $explicit->id, $unrelated->id],
            $recommendations->pluck('id')->all()
        );
    }

    protected function product(string $name, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'sku' => Str::upper(Str::slug($name)),
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => 100,
            'stock_quantity' => 10,
            'track_stock' => false,
            'status' => 'published',
            'product_type' => 'simple',
            'enable_reviews' => true,
            'is_taxable' => true,
            'is_featured' => false,
            'published_at' => now(),
        ], $overrides));
    }

    public function test_it_falls_back_to_recent_published_when_no_category_explicit_or_featured(): void
    {
        $current = $this->product('Lonely Product');
        $otherA = $this->product('Other A');
        $otherB = $this->product('Other B');

        $recommendations = app(ProductRecommendationService::class)->forProduct($current, 4);

        $this->assertCount(2, $recommendations);
        $this->assertTrue($recommendations->pluck('id')->contains($otherA->id));
        $this->assertTrue($recommendations->pluck('id')->contains($otherB->id));
    }
}
