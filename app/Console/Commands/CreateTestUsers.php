<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-test {--admin : Create admin user} {--customer : Create customer user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for user management testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('admin') && ! $this->option('customer')) {
            $this->info('Creating both admin and customer test users...');
            $this->createAdminUser();
            $this->createCustomerUser();
        } else {
            if ($this->option('admin')) {
                $this->createAdminUser();
            }
            if ($this->option('customer')) {
                $this->createCustomerUser();
            }
        }

        $this->info('Test users created successfully!');
    }

    protected function createAdminUser()
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone' => '+391234567890',
                'points_balance' => 1000,
            ]
        );

        $this->info('Admin user: admin@example.com / password');
    }

    protected function createCustomerUser()
    {
        $user = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
                'phone' => '+390987654321',
                'points_balance' => 500,
            ]
        );

        $this->info('Customer user: customer@example.com / password');
    }
}
