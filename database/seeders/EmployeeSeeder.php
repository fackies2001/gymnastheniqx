<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Add your 3 specific employees
        $employees = [
            [
                'full_name' => 'Reiniel Andres',
                'email' => 'reinielpardinesandres@gmail.com',
                'username' => 'reinielandres',
                'role_id' => 1, // or assign logic with Roles::first()->id
                'assigned_at' => 1,
            ],
            [
                'full_name' => 'Jarrie',
                'email' => 'jarrie@gmail.com',
                'username' => 'jarrie',
                'role_id' => 1, // or assign logic with Roles::first()->id
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'John Vincent Fabay',
                'email' => 'fabayjohnvincent@gmail.com',
                'username' => 'johnvfabay',
                'role_id' => 2, // or assign logic with Roles::first()->id
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'Zack Vincent Magado',
                'email' => 'zackvincentmagado@gmail.com',
                'username' => 'zackmagado',
                'role_id' => 3, // or assign logic with Roles::first()->id
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'Sampol Langto',
                'email' => 'sampollangto@gmail.com',
                'username' => 'sampollangto',
                'role_id' => 4, // or assign logic with Roles::first()->id
                'assigned_at' => 2,
            ],
        ];

        foreach ($employees as $data) {
            Employee::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'department_id' => Department::query()->inRandomOrder()->first()?->id,
                    'contact_number' => '09123456789',
                    'address' => 'Default Address',
                    'date_hired' => now(),
                    'profile_photo' => 'https://randomuser.me/api/portraits/men/' . rand(1, 99) . '.jpg',
                    'status' => 'active',
                    'last_login_at' => now(),
                ])
            );
        }

    }
}
