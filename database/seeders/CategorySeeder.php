<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Consumables only
        Category::firstOrCreate(
            ['name' => 'Consumables'],
            ['description' => 'Items that are used up like chalk, tapes, sprays, etc.']
        );
    }
}
