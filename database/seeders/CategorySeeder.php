<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // 

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Gym Equipment
        Category::firstOrCreate(
            ['name' => 'Gym Equipment'],
            ['description' => 'Equipment like dumbbells, barbells, and machines.']
        );

        // 2. Consumables
        Category::firstOrCreate(
            ['name' => 'Consumables'],
            ['description' => 'Items that are used up like chalk, tapes, sprays, etc.']
        );

        // Dagdagan mo pa dito kung may iba ka pang category...
    }
}
