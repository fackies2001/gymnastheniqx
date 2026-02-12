<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Gawa muna tayo ng Employee Record
        DB::table('employee')->updateOrInsert(
            ['id' => 1],
            [
                'full_name' => 'Admin Account',
                'email' => 'admin@test.com',
                'username' => 'admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 2. Gawa tayo ng User Login Record
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'employee_id' => 1,
                'name' => 'Admin Account',
                'password' => Hash::make('password123'),
                'is_student' => 0,
                'pincode' => '1234'
            ]
        );

        echo "\n✅ SUCCESS: Pwede ka na mag-login! \n";

        // 3. ✅ CALL OTHER SEEDERS (if needed)
        $this->call([
            // Uncomment kung gusto mo i-run yung specific seeders
            // ProductStatusSeeder::class,
            // SupplierProductSeeder::class,
            // PurchaseOrderSeeder::class,
            SerializedProductSeeder::class,  // ✅ ADD THIS
        ]);
    }
}
