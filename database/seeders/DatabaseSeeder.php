<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
            PaymentTermSeeder::class,
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

        $this->call([
            WarehouseSeeder::class,
            EmployeeSeeder::class,
            SupplierSeeder::class,
            SupplierApisSeeder::class,
            SupplierProductSeeder::class,
            SupplierBarcodesSeeder::class,
            PurchaseDocumentsSeeder::class,
            PurchaseOrderSeeder::class,
            SerializedProductSeeder::class,
            TestProductSeeder::class,
            PurchaseRequestSeeder::class,
            PersonalAccessTokenSeeder::class,
            ReportDataSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('Admin: admin@test.com / password123 (PIN 123456)');
        $this->command->info('Optional: php artisan db:seed --class=VerifyReportTablesSeeder (schema check — may warn on legacy column names).');
        $this->command->info('Not included: UserSeeder (truncates core tables), SerialNumberSeeder (factory targets wrong schema).');
    }
}
