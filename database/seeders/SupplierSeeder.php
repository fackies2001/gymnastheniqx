<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\SupplierApis;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1. DUMMY JSON SUPPLIER
         */
        $dummySupplier = Supplier::create([
            'name' => 'Dummy Gym Supplier',
            'email' => 'dummy@gym.com',
            'phone' => '123-456-7890',
            'address' => '123 Fitness St, Muscle City, Fitland',
            'created_by' => 1,
            'source_id' => 1
        ]);

        SupplierApis::create([
            'supplier_id' => $dummySupplier->id,
            'api_url' => 'https://dummyjson.com/products/category/sports-accessories',
            'headers' => null,
            'service_class' => 'App\\Services\\Suppliers\\DummyGymSupplierService'
        ]);

        Supplier::factory()->count(1)->create();
        /**
         * 2. RAPIDAPI SUPPLIER
         */
        // $rapidSupplier = Suppliers::create([
        //     'name' => 'Mobile Device Supplier',
        //     'email' => 'supplier@mobileapi.com',
        //     'phone' => '+1-555-999-8888',
        //     'address' => '456 Silicon Valley, Tech City, USA',
        // ]);

        // SupplierApis::create([
        //     'supplier_id' => $rapidSupplier->id,
        //     'api_url' => env('RAPIDAPI_DEVICE_URL'), // e.g. https://mobile-device-hardware-cpu-mem-database.p.rapidapi.com/devices?name=acer
        //     'headers' => json_encode([
        //         'x-rapidapi-key' => env('RAPIDAPI_MOBILE_DB_KEY'),
        //         'x-rapidapi-host' => env('RAPIDAPI_MOBILE_DB_HOST'),
        //         'token' => env('RAPIDAPI_MOBILE_DB_TOKEN')
        //     ]),
        //     'service_class' => 'App\\Services\\Suppliers\\MobileDeviceSupplierService'
        // ]);

        /**
         * 3. ADD OTHER 49 RANDOM SUPPLIERS
         */
        // $suppliers = Suppliers::factory()->count(49)->create();

        // foreach ($suppliers as $supplier) {
        //     SupplierApis::factory()->create([
        //         'supplier_id' => $supplier->id,
        //     ]);
        // }
    }
}
