<?php

namespace App\Http\Controllers;
/*
use App\Models\SupplierProduct;
use App\Models\Category;
use App\Models\Supplier;
use App\Helpers\SkuHelper;
use App\Services\DatatableServices;
use App\Services\SupplierProductServices;
use App\Http\Requests\StoreSupplierProductRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierProductsController extends Controller
{
    protected $datatableService;
    protected $supplierProductServices;

    public function __construct(
        SupplierProductServices $supplierProductServices,
        DatatableServices $datatableService
    ) {
        $this->supplierProductServices = $supplierProductServices;
        $this->datatableService = $datatableService;
    }

    public function index()
    {
        $suppliers = $this->supplierProductServices->get_supplier_pluck_name_id();
        $categories = $this->supplierProductServices->get_category_pluck_name_id();

        return view('supplier_products.index', compact('suppliers', 'categories'));
    }

    public function store(StoreSupplierProductRequest $request)
    {
        // 1. I-sync ang category_id mula sa Blade papunta sa 'category' field na hanap ng Service
        $request->merge(['category' => $request->category_id]);

        // 2. Tawagin ang Service para sa SKU generation at DB saving
        return $this->supplierProductServices->store_supplier_product($request);
    }

    /**
     * SCAN BARCODE METHOD - Para sa Scanner Gun functionality
     */
    /*
    public function scan($barcode)
    {
        try {
            $product = SupplierProduct::where(function ($query) use ($barcode) {
                $query->where('barcode', $barcode)
                    ->orWhere('supplier_sku', $barcode)
                    ->orWhere('system_sku', $barcode);
            })
                ->with(['supplier', 'category'])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with barcode: ' . $barcode
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product found!',
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'supplier_sku' => $product->supplier_sku ?? $product->system_sku,
                    'system_sku' => $product->system_sku,
                    'barcode' => $product->barcode,
                    'cost_price' => $product->cost_price,
                    'supplier_id' => $product->supplier_id,
                    'supplier_name' => $product->supplier?->name ?? 'N/A',
                    'category_id' => $product->category_id,
                    'category_name' => $product->category?->name ?? 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error scanning barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- 🔥 FIXED DATATABLE METHOD ---
    public function datatable(Request $request)
    {
        $query = SupplierProduct::with(['supplier', 'category'])
            ->select('supplier_product.*');

        return DataTables::of($query)
            ->addIndexColumn() // ✅ This adds DT_RowIndex automatically
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier?->name ?? 'N/A';
            })
            ->editColumn('cost_price', function ($row) {
                return number_format($row->cost_price, 2);
            })
            ->editColumn('barcode', function ($row) {
                return $row->barcode ?? 'N/A';
            })
            ->rawColumns(['supplier_name'])
            ->make(true);
    }

    public function showTable(Request $request, $id = null)
    {
        $supplier_product_id = $id ?? $request->id;
        if (!$supplier_product_id) {
            return DataTables::of(collect([]))->make(true);
        }
        return $this->datatableService->get_serialized_product_table($supplier_product_id);
    }

    public function show($id)
    {
        $product = SupplierProduct::with(['supplier', 'category'])->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function getProductsBySupplier($supplier_id)
    {
        $products = SupplierProduct::where('supplier_id', $supplier_id)->get();
        return response()->json($products);
    }
}

30-01-2026