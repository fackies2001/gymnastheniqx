<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $fullName = $this->faker->name();

        return [
            'full_name' => $fullName,
            'email' => $this->faker->unique()->safeEmail(),
            'username' => $this->faker->unique()->userName(),
            'role_id' => Role::query()->inRandomOrder()->first()?->id,
            'department_id' => Department::query()->inRandomOrder()->first()?->id,
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'date_hired' => $this->faker->date(),
            'profile_photo' => 'https://api.dicebear.com/9.x/bottts/svg?seed=' . urlencode($fullName),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'assigned_at' => Warehouse::query()->inRandomOrder()->first()?->id,
            'last_login_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
