<?php

namespace Database\Factories;

use App\Http\Controllers\SuppliersController;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Suppliers;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupplierApis>
 */
class SupplierApisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = $this->faker->unique()->company;
        return [
            'supplier_id' => Suppliers::factory(), // Automatically creates related Supplier
            'api_url' => $this->faker->url,
            'service_class' => 'App\\Services\\Suppliers\\' . Str::studly($companyName) . 'SupplierService',
        ];
    }
}
