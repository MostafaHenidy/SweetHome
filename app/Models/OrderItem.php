<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_sku', 'line_description',
        'attribute_selection', 'quantity', 'unit_price', 'total', 'total_price', 'vat_rate',
    ];

    protected $casts = [
        'attribute_selection' => 'array',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'vat_rate' => 'decimal:2',
    ];

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->total;
    }

    public function setTotalPriceAttribute($value): void
    {
        $this->attributes['total'] = $value;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
