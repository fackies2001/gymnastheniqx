<?php

namespace App\Http\Controllers;

use App\Models\RetailerOrder;
use App\Models\SerializedProduct;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
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

        // ⭐ Calculate date/range based on filter type
        $date = null;
        $dateQuery = null;

        if ($filterType === 'all_time' || !$filterType) {
            $date = null; // No filter
        } elseif ($filterType === 'today') {
            $date = Carbon::today()->toDateString();
        } elseif ($filterType === 'yesterday') {
            $date = Carbon::yesterday()->toDateString();
        } elseif ($filterType === 'custom' && $customDate) {
            $date = $customDate;
        } else {
            $date = Carbon::today()->toDateString();
        }

        // ✅ Low Stock = Products with available count below 20 (no date filter)
        $subquery = 'SELECT COUNT(*) FROM serialized_product WHERE serialized_product.product_id = supplier_product.id AND serialized_product.status = 1';
        $lowStockCount = SupplierProduct::select('supplier_product.id', DB::raw("({$subquery}) as available_count"))
            ->havingRaw('available_count < 20')
            ->count();

        // ✅ Daily Received = Scanned items from PO with status Available
        $receivedQuery = SerializedProduct::where('status', 1)
            ->whereNotNull('purchase_order_id');
        if ($date) {
            $receivedQuery->whereDate('created_at', $date);
        }
        $newArrivals = $receivedQuery->count();

        // ✅ Daily Outflow = Retailer Orders that were Approved/Completed
        $outflowQuery = RetailerOrder::whereIn('status', ['Approved', 'Completed']);
        if ($date) {
            $outflowQuery->whereDate('updated_at', $date);
        }
        $dailyOutflow = $outflowQuery->sum('quantity');

        // ✅ Damaged Count — status 4 OR 5
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
        $type = $request->get('type', null);

        // ⭐ Calculate date based on filter type
        $date = null;
        $dateQuery = null;

        if ($filterType === 'all_time' || !$filterType) {
            // No date filter - show all records
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

            // ===== DAILY RECEIVED: SCANNED ITEMS FROM PO ONLY =====
            if (!$type || $type === 'received') {
                try {
                    \Log::info("Fetching Scanned Products from PO...");

                    // ⭐ UPDATED: Build query with flexible date filtering
                    $query = SerializedProduct::with(['supplierProducts.supplier', 'supplierProducts.category', 'productStatus'])
                        ->whereNotNull('purchase_order_id'); // Must have PO ID (from scanning)

                    // Apply date filter based on filter type
                    if ($date) {
                        // Single date filter
                        $query->whereDate('created_at', $date);
                    } elseif ($dateQuery) {
                        // Date range filter
                        $query->whereBetween('created_at', [$dateQuery['start'], $dateQuery['end']]);
                    }
                    // If no date filter, show all records

                    $serializedProducts = $query->get();

                    $groupedProducts = [];

                    foreach ($serializedProducts as $item) {
                        $productId = ($item->supplierProducts && $item->supplierProducts->id) ? $item->supplierProducts->id : 'unknown';
                        $productName = ($item->supplierProducts && $item->supplierProducts->name) ? $item->supplierProducts->name : 'Unnamed Product';

                        if (!isset($groupedProducts[$productId])) {
                            $categoryName = ($item->productStatus && $item->productStatus->name) ? $item->productStatus->name : 'General';
                            $supplierName = 'N/A';
                            if ($item->supplierProducts && $item->supplierProducts->supplier) {
                                $supplierName = $item->supplierProducts->supplier->name ?? 'N/A';
                            }
                            $productImage = ($item->supplierProducts && $item->supplierProducts->thumbnail) ? $item->supplierProducts->thumbnail : null;

                            $groupedProducts[$productId] = [
                                'product_name' => $productName,
                                'category_name' => $categoryName,
                                'supplier_name' => $supplierName,
                                'quantity' => 0,
                                'image' => $productImage,
                                'serial_numbers' => [],
                                'first_received' => $item->created_at
                            ];
                        }

                        $groupedProducts[$productId]['quantity']++;
                        $groupedProducts[$productId]['serial_numbers'][] = $item->serial_number ?? 'N/A';
                    }

                    foreach ($groupedProducts as $productData) {
                        $serialNumbersList = implode(', ', array_slice($productData['serial_numbers'], 0, 5));
                        if (count($productData['serial_numbers']) > 5) {
                            $serialNumbersList .= '... +' . (count($productData['serial_numbers']) - 5) . ' more';
                        }

                        $receivedDate = Carbon::parse($productData['first_received'])->format('M d, Y h:i A');

                        $data[] = [
                            'product_name' => '<strong style="font-size: 16px; color: black;">' . $productData['product_name'] . '</strong><br><span style="font-size: 13px; color: #666;">Serial Numbers: ' . $serialNumbersList . '</span><br><span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $receivedDate . '</span>',
                            'category_name' => '<span class="badge badge-info">' . $productData['category_name'] . '</span>',
                            'traceability' => '<small><strong>Type:</strong> Scanned from PO<br><strong>Supplier:</strong> ' . $productData['supplier_name'] . '<br><strong>Total Received:</strong> ' . $productData['quantity'] . ' pcs<br><strong>Date Received:</strong> ' . $receivedDate . '</small>',
                            'quantity' => $productData['quantity'],
                            'image' => $productData['image'],
                            'status' => 'Received'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Scanned Products Section Error: " . $e->getMessage());
                }
            }

            // ===== OUTFLOW =====
            if (!$type || $type === 'outflow') {
                try {
                    // ⭐ Build query with flexible date filtering
                    $query = RetailerOrder::with(['product'])
                        ->whereIn('status', ['Approved', 'Completed']);

                    // Apply date filter
                    if ($date) {
                        $query->whereDate('updated_at', $date);
                    } elseif ($dateQuery) {
                        $query->whereBetween('updated_at', [$dateQuery['start'], $dateQuery['end']]);
                    }

                    $retailerOrders = $query->get();

                    $groupedOrders = [];

                    foreach ($retailerOrders as $order) {
                        $productKey = $order->product_name ?? 'Unknown Product';
                        $updatedDate = Carbon::parse($order->updated_at)->format('M d, Y h:i A');

                        if (!isset($groupedOrders[$productKey])) {
                            $groupedOrders[$productKey] = [
                                'product_name' => $productKey,
                                'total_qty' => 0,
                                'total_amount' => 0,
                                'retailer_names' => [],
                                'serial_numbers' => [],
                                'last_updated' => $updatedDate,
                                'image' => $order->product->thumbnail ?? null,
                            ];
                        }

                        $groupedOrders[$productKey]['total_qty'] += (int) $order->quantity;
                        $groupedOrders[$productKey]['total_amount'] += (float) $order->total_amount;
                        $groupedOrders[$productKey]['retailer_names'][] = $order->retailer_name ?? 'N/A';
                    }

                    foreach ($groupedOrders as $orderData) {
                        $retailerList = implode(', ', array_unique($orderData['retailer_names']));

                        $data[] = [
                            'product_name' => '<strong style="font-size: 16px; color: black;">' . e($orderData['product_name']) . '</strong><br><span style="font-size: 13px; color: #666;">Distributed to: ' . e($retailerList) . '</span><br><span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $orderData['last_updated'] . '</span>',
                            'category_name' => '<span class="badge badge-success">Outflow</span>',
                            'traceability' => '<small><strong>Type:</strong> Retailer Order<br><strong>Total Qty Out:</strong> ' . $orderData['total_qty'] . ' pcs<br><strong>Date:</strong> ' . $orderData['last_updated'] . '</small>',
                            'quantity' => $orderData['total_qty'],
                            'image' => $orderData['image'],
                            'status' => 'Outflow'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Outflow Section Error: " . $e->getMessage());
                }
            }

            // ===== DAMAGED =====
            if (!$type || $type === 'damage') {
                try {
                    // ⭐ Build query with flexible date filtering
                    $query = SerializedProduct::with(['supplierProducts.supplier'])
                        ->whereIn('status', [4, 5]);

                    // Apply date filter
                    if ($date) {
                        $query->whereDate('updated_at', $date);
                    } elseif ($dateQuery) {
                        $query->whereBetween('updated_at', [$dateQuery['start'], $dateQuery['end']]);
                    }

                    $damagedItems = $query->get();

                    $groupedDamaged = [];

                    foreach ($damagedItems as $item) {
                        $productId = ($item->supplierProducts && $item->supplierProducts->id) ? $item->supplierProducts->id : 'unknown';
                        $productName = ($item->supplierProducts && $item->supplierProducts->name) ? $item->supplierProducts->name : 'Unnamed Product';
                        $updatedDate = Carbon::parse($item->updated_at)->format('M d, Y h:i A');

                        if (!isset($groupedDamaged[$productId])) {
                            $supplierName = 'N/A';
                            if ($item->supplierProducts && $item->supplierProducts->supplier) {
                                $supplierName = $item->supplierProducts->supplier->name ?? 'N/A';
                            }

                            $groupedDamaged[$productId] = [
                                'product_name' => $productName,
                                'supplier_name' => $supplierName,
                                'quantity' => 0,
                                'serial_numbers' => [],
                                'last_updated' => $updatedDate,
                                'image' => $item->supplierProducts->thumbnail ?? null,
                            ];
                        }

                        $groupedDamaged[$productId]['quantity']++;
                        $groupedDamaged[$productId]['serial_numbers'][] = $item->serial_number ?? 'N/A';
                    }

                    foreach ($groupedDamaged as $damagedData) {
                        $serialNumbersList = implode(', ', array_slice($damagedData['serial_numbers'], 0, 5));
                        if (count($damagedData['serial_numbers']) > 5) {
                            $serialNumbersList .= ' ... +' . (count($damagedData['serial_numbers']) - 5) . ' more';
                        }

                        $data[] = [
                            'product_name' => '<strong style="font-size: 16px; color: black;">' . e($damagedData['product_name']) . '</strong><br><span style="font-size: 13px; color: #666;">Serial Numbers: ' . $serialNumbersList . '</span><br><span style="font-size: 12px; color: #999;"><i class="far fa-clock"></i> ' . $damagedData['last_updated'] . '</span>',
                            'category_name' => '<span class="badge badge-danger">Damaged</span>',
                            'traceability' => '<small><strong>Type:</strong> Damaged<br><strong>Total Damaged:</strong> ' . $damagedData['quantity'] . ' pcs<br><strong>Date:</strong> ' . $damagedData['last_updated'] . '</small>',
                            'quantity' => $damagedData['quantity'],
                            'image' => $damagedData['image'],
                            'status' => 'Damaged'
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
                            $badge = 'badge-danger';
                        } elseif ($qty <= 10) {
                            $urgency = 'WARNING';
                            $badge = 'badge-warning';
                        } else {
                            $urgency = 'LOW';
                            $badge = 'badge-info';
                        }

                        $data[] = [
                            'product_name' => '<strong style="font-size: 16px; color: black;">' . e($product->name) . '</strong><br><span style="font-size: 13px; color: #666;">SKU: ' . e($product->system_sku ?? 'N/A') . '</span><br><span class="badge ' . $badge . '">' . $urgency . ' - ' . $qty . ' units</span>',
                            'category_name' => '<span class="badge badge-warning">Low Stock</span>',
                            'traceability' => '<small><strong>Type:</strong> Low Stock<br><strong>Available:</strong> ' . $qty . ' units<br><strong>Status:</strong> ' . $urgency . '</small>',
                            'quantity' => $qty,
                            'image' => $product->thumbnail,
                            'status' => 'Low Stock'
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::error("Low Stock Section Error: " . $e->getMessage());
                }
            }

            \Log::info("Total data rows: " . count($data));
            \Log::info("=== DAILY REPORT END ===");

            // ✅ CORRECT DATATABLES FORMAT
            return response()->json([
                'draw' => (int) $request->get('draw', 1),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Log::error('Daily Report Error: ' . $e->getMessage());
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
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

    // ===== WEEKLY REPORT SECTION =====
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
            default: // last_7_days
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
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        $inventoryAnalysis = [];
        $products = SupplierProduct::all();

        foreach ($products as $prod) {
            $currentStock = SerializedProduct::where('product_id', $prod->id)
                ->where('status', 1)
                ->count();

            $weeklySales = RetailerOrder::where('product_name', $prod->name)
                ->whereIn('status', ['Approved', 'Completed'])
                ->whereBetween('updated_at', [$startDate, $endDate])
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
            'filterType'  // ✅ para ma-retain ang selected filter sa blade
        ));
    }

    // ===== MONTHLY REPORT SECTION =====

    public function monthlyIndex(Request $request)
    {
        // ✅ FIX #1 — Month/Year Filter
        // Get selected month and year from request, default to current month/year
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear  = $request->get('year', Carbon::now()->year);

        // Build the selected date for display and range calculation
        $selectedDate     = Carbon::create($selectedYear, $selectedMonth, 1);
        $startOfMonth     = $selectedDate->copy()->startOfMonth();
        $endOfMonth       = $selectedDate->copy()->endOfMonth();
        $startOfLastMonth = $selectedDate->copy()->subMonth()->startOfMonth();
        $endOfLastMonth   = $selectedDate->copy()->subMonth()->endOfMonth();

        // Available years for the year dropdown (from oldest PO or RetailerOrder up to current year)
        $oldestOrder  = RetailerOrder::min('created_at') ?? Carbon::now();
        $startYear    = Carbon::parse($oldestOrder)->year;
        $currentYear  = Carbon::now()->year;
        $availableYears = range($currentYear, $startYear);

        // ✅ Total Inventory Asset Value (real-time, not date-filtered — reflects current stock)
        $allProducts = SupplierProduct::all();
        $totalInventoryValue = 0;

        foreach ($allProducts as $product) {
            $stockCount = SerializedProduct::where('product_id', $product->id)
                ->where('status', 1)
                ->count();

            $totalInventoryValue += $stockCount * ($product->cost_price ?? 0);
        }

        // ✅ Current Month Sales (filtered by selected month)
        $currentMonthSales = RetailerOrder::whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        // ✅ Last Month Sales (previous month relative to selected month)
        $lastMonthSales = RetailerOrder::whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('updated_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        // Growth calculation
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

        // ✅ Top 5 Revenue Generators (filtered by selected month)
        $topProducts = RetailerOrder::select(
            'product_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(total_amount) as total_revenue')
        )
            ->whereIn('status', ['Approved', 'Completed'])
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // ✅ FIX #3 — Supplier Performance scoped to selected month
        // BEFORE (WRONG): No date filter — was showing all-time supplier data
        // AFTER  (FIXED): whereBetween order_date scoped to selected month
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
            'selectedDate',   // ✅ replaces $now — carries selected month/year
            'availableYears'  // ✅ for the year dropdown
        ));
    }

    // ===== STRATEGIC REPORT =====
    public function strategicIndex(Request $request)
    {
        $selectedYear = $request->get('year', Carbon::now()->year);

        $oldestDate  = RetailerOrder::min('created_at') ?? Carbon::now();
        $startYear   = Carbon::parse($oldestDate)->year;
        $currentYear = Carbon::now()->year;
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

            // ✅ FIX #1 — Monthly Revenue: Include BOTH 'Approved' AND 'Completed'
            // BEFORE (WRONG): ->where('status', 'Approved')
            // AFTER  (FIXED): ->whereIn('status', ['Approved', 'Completed'])
            $revenue = RetailerOrder::whereIn('status', ['Approved', 'Completed'])  // ✅ FIXED
                ->whereYear('updated_at', $selectedYear)
                ->whereMonth('updated_at', $m)
                ->sum('total_amount');

            // Cost calculation (unchanged — queries purchase_order, was fine)
            // ✅ FIXED — grand_total is directly on purchase_order table
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

        // ✅ FIX #2 — Dead Stock: Use serialized_product count instead of $prod->stock
        // BEFORE (WRONG): if ($prod->stock > 0) — $prod->stock doesn't exist!
        // AFTER  (FIXED): Count serialized_product rows with status = 1
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $allProducts  = SupplierProduct::all();
        $deadStocks   = [];

        foreach ($allProducts as $prod) {
            // ✅ Get actual stock count from serialized_product table
            $stockCount = SerializedProduct::where('product_id', $prod->id)
                ->where('status', 1)   // ✅ status=1 = available/in_inventory
                ->count();

            $lastSale = RetailerOrder::where('product_name', $prod->name)
                ->whereIn('status', ['Approved', 'Completed'])  // ✅ FIXED
                ->latest('updated_at')
                ->first();

            // ✅ Only flag as dead stock if has physical stock but no recent sales
            if ($stockCount > 0) {
                if (!$lastSale || $lastSale->updated_at < $sixMonthsAgo) {
                    $deadStocks[] = [
                        'name'      => $prod->name,
                        'stock'     => $stockCount,   // ✅ real count
                        'value'     => $stockCount * ($prod->cost_price ?? 0),
                        'last_sold' => $lastSale ? $lastSale->updated_at->format('M d, Y') : 'Never Sold'
                    ];
                }
            }
        }

        // ✅ FIX #3 — Top Items for Forecast: Same status fix
        $topItems = RetailerOrder::select('product_name', DB::raw('SUM(quantity) as total_qty'))
            ->whereIn('status', ['Approved', 'Completed'])  // ✅ FIXED
            ->whereYear('updated_at', $selectedYear)
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        // ✅ FIX #4 — Projected Stocks: Use real stock count
        $projectedStocks = [];
        foreach ($topItems as $item) {
            $prodDetails  = SupplierProduct::where('name', $item->product_name)->first();

            // ✅ Real stock count instead of $prodDetails->stock
            $currentStock = 0;
            if ($prodDetails) {
                $currentStock = SerializedProduct::where('product_id', $prodDetails->id)
                    ->where('status', 1)   // ✅ status=1 = available
                    ->count();
            }

            $forecast = ceil($item->total_qty * 1.10);

            $projectedStocks[] = [
                'product'  => $item->product_name,
                'sold'     => $item->total_qty,
                'forecast' => $forecast,
                'current'  => $currentStock   // ✅ real count
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
            $auditItems  = $request->input('audit_items', []);   // array of items
            $auditPeriod = $request->input('audit_period', '');
            $auditedBy   = auth()->user()->full_name ?? auth()->user()->name ?? 'Unknown';

            if (empty($auditItems)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No audit data to save.'
                ], 422);
            }

            $saved = 0;

            foreach ($auditItems as $item) {
                // Skip rows where actual_count was not filled in
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
        // Get distinct audit periods for the filter dropdown
        $auditPeriods = InventoryAudit::select('audit_period', DB::raw('MAX(created_at) as latest'))
            ->groupBy('audit_period')
            ->orderByDesc('latest')
            ->pluck('audit_period');

        // Filter by selected period if provided
        $selectedPeriod = $request->get('period', null);

        $query = InventoryAudit::orderByDesc('created_at');

        if ($selectedPeriod) {
            $query->where('audit_period', $selectedPeriod);
        }

        $auditRecords = $query->get();

        // Group by audit_period + audited_by + created_at date for display
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
