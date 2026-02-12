<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseStatusLibraryFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->unique()->randomElement([
            ['name' => 'Pending', 'code' => 'PENDING', 'color' => 'warning'],
            ['name' => 'Reviewed', 'code' => 'REVIEWED', 'color' => 'info'],
            ['name' => 'Approved', 'code' => 'APPROVED', 'color' => 'success'],
            ['name' => 'Partially Ordered', 'code' => 'PARTIAL_ORDER', 'color' => 'primary'],
            ['name' => 'Ordered', 'code' => 'ORDERED', 'color' => 'primary'],
            ['name' => 'Received', 'code' => 'RECEIVED', 'color' => 'success'],
            ['name' => 'Cancelled', 'code' => 'CANCELLED', 'color' => 'secondary'],
            ['name' => 'Rejected', 'code' => 'REJECTED', 'color' => 'danger'],
        ]);

        return [
            'name' => $status['name'],
            'code' => $status['code'],
            'color' => $status['color'],
            'description' => $this->faker->sentence(),
        ];
    }
}
