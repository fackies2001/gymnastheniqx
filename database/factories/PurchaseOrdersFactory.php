<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Suppliers;
use App\Models\Employees;
use App\Models\PaymentTerms;
use App\Models\PurchaseRequests;

class PurchaseOrdersFactory extends Factory
{
    public function definition(): array
    {
        $orderDate = $this->faker->date(); // generate order date
        $deliveryDate = $this->faker->dateTimeBetween($orderDate, '+1 month')->format('Y-m-d');

        return [
            'po_number' => 'PO-' . now()->year . '-' . $this->faker->unique()->numerify('###'),
            'supplier_id' => Suppliers::inRandomOrder()->first()?->id,
            'payment_term_id' => PaymentTerms::inRandomOrder()->first()?->id,
            'approved_by' => Employees::inRandomOrder()->first()?->id,
            'order_date' => $orderDate,
            'delivery_date' => $deliveryDate,
            'remarks' => $this->faker->sentence(),
            // 'request_id' => PurchaseRequests::inRandomOrder()->first()?->id, // link to valid PR if needed
        ];
    }
}
