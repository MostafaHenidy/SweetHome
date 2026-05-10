<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
    ];

    protected $casts = [
        // Add any necessary casts here
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->address;
        if ($this->city) {
            $address .= ', '.$this->city;
        }
        if ($this->state) {
            $address .= ', '.$this->state;
        }
        if ($this->zip_code) {
            $address .= ' '.$this->zip_code;
        }
        if ($this->country) {
            $address .= ', '.$this->country;
        }

        return $address;
    }
}
