<?php

namespace Database\Factories;

use App\Helpers\SkuHelper;
use App\Models\Employees;
use App\Models\ProductStatus;
use App\Models\PurchaseOrders;
use App\Models\Purchases;
use App\Models\SupplierProducts;
use App\Models\Warehouses;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SerialNumbers>
 */
class SerialNumbersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku_id' => SupplierProducts::inRandomOrder()->first()?->id,
            'serial_number' => SkuHelper::generateSystemSku('SRN'),
            'purchase_order_id' => PurchaseOrders::inRandomOrder()->first()?->id,
            'product_status_id' => ProductStatus::inRandomOrder()->first()?->id,
            'warehouse_id' => 1,
            'scanned_by' => Employees::inRandomOrder()->first()?->id,

            // 👇 RANDOM MONTH IN THE YEAR
            'created_at' => $this->faker->dateTimeBetween('2025-01-01', '2025-12-31'),
        ];
    }
}
