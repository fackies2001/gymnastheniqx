<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['id' => 1, 'name' => 'Direct/Manual', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Student', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Employee', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('source')->insertOrIgnore($sources);
    }
}
