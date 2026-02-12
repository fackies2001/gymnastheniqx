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
    public function index(Request $request)
    {
        // ============================================
        // DATE FILTERING LOGIC
        // ============================================
        $filter = $request->get('filter', 'today');
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
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                }
                break;
        }

        // ============================================
        // SMALL BOXES DATA (with date filter)
        // ============================================
        $small_boxes = [
            'supplier_counts' => Supplier::count(),
            'purchase_request_counts' => PurchaseRequest::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count(),
            'purchase_order_counts' => PurchaseOrder::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })->count(),
            'serial_number_counts' => $this->getAvailableProductCount(),
        ];

        // ============================================
        // DOUGHNUT CHART DATA
        // ============================================
        $doughnut = [
            'product_status_counts' => $this->getSerializedProductStatusCounts($startDate, $endDate),
            'purchase_request_status_counts' => $this->getPurchaseRequestStatusCounts($startDate, $endDate),
        ];

        // ============================================
        // BAR CHART DATA (Monthly Products Scanned)
        // ============================================
        $bar = [
            'monthly_products_in' => SerializedProduct::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),
        ];

        // ============================================
        // LOW STOCK PRODUCTS
        // ============================================
        $low_stock_products = $this->getLowStockProducts();

        // ============================================
        // RECENT ACTIVITY FEED
        // ============================================
        $recent_activities = $this->getRecentActivities();

        return view('dashboard.index', compact(
            'small_boxes',
            'doughnut',
            'bar',
            'low_stock_products',
            'recent_activities'
        ));
    }

    /**
     * ✅ Get available product count with column detection
     */
    private function getAvailableProductCount()
    {
        try {
            // Try different possible column names for status
            if (Schema::hasColumn('serialized_product', 'product_status_id')) {
                return SerializedProduct::where('product_status_id', 1)->count();
            } elseif (Schema::hasColumn('serialized_product', 'status_id')) {
                return SerializedProduct::where('status_id', 1)->count();
            } elseif (Schema::hasColumn('serialized_product', 'status')) {
                return SerializedProduct::where('status', 1)->count();
            }

            // Default: count all products
            return SerializedProduct::count();
        } catch (\Exception $e) {
            \Log::error('Error counting available products: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ SIMPLIFIED: Get low stock products - Debug version
     */
    private function getLowStockProducts()
    {
        try {
            \Log::info('=== LOW STOCK DEBUG START ===');

            // Check if we have supplier products
            $totalSupplierProducts = SupplierProduct::count();
            \Log::info('Total Supplier Products in DB: ' . $totalSupplierProducts);

            if ($totalSupplierProducts === 0) {
                \Log::warning('No supplier products found - returning empty collection');
                return collect();
            }

            // Get the status column
            $statusColumn = $this->detectStatusColumn();
            \Log::info('Status column detected: ' . ($statusColumn ?? 'NONE'));

            $subquery = 'SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1';

            \Log::info('Subquery: ' . $subquery);

            // Get all products with their counts - Using correct column names
            $allProductsWithCounts = SupplierProduct::select(
                'supplier_product.id',
                'supplier_product.name',
                'supplier_product.system_sku',
                DB::raw("({$subquery}) as available_count")
            )
                ->orderBy('available_count', 'asc')
                ->get();

            \Log::info('All products with counts: ' . $allProductsWithCounts->count());
            \Log::info('Sample data: ' . json_encode($allProductsWithCounts->take(3)->toArray()));

            // Filter for low stock (below 20)
            $lowStockProducts = $allProductsWithCounts->filter(function ($product) {
                return $product->available_count < 20;
            })->take(10);

            \Log::info('Low stock products (below 20): ' . $lowStockProducts->count());

            // If no low stock, return all products sorted by count
            if ($lowStockProducts->isEmpty()) {
                \Log::info('No products below 20 units - returning lowest 10');
                return $allProductsWithCounts->take(10);
            }

            return $lowStockProducts->values();
        } catch (\Exception $e) {
            \Log::error('LOW STOCK ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect();
        }
    }

    /**
     * ✅ Detect which status column exists
     */
    private function detectStatusColumn()
    {
        if (Schema::hasColumn('serialized_product', 'product_status_id')) {
            return 'product_status_id';
        } elseif (Schema::hasColumn('serialized_product', 'status_id')) {
            return 'status_id';
        } elseif (Schema::hasColumn('serialized_product', 'status')) {
            return 'status';
        }

        return null;
    }

    /**
     * ✅ Get SerializedProduct status counts with actual status names
     */
    private function getSerializedProductStatusCounts($startDate, $endDate)
    {
        try {
            // ✅ Join with product_status to get status names
            return SerializedProduct::select(
                'product_status.name as status_name',
                DB::raw('count(serialized_product.id) as count')
            )
                ->join('product_status', 'serialized_product.status', '=', 'product_status.id')
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('serialized_product.created_at', [$startDate, $endDate]);
                })
                ->groupBy('product_status.name', 'product_status.id')
                ->pluck('count', 'status_name')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error getting serialized product status counts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ Get PurchaseRequest status counts with actual status names
     */
    private function getPurchaseRequestStatusCounts($startDate, $endDate)
    {
        try {
            // ✅ Join with purchase_status_library to get status names
            return PurchaseRequest::select(
                'purchase_status_library.name as status_name',
                DB::raw('count(purchase_request.id) as count')
            )
                ->join('purchase_status_library', 'purchase_request.status_id', '=', 'purchase_status_library.id')
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('purchase_request.created_at', [$startDate, $endDate]);
                })
                ->groupBy('purchase_status_library.name', 'purchase_status_library.id')
                ->pluck('count', 'status_name')
                ->toArray();
        } catch (\Exception $e) {
            \Log::error('Error getting purchase request status counts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ SIMPLIFIED: Get recent activities - Debug version
     */
    private function getRecentActivities()
    {
        $activities = collect();

        try {
            \Log::info('=== RECENT ACTIVITIES DEBUG START ===');

            // Check Purchase Requests
            $prCount = PurchaseRequest::count();
            \Log::info('Total Purchase Requests: ' . $prCount);

            if ($prCount > 0) {
                $recentPR = PurchaseRequest::latest()->limit(3)->get();
                \Log::info('Recent PR count: ' . $recentPR->count());

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
            }

            // Check Purchase Orders
            $poCount = PurchaseOrder::count();
            \Log::info('Total Purchase Orders: ' . $poCount);

            if ($poCount > 0) {
                $recentPO = PurchaseOrder::latest()->limit(3)->get();
                \Log::info('Recent PO count: ' . $recentPO->count());

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
            }

            // Check Serialized Products
            $spCount = SerializedProduct::count();
            \Log::info('Total Serialized Products: ' . $spCount);

            if ($spCount > 0) {
                $recentSP = SerializedProduct::latest()->limit(3)->get();
                \Log::info('Recent SP count: ' . $recentSP->count());

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
            }

            $activities = $activities->sortByDesc('created_at')->take(10)->values();
            \Log::info('Total activities returned: ' . $activities->count());

            return $activities;
        } catch (\Exception $e) {
            \Log::error('RECENT ACTIVITIES ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect();
        }
    }

    /**
     * Export dashboard data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $filter = $request->get('filter', 'today');

        // Get filtered data
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

    /**
     * Get filtered data for export
     */
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

        // Get all data with filters
        $data = [
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
            'doughnut' => [
                'product_status_counts' => $this->getSerializedProductStatusCounts($startDate, $endDate),
                'purchase_request_status_counts' => $this->getPurchaseRequestStatusCounts($startDate, $endDate),
            ],
            'bar' => [
                'monthly_products_in' => SerializedProduct::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                    ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                        return $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('count', 'month')
                    ->toArray(),
            ],
            'low_stock_products' => $this->getLowStockProducts(),
        ];

        return $data;
    }

    /**
     * Export as PDF
     */
    private function exportPDF($data)
    {
        $filter = request()->get('filter', 'today');

        // Load the PDF view
        $pdf = \PDF::loadView('dashboard.export-pdf', compact('data', 'filter'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename with timestamp
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.pdf';

        // Download the PDF
        return $pdf->download($filename);
    }

    /**
     * Export as Excel
     */
    private function exportExcel($data)
    {
        $filter = request()->get('filter', 'today');

        // Generate filename with timestamp
        $filename = 'dashboard-report-' . now()->format('Y-m-d-His') . '.xlsx';

        // Use the DashboardExport class
        return \Excel::download(new \App\Exports\DashboardExport($data, $filter), $filename);
    }

    /**
     * Export as CSV
     */
    private function exportCSV($data)
    {
        // Generate CSV file
        $filename = 'dashboard-report-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['Metric', 'Value', 'Date']);

            // Add summary data
            fputcsv($file, ['Suppliers', $data['small_boxes']['supplier_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Purchase Requests', $data['small_boxes']['purchase_request_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Purchase Orders', $data['small_boxes']['purchase_order_counts'], now()->format('Y-m-d')]);
            fputcsv($file, ['Available Stock', $data['small_boxes']['serial_number_counts'], now()->format('Y-m-d')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
