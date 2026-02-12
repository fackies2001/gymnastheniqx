<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{SerializedProduct, SupplierProduct, PurchaseOrder, ProductStatus, User};
use Carbon\Carbon;

class SerializedProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if required data exists
        $supplierProducts = SupplierProduct::all();
        $purchaseOrders = PurchaseOrder::all();
        $productStatuses = ProductStatus::all();
        $users = User::all();

        if ($supplierProducts->isEmpty()) {
            $this->command->error('❌ No SupplierProduct records found. Please run SupplierProductSeeder first.');
            return;
        }

        if ($purchaseOrders->isEmpty()) {
            $this->command->error('❌ No PurchaseOrder records found. Please run PurchaseOrderSeeder first.');
            return;
        }

        if ($productStatuses->isEmpty()) {
            $this->command->error('❌ No ProductStatus records found. Please run ProductStatusSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('❌ No User records found. Please run UserSeeder first.');
            return;
        }

        $this->command->info('🚀 Starting SerializedProduct seeding...');

        // Generate realistic serialized products
        $serializedProducts = [];
        $usedSerialNumbers = [];
        $usedBarcodes = [];

        // ✅ Create 100 units PER product
        foreach ($supplierProducts as $supplierProduct) {
            $this->command->info("Creating 100 units for: {$supplierProduct->name}");

            for ($i = 1; $i <= 100; $i++) {
                $purchaseOrder = $purchaseOrders->random();
                $status = $productStatuses->random();
                $user = $users->random();

                // Generate unique serial number based on product SKU
                do {
                    $serialNumber = $this->generateSerialNumber($supplierProduct->system_sku);
                } while (in_array($serialNumber, $usedSerialNumbers));

                $usedSerialNumbers[] = $serialNumber;

                // Generate unique barcode
                do {
                    $barcode = $this->generateBarcode();
                } while (in_array($barcode, $usedBarcodes));

                $usedBarcodes[] = $barcode;

                // Random scanned date within the last 6 months
                $scannedAt = Carbon::now()->subDays(rand(0, 180));

                $serializedProducts[] = [
                    'product_id' => $supplierProduct->id,
                    'purchase_order_id' => $purchaseOrder->id,
                    'barcode' => $barcode,
                    'serial_number' => $serialNumber,
                    'status' => $status->id,
                    'scanned_by' => $user->id,
                    'scanned_at' => $scannedAt,
                    'created_at' => $scannedAt,
                    'updated_at' => Carbon::now(),
                ];

                // Insert in batches of 50 for performance
                if (count($serializedProducts) >= 50) {
                    DB::table('serialized_product')->insert($serializedProducts);
                    $this->command->info("✅ Inserted " . count($serializedProducts) . " records...");
                    $serializedProducts = [];
                }
            }
        }

        // Insert remaining records
        if (!empty($serializedProducts)) {
            DB::table('serialized_product')->insert($serializedProducts);
            $this->command->info("✅ Inserted " . count($serializedProducts) . " records...");
        }

        $totalRecords = $supplierProducts->count() * 100;
        $this->command->info('🎉 SerializedProduct seeding completed successfully!');
        $this->command->info("📊 Total records created: {$totalRecords} (100 per product)");
    }

    /**
     * Generate a unique serial number based on SKU
     */
    private function generateSerialNumber(?string $sku): string
    {
        $prefix = $sku ? strtoupper(substr($sku, 0, 3)) : 'SER';
        $timestamp = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Generate a random barcode
     */
    private function generateBarcode(): string
    {
        return 'BC-' . rand(100000000000, 999999999999);
    }
}
