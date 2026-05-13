<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadProductImages extends Command
{
    protected $signature = 'images:download
                            {--csv= : Path to the CSV file with image URLs}
                            {--limit= : Limit number of images to download}
                            {--force : Re-download images even if they exist}';

    protected $description = 'Download product and variant images from CSV file';

    private $csvData = [];
    private $downloadCount = 0;
    private $errorCount = 0;
    private $skippedCount = 0;

    public function handle()
    {
        $csvPath = $this->option('csv') ?? base_path('gfgff.csv');
        $limit = $this->option('limit');
        $force = $this->option('force');

        if (!file_exists($csvPath)) {
            $this->error("CSV file not found: {$csvPath}");
            return Command::FAILURE;
        }

        $this->info("Loading CSV data from: {$csvPath}");
        $this->loadCsvData($csvPath);

        if ($limit) {
            $this->csvData = array_slice($this->csvData, 0, (int)$limit);
        }

        $this->info("Found " . count($this->csvData) . " products to process");

        // Create directories
        $this->createDirectories();

        // Process each product/variant
        foreach ($this->csvData as $index => $row) {
            $this->processRow($row, $force);
        }

        // Summary
        $this->info(PHP_EOL . "=== Download Summary ===");
        $this->info("Total processed: " . count($this->csvData));
        $this->info("Successfully downloaded: {$this->downloadCount}");
        $this->info("Skipped: {$this->skippedCount}");
        $this->info("Errors: {$this->errorCount}");

        return Command::SUCCESS;
    }

    private function loadCsvData($csvPath)
    {
        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 9) { // Ensure we have enough columns
                $this->csvData[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
    }

    private function createDirectories()
    {
        $directories = [
            'public/products',
            'public/products/variants',
            'public/products/additional',
        ];

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
            }
        }
    }

    private function processRow($row, $force)
    {
        $variantId = $row['id'];
        $mainImageUrl = $row['image_link'];
        $additionalImages = $row['additional_image_link'];

        $this->info("Processing: {$row['title']} (ID: {$variantId})");

        // Find the variant by SKU (eager-load product for filenames; avoids N+1)
        $variant = ProductVariant::with('product')->where('sku', $variantId)->first();

        if (!$variant) {
            $this->warn("  Variant not found in database: {$variantId}");
            $this->skippedCount++;
            return;
        }

        // Release DB connection during slow HTTP downloads so the server is not held idle.
        DB::disconnect();

        // Download main image
        if (!empty($mainImageUrl)) {
            $this->downloadMainImage($variant, $mainImageUrl, $force);
        }

        // Download additional images
        if (!empty($additionalImages)) {
            $imageUrls = explode('|', $additionalImages);
            foreach ($imageUrls as $imageUrl) {
                $imageUrl = trim($imageUrl);
                if (!empty($imageUrl)) {
                    $this->downloadAdditionalImage($variant, $imageUrl, $force);
                }
            }
        }
    }

    private function downloadMainImage($variant, $imageUrl, $force)
    {
        try {
            $filename = $this->generateFilename($variant, $imageUrl, 'main');
            $filepath = "public/products/{$filename}";

            // Check if file already exists
            if (!$force && Storage::exists($filepath)) {
                $this->warn("  Main image already exists: {$filename}");
                $this->skippedCount++;
                return;
            }

            // Download the image
            $imageContent = Http::timeout(30)->get($imageUrl)->body();

            if (empty($imageContent)) {
                throw new \Exception("Empty image content received");
            }

            // Validate it's actually an image
            if (!$this->isValidImage($imageContent)) {
                throw new \Exception("Invalid image format");
            }

            // Save the image
            Storage::put($filepath, $imageContent);

            // Update the variant's image_id (using media library would be better)
            // For now, we'll store the path in a custom field or log it
            $this->info("  ✓ Downloaded main image: {$filename}");

            // If using Spatie Media Library, you would do:
            // $variant->addMediaFromUrl($imageUrl)->toMediaCollection('images');

            $this->downloadCount++;

        } catch (\Exception $e) {
            $this->error("  ✗ Failed to download main image: " . $e->getMessage());
            $this->errorCount++;
        }
    }

    private function downloadAdditionalImage($variant, $imageUrl, $force)
    {
        try {
            $filename = $this->generateFilename($variant, $imageUrl, 'additional');
            $filepath = "public/products/additional/{$filename}";

            // Check if file already exists
            if (!$force && Storage::exists($filepath)) {
                $this->warn("  Additional image already exists: {$filename}");
                return;
            }

            // Download the image
            $imageContent = Http::timeout(30)->get($imageUrl)->body();

            if (empty($imageContent)) {
                throw new \Exception("Empty image content received");
            }

            // Validate it's actually an image
            if (!$this->isValidImage($imageContent)) {
                throw new \Exception("Invalid image format");
            }

            // Save the image
            Storage::put($filepath, $imageContent);

            $this->info("  ✓ Downloaded additional image: {$filename}");

        } catch (\Exception $e) {
            $this->error("  ✗ Failed to download additional image: " . $e->getMessage());
        }
    }

    private function generateFilename($variant, $imageUrl, $type)
    {
        $extension = pathinfo(parse_url($imageUrl)['path'], PATHINFO_EXTENSION);
        $baseName = Str::slug($variant->product->name . '-' . $variant->variant_name . '-' . $type);
        return "{$baseName}-{$variant->id}.{$extension}";
    }

    private function isValidImage($imageContent)
    {
        // Simple validation - check if it starts with image magic bytes
        $magicBytes = [
            'JPEG' => "\xFF\xD8\xFF",
            'PNG' => "\x89PNG\r\n\x1a\n",
            'GIF' => 'GIF',
        ];

        foreach ($magicBytes as $type => $bytes) {
            if (substr($imageContent, 0, strlen($bytes)) === $bytes) {
                return true;
            }
        }

        return false;
    }
}