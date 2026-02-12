<?php

namespace Database\Factories;

use App\Models\Sources;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employees;

class SuppliersFactory extends Factory
{
    public function definition(): array
    {
        $companyName = $this->faker->unique()->company;

        return [
            'name' => $companyName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'created_by' => Employees::inRandomOrder()->first()?->id,
            'source_id' => 2,
        ];
    }
}
