<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Finance', 'description' => 'Handles all financial matters'],
            ['name' => 'IT', 'description' => 'Information Technology Department'],
            ['name' => 'Marketing', 'description' => 'Marketing and Promotion Department'],
            ['name' => 'Operations', 'description' => 'Daily operations management'],
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
