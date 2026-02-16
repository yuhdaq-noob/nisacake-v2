<?php

namespace Database\Factories;

use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 100,
            'price_per_base_unit' => 100000,
            'current_stock' => 1000,
            'min_stock_level' => 10,
        ];
    }
}

