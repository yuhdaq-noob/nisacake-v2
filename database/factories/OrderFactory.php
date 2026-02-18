<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'order_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement([OrderStatus::COMPLETED->value, OrderStatus::CANCELLED->value]),
            'total_price' => 0,  // Akan dihitung dari order items
            'total_hpp' => 0,    // Akan dihitung dari order items
        ];
    }

    /**
     * Untuk order yang sudah selesai
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'order_date' => fake()->dateTimeBetween('-6 months', '-1 day'),
            ];
        });
    }

    /**
     * Untuk order bulan terakhir
     */
    public function recentOrders()
    {
        return $this->state(function (array $attributes) {
            return [
                'order_date' => fake()->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }
}
