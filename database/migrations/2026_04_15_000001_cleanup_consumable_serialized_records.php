<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ✅ CLEANUP: Delete old serialized_product records for CONSUMABLE products.
 *
 * Dati, lahat ng product — consumable man o hindi — ay ginagawan ng
 * SerializedProduct record kapag na-scan sa PO. Mali ito para sa consumables
 * kasi hindi naman kailangan ng serial number/traceability passport ang
 * mga food items, supplies, etc.
 *
 * Ngayon fixed na: consumable products = ConsumableStock + StockMovement lang.
 * Kaya need natin i-delete yung old serialized records para malinis ang data.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Get all consumable product IDs
        $consumableProductIds = DB::table('supplier_product')
            ->where('is_consumable', true)
            ->pluck('id')
            ->toArray();

        if (empty($consumableProductIds)) {
            Log::info('Cleanup: No consumable products found. Nothing to clean.');
            return;
        }

        // 2. Count how many serialized records will be deleted
        $count = DB::table('serialized_product')
            ->whereIn('product_id', $consumableProductIds)
            ->count();

        Log::info("Cleanup: Found {$count} serialized records for " . count($consumableProductIds) . " consumable products.");

        if ($count === 0) {
            Log::info('Cleanup: No serialized records to delete. All clean!');
            return;
        }

        // 3. Delete the serialized records for consumable products
        $deleted = DB::table('serialized_product')
            ->whereIn('product_id', $consumableProductIds)
            ->delete();

        Log::info("Cleanup: Successfully deleted {$deleted} serialized records for consumable products.");
    }

    public function down(): void
    {
        // Cannot reverse — deleted records are gone.
        // The original data was incorrect (consumables should not have serialized records).
        Log::warning('Cleanup: Rollback not possible — serialized records for consumables were permanently deleted.');
    }
};
