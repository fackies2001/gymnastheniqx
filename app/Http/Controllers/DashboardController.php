<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Supplier;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\SerializedProduct;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;

class DashboardController extends Controller
{
    // ============================================================
    // ✅ MAIN INDEX METHOD WITH DATE FILTERING
    // ============================================================
    public function index(Request $request)
    {
        $user = auth()->user();

        // ============================================================
        // ✅ MANAGER DASHBOARD — Sales, Inventory, Reports focus lang
        // ============================================================
        if ($user->isManager()) {
            $small_boxes = [
                'serial_number_counts' => $this->getAvailableProductCount(),
                'total_sales_today'    => $this->getTotalSalesFiltered($request),
                'total_sales_alltime'  => $this->getTotalSalesAllTime(),
            ];

            $doughnut = [
                'product_status_counts'          => $this->getSerializedProductStatusCounts($request),
                'purchase_request_status_counts' => [],
            ];

            $bar = [
                'monthly_products_in' => $this->getMonthlyProductsScanned($request),
            ];

            $monthly_sales_income = $this->getMonthlyRetailerSales($request);
            $recent_activities  = $this->getRecentActivities();
            $retailer_orders    = $this->getRecentRetailerOrders();
            $low_stock_products = $this->getLowStockProducts(); // ✅

            return view('dashboard.index', compact(
                'small_boxes',
                'doughnut',
                'bar',
                'monthly_sales_income',
                'recent_activities',
                'retailer_orders',
                'low_stock_products'
            ));
        }

        // ============================================================
        // ✅ ADMIN & STAFF DASHBOARD — Full data
        // ============================================================
        $small_boxes = [
            'supplier_counts'           => Supplier::count(),
            'purchase_request_counts'   => $this->getPurchaseRequestCount($request),
            'purchase_order_counts'     => $this->getPurchaseOrderCount($request),
            'serial_number_counts'      => $this->getAvailableProductCount(),
        ];

        $doughnut = [
            'product_status_counts'             => $this->getSerializedProductStatusCounts($request),
            'purchase_request_status_counts'    => $this->getPurchaseRequestStatusCounts($request),
        ];

        $bar = [
            'monthly_products_in' => $this->getMonthlyProductsScanned($request),
        ];

        $monthly_sales_income = $this->getMonthlyRetailerSales($request);
        $low_stock_products   = $this->getLowStockProducts();
        $recent_activities    = $this->getRecentActivities();
        $retailer_orders      = collect(); // ✅ Empty para sa admin/staff

        return view('dashboard.index', compact(
            'small_boxes',
            'doughnut',
            'bar',
            'monthly_sales_income',
            'low_stock_products',
            'recent_activities',
            'retailer_orders'
        ));

        $warehouseId = auth()->user()->assigned_at;
    }

