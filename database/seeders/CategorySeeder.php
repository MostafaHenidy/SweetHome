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
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'level' => 1,
                'active' => true,
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'level' => 2,
                    'active' => true,
                ]);
            }
        }
    }
}
