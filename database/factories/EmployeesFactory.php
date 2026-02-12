<?php

namespace Database\Factories;

use App\Models\Roles;
use App\Models\Departments;
use App\Models\Warehouses;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeesFactory extends Factory
{
    public function definition(): array
    {
        $fullName = $this->faker->name;

        return [
            'full_name' => $fullName,
            'email' => $this->faker->unique()->safeEmail,
            'username' => $this->faker->unique()->userName,
            'role_id' => Roles::inRandomOrder()->first()?->id, // Nullable
            'department_id' => Departments::inRandomOrder()->first()?->id, // Nullable
            'contact_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'date_hired' => $this->faker->date(),
            'profile_photo' => 'https://api.dicebear.com/9.x/bottts/svg?seed=' . urlencode($fullName),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'assigned_at' => Warehouses::inRandomOrder()->first()?->id,
            'last_login_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
