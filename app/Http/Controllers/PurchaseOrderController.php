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
        $user     = Auth::user();
        $userRole = strtolower($user->role?->role_name ?? '');
        $isAdmin  = $userRole === 'admin';

        $purchaseOrders = PurchaseOrder::with([
            'supplier',
            'approvedBy',
            'requestedBy'
        ])
            ->when(!$isAdmin, function ($query) use ($user) {
                // Staff at Manager — sarili lang (requested_by = user_id)
                $query->where('requested_by', $user->id);
            })
            ->orderBy('id', 'desc')
            ->get();

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
            'barcode'   => 'required|string',
            'scan_type' => 'nullable|in:box,piece',
        ]);

        $scannedBarcode = $validated['barcode'];
        $scanType       = $validated['scan_type'] ?? 'piece';

        DB::beginTransaction();
        try {
            $po = PurchaseOrder::with('items.supplierProduct')
                ->lockForUpdate()
                ->findOrFail($id);

            Log::info('Scan attempt:', [
                'po_id'     => $id,
                'barcode'   => $scannedBarcode,
                'scan_type' => $scanType,
                'time'      => now(),
            ]);

            // ─────────────────────────────────────────────────────
            // 1. Hanapin ang product
            // ─────────────────────────────────────────────────────
            $product = \App\Models\SupplierProduct::where(function ($query) use ($scannedBarcode) {
                $query->where('barcode', $scannedBarcode)
                    ->orWhere('supplier_sku', $scannedBarcode)
                    ->orWhere('barcode', 'LIKE', $scannedBarcode . '%');
            })->first();

            // ✅ Fresh query para masigurado ang pieces_per_box value
            if ($product) {
                $product = \App\Models\SupplierProduct::find($product->id);
            }

            if (!$product) {
                DB::rollBack();
                Log::warning('Barcode not found:', ['barcode' => $scannedBarcode]);
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode not found. Please check if the product is registered.'
                ], 404);
            }

            // ─────────────────────────────────────────────────────
            // 2. I-check kung nasa PO ang product
            // ─────────────────────────────────────────────────────
            $poItem = $po->items->where('product_id', $product->id)->first();

            if (!$poItem) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Product "' . $product->name . '" is not in this purchase order.'
                ], 400);
            }

            // ─────────────────────────────────────────────────────
            // 3. Kalkulahin ang qty na idadagdag
            //    BOX   → pieces_per_box (e.g. 10)
            //    PIECE → 1
            // ─────────────────────────────────────────────────────
            // ✅ Force fresh read mula sa DB
            $freshProduct = \App\Models\SupplierProduct::find($product->id);
            $piecesPerBox = (int) ($freshProduct->pieces_per_box ?? 1);
            \Log::info('pieces_per_box value:', [
                'product_id'      => $product->id,
                'pieces_per_box'  => $freshProduct->pieces_per_box,
                'piecesPerBox'    => $piecesPerBox,
                'scan_type'       => $scanType,
            ]);

            $qtyToAdd     = ($scanType === 'box') ? $piecesPerBox : 1;
            $remaining    = $poItem->quantity_ordered - $poItem->quantity_scanned;

            // ─────────────────────────────────────────────────────
            // 4. Quantity validation
            // ─────────────────────────────────────────────────────
            if ($remaining <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity limit reached for "' . $product->name . '" (' . $poItem->quantity_ordered . ' max)'
                ], 400);
            }

            if ($qtyToAdd > $remaining) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Box scan would exceed ordered quantity! "
                        . "Only {$remaining} piece(s) remaining but 1 box = {$piecesPerBox} pieces. "
                        . "I-scan na lang as PIECE."
                ], 400);
            }

            // ─────────────────────────────────────────────────────
            // 5. Duplicate scan guard (within 500ms)
            // ─────────────────────────────────────────────────────
            $recentScan = SerializedProduct::where('product_id', $product->id)
                ->where('purchase_order_id', $po->id)
                ->where('scanned_at', '>=', now()->subMilliseconds(500))
                ->exists();

            if ($recentScan) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate scan detected. Please wait before scanning again.'
                ], 400);
            }

            // ─────────────────────────────────────────────────────
            // 6. Gumawa ng serialized product records
            //
            //    BOX scan:
            //      - $qtyToAdd (e.g. 10) records
            //      - LAHAT = SAME serial number (iisang box)
            //      - Example: SN-BOX-ABCD1234 x10
            //
            //    PIECE scan:
            //      - 1 record lang
            //      - UNIQUE serial number
            //      - Example: SN-PC-ABCD1234
            // ─────────────────────────────────────────────────────
            $scannedBy   = auth()->user()->employee->id ?? null;
            $warehouseId = auth()->user()->employee->assigned_at ?? null;
            $scannedAt   = now();

            if ($scanType === 'box') {
                // ✅ BOX: Generate IISANG serial number para sa buong box
                // Lahat ng pieces sa box na ito = same serial number
                $boxSerialNumber = 'SN-BOX-' . strtoupper(uniqid('', true));

                for ($i = 0; $i < $qtyToAdd; $i++) {
                    SerializedProduct::create([
                        'product_id'        => $product->id,
                        'purchase_order_id' => $po->id,
                        'barcode'           => $product->barcode,
                        'serial_number'     => $boxSerialNumber, // ← SAME para sa lahat
                        'status'            => 1,
                        'scanned_at'        => $scannedAt,
                        'scanned_by'        => $scannedBy,
                        'warehouse_id'      => $warehouseId,
                    ]);
                }

                Log::info('BOX scanned:', [
                    'product'       => $product->name,
                    'serial_number' => $boxSerialNumber,
                    'qty_added'     => $qtyToAdd,
                ]);
            } else {
                // ✅ PIECE: Generate UNIQUE serial number
                $pieceSerialNumber = 'SN-PC-' . strtoupper(uniqid('', true));

                SerializedProduct::create([
                    'product_id'        => $product->id,
                    'purchase_order_id' => $po->id,
                    'barcode'           => $product->barcode,
                    'serial_number'     => $pieceSerialNumber, // ← UNIQUE
                    'status'            => 1,
                    'scanned_at'        => $scannedAt,
                    'scanned_by'        => $scannedBy,
                    'warehouse_id'      => $warehouseId,
                ]);

                Log::info('PIECE scanned:', [
                    'product'       => $product->name,
                    'serial_number' => $pieceSerialNumber,
                ]);
            }

            // ─────────────────────────────────────────────────────
            // 6.5 ✅ I-record ang StockMovement (type = 'in')
            // Para lumabas sa Daily Received ng Reports
            // ─────────────────────────────────────────────────────
            $employeeWarehouseId = $po->warehouse_id
                ?? \App\Models\ConsumableStock::where('product_id', $product->id)
                ->value('warehouse_id')
                ?? 9; // ← fallback sa warehouse 9 (default mo based sa DB)

            if ($product->is_consumable) {
                // Consumable — i-record sa StockMovement
                \App\Models\StockMovement::record([
                    'product_id'        => $product->id,
                    'warehouse_id'      => $employeeWarehouseId,
                    'type'              => \App\Models\StockMovement::TYPE_IN,
                    'quantity'          => $qtyToAdd,
                    'reason_type'       => \App\Models\StockMovement::REASON_RECEIVED,
                    'remarks'           => 'Received via PO scan - ' . $po->po_number,
                    'purchase_order_id' => $po->id,
                    'created_by'        => auth()->id(),
                ]);
            } else {
                // Non-consumable — record lang sa StockMovement, hindi mag-a-update ng ConsumableStock
                \App\Models\StockMovement::create([
                    'product_id'        => $product->id,
                    'warehouse_id'      => $employeeWarehouseId,
                    'type'              => \App\Models\StockMovement::TYPE_IN,
                    'quantity'          => $qtyToAdd,
                    'reason_type'       => \App\Models\StockMovement::REASON_RECEIVED,
                    'remarks'           => 'Received via PO scan - ' . $po->po_number,
                    'purchase_order_id' => $po->id,
                    'created_by'        => auth()->id(),
                ]);
            }

            // ─────────────────────────────────────────────────────
            // 7. I-update ang quantity_scanned ng PO item
            // ─────────────────────────────────────────────────────
            $poItem->increment('quantity_scanned', $qtyToAdd);
            $poItem->refresh();

            $progress = ($poItem->quantity_scanned / $poItem->quantity_ordered) * 100;

            // ─────────────────────────────────────────────────────
            // 8. I-check kung kumpleto na ang lahat ng PO items
            // ─────────────────────────────────────────────────────
            $po->load('items');
            $allComplete = $po->items->every(
                fn($item) => $item->quantity_scanned >= $item->quantity_ordered
            );

            if ($allComplete) {
                $po->update(['status' => 'completed']);
                Log::info('PO completed:', ['po_id' => $po->id]);
            }

            DB::commit();

            // ─────────────────────────────────────────────────────
            // 9. Response
            // ─────────────────────────────────────────────────────
            $message = $scanType === 'box'
                ? "📦 Box scanned! {$qtyToAdd} pieces added (1 box = {$piecesPerBox} pcs)"
                : '✅ Piece scanned successfully!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => [
                    'id'               => $product->id,
                    'name'             => $product->name,
                    'image_url'        => $product->thumbnail ?? '/images/no-image.png',
                    'barcode'          => $product->barcode,
                    'unit_cost'        => $poItem->unit_cost,
                    'quantity_scanned' => $poItem->quantity_scanned,
                    'quantity_ordered' => $poItem->quantity_ordered,
                    'progress'         => round($progress, 2),
                    'scan_type'        => $scanType,
                    'qty_added'        => $qtyToAdd,
                    'pieces_per_box'   => $piecesPerBox,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Scan Item Error:', [
                'barcode' => $scannedBarcode,
                'po_id'   => $id,
                'error'   => $e->getMessage(),
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
