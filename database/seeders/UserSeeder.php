<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@sweethome.local',
            'password' => bcrypt('admin123'),
            'phone' => '+1234567890',
            'email_verified_at' => now(),
        ]);
    }
}
