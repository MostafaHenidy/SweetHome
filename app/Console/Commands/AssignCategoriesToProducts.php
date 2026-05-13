<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AssignCategoriesToProducts extends Command
{
    protected $signature = 'products:assign-categories
                            {--dry-run : Show what would be assigned without making changes}
                            {--batch=100 : Number of products to process per batch}';

    protected $description = 'Assign categories to products based on product names and descriptions';

    // Define keyword mappings to categories
    private $keywordMappings = [
        'pavimenti' => ['pavimenti', 'parquet', 'floor', 'wood floor', 'legno'],
        'gres' => ['gres', 'ceramic', 'porcelain', 'tile'],
        'rivestimenti' => ['rivestimenti', 'wall', 'muro', 'cladding'],
        'mosaici' => ['mosaic', 'mosaici'],
        'bagno' => ['bagno', 'bath', 'sanitari', 'docce', 'rubinetteria', 'shower', 'bidet', 'wc', 'lavabi'],
        'mobili' => ['mobile', 'arredo', 'furniture', 'cabinet', 'armadio'],
        'porte' => ['porta', 'door', 'portone'],
        'finestre' => ['finestra', 'window', 'vetro'],
        'maniglie' => ['maniglia', 'handle', 'knob', 'pomolo'],
        'riscaldamento' => ['riscaldamento', 'radiatore', 'heating', 'radiator'],
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch');

        $this->info('Starting category assignment process...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all categories for mapping
        $categories = Category::where('active', true)->get();
        $categoryMap = $categories->keyBy('name');

        $processed = 0;
        $assigned = 0;
        $skipped = 0;

        // Process products in batches
        Product::where('status', 'published')->chunk($batchSize, function ($products) use ($dryRun, $categoryMap, &$processed, &$assigned, &$skipped) {
            foreach ($products as $product) {
                $processed++;

                // Skip products that already have categories
                if ($product->categories()->count() > 0) {
                    $skipped++;
                    continue;
                }

                $productCategories = $this->detectCategories($product, $categoryMap);

                if (!empty($productCategories)) {
                    if (!$dryRun) {
                        $product->categories()->attach(array_keys($productCategories));
                    }

                    $assigned++;
                    $categoryNames = implode(', ', array_column($productCategories, 'name'));
                    $this->info("Product '{$product->name}' -> Categories: {$categoryNames}");
                }

                // Progress indicator
                if ($processed % 100 === 0) {
                    $this->info("Processed {$processed} products...");
                }
            }
        });

        $this->info("Completed!");
        $this->info("Processed: {$processed}");
        $this->info("Assigned categories: {$assigned}");
        $this->info("Skipped (already had categories): {$skipped}");

        if ($dryRun) {
            $this->warn('Dry run completed - no changes were made');
        }
    }

    private function detectCategories(Product $product, $categoryMap)
    {
        $detectedCategories = [];
        $searchText = strtolower($product->name . ' ' . ($product->short_description ?? '') . ' ' . ($product->description ?? ''));

        // Check each category for keyword matches
        foreach ($categoryMap as $category) {
            $categoryName = strtolower($category->name);
            $categoryNameClean = preg_replace('/[^a-z0-9]/', '', $categoryName);
            $searchTextClean = preg_replace('/[^a-z0-9]/', '', $searchText);

            // Direct name match (normalized)
            if (strpos($searchTextClean, $categoryNameClean) !== false) {
                $detectedCategories[$category->id] = ['name' => $category->name];
                continue;
            }

            // Check keyword mappings
            foreach ($this->keywordMappings as $categoryKey => $keywords) {
                if (strpos($categoryName, $categoryKey) !== false || $this->categoryMatchesKey($categoryName, $categoryKey)) {
                    foreach ($keywords as $keyword) {
                        if (strpos($searchText, $keyword) !== false) {
                            $detectedCategories[$category->id] = ['name' => $category->name];
                            break 2;
                        }
                    }
                }
            }
        }

        return $detectedCategories;
    }

    private function categoryMatchesKey($categoryName, $key)
    {
        $keyVariations = [
            'bagno' => ['bagno', 'bath', 'bathroom'],
            'riscaldamento' => ['riscaldamento', 'heating'],
        ];

        return isset($keyVariations[$key]) && in_array($categoryName, $keyVariations[$key]);
    }
}