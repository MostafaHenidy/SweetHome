<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InHouseProductSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/in_house_products.json');
        $products = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($products): void {
            foreach ($products as $index => $data) {
                $brand = $this->brand($data['brand'] ?? null);
                $categories = $this->categories($data, $index);

                $product = Product::updateOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'name' => $data['name'],
                        'slug' => $this->uniqueProductSlug($data['name'], $data['sku']),
                        'short_description' => $this->shortDescription($data),
                        'description' => $this->description($data),
                        'price' => $data['price'] ?? 0,
                        'brand_id' => $brand?->id,
                        'stock_quantity' => 100,
                        'track_stock' => false,
                        'status' => ($data['status'] ?? null) === 'نشط' ? 'published' : 'draft',
                        'sort_order' => $index + 1,
                        'product_type' => 'simple',
                        'meta_title' => $data['name'],
                        'meta_description' => $this->shortDescription($data),
                        'focus_keyword' => $data['sku'],
                        'enable_reviews' => true,
                        'is_taxable' => ((float) ($data['tax'] ?? 0)) > 0,
                        'is_featured' => $index < 12,
                        'published_at' => now(),
                    ]
                );

                $product->categories()->sync($categories->pluck('id')->all());
                $this->image($product, $data['image_url'] ?? null);
                $this->discount($product, $data);
            }
        });
    }

    protected function brand(?string $name): ?Brand
    {
        if (! $name) {
            return null;
        }

        return Brand::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'active' => true]
        );
    }

    protected function categories(array $data, int $index)
    {
        $categories = collect();
        $parent = null;

        foreach ([
            ['name' => $data['category'] ?? null, 'level' => 1],
            ['name' => $data['subcategory'] ?? null, 'level' => 2],
            ['name' => $data['sub_subcategory'] ?? null, 'level' => 3],
        ] as $item) {
            if (! $item['name']) {
                continue;
            }

            $category = $this->category($item['name'], $parent, $item['level'], $index);
            $categories->push($category);
            $parent = $category;
        }

        return $categories;
    }

    protected function category(string $name, ?Category $parent, int $level, int $sortOrder): Category
    {
        $slug = $this->categorySlug($name, $parent);

        return Category::updateOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => $parent?->id,
                'name' => $name,
                'level' => $level,
                'sort_order' => $sortOrder + 1,
                'active' => true,
            ]
        );
    }

    protected function categorySlug(string $name, ?Category $parent): string
    {
        $slug = Str::slug($name);
        $existing = Category::where('slug', $slug)->first();

        if (! $existing || (int) $existing->parent_id === (int) $parent?->id) {
            return $slug;
        }

        return Str::slug(($parent?->slug ?? 'category').'-'.$name);
    }

    protected function image(Product $product, ?string $url): void
    {
        if (! $url) {
            return;
        }

        ProductImage::updateOrCreate(
            ['product_id' => $product->id, 'sort_order' => 0],
            [
                'file_path' => $url,
                'file_name' => basename(parse_url($url, PHP_URL_PATH) ?: $product->slug.'.webp'),
                'mime_type' => 'image/webp',
                'alt_text' => $product->name,
                'title' => $product->name,
                'is_featured' => true,
            ]
        );
    }

    protected function discount(Product $product, array $data): void
    {
        $discount = (float) ($data['discount'] ?? 0);

        if ($discount <= 0) {
            Coupon::where('code', $this->discountCode($product))->delete();

            return;
        }

        Coupon::updateOrCreate(
            ['code' => $this->discountCode($product)],
            [
                'type' => 'product_specific',
                'value' => $discount,
                'product_id' => $product->id,
                'is_active' => true,
            ]
        );
    }

    protected function uniqueProductSlug(string $name, string $sku): string
    {
        $base = Str::slug($name) ?: Str::slug($sku);
        $product = Product::where('sku', $sku)->first();
        $query = Product::where('slug', $base);

        if ($product) {
            $query->whereKeyNot($product->id);
        }

        return $query->exists() ? $base.'-'.Str::slug($sku) : $base;
    }

    protected function shortDescription(array $data): string
    {
        return trim(implode(' - ', array_filter([
            $data['brand'] ?? null,
            $data['subcategory'] ?? $data['category'] ?? null,
            $data['sku'] ?? null,
        ])));
    }

    protected function description(array $data): string
    {
        $parts = [
            '<p>'.e($this->shortDescription($data)).'</p>',
        ];

        if (! empty($data['source_url'])) {
            $parts[] = '<p>Source product page: <a href="'.e($data['source_url']).'" target="_blank" rel="noopener">'.e($data['source_url']).'</a></p>';
        }

        return implode('', $parts);
    }

    protected function discountCode(Product $product): string
    {
        return 'INHOUSE-'.Str::upper(Str::slug($product->sku));
    }
}
