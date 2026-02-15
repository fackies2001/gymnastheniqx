<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\SerializedProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * Display listing with latest first
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with([
            'supplier',
            'approvedBy',
            'requestedBy'
        ])->orderBy('id', 'desc')->get();

        return view('purchase-order.index', compact('purchaseOrders'));
    }

    /**
     * Store new purchase order from approved PR
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_request_id' => 'required|exists:purchase_request,id',
        ]);

        DB::beginTransaction();
        try {
            $pr = PurchaseRequest::with('items')->findOrFail($validated['purchase_request_id']);

            // Verify PR is approved
            if ($pr->status_id !== 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase request must be approved first'
                ], 400);
            }

            // Generate PO Number
            $poNumber = $this->generatePONumber();

            // Calculate grand total
            $grandTotal = $pr->items->sum('subtotal');

            // Create Purchase Order
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $pr->id,
                'supplier_id' => $pr->supplier_id,
                'approved_by' => $pr->approved_by,
                'requested_by' => $pr->user_id,
                'order_date' => $pr->order_date ?? now(),
                'delivery_date' => $pr->estimated_delivery_date ?? now()->addDays(7),
                'payment_terms' => $pr->payment_terms ?? 'cash_on_delivery',
                'remarks' => $pr->remarks,
                'grand_total' => $grandTotal,
                'status' => 'pending_scan',
            ]);

            // Create PO Items
            foreach ($pr->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                    'quantity_scanned' => 0,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->subtotal,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'po_id' => $po->id,
                'po_number' => $poNumber
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PO Store Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show purchase order details
     */
    public function show($id)
    {
        try {
            $po = PurchaseOrder::with([
                'supplier',
                'approvedBy',
                'requestedBy',
                'items.supplierProduct',
                'purchaseRequest.department'
            ])->findOrFail($id);

            return response()->json($po);
        } catch (\Exception $e) {
            Log::error('PO Show Error:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found'
            ], 404);
        }
    }

    /**
     * Show scan view for purchase order
     */
    public function scanView($id)
    {
        try {
            $po = PurchaseOrder::with([
                'items.supplierProduct',
                'supplier'
            ])->findOrFail($id);

            return view('purchase-order.scan', compact('po'));
        } catch (\Exception $e) {
            return redirect()->route('purchase-order.index')
                ->with('error', 'Purchase order not found');
        }
    }

    /**
     * ✅ FIXED: Scan item barcode - PREVENTS DUPLICATES
     */
    public function scanItem(Request $request, $id)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        // ✅ START TRANSACTION to prevent race conditions
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::with('items.supplierProduct')->lockForUpdate()->findOrFail($id);

            Log::info('Scan attempt:', [
                'po_id' => $id,
                'barcode' => $validated['barcode'],
                'timestamp' => now()
            ]);

            // Find product by barcode OR supplier_sku
            $product = \App\Models\SupplierProduct::where(function ($query) use ($validated) {
                $query->where('barcode', $validated['barcode'])
                    ->orWhere('supplier_sku', $validated['barcode'])
                    ->orWhere('barcode', 'LIKE', $validated['barcode'] . '%'); // ⭐ Partial match
            })
                ->first();

            if (!$product) {
                DB::rollBack();
                Log::warning('Barcode not found:', ['barcode' => $validated['barcode']]);
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode not found in product list'
                ], 404);
            }

            // Check if product is in this PO
            $poItem = $po->items->where('product_id', $product->id)->first();

            if (!$poItem) {
                DB::rollBack();
                Log::warning('Product not in PO:', [
                    'product_id' => $product->id,
                    'po_id' => $id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Product not in this purchase order'
                ], 400);
            }

            // ✅ CRITICAL: Check if quantity limit reached
            if ($poItem->quantity_scanned >= $poItem->quantity_ordered) {
                DB::rollBack();
                Log::warning('Quantity limit reached:', [
                    'product' => $product->name,
                    'scanned' => $poItem->quantity_scanned,
                    'ordered' => $poItem->quantity_ordered
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity limit reached for this product (' . $poItem->quantity_ordered . ' max)'
                ], 400);
            }

            // ✅ FIXED: Check for recent duplicate scans (within 2 seconds)
            $recentScan = SerializedProduct::where('product_id', $product->id)
                ->where('purchase_order_id', $po->id)
                ->where('scanned_at', '>=', now()->subMilliseconds(500)) // Changed from 2 seconds
                ->exists();

            if ($recentScan) {
                DB::rollBack();
                Log::warning('Duplicate scan detected:', [
                    'product' => $product->name,
                    'barcode' => $validated['barcode']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate scan detected. Please wait before scanning again.'
                ], 400);
            }

            // ✅ CREATE serialized product entry
            $serialNumber = 'SN-' . strtoupper(uniqid());

            // ⭐⭐⭐ NEW CODE - GET USER INFO ⭐⭐⭐
            $scannedBy = auth()->user()->employee->id ?? null;
            $warehouseId = auth()->user()->employee->assigned_at ?? null;

            $serializedProduct = SerializedProduct::create([
                'product_id' => $product->id,
                'purchase_order_id' => $po->id,
                'barcode' => $product->barcode,
                'serial_number' => $serialNumber,
                'status' => 1,
                'scanned_at' => now(),
                'scanned_by' => $scannedBy,        // ⭐ NEW
                'warehouse_id' => $warehouseId,    // ⭐ NEW
            ]);

            Log::info('Serialized product created:', [
                'id' => $serializedProduct->id,
                'serial_number' => $serialNumber,
                'scanned_by' => $scannedBy,
                'warehouse_id' => $warehouseId
            ]);

            // ✅ UPDATE PO item quantity
            $poItem->increment('quantity_scanned');
            $poItem->refresh();

            // Calculate progress
            $progress = ($poItem->quantity_scanned / $poItem->quantity_ordered) * 100;

            // ✅ Check if ALL items in PO are complete
            $allComplete = $po->items->every(function ($item) {
                return $item->quantity_scanned >= $item->quantity_ordered;
            });

            if ($allComplete) {
                $po->update(['status' => 'completed']);
                Log::info('PO completed:', ['po_id' => $po->id]);
            }

            DB::commit();

            Log::info('Scan successful:', [
                'product' => $product->name,
                'scanned' => $poItem->quantity_scanned,
                'ordered' => $poItem->quantity_ordered,
                'progress' => $progress
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item scanned successfully',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_url' => $product->thumbnail ?? '/images/no-image.png',
                    'barcode' => $product->barcode,
                    'cost_price' => $poItem->unit_cost,
                    'unit_cost' => $poItem->unit_cost,
                    'quantity_scanned' => $poItem->quantity_scanned,
                    'quantity_ordered' => $poItem->quantity_ordered,
                    'progress' => round($progress, 2),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Scan Item Error:', [
                'barcode' => $validated['barcode'],
                'po_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error scanning item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete scanning process
     */
    public function completeScan($id)
    {
        DB::beginTransaction();
        try {
            $po = PurchaseOrder::with('items')->findOrFail($id);

            // Verify all items are scanned
            foreach ($po->items as $item) {
                if ($item->quantity_scanned < $item->quantity_ordered) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Not all items have been scanned'
                    ], 400);
                }
            }

            // Update PO status
            $po->update(['status' => 'completed']);

            // ✅ KEEP AS AVAILABLE - remove the status update
            // SerializedProduct::where('purchase_order_id', $po->id)
            //     ->update(['status' => 2]);

            DB::commit();

            Log::info('Scanning completed:', ['po_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Scanning completed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Complete scan error:', [
                'po_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error completing scan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique PO number
     */
    private function generatePONumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PO-{$year}{$month}-";

        $lastPO = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($lastPO) {
            $lastNumber = intval(substr($lastPO->po_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    /**
     * ⭐ ENHANCED: Get PO details with COMPLETE supplier information
     */
    public function getDetailsJson($id)
    {
        try {
            $po = PurchaseOrder::with([
                'supplier',
                'items.supplierProduct',
                'approvedBy',
                'requestedBy',
                'purchaseRequest.department',
                'purchaseRequest.user'
            ])->findOrFail($id);

            $department = 'N/A';
            if ($po->purchaseRequest && $po->purchaseRequest->department) {
                $department = $po->purchaseRequest->department->name ?? 'N/A';
            }

            return response()->json([
                'success' => true,
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => [
                    'name' => $po->supplier->name ?? 'N/A',
                    'contact_person' => $po->supplier->contact_person ?? 'N/A',
                    'contact_number' => $po->supplier->contact_number ?? 'N/A',
                    'email' => $po->supplier->email ?? 'N/A',
                    'address' => $po->supplier->address ?? 'N/A',
                ],
                'requested_by' => [
                    'name' => $po->requestedBy->full_name ?? 'N/A'
                ],
                'department' => $department,
                'approved_by' => [
                    'name' => $po->approvedBy->full_name ?? 'N/A'
                ],
                'items' => $po->items->map(function ($item) {
                    return [
                        'product' => [
                            'name' => $item->supplierProduct->name ?? 'Unknown Product'
                        ],
                        'quantity' => $item->quantity_ordered ?? 0,
                        'unit_cost' => $item->unit_cost ?? 0,
                        'subtotal' => $item->subtotal ?? 0
                    ];
                }),
                'grand_total' => $po->grand_total ?? 0,
                'order_date' => $po->order_date ? $po->order_date->format('m/d/Y') : 'N/A',
                'delivery_date' => $po->delivery_date ? $po->delivery_date->format('m/d/Y') : 'N/A',
                'payment_terms' => $po->payment_terms ? ucwords(str_replace('_', ' ', $po->payment_terms)) : 'N/A',
                'remarks' => $po->remarks ?? 'No remarks provided.'
            ]);
        } catch (\Exception $e) {
            Log::error('PO Details Error:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Purchase Order not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get supplier products
     */
    public function getSupplierProducts($supplierId)
    {
        try {
            $products = \App\Models\SupplierProduct::where('supplier_id', $supplierId)->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get products: ' . $e->getMessage()
            ], 500);
        }
    }
}
