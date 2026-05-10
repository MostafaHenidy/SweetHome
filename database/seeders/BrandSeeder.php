<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'LA FABBRICA AVA',
            'PANARIA CERAMICHE',
            'MGM CERAMICHE',
            'ITALWOOD DESIGN',
            'EXPOSERVICE',
            'OFFICINE ITALIANE PARQUET',
            'CERAMICHE DOLOMITE',
            'VITRUVIT',
            'COLOMBO DESIGN',
        ];

        foreach ($brands as $name) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'active' => true,
                ]
            );
        }
    }
}
