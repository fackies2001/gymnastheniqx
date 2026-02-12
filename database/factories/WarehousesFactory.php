<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehousesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company . ' Warehouse',
            'location' => fake()->address,
            'description' => fake()->sentence(),
            'is_active' => fake()->boolean(90), // 90% chance active
        ];
    }
}
