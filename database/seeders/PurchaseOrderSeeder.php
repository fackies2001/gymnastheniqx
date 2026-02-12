<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Get all unique Purchase Requests that are eligible for a PO
        $purchaseRequests = PurchaseRequest::whereNotIn('status_id', [1, 7, 8])
            ->doesntHave('purchaseOrder')
            ->get();

        foreach ($purchaseRequests as $purchaseRequest) {

            // Skip PRs that already have a PO
            if ($purchaseRequest->purchaseOrder) {
                continue;
            }

            // Create a PO linked to this PR
            PurchaseOrder::factory()->create([
                'request_id' => $purchaseRequest->id,        // link by ID
            ]);
        }
    }
}
