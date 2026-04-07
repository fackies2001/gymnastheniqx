<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'supplier_code' => 'SUP-F-' . strtoupper($this->faker->unique()->bothify('????##')),
            'name' => $this->faker->unique()->company(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'created_by' => User::query()->inRandomOrder()->first()?->id,
            'source_id' => 1,
        ];
    }
}
