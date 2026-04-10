<?php

namespace Database\Seeders;

use App\Models\SupplierBarcodes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SupplierBarcodesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('supplier_barcodes')) {
            $this->command?->warn('SupplierBarcodesSeeder: table supplier_barcodes does not exist. Skipping.');

            return;
        }

        SupplierBarcodes::factory()->count(20)->create();
    }
}
