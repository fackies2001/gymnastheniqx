<?php

namespace Database\Seeders;

use App\Models\SupplierApis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierApisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupplierApis::factory()->count(20)->create();

    }
}
