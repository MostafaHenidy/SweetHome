<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'sku', 'name', 'slug', 'short_description', 'description',
        'price', 'compare_price', 'cost_price', 'brand_id',
        'stock_quantity', 'track_stock', 'status', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
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
