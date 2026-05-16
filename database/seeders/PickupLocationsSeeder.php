<?php

namespace Database\Seeders;

use App\Models\PickupLocation;
use Illuminate\Database\Seeder;

class PickupLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Magazzino Principale Milano',
                'address' => 'Via delle Industrie, 123',
                'city' => 'Milano',
                'province' => 'MI',
                'postal_code' => '20138',
                'country' => 'IT',
                'phone' => '328 899 5628',
                'email' => 'milano@sweethome.it',
                'hours' => 'Lun-Ven: 9:00-18:00, Sab: 9:00-13:00',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Showroom Roma',
                'address' => 'Via Nazionale, 125',
                'city' => 'Roma',
                'province' => 'RM',
                'postal_code' => '00184',
                'country' => 'IT',
                'phone' => '328 899 5629',
                'email' => 'roma@sweethome.it',
                'hours' => 'Lun-Sab: 10:00-19:00',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Magazzino Napoli',
                'address' => 'Via San Giovanni a Teduccio, 87',
                'city' => 'Napoli',
                'province' => 'NA',
                'postal_code' => '80147',
                'country' => 'IT',
                'phone' => '328 899 5630',
                'email' => 'napoli@sweethome.it',
                'hours' => 'Lun-Ven: 9:00-17:00, Sab: 9:00-12:00',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($locations as $location) {
            PickupLocation::create($location);
        }

        $this->command->info('Pickup locations seeded successfully!');
    }
}