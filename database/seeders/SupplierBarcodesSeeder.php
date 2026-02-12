<?php

namespace Database\Seeders;

use App\Models\SupplierBarcodes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierBarcodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupplierBarcodes::factory()->count(20)->create();
    }
}
