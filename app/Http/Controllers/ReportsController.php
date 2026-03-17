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
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyInventoryExport;
use Illuminate\Support\Facades\DB;


class ReportsController extends Controller
{

    public function dailyIndex(Request $request)
    {
        $filterType = $request->get('filter_type', 'today');
        $customDate = $request->get('custom_date', null);

        $date = null;
        $dateQuery = null;

        if ($filterType === 'all_time' || !$filterType) {
            $date = null;
        } elseif ($filterType === 'today') {
            $date = Carbon::today()->toDateString();
        } elseif ($filterType === 'yesterday') {
            $date = Carbon::yesterday()->toDateString();
        } elseif ($filterType === 'custom' && $customDate) {
            $date = $customDate;
        } else {
            $date = Carbon::today()->toDateString();
        }

        $lowStockCount = SupplierProduct::select(
            'supplier_product.id',
            DB::raw("(SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1) as available_count")
        )
            ->havingRaw('available_count > 0 AND available_count < 20')
            ->get()
            ->count();

        $receivedQuery = SerializedProduct::where('status', 1)
            ->whereNotNull('purchase_order_id');
        if ($date) {
            $receivedQuery->whereDate('created_at', $date);
        }
        $newArrivals = $receivedQuery->count();

        $outflowQuery = RetailerOrder::whereIn('status', ['Approved', 'Completed']);
        if ($date) {
            $outflowQuery->whereDate('created_at', $date);
        }
        $dailyOutflow = $outflowQuery->sum('quantity');

        $damagedQuery = SerializedProduct::whereIn('status', [4, 5]);
        if ($date) {
            $damagedQuery->whereDate('updated_at', $date);
        }
        $damagedCount = $damagedQuery->count();

        $products = SupplierProduct::with(['supplier', 'category'])->get();

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

    public function getDailyData(Request $request)
    {
        $filterType = $request->get('filter_type', null);
        $customDate = $request->get('custom_date', null);
        $type       = $request->get('type', null);

        $date      = null;
        $dateQuery = null;

        if ($filterType === 'all_time' || !$filterType) {
            $date = null;
        } elseif ($filterType === 'today') {
            $date = Carbon::today()->toDateString();
        } elseif ($filterType === 'yesterday') {
            $date = Carbon::yesterday()->toDateString();
        } elseif ($filterType === 'last_7_days') {
            $dateQuery = ['start' => Carbon::today()->subDays(7), 'end' => Carbon::today()];
        } elseif ($filterType === 'last_30_days') {
            $dateQuery = ['start' => Carbon::today()->subDays(30), 'end' => Carbon::today()];
        } elseif ($filterType === 'this_month') {
            $dateQuery = ['start' => Carbon::now()->startOfMonth(), 'end' => Carbon::now()->endOfMonth()];
        } elseif ($filterType === 'last_month') {
            $dateQuery = ['start' => Carbon::now()->subMonth()->startOfMonth(), 'end' => Carbon::now()->subMonth()->endOfMonth()];
        } elseif ($filterType === 'this_year') {
            $dateQuery = ['start' => Carbon::now()->startOfYear(), 'end' => Carbon::now()->endOfYear()];
        } elseif ($filterType === 'custom' && $customDate) {
            $date = $customDate;
        } else {
            $date = Carbon::today()->toDateString();
        }

        $data = [];

        try {
            \Log::info("=== DAILY REPORT START ===");
            \Log::info("Filter Type: " . ($filterType ?? 'none') . " | Date: " . ($date ?? 'all') . " | Type: " . ($type ?? 'all'));

            // ===== DAILY RECEIVED — GROUPED by product + PO =====
            if (!$type || $type === 'received') {
                try {
                    $query = SerializedProduct::select(
                        'product_id',
                        'purchase_order_id',
                        DB::raw('MIN(created_at) as received_date'),
                        DB::raw('COUNT(*) as total_qty')
                    )
                        ->whereNotNull('purchase_order_id');

                    if ($date) {
                        $query->whereDate('created_at', $date);
                    } elseif ($dateQuery) {
                        $query->whereBetween('created_at', [$dateQuery['start'], $dateQuery['end']]);
                    }

                    $groupedProducts = $query
                        ->groupBy('product_id', 'purchase_order_id')
                        ->get();

                    foreach ($groupedProducts as $item) {
                        // ✅ Load relationships manually
                        $supplierProduct = \App\Models\SupplierProduct::with(['supplier', 'category'])
                            ->find($item->product_id);
                        $purchaseOrder = \App\Models\PurchaseOrder::find($item->purchase_order_id);

                        $productName  = $supplierProduct->name ?? 'Unnamed Product';
                        $supplierName = $supplierProduct->supplier->name ?? 'N/A';
                        $categoryName = $supplierProduct->category->name ?? 'General';
                        $receivedDate = \Carbon\Carbon::parse($item->received_date)->format('M d, Y h:i A');
                        $poNumber     = $purchaseOrder->po_number ?? 'N/A';

                        $data[] = [
                            'product_name'  => '<strong style="font-size: 16px; color: black;">' . e($productName) . '</strong><br>
                    <span style="font-size: 13px; color: #666;">Supplier: ' . e($supplierName) . '</span><br>
                    <span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $receivedDate . '</span>',
                            'category_name' => '<span class="badge badge-info">' . e($categoryName) . '</span>',
                            'traceability'  => '<small>
                    <strong>Type:</strong> Scanned from PO<br>
                    <strong>PO Number:</strong> ' . e($poNumber) . '<br>
                    <strong>Supplier:</strong> ' . e($supplierName) . '<br>
                    <strong>Date Received:</strong> ' . $receivedDate . '
                    </small>',
                            'quantity' => $item->total_qty,
                            'image'    => $supplierProduct->thumbnail ?? null,
                            'status'   => 'Received'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Scanned Products Section Error: " . $e->getMessage());
                }
            }

            // ===== OUTFLOW =====
            if (!$type || $type === 'outflow') {
                try {
                    $query = RetailerOrder::with(['product'])
                        ->whereIn('status', ['Approved', 'Completed']);

                    // ✅ BUG 3 FIX: Outflow — use created_at (consistent with monthly)
                    if ($date) {
                        $query->whereDate('created_at', $date);
                    } elseif ($dateQuery) {
                        $query->whereBetween('created_at', [$dateQuery['start'], $dateQuery['end']]);
                    }

                    $retailerOrders = $query->get();

                    foreach ($retailerOrders as $order) {
                        $updatedDate  = Carbon::parse($order->created_at)->format('M d, Y h:i A');
                        $productName  = $order->product_name ?? 'Unknown Product';
                        $retailerName = $order->retailer_name ?? 'N/A';

                        $data[] = [
                            'product_name'  => '<strong style="font-size: 16px; color: black;">' . e($productName) . '</strong><br>
                    <span style="font-size: 13px; color: #666;">Retailer: ' . e($retailerName) . '</span><br>
                    <span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $updatedDate . '</span>',
                            'category_name' => '<span class="badge badge-success">Outflow</span>',
                            'traceability'  => '<small>
                    <strong>Type:</strong> Retailer Order<br>
                    <strong>Order #:</strong> ' . e($order->id) . '<br>
                    <strong>Retailer:</strong> ' . e($retailerName) . '<br>
                    <strong>Qty Out:</strong> ' . $order->quantity . ' pcs<br>
                    <strong>Total Amount:</strong> ₱' . number_format($order->total_amount, 2) . '<br>
                    <strong>Date:</strong> ' . $updatedDate . '
                    </small>',
                            'quantity' => $order->quantity,
                            'image'    => $order->product->thumbnail ?? null,
                            'status'   => 'Outflow'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Outflow Section Error: " . $e->getMessage());
                }
            }

            // ===== DAMAGED =====
            if (!$type || $type === 'damage') {
                try {
                    $query = SerializedProduct::with(['supplierProducts.supplier'])
                        ->whereIn('status', [4, 5]);

                    if ($date) {
                        $query->whereDate('updated_at', $date);
                    } elseif ($dateQuery) {
                        $query->whereBetween('updated_at', [$dateQuery['start'], $dateQuery['end']]);
                    }

                    $damagedItems = $query->get();

                    foreach ($damagedItems as $item) {
                        $productName  = $item->supplierProducts->name ?? 'Unnamed Product';
                        $supplierName = $item->supplierProducts->supplier->name ?? 'N/A';
                        $updatedDate  = Carbon::parse($item->updated_at)->format('M d, Y h:i A');
                        $statusName   = $item->status == 4 ? 'Damaged' : 'Lost';
                        $badgeColor   = $item->status == 4 ? 'badge-danger' : 'badge-dark';

                        $data[] = [
                            'product_name'  => '<strong style="font-size: 16px; color: black;">' . e($productName) . '</strong><br>
                    <span style="font-size: 13px; color: #666;">SN: ' . e($item->serial_number ?? 'N/A') . '</span><br>
                    <span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $updatedDate . '</span>',
                            'category_name' => '<span class="badge ' . $badgeColor . '">' . $statusName . '</span>',
                            'traceability'  => '<small>
                    <strong>Type:</strong> ' . $statusName . '<br>
                    <strong>Serial No:</strong> ' . e($item->serial_number ?? 'N/A') . '<br>
                    <strong>Supplier:</strong> ' . e($supplierName) . '<br>
                    <strong>Remarks:</strong> ' . e($item->remarks ?? 'No remarks') . '<br>
                    <strong>Date:</strong> ' . $updatedDate . '
                    </small>',
                            'quantity' => 1,
                            'image'    => $item->supplierProducts->thumbnail ?? null,
                            'status'   => $statusName
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Damaged Section Error: " . $e->getMessage());
                }
            }

            // ===== LOW STOCK =====
            if (!$type || $type === 'low_stock') {
                try {
                    $subquery = 'SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1';

                    $lowStockProducts = SupplierProduct::select(
                        'supplier_product.id',
                        'supplier_product.name',
                        'supplier_product.system_sku',
                        'supplier_product.thumbnail',
                        'category.name as category_name',
                        'supplier.name as supplier_name',
                        DB::raw("({$subquery}) as available_count")
                    )
                        ->leftJoin('category', 'supplier_product.category_id', '=', 'category.id')
                        ->leftJoin('supplier', 'supplier_product.supplier_id', '=', 'supplier.id')
                        ->havingRaw('available_count < 20')
                        ->orderBy('available_count', 'asc')
                        ->get();

                    foreach ($lowStockProducts as $product) {
                        $qty = $product->available_count ?? 0;

                        if ($qty <= 5) {
                            $urgency = 'CRITICAL';
                            $badge   = 'badge-danger';
                        } elseif ($qty <= 10) {
                            $urgency = 'WARNING';
                            $badge   = 'badge-warning';
                        } else {
                            $urgency = 'LOW';
                            $badge   = 'badge-info';
                        }

                        $data[] = [
                            'product_name'  => '<strong style="font-size: 16px; color: black;">' . e($product->name) . '</strong><br><span style="font-size: 13px; color: #666;">SKU: ' . e($product->system_sku ?? 'N/A') . '</span><br><span class="badge ' . $badge . '">' . $urgency . ' - ' . $qty . ' units</span>',
                            'category_name' => '<span class="badge badge-warning">Low Stock</span>',
                            'traceability'  => '<small><strong>Type:</strong> Low Stock<br><strong>Available:</strong> ' . $qty . ' units<br><strong>Status:</strong> ' . $urgency . '</small>',
                            'quantity' => $qty,
                            'image'    => $product->thumbnail,
                            'status'   => 'Low Stock'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Low Stock Section Error: " . $e->getMessage());
                }
            }

            \Log::info("Total data rows: " . count($data));
            \Log::info("=== DAILY REPORT END ===");

            return response()->json([
                'draw'            => (int) $request->get('draw', 1),
                'recordsTotal'    => count($data),
                'recordsFiltered' => count($data),
                'data'            => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Daily Report Error: ' . $e->getMessage());
            return response()->json([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage()
            ], 500);
        }
    }

    public function exportDaily(Request $request)
    {
        try {
            $date = $request->get('date', Carbon::today()->toDateString());
            return Excel::download(new DailyInventoryExport($date), "GYMNASTHENIQX_Daily_Report_{$date}.xlsx");
        } catch (\Exception $e) {
            return back()->with('error', 'Export error: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id, $type)
    {
        try {
            if ($type === 'pr') {
                $pr = PurchaseRequest::findOrFail($id);
                $pr->update(['status_id' => 2]);
                return response()->json(['success' => true, 'message' => 'Purchase Request approved successfully!']);
            } elseif ($type === 'po') {
                $po = PurchaseOrder::findOrFail($id);
                if ($po->purchaseRequest) {
                    $po->purchaseRequest->update(['status_id' => 5]);
                }
                return response()->json(['success' => true, 'message' => 'Purchase Order approved successfully!']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id, $type)
    {
        try {
            if ($type === 'pr') {
                $pr = PurchaseRequest::findOrFail($id);
                $pr->update(['status_id' => 3]);
                return response()->json(['success' => true, 'message' => 'Purchase Request rejected!']);
            } elseif ($type === 'po') {
                $po = PurchaseOrder::findOrFail($id);
                if ($po->purchaseRequest) {
                    $po->purchaseRequest->update(['status_id' => 8]);
                }
                return response()->json(['success' => true, 'message' => 'Purchase Order rejected!']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ===== WEEKLY REPORT =====
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
            $currentStock = SerializedProduct::where('product_id', $prod->id)
                ->where('status', 1)
                ->count();

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
                'badge'         => $badge
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

    // ===== MONTHLY REPORT =====
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

        // ✅ BUG 1 FIX — Total Inventory Asset Value
        // BEFORE: cost_price is null/0 on most products → kaya ₱50 lang
        // AFTER:  fallback to avg unit_cost from purchase_order_item table
        $allProducts         = SupplierProduct::all();
        $totalInventoryValue = 0;

        foreach ($allProducts as $product) {
            $stockCount = SerializedProduct::where('product_id', $product->id)
                ->where('status', 1)
                ->count();

            $costPrice = $product->cost_price ?? 0;

            if ($costPrice == 0) {
                // Fallback: avg unit_cost mula sa purchase_order_item
                $avgCost   = PurchaseOrderItem::where('product_id', $product->id)
                    ->avg('unit_cost');
                $costPrice = $avgCost ?? 0;
            }

            $totalInventoryValue += $stockCount * $costPrice;
        }

        // ✅ BUG 2 FIX — Monthly Sales Growth
        // BEFORE: updated_at → nagbabago kapag na-complete ang order kaya nawala ang Feb data
        // AFTER:  created_at → yung actual na petsa ng pag-order
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
            $growthPercentage = 100; // walang previous month data
        }

        if ($growthPercentage > 0) {
            $growthStatus = 'increase';
        } elseif ($growthPercentage < 0) {
            $growthStatus = 'decrease';
        }

        // ✅ Top 5 Revenue Generators — fixed to use created_at
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

    // ===== STRATEGIC REPORT =====
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

        $quarterlyData = [
            1 => ['revenue' => 0, 'cost' => 0],
            2 => ['revenue' => 0, 'cost' => 0],
            3 => ['revenue' => 0, 'cost' => 0],
            4 => ['revenue' => 0, 'cost' => 0],
        ];

        $totalYearlyRevenue = 0;
        $totalYearlyCost    = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create()->month($m)->format('M');
            $months[]  = $monthName;

            // ✅ Use created_at (consistent with monthly report fix)
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
            $stockCount = SerializedProduct::where('product_id', $prod->id)
                ->where('status', 1)
                ->count();

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
                        'last_sold' => $lastSale ? $lastSale->updated_at->format('M d, Y') : 'Never Sold'
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
                $currentStock = SerializedProduct::where('product_id', $prodDetails->id)
                    ->where('status', 1)
                    ->count();
            }

            $forecast = ceil($item->total_qty * 1.10);

            $projectedStocks[] = [
                'product'  => $item->product_name,
                'sold'     => $item->total_qty,
                'forecast' => $forecast,
                'current'  => $currentStock
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

        $serialNumber = \App\Models\SerializedProduct::findOrFail($request->serial_number_id);
        $serialNumber->update([
            'status'  => 5,
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Product reported as damaged successfully.');
    }

    // ===== SAVE INVENTORY AUDIT =====
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

                if ($variance === 0) {
                    $status = 'Match';
                } elseif ($variance < 0) {
                    $status = 'Missing';
                } else {
                    $status = 'Surplus';
                }

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

    // ===== VIEW AUDIT HISTORY =====
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
