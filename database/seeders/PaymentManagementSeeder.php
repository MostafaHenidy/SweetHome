<?php

namespace Database\Seeders;

use App\Models\InstallmentPolicy;
use Illuminate\Database\Seeder;

class PaymentManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding payment management policies...');

        // Create standard installment policies
        $policies = [
            [
                'name' => 'Standard 3 Rate',
                'description' => 'Rateizzazione standard in 3 rate senza interessi',
                'max_installments' => 3,
                'min_order_amount' => 100.00,
                'interest_rate' => 0.00,
            ],
            [
                'name' => 'Rateizzazione 6 Rate',
                'description' => 'Rateizzazione in 6 rate senza interessi',
                'max_installments' => 6,
                'min_order_amount' => 500.00,
                'interest_rate' => 0.00,
            ],
            [
                'name' => 'Rateizzazione 12 Rate',
                'description' => 'Rateizzazione in 12 rate con piccolo interesse',
                'max_installments' => 12,
                'min_order_amount' => 1000.00,
                'interest_rate' => 2.50,
            ],
            [
                'name' => 'Rateizzazione Lungo Termine',
                'description' => 'Rateizzazione fino a 24 rate per ordini di grandi dimensioni',
                'max_installments' => 24,
                'min_order_amount' => 5000.00,
                'interest_rate' => 5.00,
            ],
        ];

        foreach ($policies as $policyData) {
            $policy = InstallmentPolicy::updateOrCreate(
                ['name' => $policyData['name']],
                $policyData
            );
            $this->command->info("Created/Updated policy: {$policyData['name']}");
        }

        $this->command->info('Payment management seeder completed!');
    }
}