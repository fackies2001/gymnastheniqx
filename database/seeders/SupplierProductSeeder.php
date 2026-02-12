<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplierProduct;
use App\Models\SupplierBarcodes;

class SupplierProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 Supplier Products
        SupplierProduct::factory()->count(50)->create();
    }
}
