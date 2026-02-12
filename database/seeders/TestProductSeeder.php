<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
// Palitan mo itong mga nasa baba depende sa totoong file name sa app/Models/
use App\Models\SupplierProducts;
use App\Models\PurchaseOrders;
use App\Models\SerializedProduct;
use App\Models\ProductStatus;

class TestProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Siguraduhin nating may Status (Halimbawa ID 1)
        // Kung wala kang ProductStatus Model, i-skip ito o manual ID gamitin

        // 2. Kumuha ng existing data para hindi mag-error sa Foreign Keys
        $product = \App\Models\SupplierProducts::first();
        $po = \App\Models\PurchaseOrders::first();
        $user = User::first();

        if (!$product || !$po) {
            $this->command->error("Wala pang laman ang SupplierProducts o PurchaseOrders table. Mag-add ka muna ng kahit isa sa UI.");
            return;
        }

        // 3. Gagawa tayo ng 5 Test Serialized Products
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\SerializedProduct::create([
                'supplier_product_id' => $product->id,
                'purchase_order_id'   => $po->id,
                'serial_number'       => 'GYM-TEST-' . rand(1000, 9999),
                'status_id'           => 1, // Siguraduhin mong may ID 1 sa product_statuses table
                'scanned_by'          => $user->id,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        $this->command->info("Success! 5 Test products added to Gymnastheniqx.");
    }
}
