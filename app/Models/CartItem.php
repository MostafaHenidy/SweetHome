<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'user_id', 'product_id', 'attribute_selection', 'quantity',
    ];

    protected $casts = [
        'attribute_selection' => 'array',
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }
}
