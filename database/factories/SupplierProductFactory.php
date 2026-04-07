<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProduct>
 */
class SupplierProductFactory extends Factory
{
    protected $model = SupplierProduct::class;

    public function definition(): array
    {
        $category = Category::query()->inRandomOrder()->first();
        $supplier = Supplier::query()->inRandomOrder()->first();

        return [
            'supplier_id' => $supplier?->id ?? Supplier::factory(),
            'category_id' => $category?->id ?? 1,
            'name' => $this->faker->words(3, true),
            'is_consumable' => false,
            'pieces_per_box' => 1,
            'supplier_sku' => 'SUP-' . $this->faker->unique()->bothify('###??'),
            'system_sku' => 'SYS-' . $this->faker->unique()->bothify('####??'),
            'cost_price' => $this->faker->randomFloat(2, 100, 5000),
            'selling_price' => $this->faker->randomFloat(2, 150, 6000),
            'stock' => $this->faker->numberBetween(0, 100),
            'barcode' => $this->faker->unique()->ean13(),
            'images' => '[]',
            'dimensions' => json_encode(['width' => 10, 'height' => 10, 'depth' => 10]),
        ];
    }
}
