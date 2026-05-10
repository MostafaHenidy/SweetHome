<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PaymentInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentInstallmentFactory extends Factory
{
    protected $model = PaymentInstallment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'installment_number' => 1,
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'percentage' => $this->faker->numberBetween(10, 100),
            'due_stage' => $this->faker->randomElement(['deposit', 'on_delivery', 'on_construction']),
            'status' => 'pending',
        ];
    }
}