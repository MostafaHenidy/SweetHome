<?php

/**
 * Standalone script to download product and variant images from CSV
 * Run with: php download_product_images.php [csv_path] [options]
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProductVariant;

class ProductImageDownloader
{
    private $csvPath;
    private $limit;
    private $force;
    private $dryRun;
    private $stats = [
        'processed' => 0,
        'downloaded' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function __construct($csvPath = null, $options = [])
    {
        $this->csvPath = $csvPath ?? base_path('gfgff.csv');
        $this->limit = $options['limit'] ?? null;
        $this->force = $options['force'] ?? false;
        $this->dryRun = $options['dry-run'] ?? false;
    }

    public function run()
    {
        echo "Product Image Downloader\n";
        echo "========================\n\n";

        if (!file_exists($this->csvPath)) {
            $this->error("CSV file not found: {$this->csvPath}");
            return false;
        }

        echo "Loading CSV data from: {$this->csvPath}\n";
        $csvData = $this->loadCsvData();

        if ($this->limit) {
            $csvData = array_slice($csvData, 0, (int)$this->limit);
        }

        echo "Found " . count($csvData) . " products to process\n";

        if ($this->dryRun) {
            echo "DRY RUN MODE - No files will be downloaded\n\n";
        }

        // Create directories
        $this->createDirectories();

        // Process each product/variant
        foreach ($csvData as $index => $row) {
            $this->processRow($row);
            $this->stats['processed']++;
        }

        // Display summary
        $this->displaySummary();

        return true;
    }

    private function loadCsvData()
    {
        $handle = fopen($this->csvPath, 'r');
        $headers = fgetcsv($handle);
        $data = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 9) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    private function createDirectories()
    {
        $directories = [
            'public/products/main',
            'public/products/variants',
            'public/products/additional',
        ];

        foreach ($directories as $dir) {
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir);
            }
        }
    }

    private function processRow($row)
    {
        $variantSku = $row['id'];
        $mainImageUrl = trim($row['image_link'] ?? '');
        $additionalImages = trim($row['additional_image_link'] ?? '');

        $variant = ProductVariant::where('sku', $variantSku)->first();

        if (!$variant) {
            $this->warn("Variant not found: {$variantSku}");
            $this->stats['skipped']++;
            return;
        }

        echo "Processing: {$variant->product->name} - {$variant->variant_name}\n";

        // Process main image
        if (!empty($mainImageUrl)) {
            $this->downloadImage($variant, $mainImageUrl, 'main');
        }

        // Process additional images
        if (!empty($additionalImages)) {
            $imageUrls = array_map('trim', explode('|', $additionalImages));
            foreach ($imageUrls as $index => $imageUrl) {
                if (!empty($imageUrl)) {
                    $this->downloadImage($variant, $imageUrl, 'additional', $index);
                }
            }
        }
    }

    private function downloadImage($variant, $imageUrl, $type, $index = 0)
    {
        try {
            $filename = $this->generateFilename($variant, $imageUrl, $type, $index);
            $filepath = "public/products/{$type}/{$filename}";

            // Check if file already exists
            if (!$this->force && Storage::exists($filepath)) {
                $this->warn("  Image already exists: {$filename}");
                $this->stats['skipped']++;
                return;
            }

            if ($this->dryRun) {
                echo "  Would download: {$imageUrl} as {$filename}\n";
                return;
            }

            // Download the image
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'SweetHome-ImageDownloader/1.0',
                ])
                ->get($imageUrl);

            if (!$response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }

            $imageContent = $response->body();

            if (empty($imageContent)) {
                throw new \Exception("Empty response");
            }

            // Validate image
            if (!$this->validateImage($imageContent)) {
                throw new \Exception("Invalid image format");
            }

            // Save the image
            Storage::put($filepath, $imageContent);

            echo "  ✓ Downloaded {$type}: {$filename}\n";
            $this->stats['downloaded']++;

        } catch (\Exception $e) {
            $this->error("  ✗ Failed to download {$type}: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function validateImage($content)
    {
        $magicBytes = [
            "\xFF\xD8\xFF" => 'JPEG',
            "\x89PNG\r\n\x1a\n" => 'PNG',
            'GIF' => 'GIF',
            'RIFF' => 'WEBP',
        ];

        foreach ($magicBytes as $bytes => $format) {
            if (substr($content, 0, strlen($bytes)) === $bytes) {
                return true;
            }
        }

        return false;
    }

    private function generateFilename($variant, $imageUrl, $type, $index = 0)
    {
        $extension = strtolower(pathinfo(parse_url($imageUrl)['path'], PATHINFO_EXTENSION));
        $extension = $extension ?: 'jpg';

        $baseName = Str::slug("{$variant->product->name}-{$variant->variant_name}-{$type}");

        if ($index > 0) {
            $baseName .= "-{$index}";
        }

        return "{$baseName}-v{$variant->id}.{$extension}";
    }

    private function displaySummary()
    {
        echo "\n=== Download Summary ===\n";
        echo "Processed: {$this->stats['processed']}\n";
        echo "Downloaded: {$this->stats['downloaded']}\n";
        echo "Skipped: {$this->stats['skipped']}\n";
        echo "Errors: {$this->stats['errors']}\n";

        if ($this->stats['errors'] > 0) {
            echo "Check storage/logs/laravel.log for detailed error information\n";
        }
    }

    private function error($message)
    {
        echo "ERROR: {$message}\n";
    }

    private function warn($message)
    {
        echo "WARNING: {$message}\n";
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $csvPath = $argv[1] ?? null;
    $options = [];

    // Parse command line options
    for ($i = 2; $i < count($argv); $i++) {
        if (strpos($argv[$i], '--limit=') === 0) {
            $options['limit'] = (int)substr($argv[$i], 8);
        } elseif ($argv[$i] === '--force') {
            $options['force'] = true;
        } elseif ($argv[$i] === '--dry-run') {
            $options['dry-run'] = true;
        } elseif ($argv[$i] === '--help') {
            echo "Usage: php download_product_images.php [csv_path] [options]\n\n";
            echo "Options:\n";
            echo "  --limit=N       Limit number of products to process\n";
            echo "  --force         Re-download existing images\n";
            echo "  --dry-run       Show what would be done without downloading\n";
            echo "  --help          Show this help message\n\n";
            echo "Examples:\n";
            echo "  php download_product_images.php\n";
            echo "  php download_product_images.php /path/to/images.csv --limit=10\n";
            echo "  php download_product_images.php --force --dry-run\n";
            exit(0);
        }
    }

    $downloader = new ProductImageDownloader($csvPath, $options);
    $success = $downloader->run();
    exit($success ? 0 : 1);
}

// Make the class available for use in other contexts
return ProductImageDownloader::class;