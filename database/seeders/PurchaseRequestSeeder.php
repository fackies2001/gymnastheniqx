<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseRequests;
use App\Models\Suppliers;

//class PurchaseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   /**  public function run(): void
    {
        // Get first supplier or create one
        $supplier = Suppliers::first();

        if (!$supplier) {
            $supplier = Suppliers::create([
                'name' => 'Sample Supplier',
                'contact_person' => 'John Doe',
                'email' => 'supplier@example.com',
                'phone' => '1234567890',
            ]);
        }

        // Create sample purchase requests with different statuses
        $statuses = [
            1 => 5,  // 5 Pending
            2 => 3,  // 3 Reviewed
            3 => 7,  // 7 Approved
            4 => 2,  // 2 Rejected
        ];

        $counter = 1;
        foreach ($statuses as $statusId => $count) {
            for ($i = 0; $i < $count; $i++) {
                PurchaseRequests::create([
                    'request_number' => 'PR-' . date('Ymd') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'supplier_id' => $supplier->id,
                    'items' => json_encode([]),
                    'requested_by' => 1,
                    'status_id' => $statusId,
                ]);
                $counter++;
            }
        }

        $this->command->info('✅ Created ' . ($counter - 1) . ' sample purchase requests!');
    }
}
