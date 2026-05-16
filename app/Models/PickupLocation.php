<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'phone',
        'email',
        'hours',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get active pickup locations
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get formatted address for display
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [];

        if ($this->address) {
            $parts[] = $this->address;
        }

        if ($this->postal_code && $this->city) {
            $parts[] = "{$this->postal_code} {$this->city}";
        } elseif ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->province) {
            $parts[] = $this->province;
        }

        if ($this->country) {
            $parts[] = $this->country;
        }

        return implode(', ', $parts);
    }

    /**
     * Get display name with address for dropdown
     */
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->name} - {$this->city}";
    }
}