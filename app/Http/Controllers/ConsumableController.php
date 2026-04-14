<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SupplierProduct;
use App\Models\ConsumableStock;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\RetailerOrder;

class ConsumableController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // INDEX — Stock Level Overview (replaces serialized_products index)
    // ROUTE: GET /consumables
    // VIEW: consumables/index.blade.php
    // ─────────────────────────────────────────────────────────
    public function index()
    {
        $warehouseId = auth()->user()->assigned_at;

        // ✅ Kuhanin lahat ng consumable products with current stock
        $stocks = ConsumableStock::with(['product.supplier', 'warehouse'])
            ->when($warehouseId, fn($q) => $q->forWarehouse($warehouseId))
            ->get();

        // ✅ Low stock count para sa dashboard card
        $lowStockCount = $stocks->filter(fn($s) => $s->isLowStock())->count();

        return view('consumables.index', compact('stocks', 'lowStockCount'));
    }

    // ─────────────────────────────────────────────────────────
    // SHOW — Movement history ng isang product
    // ROUTE: GET /consumables/{id}
    // VIEW: consumables/show.blade.php
    // ─────────────────────────────────────────────────────────
    public function show($id)
    {
        $product = SupplierProduct::where('is_consumable', true)
            ->findOrFail($id);

        $warehouseId = auth()->user()->assigned_at;

        $stock = ConsumableStock::where('product_id', $id)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->first();

        $movements = StockMovement::with(['createdBy', 'purchaseOrder', 'retailerOrder'])
            ->where('product_id', $id)
            ->when($warehouseId, fn($q) => $q->forWarehouse($warehouseId))
            ->latest()
            ->paginate(15);

        return view('consumables.show', compact('product', 'stock', 'movements'));
    }

    // ─────────────────────────────────────────────────────────
    // STOCK IN — Natanggap na delivery (from Purchase Order)
    // ROUTE: POST /consumables/stock-in
    // CALLED FROM: Purchase Order receive flow
    // ─────────────────────────────────────────────────────────
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id'        => 'required|exists:supplier_product,id',
            'quantity'          => 'required|integer|min:1',
            'purchase_order_id' => 'required|exists:purchase_order,id',
            'defective_qty'     => 'nullable|integer|min:0',
            'remarks'           => 'nullable|string|max:500',
        ]);

        $warehouseId = auth()->user()->assigned_at;

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Walang assigned warehouse ang iyong account.'
            ], 422);
        }

        return DB::transaction(function () use ($request, $warehouseId) {
            $goodQty      = $request->quantity;
            $defectiveQty = $request->defective_qty ?? 0;

            // ✅ Record good qty as IN
            StockMovement::record([
                'product_id'        => $request->product_id,
                'warehouse_id'      => $warehouseId,
                'type'              => StockMovement::TYPE_IN,
                'quantity'          => $goodQty,
                'reason_type'       => StockMovement::REASON_RECEIVED,
                'remarks'           => $request->remarks,
                'purchase_order_id' => $request->purchase_order_id,
                'created_by'        => auth()->id(),
            ]);

            // ✅ If may defective on arrival — i-record agad as DAMAGE
            if ($defectiveQty > 0) {
                StockMovement::record([
                    'product_id'        => $request->product_id,
                    'warehouse_id'      => $warehouseId,
                    'type'              => StockMovement::TYPE_DAMAGE,
                    'quantity'          => $defectiveQty,
                    'reason_type'       => StockMovement::REASON_DOA,
                    'remarks'           => 'Defective on arrival — PO#' . $request->purchase_order_id,
                    'purchase_order_id' => $request->purchase_order_id,
                    'created_by'        => auth()->id(),
                ]);
            }

            return response()->json([
                'success'      => true,
                'message'      => "Stock in recorded. Good: {$goodQty} pcs" .
                    ($defectiveQty > 0 ? ", DOA: {$defectiveQty} pcs" : ''),
                'net_received' => $goodQty,
                'doa_recorded' => $defectiveQty,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────
    // STOCK OUT — Ibinenta sa retailer
    // ROUTE: POST /consumables/stock-out
    // CALLED FROM: Retailer Order complete flow
    // ─────────────────────────────────────────────────────────
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id'        => 'required|exists:supplier_product,id',
            'quantity'          => 'required|integer|min:1',
            'retailer_order_id' => 'nullable|exists:retailer_orders,id',
            'remarks'           => 'nullable|string|max:500',
        ]);

        $warehouseId = auth()->user()->assigned_at;

        // ✅ Check kung may sapat na stock
        $stock = ConsumableStock::where('product_id', $request->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock || $stock->current_qty < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Kulang ang stock. Available: ' . ($stock->current_qty ?? 0) . ' pcs.'
            ], 422);
        }

        StockMovement::record([
            'product_id'        => $request->product_id,
            'warehouse_id'      => $warehouseId,
            'type'              => StockMovement::TYPE_OUT,
            'quantity'          => $request->quantity,
            'reason_type'       => StockMovement::REASON_SOLD,
            'remarks'           => $request->remarks,
            'retailer_order_id' => $request->retailer_order_id,
            'created_by'        => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Stock out recorded: {$request->quantity} pcs.",
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // REPORT DAMAGE / LOSS — Nasira o nawala
    // ROUTE: POST /consumables/report-incident
    // CALLED FROM: Manual report form sa dashboard
    // ─────────────────────────────────────────────────────────

    public function reportIncident(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:supplier_product,id',
            'type'        => 'required|in:damage,loss',
            'quantity'    => 'required|integer|min:1',
            'reason_type' => 'required|in:defective_on_arrival,damaged_in_storage,leaked,expired,lost_in_transit,missing_in_count,other',
            'remarks'     => 'nullable|string|max:500',
        ]);

        $warehouseId = auth()->user()->assigned_at;

        if (!$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Walang assigned warehouse ang iyong account.'
            ], 422);
        }

        $product = SupplierProduct::find($request->product_id);

        StockMovement::record([
            'product_id'   => $request->product_id,
            'warehouse_id' => $warehouseId,
            'type'         => $request->type,
            'quantity'     => $request->quantity,
            'reason_type'  => $request->reason_type,
            'remarks'      => $request->remarks,
            'created_by'   => auth()->id(),
        ]);

        if ($product && $product->is_consumable) {
            // ✅ Consumable ONLY — firstOrCreate dito lang
            $stock = ConsumableStock::firstOrCreate(
                ['product_id' => $request->product_id, 'warehouse_id' => $warehouseId],
                ['current_qty' => 0, 'min_stock_level' => 20]
            );
            $stock->decrement('current_qty', $request->quantity);
        } else {
            // ✅ Serialized — update status ng individual records
            $newStatus = $request->type === 'damage' ? 4 : 5;

            \App\Models\SerializedProduct::where('product_id', $request->product_id)
                ->where('status', 1)
                ->where('warehouse_id', $warehouseId)
                ->limit($request->quantity)
                ->get()
                ->each(function ($item) use ($newStatus, $request) {
                    $item->update([
                        'status'  => $newStatus,
                        'remarks' => $request->remarks,
                    ]);
                });
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->type) . " reported: {$request->quantity} pcs.",
        ]);
    }   


    // ─────────────────────────────────────────────────────────
    // ADJUSTMENT — Stock correction (manual count)
    // ROUTE: POST /consumables/adjust
    // CALLED FROM: Inventory audit form
    // ─────────────────────────────────────────────────────────
    public function adjust(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:supplier_product,id',
            'actual_qty'     => 'required|integer|min:0',
            'remarks'        => 'required|string|max:500',
        ]);

        $warehouseId = auth()->user()->assigned_at;

        $stock = ConsumableStock::firstOrCreate(
            ['product_id' => $request->product_id, 'warehouse_id' => $warehouseId],
            ['current_qty' => 0, 'min_stock_level' => 20]
        );

        // ✅ Difference = actual count vs system count
        $difference = $request->actual_qty - $stock->current_qty;

        StockMovement::record([
            'product_id'  => $request->product_id,
            'warehouse_id' => $warehouseId,
            'type'        => StockMovement::TYPE_ADJUSTMENT,
            'quantity'    => $difference,   // can be negative
            'reason_type' => StockMovement::REASON_CORRECTION,
            'remarks'     => $request->remarks . " (System: {$stock->current_qty}, Actual: {$request->actual_qty})",
            'created_by'  => auth()->id(),
        ]);

        return response()->json([
            'success'     => true,
            'message'     => "Adjustment recorded: " . ($difference >= 0 ? "+{$difference}" : $difference) . " pcs.",
            'old_qty'     => $stock->current_qty,
            'new_qty'     => $request->actual_qty,
            'difference'  => $difference,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // DAILY SUMMARY — Para sa Daily Report dashboard cards
    // ROUTE: GET /consumables/daily-summary
    // CALLED FROM: ReportsController o DashboardController
    // ─────────────────────────────────────────────────────────
    public function dailySummary(Request $request)
    {
        $warehouseId = auth()->user()->assigned_at;

        $summary = StockMovement::dailySummary($warehouseId);

        // ✅ Low stock count
        $lowStockCount = ConsumableStock::lowStock()
            ->when($warehouseId, fn($q) => $q->forWarehouse($warehouseId))
            ->count();

        $summary['low_stock_count'] = $lowStockCount;

        return response()->json($summary);
    }

    // ─────────────────────────────────────────────────────────
    // DATATABLE — For the stock level overview table
    // ROUTE: GET /consumables/table
    // CALLED FROM: index.blade.php via Ajax/DataTables
    // ─────────────────────────────────────────────────────────
    public function table(Request $request)
    {
        $warehouseId = auth()->user()->assigned_at;

        $query = ConsumableStock::with(['product.supplier', 'warehouse'])
            ->when($warehouseId, fn($q) => $q->forWarehouse($warehouseId));

        // ✅ DataTables response format (compatible sa existing DatatableServices mo)
        $data = $query->get()->map(function ($stock) {
            return [
                'id'               => $stock->product_id,
                'product_name'     => $stock->product->name ?? '—',
                'system_sku'       => $stock->product->system_sku ?? '—',
                'supplier_name'    => $stock->product->supplier->name ?? '—',
                'warehouse'        => $stock->warehouse->name ?? '—',
                'current_qty'      => $stock->current_qty,
                'min_stock_level'  => $stock->min_stock_level,
                'is_low_stock'     => $stock->isLowStock(),
                'status_badge'     => $stock->isLowStock()
                    ? '<span class="badge badge-danger">Low Stock</span>'
                    : '<span class="badge badge-success">OK</span>',
            ];
        });

        return response()->json(['data' => $data]);
    }

    // ─────────────────────────────────────────────────────────
    // MOVEMENT HISTORY DATATABLE — Per product
    // ROUTE: GET /consumables/{id}/movements
    // CALLED FROM: show.blade.php via Ajax/DataTables
    // ─────────────────────────────────────────────────────────
    public function movements(Request $request, $productId)
    {
        $warehouseId = auth()->user()->assigned_at;

        $movements = StockMovement::with(['createdBy', 'purchaseOrder', 'retailerOrder'])
            ->where('product_id', $productId)
            ->when($warehouseId, fn($q) => $q->forWarehouse($warehouseId))
            ->latest()
            ->get()
            ->map(function ($m) {
                $typeColors = [
                    'in'         => 'success',
                    'out'        => 'primary',
                    'damage'     => 'warning',
                    'loss'       => 'danger',
                    'adjustment' => 'secondary',
                ];

                $typeIcons = [
                    'in'         => '➕',
                    'out'        => '➖',
                    'damage'     => '❌',
                    'loss'       => '⚠️',
                    'adjustment' => '🔄',
                ];

                $color = $typeColors[$m->type] ?? 'secondary';
                $icon  = $typeIcons[$m->type] ?? '';

                return [
                    'date'        => $m->created_at->format('M d, Y h:i A'),
                    'type'        => "<span class='badge badge-{$color}'>{$icon} " . strtoupper($m->type) . '</span>',
                    'quantity'    => in_array($m->type, ['in', 'adjustment']) && $m->quantity > 0
                        ? "+{$m->quantity} pcs"
                        : "-{$m->quantity} pcs",
                    'reason'      => $m->reason_type ? str_replace('_', ' ', ucfirst($m->reason_type)) : '—',
                    'remarks'     => $m->remarks ?? '—',
                    'reference'   => $m->purchase_order_id
                        ? "PO #{$m->purchase_order_id}"
                        : ($m->retailer_order_id ? "Order #{$m->retailer_order_id}" : '—'),
                    'recorded_by' => $m->createdBy->full_name ?? '—',
                ];
            });

        return response()->json(['data' => $movements]);
    }

    // ─────────────────────────────────────────────────────────
    // SET MIN STOCK LEVEL — Para ma-configure ang low stock threshold
    // ROUTE: POST /consumables/{id}/set-min-stock
    // CALLED FROM: index.blade.php modal
    // ─────────────────────────────────────────────────────────
    public function setMinStock(Request $request, $productId)
    {
        $request->validate([
            'min_stock_level' => 'required|integer|min:1',
        ]);

        $warehouseId = auth()->user()->assigned_at;

        ConsumableStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->update(['min_stock_level' => $request->min_stock_level]);

        return response()->json([
            'success' => true,
            'message' => 'Minimum stock level updated.',
        ]);
    }
}
