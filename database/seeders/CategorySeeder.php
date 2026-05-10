<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Pavimenti' => [
                'Gres',
                'Parquet',
                'Pavimenti per esterni',
            ],
            'Battiscopa' => [
                'Battiscopa PVC',
                'Battiscopa MDF',
                'Battiscopa Legno',
                'Battiscopa Laminati',
                'Battiscopa Gres',
            ],
            'Rivestimenti' => [],
            'Mosaici' => [],
            'Arredo Bagno' => [
                'Mobili',
                'Accessori',
                'Complementi Bagno',
                'Specchi',
            ],
            'Sanitari' => [
                'Vasche',
                'Complementi Sanitari',
                'Bidet / WC / Lavabi',
            ],
            'Docce' => [
                'Piatti doccia',
                'Cabine doccia',
                'Box doccia',
            ],
            'Rubinetteria' => [
                'Rubinetteria Cucina',
                'Rubinetteria Bagno',
            ],
            'Riscaldamento' => [
                'Termoarredi',
                'Radiatori',
                'Complementi Riscaldamento',
            ],
            'Porte' => [],
            'Finestre' => [],
            'Maniglie' => [
                'Maniglie Contemporanee',
                'Maniglie a incasso',
                'Maniglioni',
                'Pomoli',
            ],
        ];

        foreach ($categories as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'level' => 1,
                    'active' => true,
                ]
            );

            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'level' => 2,
                        'active' => true,
                    ]
                );
            }
        }
    }
}
