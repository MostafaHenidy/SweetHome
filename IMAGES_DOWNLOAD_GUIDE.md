# Product Images Download Guide

I've created a complete solution to download and display product images from your CSV file. Here's everything you need:

## 📁 Files Created

### 1. Artisan Commands
- **`app/Console/Commands/SyncProductImages.php`** - Main command using Spatie Media Library
- **`app/Console/Commands/DownloadProductImages.php`** - Alternative command with basic storage

### 2. Standalone Script
- **`scripts/download_product_images.php`** - Run without Artisan
- **`scripts/README_IMAGES.md`** - Detailed documentation

### 3. Helper & Views
- **`app/Helpers/ImageHelper.php`** - Helper for image display
- **`resources/views/products/show.blade.php`** - Example product page

### 4. Updated Models
- **`app/Models/ProductVariant.php`** - Added Spatie Media Library support
- **`app/Models/ProductVariantOption.php`** - Created for variant options

## 🚀 Quick Start

### Step 1: Download Images
```bash
# Test with dry-run first
php artisan images:sync --dry-run --limit=5

# Download all images (use --limit for testing)
php artisan images:sync --limit=10

# Download all images from CSV
php artisan images:sync
```

### Step 2: Use in Views
```php
use App\Helpers\ImageHelper;

// Get main image for a product
$mainImage = ImageHelper::getProductMainImage($product);

// Get all images for a variant
$images = ImageHelper::getVariantImages($variant);

// Check if product has images
$hasImages = ImageHelper::hasImages($product);
```

### Step 3: Display in Blade Templates
```blade
<!-- Main product image -->
<img src="{{ ImageHelper::getProductMainImage($product) }}" alt="{{ $product->name }}">

<!-- Variant images -->
@foreach($product->variants as $variant)
    @if(ImageHelper::getVariantMainImage($variant))
        <img src="{{ ImageHelper::getVariantMainImage($variant) }}" alt="{{ $variant->variant_name }}">
    @endif
@endforeach
```

## 📊 Current Status

After testing, the system has successfully:
- ✅ Downloaded **3 images** for the first product
- ✅ Stored them in Spatie Media Library
- ✅ Associated with the correct product variant
- ✅ Created proper relationships

## 🎯 CSV Processing

Your CSV file (`gfgff.csv`) contains:
- **6,740 rows** of product data
- **Image URLs** in `image_link` column
- **Additional images** in `additional_image_link` (pipe-separated)
- **Variant SKUs** in the `id` column

The script matches these SKUs with your database variants and downloads the corresponding images.

## ⚙️ Configuration

### Image Storage
Images are stored using Spatie Media Library:
- **Location**: `storage/app/public/media/`
- **Public Access**: Via symbolic links in `public/storage/`
- **Collections**: 
  - `main` - Main product/variant images
  - `additional_X` - Additional images (indexed)

### Processing Options
```bash
--csv=/path/to/file.csv    # Specify custom CSV file
--limit=N                  # Process only N products
--force                    # Re-download existing images
--dry-run                  # Preview without downloading
```

## 🖼️ Image Display Examples

### Product Page (Complete Example)
See `resources/views/products/show.blade.php` for a full implementation including:
- Main image display
- Variant thumbnail gallery
- Additional images grid
- Fallback placeholders

### Cart Page Integration
Your cart page (`cart-page.blade.php`) already has image display logic. Update the cart logic to use:
```php
// In your cart Livewire component
$image = ImageHelper::getVariantMainImage($variant);
```

## 🔧 Troubleshooting

### Common Issues

1. **"Variant not found" errors**
   - Ensure CSV SKU matches database variant SKUs
   - Check: `php artisan tinker --execute="echo ProductVariant::count();"`

2. **Memory issues with large CSV**
   - Use `--limit` to process in batches
   - Example: `for i in {0..10}; do php artisan images:sync --limit=100 --offset=$((i*100)); done`

3. **Image download failures**
   - Check network connectivity to image URLs
   - Verify URLs are accessible
   - Check `storage/logs/laravel.log` for details

### Verify Setup
```bash
# Check if images are being stored
php artisan tinker --execute="use App\\Models\\ProductVariant; echo ProductVariant::with('media')->first()->media->count();"

# Check storage directory
find storage/app/public -name "*.jpeg" -o -name "*.png" | wc -l
```

## 📈 Next Steps

1. **Complete the download**:
   ```bash
   php artisan images:sync --limit=100  # Test with 100 products
   php artisan images:sync              # Process all
   ```

2. **Update your product pages** to use the ImageHelper

3. **Optimize images** by adding conversions to the ProductVariant model:
   ```php
   public function registerMediaConversions(Media $media = null): void
   {
       $this->addMediaConversion('thumbnail')->width(150)->height(150);
       $this->addMediaConversion('medium')->width(300)->height(300);
       $this->addMediaConversion('large')->width(600)->height(600);
   }
   ```

## 🎨 Image Display Options

The ImageHelper provides these methods:
- `getProductMainImage($product)` - Main image for product
- `getVariantMainImage($variant)` - Main image for variant
- `getVariantImages($variant)` - All images for variant
- `getVariantAdditionalImages($variant)` - Only additional images
- `getAllProductImages($product)` - Complete image collection
- `hasImages($model)` - Check if images exist

## 📝 Notes

- Images are downloaded once and stored permanently
- The script skips already-downloaded images (unless `--force` is used)
- Failed downloads are logged but don't stop the process
- Spatie Media Library handles all image management automatically

The system is now ready to download and display all your product images!