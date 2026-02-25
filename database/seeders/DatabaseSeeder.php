<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SourceSeeder::class,
            PurchaseStatusLibrarySeeder::class,
            ProductStatusSeeder::class,
            CategorySeeder::class,
            DepartmentSeeder::class,
        ]);

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
        echo "   Email: admin@test.com\n";
        echo "   Password: password123\n";
        echo "   PIN: 123456\n\n";
    }
}
