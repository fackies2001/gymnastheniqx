<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\SerializedProduct;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestProductSeeder extends Seeder
{
    public function run(): void
    {
        $product = SupplierProduct::query()->first();
        $po = PurchaseOrder::query()->first();
        $user = User::query()->first();

        if (!$product || !$po || !$user) {
            $this->command->warn('TestProductSeeder: need SupplierProduct, PurchaseOrder, and User. Skipping.');

            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            SerializedProduct::query()->create([
                'product_id' => $product->id,
                'purchase_order_id' => $po->id,
                'serial_number' => 'GYM-TEST-' . random_int(1000, 9999),
                'barcode' => '88' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT),
                'status' => 1,
                'scanned_by' => $user->id,
                'scanned_at' => now(),
            ]);
        }

        $this->command->info('TestProductSeeder: added 5 serialized_product rows.');
    }
}
