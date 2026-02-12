<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentsFactory extends Factory
{
    protected $model = \App\Models\Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company, // Example: 'Marketing', 'Finance'
            'description' => $this->faker->sentence(),
        ];
    }
}
