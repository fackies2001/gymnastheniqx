<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ STEP 1: Create roles FIRST
        $this->call([
            RoleSeeder::class,
        ]);

        // ✅ STEP 2: Seed Source table
        $this->call([
            SourceSeeder::class,
        ]);

        // ✅ STEP 3: Seed Purchase Status Library
        $this->call([
            PurchaseStatusLibrarySeeder::class,
        ]);

        // ✅ STEP 4: Seed Product Statuses (fixes FK constraint on serialized_product.status)
        $this->call([
            ProductStatusSeeder::class,
        ]);

        // ✅ STEP 5: Seed Categories (fixes Consumables not showing in dropdown)
        $this->call([
            CategorySeeder::class,
        ]);

        // ✅ STEP 6: Create admin account
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'full_name' => 'Admin Account',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'pin' => Hash::make('123456'),
                'status' => 'active',
                'role_id' => 1,
            ]
        );

        echo "\n✅ SUCCESS: Admin account created!\n";
        echo "   Username: admin\n";
        echo "   Email: admin@test.com\n";
        echo "   Password: password123\n";
        echo "   PIN: 123456\n\n";
    }
}
