<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::all();
        $categories = Category::all();

        $sampleProducts = [
            [
                'name' => 'Abisko Grigio 20x120',
                'sku' => 'ABISKO-GR-20120',
                'price' => 45.90,
                'category_slugs' => ['gres', 'pavimenti'],
                'brand_slug' => 'la-fabbrica-ava',
                'short_description' => 'Gres porcellanato effetto pietra, formato 20x120 cm.',
                'attributes' => [
                    ['name' => 'Dimensioni', 'values' => ['20x120x0,9', '20x180x1,5', '30x180x1,5']],
                ],
            ],
            [
                'name' => 'Rovere Naturale Parquet',
                'sku' => 'ROVERE-NAT-14',
                'price' => 89.00,
                'category_slugs' => ['parquet', 'pavimenti'],
                'brand_slug' => 'italwood-design',
                'short_description' => 'Parquet in rovere naturale, spessore 14mm.',
                'attributes' => [
                    ['name' => 'Spessore', 'values' => ['14mm', '20mm']],
                ],
            ],
            [
                'name' => 'Lavabo Sospeso Bianco',
                'sku' => 'LAVABO-SOSP-BI',
                'price' => 120.00,
                'category_slugs' => ['bidet-wc-lavabi', 'sanitari'],
                'brand_slug' => 'ceramiche-dolomite',
                'short_description' => 'Lavabo sospeso in ceramica bianca, design moderno.',
                'attributes' => [],
            ],
            [
                'name' => 'Maniglia Colombo Design C33',
                'sku' => 'COL-C33-CR',
                'price' => 35.50,
                'category_slugs' => ['contemporanee', 'maniglie'],
                'brand_slug' => 'colombo-design',
                'short_description' => 'Maniglia contemporanea Colombo Design modello C33.',
                'attributes' => [
                    ['name' => 'Finitura', 'values' => ['Cromo', 'Nichel Satinato', 'Nero Opaco']],
                ],
            ],
        ];

        foreach ($sampleProducts as $data) {
            $brand = $brands->firstWhere('slug', $data['brand_slug']);
            $categoryIds = Category::whereIn('slug', $data['category_slugs'])->pluck('id');

            $product = Product::create([
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'price' => $data['price'],
                'brand_id' => $brand?->id,
                'short_description' => $data['short_description'],
                'description' => '<p>' . $data['short_description'] . '</p><p>Prodotto di alta qualità, made in Italy.</p>',
                'status' => 'published',
                'stock_quantity' => rand(10, 100),
            ]);

            $product->categories()->attach($categoryIds);

            foreach ($data['attributes'] as $attr) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'name' => $attr['name'],
                    'values' => $attr['values'],
                ]);
            }
        }
    }
}
