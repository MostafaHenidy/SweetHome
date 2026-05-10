<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'SH-'.date('Ymd').'-'.str_pad($this->faker->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'type' => 'direct',
            'user_id' => User::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->unique()->email(),
            'customer_phone' => $this->faker->phoneNumber(),
            'shipping_address' => [
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'province' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => 'IT',
            ],
            'subtotal' => $this->faker->randomFloat(2, 100, 10000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
            'tax' => 0,
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'pending',
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal', 'fawaterak', 'bank_transfer']),
            'payment_status' => 'pending',
        ];
    }
}