# Product Images Download Script

This script downloads product and variant images from a CSV file and associates them with products in your Laravel application.

## CSV Format

The script expects a CSV file with the following columns:
- `id`: Variant SKU (e.g., "wc_post_id_324")
- `title`: Product/variant name
- `image_link`: Main image URL
- `additional_image_link`: Additional image URLs separated by `|` pipe characters

## Usage

### Using Artisan Command (Recommended)

```bash
# Basic usage - downloads images from gfgff.csv
php artisan images:sync

# Specify custom CSV file
php artisan images:sync --csv=/path/to/your.csv

# Limit number of products to process
php artisan images:sync --limit=10

# Re-download existing images
php artisan images:sync --force

# Dry run (show what would be done without downloading)
php artisan images:sync --dry-run

# Combine options
php artisan images:sync --csv=/path/to/images.csv --limit=50 --force
```

### Using Standalone Script

```bash
# Basic usage
php scripts/download_product_images.php

# Specify custom CSV file
php scripts/download_product_images.php /path/to/your.csv

# With options
php scripts/download_product_images.php /path/to/images.csv --limit=10 --force
php scripts/download_product_images.php --dry-run --force

# Show help
php scripts/download_product_images.php --help
```

## Features

- **Smart downloading**: Only downloads images that don't exist
- **Image validation**: Checks file format before saving
- **Error handling**: Continues processing even if individual images fail
- **Progress tracking**: Shows detailed progress and summary
- **Dry run mode**: Preview what would be downloaded without making changes
- **Spatie Media Library**: Integrates with Laravel's media management

## Directory Structure

Downloaded images are stored in:
```
storage/app/public/
├── products/
│   ├── main/          # Main product images
│   ├── variants/      # Variant-specific images
│   └── additional/    # Additional product images
```

The script automatically creates symbolic links for public access.

## Database Integration

The script:
1. Finds variants by matching the CSV `id` column with variant `sku`
2. Downloads images using Spatie Media Library
3. Associates images with the correct product variant
4. Maintains image collections for different types (main, additional)

## Error Handling

- Failed downloads are logged and processing continues
- Check `storage/logs/laravel.log` for detailed error information
- Invalid images are skipped with a warning
- Network timeouts are handled gracefully

## Performance Tips

- Use `--limit` for testing with a small subset
- Run during off-peak hours for large batches
- Ensure adequate disk space for all images
- Check network connectivity for remote image URLs

## Troubleshooting

**Images not downloading:**
- Check CSV file format and column names
- Verify image URLs are accessible
- Check permissions on storage directories

**Variants not found:**
- Ensure product variants are seeded in the database
- Verify SKU format matches CSV `id` column

**Memory issues:**
- Use `--limit` to process in smaller batches
- Increase PHP memory limit if needed