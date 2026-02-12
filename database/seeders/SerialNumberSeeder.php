<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SerialNumber;
use App\Models\Purchases;

class SerialNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SerialNumber::factory()->count(600)->create();
    }
}
