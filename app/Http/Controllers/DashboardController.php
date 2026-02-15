<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Supplier;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\SerializedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ============================================================
    // ✅ MAIN INDEX METHOD WITH DATE FILTERING
    // ============================================================
    public function index(Request $request)
    {
        // Small boxes data
        $small_boxes = [
            'supplier_counts' => Supplier::count(),
            'purchase_request_counts' => $this->getPurchaseRequestCount($request),
            'purchase_order_counts' => $this->getPurchaseOrderCount($request),
            'serial_number_counts' => $this->getAvailableProductCount(),
        ];

        // Doughnut chart data
        $doughnut = [
            'product_status_counts' => $this->getSerializedProductStatusCounts($request),
            'purchase_request_status_counts' => $this->getPurchaseRequestStatusCounts($request),
        ];

        // Bar chart data
        $bar = [
            'monthly_products_in' => $this->getMonthlyProductsScanned($request),
        ];

        // Low stock products and recent activities
        $low_stock_products = $this->getLowStockProducts();
        $recent_activities = $this->getRecentActivities();

        return view('dashboard.index', compact(
            'small_boxes',
            'doughnut',
            'bar',
            'low_stock_products',
            'recent_activities'
        ));
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

            // ✅ Apply date filter with table name specified
            $this->applyDateFilter($query, $request, 'serialized_product');

            $results = $query->groupBy('product_status.name', 'product_status.id')
                ->pluck('count', 'status_name')
                ->toArray();

            \Log::info('Product Status Counts:', $results);

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

            // ✅ Apply date filter with table name specified
            $this->applyDateFilter($query, $request, 'purchase_request');

            $results = $query->groupBy('purchase_status_library.name', 'purchase_status_library.id')
                ->pluck('count', 'status_name')
                ->toArray();

            \Log::info('Purchase Request Status Counts:', $results);

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

            // ✅ Apply date filter with table name specified
            $this->applyDateFilter($query, $request, 'serialized_product');

            $results = $query->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            \Log::info('Monthly Products Scanned:', $results);

            return $results;
        } catch (\Exception $e) {
            \Log::error('Error getting monthly products scanned: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // ✅ HELPER: Get low stock products
    // ============================================================
    private function getLowStockProducts()
    {
        try {
            $subquery = 'SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1';

            $allProductsWithCounts = SupplierProduct::select(
                'supplier_product.id',
                'supplier_product.name',
                'supplier_product.system_sku',
                DB::raw("({$subquery}) as available_count")
            )
                ->orderBy('available_count', 'asc')
                ->get();

            $lowStockProducts = $allProductsWithCounts->filter(function ($product) {
                return $product->available_count < 20;
            })->take(10);

            if ($lowStockProducts->isEmpty()) {
                return $allProductsWithCounts->take(10);
            }

            return $lowStockProducts->values();
        } catch (\Exception $e) {
            \Log::error('LOW STOCK ERROR: ' . $e->getMessage());
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
            // Purchase Requests
            $recentPR = PurchaseRequest::latest()->limit(3)->get();
            foreach ($recentPR as $pr) {
                $activities->push((object)[
                    'user_name' => 'System',
                    'description' => "Purchase Request created (ID: {$pr->id})",
                    'time_ago' => $pr->created_at->diffForHumans(),
                    'icon' => 'file-alt',
                    'type_color' => 'primary',
                    'created_at' => $pr->created_at,
                ]);
            }

            // Purchase Orders
            $recentPO = PurchaseOrder::latest()->limit(3)->get();
            foreach ($recentPO as $po) {
                $activities->push((object)[
                    'user_name' => 'System',
                    'description' => "Purchase Order created (ID: {$po->id})",
                    'time_ago' => $po->created_at->diffForHumans(),
                    'icon' => 'shopping-cart',
                    'type_color' => 'success',
                    'created_at' => $po->created_at,
                ]);
            }

            // Serialized Products
            $recentSP = SerializedProduct::latest()->limit(3)->get();
            foreach ($recentSP as $sp) {
                $serialNum = $sp->serial_number ?? $sp->id;
                $activities->push((object)[
                    'user_name' => 'System',
                    'description' => "Product scanned (Serial: {$serialNum})",
                    'time_ago' => $sp->created_at->diffForHumans(),
                    'icon' => 'barcode',
                    'type_color' => 'info',
                    'created_at' => $sp->created_at,
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
        $data = $this->getFilteredData($filter);

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
        $endDate = null;

        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                if (request()->filled('start_date') && request()->filled('end_date')) {
                    $startDate = Carbon::parse(request()->start_date);
                    $endDate = Carbon::parse(request()->end_date)->endOfDay();
                }
                break;
        }

        return [
            'small_boxes' => [
                'supplier_counts' => Supplier::count(),
                'purchase_request_counts' => PurchaseRequest::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })->count(),
                'purchase_order_counts' => PurchaseOrder::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })->count(),
                'serial_number_counts' => $this->getAvailableProductCount(),
            ],
            'low_stock_products' => $this->getLowStockProducts(),
        ];
    }

    private function exportPDF($data)
    {
        $filter = request()->get('filter', 'today');
        $pdf = \PDF::loadView('dashboard.export-pdf', compact('data', 'filter'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.pdf';
        return $pdf->download($filename);
    }

    private function exportExcel($data)
    {
        $filter = request()->get('filter', 'today');
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.xlsx';
        return \Excel::download(new \App\Exports\DashboardExport($data, $filter), $filename);
    }

    private function exportCSV($data)
    {
        $filename = 'dashboard-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
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
}
