<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            UserManagementSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            InHouseProductSeeder::class,
            SettingSeeder::class,
            PointsConfigurationSeeder::class,
            ProductCsvSeeder::class,
        ]);
    }
}
