<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointsConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'points_per_euro',
        'points_per_order_percentage',
        'is_active',
    ];

    protected $casts = [
        'points_per_euro' => 'decimal:2',
        'points_per_order_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (PointsConfiguration $configuration): void {
            if ($configuration->is_active) {
                static::whereKeyNot($configuration->getKey())->update(['is_active' => false]);
            }
        });
    }

    public static function active(): self
    {
        return static::where('is_active', true)->latest()->first()
            ?? static::create([
                'points_per_euro' => 100,
                'points_per_order_percentage' => 5,
                'is_active' => true,
            ]);
    }
}
