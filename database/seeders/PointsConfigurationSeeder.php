<?php

namespace Database\Seeders;

use App\Models\PointsConfiguration;
use Illuminate\Database\Seeder;

class PointsConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        PointsConfiguration::firstOrCreate(
            ['is_active' => true],
            [
                'points_per_euro' => 100,
                'points_per_order_percentage' => 5,
            ],
        );
    }
}
