<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ProductRecommendationService
{
    public function forProduct(Product $product, int $limit = 4): Collection
    {
        $recommendations = collect();

        // Prefer products that share categories with the current product
        $this->append($recommendations, $this->categoryRelatedProducts($product, $limit), $product, $limit);

        if ($recommendations->count() < $limit) {
            $this->append($recommendations, $this->explicitRelatedProducts($product, $limit), $product, $limit);
        }

        if ($recommendations->count() < $limit) {
            $this->append($recommendations, $this->featuredFallbackProducts($product, $limit), $product, $limit);
        }

        if ($recommendations->count() < $limit) {
            $this->append($recommendations, $this->recentPublishedFallbackProducts($product, $limit), $product, $limit);
        }

        return new Collection($recommendations->take($limit)->values()->all());
    }

    protected function explicitRelatedProducts(Product $product, int $limit): Collection
    {
        return $product->relatedProducts()
            ->published()
            ->with(['brand', 'images'])
            ->limit($limit)
            ->get();
    }

    protected function categoryRelatedProducts(Product $product, int $limit): Collection
    {
        $categoryIds = $product->relationLoaded('categories')
            ? $product->categories->pluck('id')
            : $product->categories()->pluck('categories.id');

        if ($categoryIds->isEmpty()) {
            return new Collection;
        }

        return Product::published()
            ->whereKeyNot($product->id)
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $categoryIds))
            ->with(['brand', 'images'])
            ->withCount([
                'categories as shared_categories_count' => fn ($query) => $query->whereIn('categories.id', $categoryIds),
            ])
            ->orderByDesc('shared_categories_count')
            ->latest()
            ->limit($limit)
            ->get();
    }

    protected function featuredFallbackProducts(Product $product, int $limit): Collection
    {
        return Product::published()
            ->whereKeyNot($product->id)
            ->where('is_featured', true)
            ->with(['brand', 'images'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Last resort: other published products so the related strip is not empty.
     */
    protected function recentPublishedFallbackProducts(Product $product, int $limit): Collection
    {
        return Product::published()
            ->whereKeyNot($product->id)
            ->with(['brand', 'images'])
            ->latest('published_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    protected function append(SupportCollection $target, Collection $products, Product $currentProduct, int $limit): void
    {
        foreach ($products as $product) {
            if ($target->count() >= $limit) {
                return;
            }

            if ($product->is($currentProduct) || $target->contains(fn (Product $item) => $item->is($product))) {
                continue;
            }

            $target->push($product);
        }
    }
}
