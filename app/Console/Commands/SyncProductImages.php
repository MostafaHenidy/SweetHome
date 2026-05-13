<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SyncProductImages extends Command
{
    protected $signature = 'images:sync
                            {--csv= : Path to the CSV file with image URLs}
                            {--limit= : Limit number of images to process}
                            {--force : Re-download and re-sync images even if they exist}
                            {--dry-run : Show what would be done without actually downloading}';

    protected $description = 'Sync product and variant images from CSV using Spatie Media Library';

    private $csvData = [];
    private $stats = [
        'processed' => 0,
        'downloaded' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function handle()
    {
        $csvPath = $this->option('csv') ?? base_path('gfgff.csv');
        $limit = $this->option('limit');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

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
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be downloaded");
        }

        $totalRows = count($this->csvData);
        $bar = $this->output->createProgressBar($totalRows);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('starting');
        $bar->start();

        // Process each product/variant (continue on DB / network errors per row)
        foreach ($this->csvData as $index => $row) {
            $bar->setMessage('row '.($index + 1).' / '.$totalRows);
            $this->processRowResilient($row, $force, $dryRun);
            $this->stats['processed']++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->displaySummary();

        return Command::SUCCESS;
    }

    private function loadCsvData($csvPath)
    {
        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 9) {
                $this->csvData[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
    }

    /**
     * Run processRow with one reconnect+retry on transient MySQL connection loss; then continue.
     */
    private function processRowResilient(array $row, bool $force, bool $dryRun): void
    {
        $sku = $row['id'] ?? '(unknown)';

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $this->processRow($row, $force, $dryRun);

                return;
            } catch (QueryException $e) {
                if ($attempt === 0 && $this->isTransientMysqlConnectionError($e)) {
                    $this->warn("MySQL connection lost for SKU {$sku}; reconnecting and retrying once…");
                    DB::reconnect();
                    continue;
                }
                $this->error("SKU {$sku}: " . $e->getMessage());
                $this->stats['errors']++;
                Log::error('images:sync row failed (QueryException)', ['sku' => $sku, 'error' => $e->getMessage()]);

                return;
            } catch (\Throwable $e) {
                if ($attempt === 0 && $this->isTransientMysqlConnectionError($e)) {
                    $this->warn("MySQL connection lost for SKU {$sku}; reconnecting and retrying once…");
                    DB::reconnect();
                    continue;
                }
                $this->error("SKU {$sku}: " . $e->getMessage());
                $this->stats['errors']++;
                Log::error('images:sync row failed', ['sku' => $sku, 'error' => $e->getMessage()]);

                return;
            }
        }
    }

    private function isTransientMysqlConnectionError(\Throwable $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'server has gone away')
            || str_contains($msg, 'lost connection')
            || str_contains($msg, 'reading greeting packet')
            || str_contains($msg, '[2006]')
            || str_contains($msg, '2006');
    }

    private function processRow($row, $force, $dryRun)
    {
        $variantId = $row['id'];
        $mainImageUrl = trim($row['image_link'] ?? '');
        $additionalImages = trim($row['additional_image_link'] ?? '');

        $variant = ProductVariant::with('product')->where('sku', $variantId)->first();

        if (!$variant) {
            $this->warn("Variant not found: {$variantId}");
            $this->stats['skipped']++;
            return;
        }

        $this->info("Processing: {$variant->product->name} - {$variant->variant_name}");

        // Process main image
        if (!empty($mainImageUrl)) {
            $this->processImage($variant, $mainImageUrl, 'main', $force, $dryRun);
        }

        // Process additional images
        if (!empty($additionalImages)) {
            $imageUrls = array_map('trim', explode('|', $additionalImages));
            foreach ($imageUrls as $index => $imageUrl) {
                if (!empty($imageUrl)) {
                    $this->processImage($variant, $imageUrl, "additional_{$index}", $force, $dryRun);
                }
            }
        }
    }

    private function processImage($variant, $imageUrl, $type, $force, $dryRun)
    {
        try {
            // Check if image already exists
            $existingMedia = Media::where('model_id', $variant->id)
                ->where('model_type', ProductVariant::class)
                ->where('collection_name', $type)
                ->first();

            if ($existingMedia && !$force) {
                $this->warn("  Image already exists: {$type}");
                $this->stats['skipped']++;
                return;
            }

            if ($dryRun) {
                $this->info("  Would download: {$imageUrl} as {$type}");
                return;
            }

            // Download and add to media library
            $this->downloadAndAddToMedia($variant, $imageUrl, $type);

        } catch (\Throwable $e) {
            if ($this->isTransientMysqlConnectionError($e)) {
                throw $e;
            }

            $this->error("  Error processing image {$type}: " . $e->getMessage());
            $this->stats['errors']++;
            Log::error("Image sync error", [
                'variant_id' => $variant->id,
                'image_url' => $imageUrl,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function downloadAndAddToMedia($variant, $imageUrl, $type)
    {
        try {
            // Avoid holding a DB connection idle during slow HTTP (reduces wait_timeout / gone away).
            DB::disconnect();

            // Download image with timeout
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'SweetHome-ImageSync/1.0',
                ])
                ->get($imageUrl);

            if (!$response->successful()) {
                throw new \Exception("HTTP {$response->status()} - Failed to download");
            }

            $imageContent = $response->body();

            if (empty($imageContent)) {
                throw new \Exception("Empty response received");
            }

            // Validate image
            if (!$this->validateImage($imageContent)) {
                throw new \Exception("Invalid image format");
            }

            // Generate filename
            $filename = $this->generateFilename($variant, $imageUrl, $type);

            // Add to media library
            $media = $variant->addMediaFromUrl($imageUrl)
                ->usingName($this->generateName($variant, $type))
                ->usingFileName($filename)
                ->toMediaCollection($type);

            $this->info("  ✓ Downloaded {$type}: {$filename}");
            $this->stats['downloaded']++;

        } catch (\Exception $e) {
            throw new \Exception("Download failed: " . $e->getMessage());
        }
    }

    private function validateImage($content)
    {
        $magicBytes = [
            "\xFF\xD8\xFF" => 'JPEG',
            "\x89PNG\r\n\x1a\n" => 'PNG',
            'GIF' => 'GIF',
            'RIFF' => 'WEBP', // RIFF header for WebP
        ];

        foreach ($magicBytes as $bytes => $format) {
            if (substr($content, 0, strlen($bytes)) === $bytes) {
                return true;
            }
        }

        return false;
    }

    private function generateFilename($variant, $imageUrl, $type)
    {
        $extension = strtolower(pathinfo(parse_url($imageUrl)['path'], PATHINFO_EXTENSION));
        $extension = $extension ?: 'jpg'; // Default to jpg if no extension

        $baseName = Str::slug("{$variant->product->name}-{$variant->variant_name}-{$type}");
        return "{$baseName}-v{$variant->id}.{$extension}";
    }

    private function generateName($variant, $type)
    {
        return "{$variant->product->name} - {$variant->variant_name} ({$type})";
    }

    private function displaySummary()
    {
        $this->info(PHP_EOL . "=== Image Sync Summary ===");
        $this->info("Processed: {$this->stats['processed']}");
        $this->info("Downloaded: {$this->stats['downloaded']}");
        $this->info("Skipped: {$this->stats['skipped']}");
        $this->info("Errors: {$this->stats['errors']}");

        if ($this->stats['errors'] > 0) {
            $this->warn("Check storage/logs/laravel.log for detailed error information");
        }
    }
}