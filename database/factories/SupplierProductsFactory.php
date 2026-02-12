<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupplierProducts;
use App\Models\Suppliers;
use App\Models\Categories;

class SupplierProductsFactory extends Factory
{
    protected $model = SupplierProducts::class;

    public function definition(): array
    {
        // 1. KUNIN MUNA ANG CATEGORY
        // Siguraduhin natin na kukuha tayo ng existing category o gagawa ng bago
        $category = Categories::inRandomOrder()->first();

        // Kung walang category sa database, gumawa ng default
        if (!$category) {
            $category = Categories::factory()->create();
        }

        // 2. LISTAHAN NG MGA PANGALAN
        $gymItems = [
            'Rubber Hex Dumbbell',

        ];

        $consumableItems = [
            'Whey Protein',

        ];

        // 3. LOGIC: PUMILI NG PANGALAN BASE SA CATEGORY
        $productName = '';

        // Check kung ang category name ay may "Equip" o "Gym"
        if (stripos($category->name, 'Equip') !== false || stripos($category->name, 'Gym') !== false) {
            $productName = $this->faker->randomElement($gymItems);
        }
        // Check kung ang category name ay "Consumable"
        elseif (stripos($category->name, 'Consum') !== false) {
            $productName = $this->faker->randomElement($consumableItems);
        }
        // Default kung sakaling iba ang category name
        else {
            $productName = $this->faker->randomElement(array_merge($gymItems, $consumableItems));
        }

        return [
            'supplier_id' => Suppliers::inRandomOrder()->first()?->id ?? Suppliers::factory(),

            // Importante: Gamitin ang ID ng category na chineck natin sa taas
            'category_id' => $category->id,

            // Ito yung pangalan na naka-match na sa category
            'name' => $productName,

            'supplier_sku' => 'SUP-' . $this->faker->unique()->bothify('###??'),
            'system_sku' => 'SYS-' . $this->faker->unique()->bothify('###??'),
            'cost_price' => $this->faker->randomFloat(2, 500, 20000),
            'discount' => $this->faker->randomFloat(2, 0, 500),
            'stock' => $this->faker->numberBetween(0, 100),
            'availability_status' => $this->faker->randomElement(['In Stock', 'Out of Stock']),
            'shipping_information' => 'Ships in 3-5 days',
            'warranty_information' => 'Standard Warranty',
            'return_policy' => '7 days return',
            'dimensions' => json_encode(['width' => 10, 'height' => 10, 'depth' => 10]),
            'barcode' => $this->faker->ean13(),
            'thumbnail' => null,
            'images' => json_encode([]),
            'source_id' => 1,
        ];
    }
}
