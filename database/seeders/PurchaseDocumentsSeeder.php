<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample PR + PO rows for workflows that need linked documents (e.g. SerializedProductSeeder).
 */
class PurchaseDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        $supplier = Supplier::query()->first();

        if (!$user || !$supplier) {
            $this->command->warn('PurchaseDocumentsSeeder: need at least one employee user and one supplier. Skipping.');

            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            $pr = PurchaseRequest::create([
                'request_number' => 'PR-SEED-' . now()->format('Ymd') . "-{$i}-" . Str::lower(Str::random(4)),
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'supplier_id' => $supplier->id,
                'status_id' => 3,
                'order_date' => now()->toDateString(),
                'remarks' => 'Seeded purchase request ' . $i,
            ]);

            PurchaseOrder::create([
                'po_number' => 'PO-SEED-' . now()->format('Ymd') . "-{$i}-" . Str::lower(Str::random(4)),
                'purchase_request_id' => $pr->id,
                'supplier_id' => $supplier->id,
                'approved_by' => $user->id,
                'requested_by' => $user->id,
                'order_date' => now()->toDateString(),
                'delivery_date' => now()->addDays(7)->toDateString(),
                'payment_terms' => 'cash_on_delivery',
                'remarks' => 'Seeded purchase order ' . $i,
                'status' => 'pending_scan',
                'grand_total' => 1000 * $i,
            ]);
        }
    }
}
