<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone' => '+391234567890',
                'points_balance' => 1000,
            ]
        );

        // Create test customer if not exists
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone' => '+390987654321',
                'points_balance' => 500,
            ]
        );

        // Create a few more test customers
        $customers = [
            [
                'name' => 'Mario Rossi',
                'email' => 'mario.rossi@example.com',
                'phone' => '+393331234567',
                'points_balance' => 250,
            ],
            [
                'name' => 'Giulia Bianchi',
                'email' => 'giulia.bianchi@example.com',
                'phone' => '+393337654321',
                'points_balance' => 750,
            ],
        ];

        foreach ($customers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                array_merge($customer, [
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command->info('User Management seeder completed!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Customer: customer@example.com / password');
    }
}
