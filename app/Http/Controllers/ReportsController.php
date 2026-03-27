<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\SerializedProduct;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\ProductStatus;
use App\Models\Sale;
use App\Models\InventoryAudit;
use App\Models\StockMovement;      // ✅ DAGDAG — para sa consumables
use App\Models\ConsumableStock;   // ✅ DAGDAG — para sa low stock ng consumables
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyInventoryExport;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{

    // =========================================================
    // ✅ FUNCTION 1: dailyIndex()
    // CONSUMABLES ONLY — tinanggal na ang non-consumable branches
    // =========================================================

    public function dailyIndex(Request $request)
    {
        $filterType = $request->get('filter_type', 'today');
        $customDate = $request->get('custom_date', null);

        $now  = now()->timezone('Asia/Manila');
        $date = null;

        if ($filterType === 'all_time' || !$filterType) {
            $date = null;
        } elseif ($filterType === 'today') {
            $date = $now->toDateString();
        } elseif ($filterType === 'yesterday') {
            $date = $now->copy()->subDay()->toDateString();
        } elseif ($filterType === 'custom' && $customDate) {
            $date = $customDate;
        } else {
            $date = $now->toDateString();
        }

        $warehouseId = auth()->user()->assigned_at
            ?? auth()->user()->warehouse_id
            ?? null;

        $dateFilter = function ($query, $column = 'created_at') use ($date) {
            if ($date) {
                $query->whereRaw(
                    "DATE(CONVERT_TZ({$column}, '+00:00', '+08:00')) = ?",
                    [$date]
                );
            }
            return $query;
        };

        // ─── LOW STOCK ───────────────────────────────────────
        $lowStockCount = ConsumableStock::lowStock()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->count();

        // ─── RECEIVED ────────────────────────────────────────
        $receivedQ = StockMovement::where('type', 'in');
        // ->when($warehouseId, ...) ← wag muna
        $dateFilter($receivedQ);
        $newArrivals = $receivedQ->count();

        // ─── OUTFLOW ─────────────────────────────────────────
        // ✅ FIX: Bawat ROW sa RetailerOrder = 1 product line
        // Kaya count() = bilang ng product lines, hindi bilang ng orders
        $outflowQ = RetailerOrder::whereIn('status', ['Approved', 'Completed']);
        $dateFilter($outflowQ);
        $dailyOutflow = $outflowQ->count();

        // ─── DAMAGED / LOST ──────────────────────────────────
        $damagedQ = StockMovement::whereIn('type', ['damage', 'loss'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));
        $dateFilter($damagedQ);
        $damagedCount = $damagedQ->count();

        $products = SupplierProduct::with(['supplier', 'category'])->get();

        // ✅ FIX: I-log para ma-debug mo kung may zero pa rin
        \Log::info('=== DAILY INDEX CARD COUNTS ===', [
            'date'          => $date,
            'filter_type'   => $filterType,
            'warehouse_id'  => $warehouseId,
            'newArrivals'   => $newArrivals,
            'dailyOutflow'  => $dailyOutflow,
            'damagedCount'  => $damagedCount,
            'lowStockCount' => $lowStockCount,
        ]);

        return view('reports.daily', compact(
            'date',
            'filterType',
            'lowStockCount',
            'newArrivals',
            'dailyOutflow',
            'damagedCount',
            'products'
        ));
    }

    // =========================================================
    // ✅ FUNCTION 2: getDailyData()
    // CONSUMABLES ONLY — tinanggal na ang non-consumable branches
    // =========================================================
    public function getDailyData(Request $request)
    {
        $filterType = $request->get('filter_type', 'today'); // ✅ FIX: 'today' na ang default, hindi null
        $customDate = $request->get('custom_date', null);
        $type       = $request->get('type', null);

        $date      = null;
        $dateQuery = null;

        $now = now()->timezone('Asia/Manila');

        if ($filterType === 'all_time') {
            $date = null;
        } elseif ($filterType === 'today') {
            $date = $now->toDateString();
        } elseif ($filterType === 'yesterday') {
            $date = $now->copy()->subDay()->toDateString();
        } elseif ($filterType === 'last_7_days') {
            $dateQuery = [
                'start' => $now->copy()->subDays(7)->startOfDay()->utc(),
                'end'   => $now->copy()->endOfDay()->utc(),
            ];
        } elseif ($filterType === 'last_30_days') {
            $dateQuery = [
                'start' => $now->copy()->subDays(30)->startOfDay()->utc(),
                'end'   => $now->copy()->endOfDay()->utc(),
            ];
        } elseif ($filterType === 'this_month') {
            $dateQuery = [
                'start' => $now->copy()->startOfMonth()->utc(),
                'end'   => $now->copy()->endOfMonth()->utc(),
            ];
        } elseif ($filterType === 'last_month') {
            $dateQuery = [
                'start' => $now->copy()->subMonth()->startOfMonth()->utc(),
                'end'   => $now->copy()->subMonth()->endOfMonth()->utc(),
            ];
        } elseif ($filterType === 'this_year') {
            $dateQuery = [
                'start' => $now->copy()->startOfYear()->utc(),
                'end'   => $now->copy()->endOfYear()->utc(),
            ];
        } elseif ($filterType === 'custom' && $customDate) {
            $date = $customDate;
        } else {
            // ✅ FIX: fallback = today palagi, hindi null/all_time
            $date = $now->toDateString();
        }

        // ✅ FIX: Same fallback logic para in-sync sa dailyIndex()
        $warehouseId = auth()->user()->assigned_at
            ?? auth()->user()->warehouse_id
            ?? null;
        $data        = [];

        $dateFilter = function ($query, $column = 'created_at') use ($date, $dateQuery) {
            if ($date) {
                $query->whereRaw(
                    "DATE(CONVERT_TZ({$column}, '+00:00', '+08:00')) = ?",
                    [$date]
                );
            } elseif ($dateQuery) {
                $query->whereBetween($column, [
                    $dateQuery['start'],
                    $dateQuery['end'],
                ]);
            }
            return $query;
        };

        try {
            \Log::info("=== DAILY REPORT GET DATA ===", [
                'filter_type' => $filterType,
                'date'        => $date,
                'type'        => $type,
                'warehouse'   => $warehouseId,
            ]);

            // =====================================================
            // SECTION 1: DAILY RECEIVED
            // =====================================================
            if (!$type || $type === 'received') {
                try {
                    $consumableQuery = StockMovement::with(['product.supplier', 'product.category', 'purchaseOrder'])
                        ->where('type', 'in')
                        ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

                    $dateFilter($consumableQuery);
                    
                    $movements = $consumableQuery->get()
                        ->groupBy('product_id');

                    foreach ($movements as $productId => $group) {
                        $movement      = $group->first();
                        $totalQty      = $group->sum('quantity');
                        $productName   = $movement->product->name           ?? 'Unnamed Product';
                        $categoryName  = $movement->product->category->name ?? 'Consumables';
                        $poNumber      = $movement->purchaseOrder->po_number ?? 'N/A';
                        $originalPrice = $movement->product->cost_price      ?? 0;
                        $totalAmount   = $totalQty * $originalPrice;

                        $reasonLabel = 'Received from Supplier';

                        $data[] = [
                            'product_name'  => '<strong style="font-size:15px;color:black;">' . e($productName) . '</strong>',
                            'category_name' => '<span class="badge badge-info">' . e($categoryName) . '</span>',
                            'traceability'  => '<small>
                                <strong>Type:</strong> ' . $reasonLabel . '<br>
                                <strong>PO Number:</strong> ' . e($poNumber) . '<br>
                                <strong>Original Price:</strong> <span class="text-danger font-weight-bold">₱' . number_format($originalPrice, 2) . '</span><br>
                                <strong>Total Amount:</strong> <span class="text-primary font-weight-bold">₱' . number_format($totalAmount, 2) . '</span>
                            </small>',
                            'quantity' => $totalQty,
                            'status'   => 'Received',
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Received Section Error: " . $e->getMessage());
                }
            }

            // =====================================================
            // SECTION 2: OUTFLOW
            // =====================================================
            if (!$type || $type === 'outflow') {
                try {
                    $query = RetailerOrder::with(['product'])
                        ->whereIn('status', ['Approved', 'Completed']);

                    $dateFilter($query);

                    foreach ($query->get() as $order) {
                        $productName   = $order->product_name  ?? 'Unknown Product';
                        $retailerName  = $order->retailer_name ?? 'N/A';
                        $originalPrice = 0;

                        if ($order->product_id) {
                            $supplierProd  = SupplierProduct::find($order->product_id);
                            $originalPrice = $supplierProd->cost_price ?? 0;
                        }

                        $sellingPrice = $order->unit_price   ?? 0;
                        $totalAmount  = $order->total_amount ?? 0;
                        $markup       = $sellingPrice - $originalPrice;
                        $markupPct    = $originalPrice > 0
                            ? number_format((($markup / $originalPrice) * 100), 1)
                            : 'N/A';

                        $data[] = [
                            'product_name'  => '<strong style="font-size:15px;color:black;">' . e($productName) . '</strong><br>
                        <span style="font-size:13px;color:#666;">Retailer: ' . e($retailerName) . '</span>',
                            'category_name' => '<span class="badge badge-success">Outflow</span>',
                            'traceability'  => '<small>
                        <strong>Type:</strong> Retailer Order<br>
                        <strong>Order #:</strong> ' . e($order->id) . '<br>
                        <strong>Retailer:</strong> ' . e($retailerName) . '<br>
                        <strong>Original Price:</strong> <span class="text-secondary font-weight-bold">₱' . number_format($originalPrice, 2) . '</span><br>
                        <strong>Selling Price:</strong> <span class="text-success font-weight-bold">₱' . number_format($sellingPrice, 2) . '</span><br>
                        <strong>Markup:</strong> <span class="text-info font-weight-bold">₱' . number_format($markup, 2) . ' (' . $markupPct . '%)</span><br>
                        <strong>Total Amount:</strong> <span class="text-primary font-weight-bold">₱' . number_format($totalAmount, 2) . '</span>
                    </small>',
                            'quantity' => $order->quantity,
                            'status'   => 'Outflow',
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Outflow Section Error: " . $e->getMessage());
                }
            }

            // =====================================================
            // SECTION 3: DAMAGED / LOST
            // =====================================================
            if (!$type || $type === 'damage') {
                try {
                    $query = StockMovement::with(['product.supplier', 'createdBy'])
                        ->whereIn('type', ['damage', 'loss'])
                        ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

                    $dateFilter($query);

                    foreach ($query->get() as $movement) {
                        $productName  = $movement->product->name           ?? 'Unnamed Product';
                        $supplierName = $movement->product->supplier->name ?? 'N/A';
                        $statusName   = $movement->type === 'damage' ? 'Damaged' : 'Lost';
                        $badgeColor   = $movement->type === 'damage' ? 'badge-warning' : 'badge-danger';
                        $reasonLabel  = $movement->reason_type
                            ? ucwords(str_replace('_', ' ', $movement->reason_type))
                            : 'No reason specified';

                        $data[] = [
                            'product_name'  => '<strong style="font-size:15px;color:black;">' . e($productName) . '</strong>',
                            'category_name' => '<span class="badge ' . $badgeColor . '">' . $statusName . '</span>',
                            'serial_number' => 'N/A',
                            'traceability'  => '<small>
                        <strong>Type:</strong> ' . $statusName . '<br>
                        <strong>Reason:</strong> ' . e($reasonLabel) . '<br>
                        <strong>Supplier:</strong> ' . e($supplierName) . '<br>
                        <strong>Recorded By:</strong> ' . e($movement->createdBy->full_name ?? 'N/A') . '<br>
                        <strong>Remarks:</strong> ' . e($movement->remarks ?? 'No remarks') . '
                    </small>',
                            'quantity' => $movement->quantity,
                            'status'   => $statusName,
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Damaged Section Error: " . $e->getMessage());
                }
            }

            // =====================================================
            // SECTION 4: LOW STOCK
            // =====================================================
            if (!$type || $type === 'low_stock') {
                try {
                    $lowConsumables = ConsumableStock::with(['product.supplier'])
                        ->whereColumn('current_qty', '<=', 'min_stock_level')
                        ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                        ->get();

                    foreach ($lowConsumables as $stock) {
                        $productName  = $stock->product->name           ?? 'Unnamed Product';
                        $supplierName = $stock->product->supplier->name ?? 'N/A';
                        $qty = max(0, $stock->current_qty);
                        $reorderQty   = max(0, $stock->min_stock_level - $qty);

                        $lastReceived = StockMovement::where('product_id', $stock->product_id)
                            ->where('type', 'in')
                            ->latest()
                            ->value('created_at');
                        $lastReceivedFormatted = $lastReceived
                            ? Carbon::parse($lastReceived)->timezone('Asia/Manila')->format('M d, Y')
                            : 'No record';

                        $data[] = [
                            'product_name'  => '<strong style="font-size:15px;color:black;">' . e($productName) . '</strong><br>
                        <span style="font-size:12px;color:#999;">SKU: ' . e($stock->product->system_sku ?? 'N/A') . '</span>',
                            'category_name' => '<span class="badge badge-warning">Low Stock</span>',
                            'traceability'  => '<small>
                        <strong>Supplier:</strong> ' . e($supplierName) . '<br>
                        <strong>Last Received:</strong> ' . $lastReceivedFormatted . '<br>
                        <strong>Min Level:</strong> ' . $stock->min_stock_level . ' pcs<br>
                        <strong>Reorder Needed:</strong> <span class="text-danger font-weight-bold">' . $reorderQty . ' pcs</span>
                    </small>',
                            'quantity' => $qty,
                            'status'   => 'Low Stock',
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Low Stock Section Error: " . $e->getMessage());
                }
            }

            return response()->json([
                'draw'            => (int) $request->get('draw', 1),
                'recordsTotal'    => count($data),
                'recordsFiltered' => count($data),
                'data'            => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('Daily Report Error: ' . $e->getMessage());
            return response()->json([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // ✅ HINDI BINAGO — weeklyIndex()
    // =========================================================
    public function weeklyIndex(Request $request)
    {
        $filterType  = $request->get('filter_type', 'last_7_days');
        $customStart = $request->get('custom_start', null);
        $customEnd   = $request->get('custom_end', null);

        switch ($filterType) {
            case 'this_week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now()->endOfWeek();
                break;
            case 'last_14_days':
                $startDate = Carbon::now()->subDays(14)->startOfDay();
                $endDate   = Carbon::now()->endOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $startDate = Carbon::parse($customStart)->startOfDay();
                $endDate   = Carbon::parse($customEnd)->endOfDay();
                break;
            default:
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate   = Carbon::now()->endOfDay();
                break;
        }

        $topProducts = RetailerOrder::select(
            'product_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
            ->whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $inventoryAnalysis = [];
        $products          = SupplierProduct::all();

        foreach ($products as $prod) {
            // ✅ Dual-source stock count
            if ($prod->is_consumable) {
                $warehouseId  = auth()->user()->assigned_at;
                $currentStock = ConsumableStock::where('product_id', $prod->id)
                    ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                    ->value('current_qty') ?? 0;
            } else {
                $currentStock = SerializedProduct::where('product_id', $prod->id)
                    ->where('status', 1)
                    ->count();
            }

            $weeklySales = RetailerOrder::where('product_name', $prod->name)
                ->whereIn('status', ['Approved', 'Completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');

            $ratio  = 0;
            $status = 'No Movement';
            $badge  = 'secondary';

            if ($weeklySales > 0) {
                $ratio = $currentStock > 0 ? round($currentStock / $weeklySales, 2) : 0;
                if ($ratio < 1) {
                    $status = 'Critical / Restock Now';
                    $badge  = 'danger';
                } elseif ($ratio >= 1 && $ratio <= 4) {
                    $status = 'Healthy';
                    $badge  = 'success';
                } else {
                    $status = 'Overstocked';
                    $badge  = 'warning';
                }
            } else {
                if ($currentStock > 0) {
                    $status = 'Non-Moving / Overstocked';
                    $badge  = 'warning';
                } else {
                    $status = 'Out of Stock';
                    $badge  = 'dark';
                }
            }

            $inventoryAnalysis[] = [
                'name'          => $prod->name,
                'sku'           => $prod->system_sku ?? $prod->sku ?? 'N/A',
                'current_stock' => $currentStock,
                'weekly_sales'  => $weeklySales,
                'ratio'         => number_format($ratio, 2),
                'status'        => $status,
                'badge'         => $badge,
            ];
        }

        return view('reports.weekly', compact(
            'topProducts',
            'inventoryAnalysis',
            'startDate',
            'endDate',
            'filterType'
        ));
    }

    // =========================================================
    // ✅ HINDI BINAGO — monthlyIndex()
    // =========================================================
    public function monthlyIndex(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear  = $request->get('year', Carbon::now()->year);

        $selectedDate     = Carbon::create($selectedYear, $selectedMonth, 1);
        $startOfMonth     = $selectedDate->copy()->startOfMonth();
        $endOfMonth       = $selectedDate->copy()->endOfMonth();
        $startOfLastMonth = $selectedDate->copy()->subMonth()->startOfMonth();
        $endOfLastMonth   = $selectedDate->copy()->subMonth()->endOfMonth();

        $oldestOrder    = RetailerOrder::min('created_at') ?? Carbon::now();
        $startYear      = Carbon::parse($oldestOrder)->year;
        $currentYear    = Carbon::now()->year;
        $availableYears = range($currentYear, $startYear);

        $allProducts         = SupplierProduct::all();
        $totalInventoryValue = 0;
        $warehouseId         = auth()->user()->assigned_at;

        foreach ($allProducts as $product) {
            // ✅ Dual-source stock count para sa inventory value
            if ($product->is_consumable) {
                $stockCount = ConsumableStock::where('product_id', $product->id)
                    ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                    ->value('current_qty') ?? 0;
            } else {
                $stockCount = SerializedProduct::where('product_id', $product->id)
                    ->where('status', 1)
                    ->count();
            }

            $costPrice = $product->cost_price ?? 0;
            if ($costPrice == 0) {
                $avgCost   = PurchaseOrderItem::where('product_id', $product->id)->avg('unit_cost');
                $costPrice = $avgCost ?? 0;
            }

            $totalInventoryValue += $stockCount * $costPrice;
        }

        $currentMonthSales = RetailerOrder::whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $lastMonthSales = RetailerOrder::whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        $growthPercentage = 0;
        $growthStatus     = 'stable';

        if ($lastMonthSales > 0) {
            $growthPercentage = (($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100;
        } elseif ($currentMonthSales > 0) {
            $growthPercentage = 100;
        }

        if ($growthPercentage > 0) {
            $growthStatus = 'increase';
        } elseif ($growthPercentage < 0) {
            $growthStatus = 'decrease';
        }

        $topProducts = RetailerOrder::select(
            'product_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
            ->whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        $supplierPerformance = PurchaseOrder::with('supplier')
            ->select('supplier_id', DB::raw('count(*) as total_pos'), DB::raw('sum(grand_total) as total_spent'))
            ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->groupBy('supplier_id')
            ->orderByDesc('total_pos')
            ->get();

        return view('reports.monthly', compact(
            'totalInventoryValue',
            'currentMonthSales',
            'lastMonthSales',
            'growthPercentage',
            'growthStatus',
            'topProducts',
            'supplierPerformance',
            'selectedDate',
            'availableYears'
        ));
    }

    // =========================================================
    // ✅ HINDI BINAGO — strategicIndex()
    // =========================================================
    public function strategicIndex(Request $request)
    {
        $selectedYear = $request->get('year', Carbon::now()->year);

        $oldestDate     = RetailerOrder::min('created_at') ?? Carbon::now();
        $startYear      = Carbon::parse($oldestDate)->year;
        $currentYear    = Carbon::now()->year;
        $availableYears = range($currentYear, $startYear);

        $monthlyRevenue = [];
        $monthlyCost    = [];
        $months         = [];
        $warehouseId    = auth()->user()->assigned_at;

        $quarterlyData = [
            1 => ['revenue' => 0, 'cost' => 0],
            2 => ['revenue' => 0, 'cost' => 0],
            3 => ['revenue' => 0, 'cost' => 0],
            4 => ['revenue' => 0, 'cost' => 0],
        ];

        $totalYearlyRevenue = 0;
        $totalYearlyCost    = 0;

        for ($m = 1; $m <= 12; $m++) {
            $months[] = Carbon::create()->month($m)->format('M');

            $revenue = RetailerOrder::whereIn('status', ['Approved', 'Completed'])
                ->whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $m)
                ->sum('total_amount');

            $cost = PurchaseOrder::whereYear('order_date', $selectedYear)
                ->whereMonth('order_date', $m)
                ->sum('grand_total');

            $monthlyRevenue[] = $revenue;
            $monthlyCost[]    = $cost;

            $totalYearlyRevenue += $revenue;
            $totalYearlyCost    += $cost;

            $quarter = ceil($m / 3);
            $quarterlyData[$quarter]['revenue'] += $revenue;
            $quarterlyData[$quarter]['cost']    += $cost;
        }

        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $allProducts  = SupplierProduct::all();
        $deadStocks   = [];

        foreach ($allProducts as $prod) {
            // ✅ Dual-source stock count para sa dead stocks
            if ($prod->is_consumable) {
                $stockCount = ConsumableStock::where('product_id', $prod->id)
                    ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                    ->value('current_qty') ?? 0;
            } else {
                $stockCount = SerializedProduct::where('product_id', $prod->id)
                    ->where('status', 1)
                    ->count();
            }

            $lastSale = RetailerOrder::where('product_name', $prod->name)
                ->whereIn('status', ['Approved', 'Completed'])
                ->latest('updated_at')
                ->first();

            if ($stockCount > 0) {
                if (!$lastSale || $lastSale->updated_at < $sixMonthsAgo) {
                    $deadStocks[] = [
                        'name'      => $prod->name,
                        'stock'     => $stockCount,
                        'value'     => $stockCount * ($prod->cost_price ?? 0),
                        'last_sold' => $lastSale ? $lastSale->updated_at->format('M d, Y') : 'Never Sold',
                    ];
                }
            }
        }

        $topItems = RetailerOrder::select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->whereIn('status', ['Approved', 'Completed'])
            ->whereYear('created_at', $selectedYear)
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $projectedStocks = [];
        foreach ($topItems as $item) {
            $prodDetails  = SupplierProduct::where('name', $item->product_name)->first();
            $currentStock = 0;

            if ($prodDetails) {
                if ($prodDetails->is_consumable) {
                    $currentStock = ConsumableStock::where('product_id', $prodDetails->id)
                        ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                        ->value('current_qty') ?? 0;
                } else {
                    $currentStock = SerializedProduct::where('product_id', $prodDetails->id)
                        ->where('status', 1)
                        ->count();
                }
            }

            $projectedStocks[] = [
                'product'  => $item->product_name,
                'sold'     => $item->total_qty,
                'forecast' => ceil($item->total_qty * 1.10),
                'current'  => $currentStock,
            ];
        }

        return view('reports.strategic', compact(
            'availableYears',
            'selectedYear',
            'months',
            'monthlyRevenue',
            'monthlyCost',
            'quarterlyData',
            'totalYearlyRevenue',
            'totalYearlyCost',
            'deadStocks',
            'projectedStocks'
        ));
    }

    // =========================================================
    // ✅ HINDI BINAGO — lahat ng ibang methods
    // =========================================================
    public function getWeeklyData(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function exportWeekly(Request $request)
    {
        return response()->json(['message' => 'Weekly export']);
    }

    public function recordSale(Request $request)
    {
        try {
            $serialNumber      = $request->input('serial_number');
            $serializedProduct = SerializedProduct::where('serial_number', $serialNumber)->first();

            if ($serializedProduct) {
                $serializedProduct->update(['status' => 3, 'updated_at' => now()]);
                return response()->json(['success' => true, 'message' => 'Sale recorded successfully!']);
            }
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function reportDamage(Request $request)
    {
        $request->validate([
            'serial_number_id' => 'required|exists:serialized_products,id',
            'remarks'          => 'nullable|string',
        ]);

        $serialNumber = SerializedProduct::findOrFail($request->serial_number_id);
        $serialNumber->update([
            'status'  => 5,
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Product reported as damaged successfully.');
    }

    public function saveAudit(Request $request)
    {
        try {
            $auditItems  = $request->input('audit_items', []);
            $auditPeriod = $request->input('audit_period', '');
            $auditedBy   = auth()->user()->full_name ?? auth()->user()->name ?? 'Unknown';

            if (empty($auditItems)) {
                return response()->json(['success' => false, 'message' => 'No audit data to save.'], 422);
            }

            $saved = 0;

            foreach ($auditItems as $item) {
                if (!isset($item['actual_count']) || $item['actual_count'] === '' || $item['actual_count'] === null) {
                    continue;
                }

                $systemCount = (int) $item['system_count'];
                $actualCount = (int) $item['actual_count'];
                $variance    = $actualCount - $systemCount;

                $status = match (true) {
                    $variance === 0 => 'Match',
                    $variance < 0   => 'Missing',
                    default         => 'Surplus',
                };

                InventoryAudit::create([
                    'product_name' => $item['product_name'],
                    'product_sku'  => $item['product_sku'] ?? null,
                    'system_count' => $systemCount,
                    'actual_count' => $actualCount,
                    'variance'     => $variance,
                    'status'       => $status,
                    'audit_period' => $auditPeriod,
                    'audited_by'   => $auditedBy,
                ]);

                $saved++;
            }

            if ($saved === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rows were saved. Please enter at least one actual count.'
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Audit saved successfully! {$saved} item(s) recorded."
            ]);
        } catch (\Exception $e) {
            \Log::error('Save Audit Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving audit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function auditHistory(Request $request)
    {
        $auditPeriods = InventoryAudit::select('audit_period', DB::raw('MAX(created_at) as latest'))
            ->groupBy('audit_period')
            ->orderByDesc('latest')
            ->pluck('audit_period');

        $selectedPeriod = $request->get('period', null);
        $query          = InventoryAudit::orderByDesc('created_at');

        if ($selectedPeriod) {
            $query->where('audit_period', $selectedPeriod);
        }

        $auditRecords = $query->get();

        $groupedAudits = $auditRecords->groupBy(function ($item) {
            return $item->audit_period . '||' . $item->audited_by . '||' . $item->created_at->format('M d, Y h:i A');
        });

        return view('reports.audit-history', compact(
            'groupedAudits',
            'auditPeriods',
            'selectedPeriod'
        ));
    }
}
