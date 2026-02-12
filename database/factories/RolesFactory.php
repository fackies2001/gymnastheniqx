<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RolesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role_name' => $this->faker->unique()->randomElement(['admin', 'staff', 'manager']),
        ];
    }
}
