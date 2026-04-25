<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProductFilter;
use App\Models\ProductImage;
use App\Models\ProductLabel;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'sku', 'name', 'slug', 'short_description', 'description',
        'price', 'compare_price', 'cost_price', 'brand_id',
        'stock_quantity', 'track_stock', 'status', 'sort_order',
        'gender', 'product_type', 'meta_title', 'meta_description',
        'focus_keyword', 'weight', 'length', 'width', 'height',
        'shipping_class', 'free_shipping', 'enable_reviews',
        'is_taxable', 'is_featured', 'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
        'free_shipping' => 'boolean',
        'enable_reviews' => 'boolean',
        'is_taxable' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantOptions(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    public function filters(): HasMany
    {
        return $this->hasMany(ProductFilter::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(ProductLabel::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function activeCoupons(): HasMany
    {
        return $this->coupons()->active();
    }

    public function getFeaturedImageAttribute(): ?string
    {
        if ($this->relationLoaded('images')) {
            $first = $this->images->sortBy('sort_order')->first();
        } else {
            $first = $this->images()->orderBy('sort_order')->first();
        }
        return $first?->url;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeWithActiveDiscounts(Builder $query): Builder
    {
        return $query->withExists([
            'coupons as has_active_discount' => fn (Builder $query) => $query
                ->where('type', 'product_specific')
                ->active(),
        ]);
    }

    public function scopeOrderByDiscounts(Builder $query): Builder
    {
        $now = now()->toDateTimeString();

        return $query->orderByRaw("
            CASE
                WHEN EXISTS (
                    SELECT 1 FROM coupons
                    WHERE coupons.product_id = products.id
                    AND coupons.type = 'product_specific'
                    AND coupons.is_active = 1
                    AND (coupons.starts_at IS NULL OR coupons.starts_at <= ?)
                    AND (coupons.expires_at IS NULL OR coupons.expires_at > ?)
                    AND (coupons.max_uses IS NULL OR coupons.used_count < coupons.max_uses)
                )
                THEN 0
                ELSE 1
            END
        ", [$now, $now]);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '€' . number_format($this->price, 2, ',', '.');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->withResponsiveImages();

        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->crop('crop-center');

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->crop('crop-center');
    }
}
