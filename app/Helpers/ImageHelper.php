<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\ProductVariant;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ImageHelper
{
    /**
     * Get the main image URL for a product
     */
    public static function getProductMainImage(Product $product, $conversion = '')
    {
        // Try to get image from the first variant
        $variant = $product->variants()->with('media')->first();
        if ($variant) {
            return self::getVariantMainImage($variant, $conversion);
        }

        // Fallback: try to get from product media
        $media = $product->getFirstMedia('main');
        if ($media) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }

        return null;
    }

    /**
     * Get the main image URL for a variant
     */
    public static function getVariantMainImage(ProductVariant $variant, $conversion = '')
    {
        $media = $variant->getFirstMedia('main');
        if ($media) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }

        return null;
    }

    /**
     * Get all images for a variant
     */
    public static function getVariantImages(ProductVariant $variant)
    {
        return $variant->getMedia();
    }

    /**
     * Get additional images for a variant
     */
    public static function getVariantAdditionalImages(ProductVariant $variant)
    {
        return $variant->getMedia('additional_%');
    }

    /**
     * Get image URL by collection name
     */
    public static function getImageByCollection(ProductVariant $variant, $collectionName, $conversion = '')
    {
        $media = $variant->getFirstMedia($collectionName);
        if ($media) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }

        return null;
    }

    /**
     * Check if a product/variant has images
     */
    public static function hasImages($model)
    {
        return $model->media->count() > 0;
    }

    /**
     * Get all image URLs for a product (including variants)
     */
    public static function getAllProductImages(Product $product)
    {
        $images = [];

        // Product-level images
        foreach ($product->getMedia() as $media) {
            $images[] = [
                'url' => $media->getUrl(),
                'collection' => $media->collection_name,
                'name' => $media->file_name,
            ];
        }

        // Variant-level images
        foreach ($product->variants as $variant) {
            foreach ($variant->getMedia() as $media) {
                $images[] = [
                    'url' => $media->getUrl(),
                    'collection' => $media->collection_name,
                    'name' => $media->file_name,
                    'variant' => $variant->variant_name,
                ];
            }
        }

        return $images;
    }

    /**
     * Get placeholder image URL when no image is available
     */
    public static function getPlaceholder($type = 'product')
    {
        $placeholders = [
            'product' => '/images/placeholder-product.jpg',
            'variant' => '/images/placeholder-variant.jpg',
            'category' => '/images/placeholder-category.jpg',
        ];

        return $placeholders[$type] ?? $placeholders['product'];
    }

    /**
     * Generate srcset for responsive images
     */
    public static function getSrcset(Media $media, $conversions = ['thumbnail', 'medium', 'large'])
    {
        $srcset = [];
        foreach ($conversions as $conversion) {
            if ($media->hasGeneratedConversion($conversion)) {
                $srcset[] = $media->getUrl($conversion) . ' ' . $this->getConversionWidth($conversion) . 'w';
            }
        }
        return implode(', ', $srcset);
    }

    /**
     * Get conversion width (helper for srcset)
     */
    private static function getConversionWidth($conversion)
    {
        $widths = [
            'thumbnail' => 150,
            'medium' => 300,
            'large' => 600,
            'xlarge' => 1200,
        ];
        return $widths[$conversion] ?? 600;
    }
}