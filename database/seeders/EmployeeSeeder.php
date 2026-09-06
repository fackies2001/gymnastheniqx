<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = \App\Models\Role::where('role_name', 'admin')->value('id') ?? 3;
        $accountStaffRole = \App\Models\Role::where('role_name', 'account staff')->value('id') ?? 1;
        $staffRole = \App\Models\Role::where('role_name', 'staff')->value('id') ?? 2;
        $requestorRole = \App\Models\Role::where('role_name', 'requestor')->value('id') ?? 4;

        $employees = [
            [
                'full_name' => 'Reiniel Andres',
                'email' => 'reinielpardinesandres@gmail.com',
                'username' => 'reinielandres',
                'role_id' => $accountStaffRole,
                'assigned_at' => 1,
            ],
            [
                'full_name' => 'Jarrie',
                'email' => 'jarrie@gmail.com',
                'username' => 'jarrie',
                'role_id' => $accountStaffRole,
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'John Vincent Fabay',
                'email' => 'fabayjohnvincent@gmail.com',
                'username' => 'johnvfabay',
                'role_id' => $adminRole,
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'Zack Vincent Magado',
                'email' => 'zackvincentmagado@gmail.com',
                'username' => 'zackmagado',
                'role_id' => $adminRole,
                'assigned_at' => 2,
            ],
            [
                'full_name' => 'Sampol Langto',
                'email' => 'sampollangto@gmail.com',
                'username' => 'sampollangto',
                'role_id' => $requestorRole,
                'assigned_at' => 2,
            ],
        ];

        foreach ($employees as $data) {
            Employee::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('password123'),
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
