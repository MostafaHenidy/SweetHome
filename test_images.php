<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Helpers\ImageHelper;

echo "Testing product images...\n";

// Get products with images
$products = Product::query()
    ->published()
    ->with(['activeCoupons', 'variants.media', 'coupons' => function($q) {
        $q->where('type', 'product_specific')->active();
    }])
    ->whereHas('variants.media')
    ->limit(5)
    ->get();

echo "Found " . $products->count() . " products with images\n\n";

foreach ($products as $product) {
    echo "Product: " . $product->name . "\n";
    echo "Variants: " . $product->variants->count() . "\n";

    // Count total media
    $totalMedia = $product->variants->sum(function($v) { return $v->media->count(); });
    echo "Total images: " . $totalMedia . "\n";

    // Get image URL
    $image = ImageHelper::getProductMainImage($product);
    echo "Main image: " . ($image ?: 'NOT FOUND') . "\n";

    // Check if file exists
    if ($image) {
        $path = parse_url($image, PHP_URL_PATH);
        $file = public_path(ltrim($path, '/'));
        echo "File path: " . $file . "\n";
        echo "File exists: " . (file_exists($file) ? 'YES' : 'NO') . "\n";

        // Alternative check using storage path
        $altPath = str_replace('/storage/', '/storage/app/public/', $path);
        $altFile = base_path(ltrim($altPath, '/'));
        echo "Alt path: " . $altFile . "\n";
        echo "Alt exists: " . (file_exists($altFile) ? 'YES' : 'NO') . "\n";
    }

    echo "---\n";
}

echo "Done!\n";