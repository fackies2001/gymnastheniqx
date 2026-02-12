<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Linisin ang lahat ng tables na kailangan natin
        $tables = ['users', 'user', 'employee', 'role', 'warehouse', 'source'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. SEED SOURCE (Importante: Ito ang dahilan ng Foreign Key error mo)
        DB::table('source')->insert([
            ['id' => 1, 'name' => 'Default', 'created_at' => now()],
            ['id' => 2, 'name' => 'Student', 'created_at' => now()],
            ['id' => 3, 'name' => 'Admin/Staff', 'created_at' => now()],
        ]);

        // 3. SEED ROLE
        $roleId = DB::table('role')->insertGetId([
            'role_name'  => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. SEED WAREHOUSE (Para hindi "No Warehouse" sa sidebar)
        $warehouseId = DB::table('warehouse')->insertGetId([
            'name'        => 'Main Warehouse',
            'location'    => 'Default Location',
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 5. GUMAWA NG EMPLOYEE (Dito natin ikakabit ang warehouse_id)
        $employeeId = DB::table('employee')->insertGetId([
            'full_name'     => 'Vincent Admin',
            'email'         => 'admin@gmail.com',
            'username'      => 'admin_vincent',
            'role_id'       => $roleId,
            'status'        => 'active',
            'assigned_at'   => null,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // 6. GUMAWA NG ADMIN USER
        User::create([
            'name'              => 'Vincent Admin',
            'email'             => 'admin@gmail.com',
            'password'          => Hash::make('admin123'),
            'employee_id'       => $employeeId,
            'email_verified_at' => now(),
            'is_student'        => 0,
            'remember_token'    => Str::random(10),
        ]);
    }
}
