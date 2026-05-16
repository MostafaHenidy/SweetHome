<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnhancedProductCsvSeeder extends Seeder
{
    private $productMapping = [];
    private $brandMapping = [];

    public function run(): void
    {
        $this->command->info('Starting enhanced product CSV seeding...');

        // First, let's create a mapping of brand names to brand IDs
        $this->createBrandMapping();

        // Seed products
        $productsFile = base_path('products.csv');
        if (!file_exists($productsFile)) {
            $this->command->error('products.csv file not found');
            return;
        }

        $products = $this->readCsv($productsFile);
        $this->command->info("Found " . count($products) . " products to import");

        DB::transaction(function () use ($products) {
            foreach ($products as $index => $productData) {
                try {
                    // Skip if product with same SKU already exists
                    if (Product::where('sku', $productData['sku'])->exists()) {
                        $this->command->info("Skipping product with SKU: {$productData['sku']} (already exists)");
                        continue;
                    }

                    $slug = $productData['slug'] ?? Str::slug($productData['name']);
                    // Ensure slug is unique
                    $originalSlug = $slug;
                    $counter = 1;
                    while (Product::where('slug', $slug)->exists()) {
                        $slug = $originalSlug . '-' . $counter;
                        $counter++;
                    }

                    // Extract brand from product_type field
                    $brandId = $this->extractBrandId($productData['product_type'] ?? '');

                    $product = Product::create([
                        'sku' => $productData['sku'],
                        'name' => $productData['name'],
                        'slug' => $slug,
                        'short_description' => $productData['short_description'] ?? null,
                        'quote_line_text' => $productData['quote_line_text'] ?? null,
                        'description' => $productData['description'] ?? null,
                        'price' => $this->parseDecimal($productData['price']),
                        'compare_price' => $this->parseDecimal($productData['compare_price']),
                        'cost_price' => $this->parseDecimal($productData['cost_price']),
                        'brand_id' => $brandId,
                        'gender' => $this->parseEnum($productData['gender'], ['male', 'female', 'unisex', 'kids']),
                        'product_type' => $this->parseEnum($productData['product_type'], ['simple', 'variable', 'grouped', 'external'], 'simple'),
                        'meta_title' => $productData['meta_title'] ?? null,
                        'meta_description' => $productData['meta_description'] ?? null,
                        'focus_keyword' => $productData['focus_keyword'] ?? null,
                        'weight' => $this->parseDecimal($productData['weight']),
                        'length' => $this->parseDecimal($productData['length']),
                        'width' => $this->parseDecimal($productData['width']),
                        'height' => $this->parseDecimal($productData['height']),
                        'shipping_class' => $productData['shipping_class'] ?? null,
                        'free_shipping' => isset($productData['free_shipping']) ? filter_var($productData['free_shipping'], FILTER_VALIDATE_BOOLEAN) : false,
                        'enable_reviews' => isset($productData['enable_reviews']) ? filter_var($productData['enable_reviews'], FILTER_VALIDATE_BOOLEAN) : false,
                        'is_taxable' => isset($productData['is_taxable']) ? filter_var($productData['is_taxable'], FILTER_VALIDATE_BOOLEAN) : false,
                        'vat_rate' => $this->parseDecimal($productData['vat_rate']),
                        'unit_of_measure' => $productData['unit_of_measure'] ?? null,
                        'is_featured' => isset($productData['is_featured']) ? filter_var($productData['is_featured'], FILTER_VALIDATE_BOOLEAN) : false,
                        'published_at' => $productData['published_at'] ?? null,
                        'stock_quantity' => $this->parseInteger($productData['stock_quantity'], 0),
                        'track_stock' => isset($productData['track_stock']) ? filter_var($productData['track_stock'], FILTER_VALIDATE_BOOLEAN) : false,
                        'status' => $this->parseEnum($productData['status'], ['draft', 'published'], 'published'),
                        'sort_order' => $this->parseInteger($productData['sort_order'], 0),
                    ]);

                    // Store the mapping from original CSV ID to new database ID
                    if (isset($productData['id'])) {
                        $this->productMapping[$productData['id']] = $product->id;
                    }

                    $this->command->info("Imported product: {$product->name} (SKU: {$product->sku}, Brand ID: {$brandId})");

                } catch (\Exception $e) {
                    $this->command->error("Failed to import product at row " . ($index + 2) . ": " . $e->getMessage());
                    Log::error("Product CSV import failed", [
                        'row' => $index + 2,
                        'data' => $productData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Seed product variants
        $this->command->info('Seeding product variants from CSV...');
        $variantsFile = base_path('product_variants.csv');
        if (!file_exists($variantsFile)) {
            $this->command->error('product_variants.csv file not found');
            return;
        }

        $variants = $this->readCsv($variantsFile);
        $this->command->info("Found " . count($variants) . " variants to import");

        DB::transaction(function () use ($variants) {
            foreach ($variants as $index => $variantData) {
                try {
                    // Check if product exists using the ID mapping
                    $originalProductId = $variantData['product_id'];
                    $newProductId = $this->productMapping[$originalProductId] ?? null;

                    $product = $newProductId ? Product::find($newProductId) : null;
                    if (!$product) {
                        $this->command->info("Skipping variant - product not found: Original ID {$originalProductId}");
                        continue;
                    }

                    // Skip if variant with same SKU already exists
                    if (ProductVariant::where('sku', $variantData['sku'])->exists()) {
                        $this->command->info("Skipping variant with SKU: {$variantData['sku']} (already exists)");
                        continue;
                    }

                    $variant = ProductVariant::create([
                        'product_id' => $newProductId,
                        'variant_name' => $variantData['variant_name'],
                        'sku' => $variantData['sku'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'regular_price' => $this->parseDecimal($variantData['regular_price']),
                        'sale_price' => $this->parseDecimal($variantData['sale_price']),
                        'cost_price' => $this->parseDecimal($variantData['cost_price']),
                        'stock_quantity' => $this->parseInteger($variantData['stock_quantity'], 0),
                        'image_id' => $this->parseInteger($variantData['image_id']),
                        'is_active' => isset($variantData['is_active']) ? filter_var($variantData['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                        'sort_order' => $this->parseInteger($variantData['sort_order'], 0),
                    ]);

                    $this->command->info("Imported variant: {$variant->variant_name} (SKU: {$variant->sku}) for product ID: {$variant->product_id}");

                } catch (\Exception $e) {
                    $this->command->error("Failed to import variant at row " . ($index + 2) . ": " . $e->getMessage());
                    Log::error("Variant CSV import failed", [
                        'row' => $index + 2,
                        'data' => $variantData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        // Seed product variant options
        $this->command->info('Seeding product variant options from CSV...');
        $variantOptionsFile = base_path('product_variant_options.csv');
        if (!file_exists($variantOptionsFile)) {
            $this->command->error('product_variant_options.csv file not found');
            return;
        }

        $variantOptions = $this->readCsv($variantOptionsFile);
        $this->command->info("Found " . count($variantOptions) . " variant options to import");

        DB::transaction(function () use ($variantOptions) {
            foreach ($variantOptions as $index => $optionData) {
                try {
                    // Check if variant exists using the original variant ID
                    $originalVariantId = $optionData['variant_id'];
                    $variant = ProductVariant::find($originalVariantId);
                    if (!$variant) {
                        $this->command->info("Skipping option - variant not found: ID {$originalVariantId}");
                        continue;
                    }

                    // Skip if option already exists for this variant
                    if (ProductVariantOption::where('variant_id', $optionData['variant_id'])
                        ->where('option_name', $optionData['option_name'])
                        ->exists()) {
                        $this->command->info("Skipping option {$optionData['option_name']} for variant ID: {$optionData['variant_id']} (already exists)");
                        continue;
                    }

                    $option = ProductVariantOption::create([
                        'variant_id' => $this->parseInteger($optionData['variant_id']),
                        'option_name' => $optionData['option_name'],
                        'option_value' => $optionData['option_value'],
                    ]);

                    $this->command->info("Imported option: {$option->option_name} = {$option->option_value} for variant ID: {$option->variant_id}");

                } catch (\Exception $e) {
                    $this->command->error("Failed to import variant option at row " . ($index + 2) . ": " . $e->getMessage());
                    Log::error("Variant option CSV import failed", [
                        'row' => $index + 2,
                        'data' => $optionData,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $this->command->info('Enhanced CSV seeding completed successfully!');
        $this->command->info("Created " . count($this->brandMapping) . " new brands from product data");
    }

    /**
     * Create mapping of brand names to brand IDs
     */
    private function createBrandMapping(): void
    {
        $this->command->info('Creating brand mapping from existing brands...');

        // Get all existing brands
        $brands = Brand::all();
        foreach ($brands as $brand) {
            $this->brandMapping[strtolower($brand->name)] = $brand->id;
        }

        $this->command->info("Found " . count($this->brandMapping) . " existing brands");
    }

    /**
     * Extract brand ID from product_type field
     */
    private function extractBrandId($productType): ?int
    {
        if (empty($productType)) {
            return null;
        }

        // Extract brand name from product_type
        // Examples:
        // "OFFICINE ITALIANE PARQUET > Maxiplancia rovere..." -> "OFFICINE ITALIANE PARQUET"
        // "Collezione MAYKA" -> "Mayka" (or we could treat "Collezione" as a brand)
        // "VITRUVIT > Collezioni" -> "VITRUVIT"

        $brandName = $this->extractBrandNameFromProductType($productType);

        if (!$brandName) {
            return null;
        }

        // Check if brand already exists in our mapping
        $brandKey = strtolower($brandName);
        if (isset($this->brandMapping[$brandKey])) {
            return $this->brandMapping[$brandKey];
        }

        // Create new brand
        try {
            $brand = Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName),
                'active' => true,
            ]);

            $this->brandMapping[$brandKey] = $brand->id;
            $this->command->info("Created new brand: {$brandName}");

            return $brand->id;
        } catch (\Exception $e) {
            $this->command->warning("Failed to create brand '{$brandName}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract brand name from product_type field using various patterns
     */
    private function extractBrandNameFromProductType($productType): ?string
    {
        $productType = trim($productType);

        if (empty($productType)) {
            return null;
        }

        // Pattern 1: "BRAND > Collection Name" -> extract "BRAND"
        if (strpos($productType, '>') !== false) {
            $parts = explode('>', $productType);
            $brandName = trim($parts[0]);
            if (!empty($brandName) && strlen($brandName) > 2) {
                return $brandName;
            }
        }

        // Pattern 2: "Collezione BRANDNAME" -> extract "BRANDNAME"
        if (stripos($productType, 'Collezione ') === 0) {
            $brandName = trim(substr($productType, 11)); // Remove "Collezione "
            if (!empty($brandName) && strlen($brandName) > 2) {
                return $brandName;
            }
        }

        // Pattern 3: "BRAND" (standalone brand name)
        // Check if it matches our existing brands or looks like a brand
        $simpleBrands = ['EXPOSERVICE', 'OFFICINE ITALIANE PARQUET', 'VITRUVIT', 'LA FABBRICA AVA',
                        'PANARIA CERAMICHE', 'MGM CERAMICHE', 'ITALWOOD DESIGN',
                        'CERAMICHE DOLOMITE', 'COLOMBO DESIGN'];

        foreach ($simpleBrands as $brand) {
            if (stripos($productType, $brand) === 0) {
                return $brand;
            }
        }

        // For other cases, use the entire product_type as brand if it's not too long
        if (strlen($productType) <= 50 && !stripos($productType, 'collezione')) {
            return $productType;
        }

        return null;
    }

    /**
     * Read CSV file and return as array
     */
    private function readCsv(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row);
                }
            }

            fclose($handle);
        }

        return $data;
    }

    /**
     * Parse decimal value, converting empty strings to null
     */
    private function parseDecimal($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        // Convert to float and return, handling any non-numeric characters
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);
        return $cleaned !== '' ? (float) $cleaned : null;
    }

    /**
     * Parse integer value, converting empty strings to null or default
     */
    private function parseInteger($value, $default = null)
    {
        if ($value === null || $value === '' || $value === false) {
            return $default;
        }

        // Convert to integer and return, handling any non-numeric characters
        $cleaned = preg_replace('/[^0-9-]/', '', $value);
        return $cleaned !== '' ? (int) $cleaned : $default;
    }

    /**
     * Parse enum value, validating against allowed values
     */
    private function parseEnum($value, array $allowedValues, $default = null)
    {
        if ($value === null || $value === '' || $value === false) {
            return $default;
        }

        $trimmed = trim($value);
        if (in_array($trimmed, $allowedValues)) {
            return $trimmed;
        }

        return $default;
    }
}