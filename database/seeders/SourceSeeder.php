<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $source = [
            ['id' => 1, 'name' => 'API', 'description' => 'Imported from api'],
            ['id' => 2, 'name' => 'Manual Student Creation', 'description' => 'Created Locally'],
            ['id' => 3, 'name' => 'Manual Company Creation', 'description' => 'Created Locally'],
        ];

        foreach ($source as $source) {
            // Siguraduhing 'source' ang table name dito
            \DB::table('source')->updateOrInsert(['id' => $source['id']], $source);
        }
    }
}
