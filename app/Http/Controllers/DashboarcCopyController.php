<?php
/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupplierProductServices;
use App\Services\SystemProductServices;
use App\Models\PurchaseOrder;
use App\Models\SerializedProduct;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected $supplierProductServices;
    protected $systemProductServices;

    public function __construct(
        SupplierProductServices $supplierProductServices,
        SystemProductServices $systemProductServices,
    ) {
        $this->supplierProductServices = $supplierProductServices;
        $this->systemProductServices = $systemProductServices;
    }

    public function index()
    {
        Log::info('========================================');
        Log::info('📊 DASHBOARD LOADED - Session Check:', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->full_name ?? 'Unknown',
            'has_pin' => !empty(auth()->user()->pin) ? 'YES' : 'NO',
            'session_show_pin_modal' => session('show_pin_modal'),
            'session_pin_verified' => session('pin_verified'),
            'session_pin_mode' => session('pin_mode'),
            'warehouse_id' => auth()->user()->warehouse_id ?? null,
            'session_warehouse' => session('warehouse_id'),
            'time' => now()
        ]);
        Log::info('========================================');

        // ✅ Suppliers count
        $supplier_counts = $this->supplierProductServices->get_all_supplier()->count();

        // ✅ Purchase Requests count
        $purchase_request_counts = $this->systemProductServices->get_all_purchase_request()->count();

        // ✅ Purchase Orders count
        $purchase_order_counts = PurchaseOrder::query()
            ->filterByStudent()
            ->filterByWarehouse()
            ->count();

        // ✅ Count AVAILABLE serialized products only
        $serial_number_counts = SerializedProduct::query()
            ->filterByStudent()
            ->filterByWarehouse()
            ->where('status', 1)
            ->count();

        // ✅ Monthly Products Scanned
        $serial_numbers = SerializedProduct::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = collect(range(1, 12))
            ->mapWithKeys(fn($m) => [$m => $serial_numbers[$m] ?? 0]);

        // ✅ Product Status Counts
        $product_status_counts = $this->getProductStatusCounts();

        // ✅ Purchase Request Status Counts
        $purchase_request_status_counts = $this->systemProductServices->get_count_purchase_request_per_status();

        // 🔥 LOW STOCK ALERT - SIMPLIFIED APPROACH
        // Using RAW SQL for maximum reliability
        $warehouseId = session('warehouse_id') ?? auth()->user()->warehouse_id ?? null;

        $sql = "
            SELECT 
                sp.id,
                sp.name,
                sp.system_sku,
                COUNT(CASE WHEN ser.status = 1 THEN 1 END) as available_count
            FROM supplier_product sp
            LEFT JOIN serialized_product ser ON ser.product_id = sp.id
        ";

        // Add warehouse filter only if warehouse_id is set
        if ($warehouseId) {
            $sql .= " AND (ser.warehouse_id = ? OR ser.warehouse_id IS NULL)";
            $low_stock_products = DB::select($sql . "
                GROUP BY sp.id, sp.name, sp.system_sku
                HAVING available_count < 20 AND available_count > 0
                ORDER BY available_count ASC
                LIMIT 20
            ", [$warehouseId]);
        } else {
            // No warehouse filter
            $low_stock_products = DB::select($sql . "
                GROUP BY sp.id, sp.name, sp.system_sku
                HAVING available_count < 20 AND available_count > 0
                ORDER BY available_count ASC
                LIMIT 20
            ");
        }

        // Convert to collection for blade compatibility
        $low_stock_products = collect($low_stock_products)->map(function ($item) {
            return (object)[
                'id' => $item->id,
                'name' => $item->name,
                'system_sku' => $item->system_sku,
                'available_count' => $item->available_count
            ];
        });

        Log::info('🔍 LOW STOCK RESULTS:', [
            'warehouse_id' => $warehouseId,
            'count' => $low_stock_products->count(),
            'data' => $low_stock_products->toArray()
        ]);

        $small_boxes = [
            'supplier_counts' => $supplier_counts,
            'purchase_request_counts' => $purchase_request_counts,
            'purchase_order_counts' => $purchase_order_counts,
            'serial_number_counts' => $serial_number_counts,
        ];

        $doughnut = [
            'product_status_counts' => $product_status_counts,
            'purchase_request_status_counts' => $purchase_request_status_counts,
        ];

        $bar = [
            'monthly_products_in' => $months->values()->toArray(),
        ];

        $list = [
            'serialized_products' => [],
        ];

        return view('dashboard.index', compact('small_boxes', 'doughnut', 'bar', 'list', 'low_stock_products'));
    }

    private function getProductStatusCounts()
    {
        $columns = Schema::getColumnListing('product_status');

        $possibleNames = [
            'status_name',
            'name',
            'product_status_name',
            'status',
            'label',
            'title'
        ];

        $nameColumn = null;
        foreach ($possibleNames as $possible) {
            if (in_array($possible, $columns)) {
                $nameColumn = $possible;
                break;
            }
        }

        if (!$nameColumn) {
            Log::warning('Could not find name column in product_status table. Using id instead.');
            $nameColumn = 'id';
        }

        try {
            $result = SerializedProduct::join('product_status', 'serialized_product.status', '=', 'product_status.id')
                ->select("product_status.{$nameColumn} as name", DB::raw('count(serialized_product.id) as total'))
                ->groupBy("product_status.{$nameColumn}")
                ->get()
                ->toArray();

            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting product status counts: ' . $e->getMessage());
            return [];
        }
    }
}
