<?php

namespace Database\Factories;

use App\Models\SupplierProducts;
use App\Models\Suppliers;
use App\Models\SupplierApis;
use Illuminate\Database\Eloquent\Factories\Factory;


class SupplierBarcodesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Suppliers::inRandomOrder()->first()?->id,
            'sku_id' => SupplierProducts::inRandomOrder()->first()?->id,
            'barcode' => $this->faker->unique()->ean13,
        ];
    }
}
