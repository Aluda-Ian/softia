<?php

namespace Database\Factories;

use App\Enums\CustomerType;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => OrderType::Order,
            'status' => OrderStatus::Pending,
            'customer_type' => CustomerType::Retail,
            'customer_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '+2547'.fake()->numerify('########'),
            'delivery_address' => fake()->address(),
            'payment_method' => PaymentMethod::PayOnDelivery,
            'payment_status' => PaymentStatus::Unpaid,
            'subtotal' => 1000,
            'delivery_fee' => 0,
            'total' => 1000,
        ];
    }

    public function quote(): static
    {
        return $this->state([
            'type' => OrderType::Quote,
            'customer_type' => CustomerType::Business,
            'company_name' => fake()->company(),
            'payment_method' => PaymentMethod::Invoice,
        ]);
    }
}
