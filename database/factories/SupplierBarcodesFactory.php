<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierBarcodes;
use App\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierBarcodes>
 */
class SupplierBarcodesFactory extends Factory
{
    protected $model = SupplierBarcodes::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::query()->inRandomOrder()->first()?->id ?? Supplier::factory(),
            'sku_id' => SupplierProduct::query()->inRandomOrder()->first()?->id ?? SupplierProduct::factory(),
            'barcode' => $this->faker->unique()->ean13(),
        ];
    }
}