    // ============================================================
    // ✅ MANAGER HELPER: Total Sales Today (Retailer Orders)
    // ============================================================
    private function getTotalSalesToday()
    {
        try {
            return RetailerOrder::whereDate('created_at', Carbon::today())
                ->whereIn('status', ['Completed', 'completed', 'Approved', 'approved'])
                ->sum('total_amount') ?? 0;
        } catch (\Exception $e) {
            \Log::error('Error getting total sales today: ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // ✅ MANAGER HELPER: Total Sales All Time (Retailer Orders)
    // ============================================================
    private function getTotalSalesAllTime()
    {
        try {
            return RetailerOrder::whereIn('status', ['Completed', 'completed', 'Approved', 'approved'])
                ->sum('total_amount') ?? 0;
        } catch (\Exception $e) {
            \Log::error('Error getting total sales all time: ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // ✅ MANAGER HELPER: Low Stock Count
    // ============================================================
    private function getLowStockCount()
    {
        try {
            return SupplierProduct::select(
                DB::raw("(SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1) as available_count")
            )
                ->having('available_count', '<', 20)
                ->count();
        } catch (\Exception $e) {
            \Log::error('Error getting low stock count: ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // ✅ HELPER: Apply date filter to query
    // ============================================================
    private function applyDateFilter($query, Request $request, $tableName = null)
    {
        if (!$request->filled('filter_type')) {
            return $query;
        }

        $filterType = $request->filter_type;
        $today = Carbon::today()->startOfDay();
        $createdAtColumn = $tableName ? "{$tableName}.created_at" : 'created_at';

        switch ($filterType) {
            case 'today':
                $query->whereDate($createdAtColumn, $today);
                break;
            case 'yesterday':
                $query->whereDate($createdAtColumn, $today->copy()->subDay());
                break;
            case 'last_7_days':
                $query->whereBetween($createdAtColumn, [
                    $today->copy()->subDays(6)->startOfDay(),
                    Carbon::now()->endOfDay()
                ]);
                break;
            case 'last_30_days':
                $query->whereBetween($createdAtColumn, [
                    $today->copy()->subDays(29)->startOfDay(),
                    Carbon::now()->endOfDay()
                ]);
                break;
            case 'this_month':
                $query->whereMonth($createdAtColumn, Carbon::now()->month)
                    ->whereYear($createdAtColumn, Carbon::now()->year);
                break;
            case 'last_month':
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth($createdAtColumn, $lastMonth->month)
                    ->whereYear($createdAtColumn, $lastMonth->year);
                break;
            case 'this_year':
                $query->whereYear($createdAtColumn, Carbon::now()->year);
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query->whereBetween($createdAtColumn, [
                        $request->start_date . ' 00:00:00',
                        $request->end_date . ' 23:59:59'
                    ]);
                }
                break;
        }

        return $query;
    }

    // ============================================================
    // ✅ HELPER: Get purchase request count
    // ============================================================
    private function getPurchaseRequestCount(Request $request)
    {
        $query = PurchaseRequest::query();
        $this->applyDateFilter($query, $request, 'purchase_request');
        return $query->count();
    }

    // ============================================================
    // ✅ HELPER: Get purchase order count
    // ============================================================
    private function getPurchaseOrderCount(Request $request)
    {
        $query = PurchaseOrder::query();
        $this->applyDateFilter($query, $request, 'purchase_order');
        return $query->count();
    }

    // ============================================================
    // ✅ HELPER: Get available product count
    // ============================================================
    private function getAvailableProductCount()
    {
        try {
            if (Schema::hasColumn('serialized_product', 'product_status_id')) {
                return SerializedProduct::where('product_status_id', 1)->count();
            } elseif (Schema::hasColumn('serialized_product', 'status_id')) {
                return SerializedProduct::where('status_id', 1)->count();
            } elseif (Schema::hasColumn('serialized_product', 'status')) {
                return SerializedProduct::where('status', 1)->count();
            }
            return SerializedProduct::count();
        } catch (\Exception $e) {
            \Log::error('Error counting available products: ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // ✅ HELPER: Get serialized product status counts
    // ============================================================
    private function getSerializedProductStatusCounts(Request $request)
    {
        try {
            $query = SerializedProduct::select(
                'product_status.name as status_name',
                DB::raw('count(serialized_product.id) as count')
            )
                ->join('product_status', 'serialized_product.status', '=', 'product_status.id');

            // ✅ GAMIT ang updated_at — para makita yung mga na-UPDATE na status ngayon
            // Hindi created_at — kasi yung created_at ay kung kailan na-scan, hindi kung kailan nag-bago ang status
            if ($request->filled('filter_type')) {
                $this->applyDateFilter($query, $request, 'serialized_product');

                // ✅ Override — gamitin updated_at instead of created_at
                $filterType = $request->filter_type;
                $today = Carbon::today()->startOfDay();

                // Reset yung query filter at gamitin updated_at
                $query = SerializedProduct::select(
                    'product_status.name as status_name',
                    DB::raw('count(serialized_product.id) as count')
                )
                    ->join('product_status', 'serialized_product.status', '=', 'product_status.id');

                switch ($filterType) {
                    case 'today':
                        $query->whereDate('serialized_product.updated_at', $today);
                        break;
                    case 'yesterday':
                        $query->whereDate('serialized_product.updated_at', $today->copy()->subDay());
                        break;
                    case 'last_7_days':
                        $query->whereBetween('serialized_product.updated_at', [
                            $today->copy()->subDays(6)->startOfDay(),
                            Carbon::now()->endOfDay()
                        ]);
                        break;
                    case 'last_30_days':
                        $query->whereBetween('serialized_product.updated_at', [
                            $today->copy()->subDays(29)->startOfDay(),
                            Carbon::now()->endOfDay()
                        ]);
                        break;
                    case 'this_month':
                        $query->whereMonth('serialized_product.updated_at', Carbon::now()->month)
                            ->whereYear('serialized_product.updated_at', Carbon::now()->year);
                        break;
                    case 'last_month':
                        $lastMonth = Carbon::now()->subMonth();
                        $query->whereMonth('serialized_product.updated_at', $lastMonth->month)
                            ->whereYear('serialized_product.updated_at', $lastMonth->year);
                        break;
                    case 'this_year':
                        $query->whereYear('serialized_product.updated_at', Carbon::now()->year);
                        break;
                    case 'custom':
                        if ($request->filled('start_date') && $request->filled('end_date')) {
                            $query->whereBetween('serialized_product.updated_at', [
                                $request->start_date . ' 00:00:00',
                                $request->end_date . ' 23:59:59'
                            ]);
                        }
                        break;
                }
            }
            // ✅ Kung walang filter (All Time) — lahat ng current status
            // Walang date filter — show all

            $results = $query->groupBy('product_status.name', 'product_status.id')
                ->pluck('count', 'status_name')
                ->toArray();

            return $results;
        } catch (\Exception $e) {
            \Log::error('Error getting serialized product status counts: ' . $e->getMessage());
            return [];
        }
    }
    // ============================================================
    // ✅ HELPER: Get purchase request status counts
    // ============================================================
    private function getPurchaseRequestStatusCounts(Request $request)
    {
        try {
            $query = PurchaseRequest::select(
                'purchase_status_library.name as status_name',
                DB::raw('count(purchase_request.id) as count')
            )
                ->join('purchase_status_library', 'purchase_request.status_id', '=', 'purchase_status_library.id');

            $this->applyDateFilter($query, $request, 'purchase_request');

            $results = $query->groupBy('purchase_status_library.name', 'purchase_status_library.id')
                ->pluck('count', 'status_name')
                ->toArray();

            return $results;
        } catch (\Exception $e) {
            \Log::error('Error getting purchase request status counts: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // ✅ HELPER: Get monthly products scanned
    // ============================================================
    private function getMonthlyProductsScanned(Request $request)
    {
        try {
            $query = SerializedProduct::select(
                DB::raw('MONTH(serialized_product.created_at) as month'),
                DB::raw('COUNT(*) as count')
            );

            $this->applyDateFilter($query, $request, 'serialized_product');

            $results = $query->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            return $results;
        } catch (\Exception $e) {
            \Log::error('Error getting monthly products scanned: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Monthly retailer sales (approved + completed) for the income chart — one series per calendar month.
     */
    private function getMonthlyRetailerSales(Request $request): array
    {
        $year = (int) Carbon::now()->year;
        if ($request->filled('filter_type')) {
            switch ($request->filter_type) {
                case 'last_month':
                    $year = (int) Carbon::now()->subMonth()->year;
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $year = (int) Carbon::parse($request->start_date)->year;
                    }
                    break;
            }
        }

        $amounts = array_fill(0, 12, 0.0);
        try {
            $rows = RetailerOrder::query()
                ->select(
                    DB::raw('MONTH(created_at) as m'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->whereYear('created_at', $year)
                ->whereIn('status', ['Completed', 'Approved', 'completed', 'approved'])
                ->groupBy('m')
                ->pluck('total', 'm');

            foreach ($rows as $month => $sum) {
                $idx = (int) $month - 1;
                if ($idx >= 0 && $idx < 12) {
                    $amounts[$idx] = round((float) $sum, 2);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error getting monthly retailer sales: ' . $e->getMessage());
        }

        return [
            'year'    => $year,
            'amounts' => $amounts,
        ];
    }

    // ============================================================
    // ✅ HELPER: Get low stock products
    // ============================================================
    private function getLowStockProducts()
    {
        try {
            // ✅ Consumables
            $consumables = \App\Models\ConsumableStock::with(['product'])
                ->orderBy('current_qty', 'asc')
                ->get()
                ->map(function ($stock) {
                    $status = $stock->getStockStatus();
                    return (object)[
                        'id'              => $stock->product_id,
                        'name'            => $stock->product->name ?? 'Unknown',
                        'system_sku'      => $stock->product->system_sku ?? 'N/A',
                        'available_count' => (int) ($stock->current_qty ?? 0),
                        'status_label'    => $status->label,
                        'status_color'    => $status->color,
                        'status_icon'     => $status->icon,
                    ];
                });

            // ✅ Non-consumables
            $serialized = \App\Models\SupplierProduct::query()
                ->where(function ($q) {
                    $q->whereNull('is_consumable')->orWhere('is_consumable', 0);
                })
                ->get()
                ->map(function ($p) {
                    $status = $p->getStockStatus();
                    return (object)[
                        'id'              => $p->id,
                        'name'            => $p->name ?? 'Unknown',
                        'system_sku'      => $p->system_sku ?? 'N/A',
                        'available_count' => (int) ($p->available_stock ?? 0),
                        'status_label'    => $status->label,
                        'status_color'    => $status->color,
                        'status_icon'     => $status->icon,
                    ];
                });

            // Combine and sort by available count (Priority to lowest)
            return $consumables
                ->concat($serialized)
                ->sortBy('available_count')
                ->take(15) // Show top 15 most urgent items (Red to Green)
                ->values();
        } catch (\Exception $e) {
            \Log::error('STOCK STATUS ERROR: ' . $e->getMessage());
            return collect();
        }
    }


    // ============================================================
    // ✅ HELPER: Get recent activities
    // ============================================================
    private function getRecentActivities()
    {
        $activities = collect();

        try {
            $recentPR = PurchaseRequest::with(['user', 'supplier'])
                ->whereNotNull('supplier_id')  // ✅ exclude yung walang supplier
                ->latest()
                ->limit(3)
                ->get();
            foreach ($recentPR as $pr) {
                $activities->push((object)[
                    'user_name'   => $pr->user->full_name ?? 'System',
                    'description' => "Created PR #" . ($pr->request_number ?? 'N/A') . " from " . ($pr->supplier->name ?? 'N/A'),
                    'time_ago'    => (string) $pr->created_at->diffForHumans(),
                    'icon'        => 'file-alt',
                    'type_color'  => 'primary',
                    'created_at'  => $pr->created_at,
                    'kind'        => 'purchase_request',
                    'url'         => route('pr.index', ['focus_pr' => $pr->id]),
                    'sales_month_index' => null,
                ]);
            }

            $recentPO = PurchaseOrder::with(['approvedBy', 'supplier'])->latest()->limit(3)->get();
            foreach ($recentPO as $po) {
                $activities->push((object)[
                    'user_name'   => $po->approvedBy->full_name ?? 'System',
                    'description' => "Created PO #" . ($po->po_number ?? 'N/A') . " from " . ($po->supplier->name ?? 'Unknown Supplier'),
                    'time_ago'    => (string) $po->created_at->diffForHumans(),
                    'icon'        => 'shopping-cart',
                    'type_color'  => 'success',
                    'created_at'  => $po->created_at,
                    'kind'        => 'purchase_order',
                    'url'         => route('purchase-order.scan', $po->id),
                    'sales_month_index' => null,
                ]);
            }

            $recentSP = SerializedProduct::with(['scannedBy', 'supplierProducts'])->latest()->limit(3)->get();
            foreach ($recentSP as $sp) {
                $serial = $sp->serial_number ?? '';
                $spUrl  = $serial !== ''
                    ? route('serialized_products.overview', $serial)
                    : route('serialized_products.index');

                $activities->push((object)[
                    'user_name'   => $sp->scannedBy->full_name ?? 'System',
                    'description' => "Scanned " . ($sp->supplierProducts->name ?? 'Unknown Product'),
                    'time_ago'    => (string) $sp->created_at->diffForHumans(),
                    'icon'        => 'barcode',
                    'type_color'  => 'info',
                    'created_at'  => $sp->created_at,
                    'kind'        => 'serialized_product',
                    'url'         => $spUrl,
                    'sales_month_index' => null,
                ]);
            }

            $recentRO = RetailerOrder::with(['creatorUser'])->latest()->limit(3)->get();
            foreach ($recentRO as $ro) {
                $created   = $ro->created_at;
                $monthIdx  = $created ? ((int) $created->format('n')) - 1 : null;
                $activities->push((object)[
                    'user_name'   => $ro->creatorUser->full_name ?? $ro->created_by ?? 'System',
                    'description' => "Created Retailer Order #" . ($ro->id) . " for " . ($ro->retailer_name ?? 'Unknown Retailer'),
                    'time_ago'    => (string) $ro->created_at->diffForHumans(),
                    'icon'        => 'store',
                    'type_color'  => 'warning',
                    'created_at'  => $ro->created_at,
                    'kind'        => 'retailer_order',
                    'url'         => route('retailer.orders.index', ['focus_order' => $ro->id]),
                    'sales_month_index' => $monthIdx,
                ]);
            }

            return $activities->sortByDesc('created_at')->take(10)->values();
        } catch (\Exception $e) {
            \Log::error('RECENT ACTIVITIES ERROR: ' . $e->getMessage());
            return collect();
        }
    }

    // ============================================================
    // ✅ EXPORT METHODS
    // ============================================================
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $filter = $request->get('filter', 'today');
        $data   = $this->getFilteredData($filter);

        switch ($format) {
            case 'pdf':
                return $this->exportPDF($data);
            case 'excel':
                return $this->exportExcel($data);
            case 'csv':
                return $this->exportCSV($data);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }

    private function getFilteredData($filter)
    {
        $startDate = null;
        $endDate   = null;

        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate   = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                if (request()->filled('start_date') && request()->filled('end_date')) {
                    $startDate = Carbon::parse(request()->start_date);
                    $endDate   = Carbon::parse(request()->end_date)->endOfDay();
                }
                break;
        }

        return [
            'small_boxes' => [
                'supplier_counts'         => Supplier::count(),
                'purchase_request_counts' => PurchaseRequest::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })->count(),
                'purchase_order_counts'   => PurchaseOrder::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })->count(),
                'serial_number_counts'    => $this->getAvailableProductCount(),
            ],
            'low_stock_products' => $this->getLowStockProducts(),
        ];
    }

    private function exportPDF($data)
    {
        $filter   = request()->get('filter', 'today');
        $pdf      = \PDF::loadView('dashboard.export-pdf', compact('data', 'filter'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($data)
    {
        $filter   = request()->get('filter', 'today');
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.xlsx';
        return \Excel::download(new \App\Exports\DashboardExport($data, $filter), $filename);
    }

    private function exportCSV($data)
    {
        $filename = 'dashboard-report-' . now()->format('Y-m-d') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Metric', 'Value', 'Date']);
            fputcsv($file, ['Suppliers', $data['small_boxes']['supplier_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Purchase Requests', $data['small_boxes']['purchase_request_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Purchase Orders', $data['small_boxes']['purchase_order_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Available Stock', $data['small_boxes']['serial_number_counts'], now()->format('Y-m-d')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getRecentRetailerOrders()
    {
        try {
            return RetailerOrder::with(['creatorUser'])
                ->latest()
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting retailer orders: ' . $e->getMessage());
            return collect();
        }
    }

    private function getTotalSalesFiltered(Request $request)
    {
        try {
            $query = RetailerOrder::whereIn('status', ['Completed', 'completed', 'Approved', 'approved']);

            if ($request->filled('filter_type')) {
                $this->applyDateFilter($query, $request);
            } else {
                $query->whereDate('created_at', Carbon::today());
            }

            return $query->sum('total_amount') ?? 0;
        } catch (\Exception $e) {
            \Log::error('Error getting filtered sales: ' . $e->getMessage());
            return 0;
        }
    }
}
