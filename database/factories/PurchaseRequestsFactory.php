<?php

namespace Database\Factories;

use App\Models\Sources;
use App\Models\Suppliers;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupplierProducts;
use App\Models\Employees;
use App\Models\PurchaseStatusLibrary;
use Illuminate\Support\Str;

class PurchaseRequestsFactory extends Factory
{
    public function definition(): array
    {
        $itemsCount = $this->faker->numberBetween(1, 5); // 1–5 items per PR
        $items = [];

        for ($i = 0; $i < $itemsCount; $i++) {
            $product = SupplierProducts::inRandomOrder()->first();
            $quantity = $this->faker->numberBetween(1, 20);
            $cost_price = $product?->cost_price;
            $discount = round($cost_price * $this->faker->randomFloat(2, 0.05, 0.30), 2);
            $subtotal = round($quantity * $cost_price - $discount, 2);

            $items[] = [
                'supplier_product_id' => $product?->id,
                'quantity' => $quantity,
                'cost_price' => $cost_price,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'barcode' => $product?->barcode,
            ];
        }

        // Exclude status 9 for PRs not linked to a PO
        $statusId = PurchaseStatusLibrary::where('id', '!=', 9)->inRandomOrder()->first()?->id;

        return [
            'supplier_id' => Suppliers::inRandomOrder()->first()?->id,
            'request_number' => 'PR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'items' => $items, // store as JSON
            'requested_by' => Employees::inRandomOrder()->first()?->id,
            'status_id' => $statusId,
            // 'source_id' => 1,
        ];
    }
}
