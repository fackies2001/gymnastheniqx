<?php

namespace App\Services;

use App\Models\PurchaseOrders;
use App\Models\PurchaseRequests;
use App\Models\SerialNumbers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    /**
     * Creates or updates PO with received items & serials
     */
    public function createOrUpdate(array $data, ?int $poId = null): PurchaseOrders
    {
        return DB::transaction(function () use ($data, $poId) {
            $pr = PurchaseRequests::findOrFail($data['request_id'] ?? 0);

            if ($pr->status_id !== 3) {
                throw new \Exception('Purchase Request must be approved first.');
            }

            $po = $poId ? PurchaseOrders::findOrFail($poId) : new PurchaseOrders();

            $po->fill([
                'po_number'     => $data['po_number'],
                'supplier_id'   => $pr->supplier_id,
                'request_id'    => $pr->id,
                'order_date'    => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'remarks'       => $data['remarks'] ?? null,
                'total_amount'  => collect($data['received_items'] ?? [])->sum(fn($item) => $item['received_qty'] * ($item['unit_cost'] ?? 0)),
            ]);

            $po->received_items = $data['received_items'] ?? [];
            $po->save();

            // Create serial numbers
            foreach ($po->received_items as $item) {
                $productId = $item['supplier_product_id'];
                $receivedQty = $item['received_qty'] ?? 0;

                // Optional: validate received <= ordered
                $prItem = collect($pr->items)->firstWhere('supplier_product_id', $productId);
                if ($prItem && $receivedQty > ($prItem['quantity'] ?? 0)) {
                    throw new \Exception("Received quantity exceeds ordered for product ID {$productId}");
                }

                for ($i = 0; $i < $receivedQty; $i++) {
                    SerialNumbers::create([
                        'sku_id'             => $productId,
                        'serial_number'      => $item['serial_numbers'][$i] ?? 'AUTO-' . uniqid(),
                        'purchase_order_id'  => $po->id,
                        'product_status_id'  => 1, // assuming 1 = Available
                        'scanned_by'         => auth()->user()->employee?->id,
                    ]);
                }
            }

            return $po;
        });
    }
}
