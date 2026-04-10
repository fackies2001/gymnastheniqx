<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierApis;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SupplierApis>
 */
class SupplierApisFactory extends Factory
{
    protected $model = SupplierApis::class;

    public function definition(): array
    {
        $companyName = $this->faker->unique()->company();

        return [
            'supplier_id' => Supplier::factory(),
            'api_url' => $this->faker->url(),
            'headers' => null,
            'service_class' => 'App\\Services\\Suppliers\\' . Str::studly($companyName) . 'SupplierService',
        ];
    }
}
