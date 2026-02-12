<?php

namespace App\Http\Controllers;

use App\Models\SupplierProduct;
use App\Models\Category;
use App\Models\Supplier;
use App\Helpers\SkuHelper;
use App\Services\DatatableServices;
use App\Services\SupplierProductServices;
use App\Http\Requests\StoreSupplierProductRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            // 1. Merge category_id to category
            $request->merge(['category' => $request->category_id]);

            // 2. Call service
            $serviceResponse = $this->supplierProductServices->store_supplier_product($request);

            // 3. Decode the JSON response properly
            $responseData = json_decode($serviceResponse->getContent(), true);

            // 4. Get the product from response
            $productData = $responseData['supplier_product'] ?? null;

            if (!$productData || !isset($productData['id'])) {
                Log::error('Product ID not found in service response', ['response' => $responseData]);

                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully!',
                    'reload' => true
                ], 201);
            }

            // 5. Load fresh data with relationships
            $product = SupplierProduct::with(['supplier', 'category'])
                ->find($productData['id']);

            if (!$product) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully!',
                    'reload' => true
                ], 201);
            }

            // 6. Return complete data for instant table update
            return response()->json([
                'success' => true,
                'message' => 'Supplier product created successfully!',
                'data' => [
                    'DT_RowId'      => 'row_' . $product->id,  // ✅ IMPORTANT!
                    'supplier_name' => $product->supplier?->name ?? 'N/A',
                    'name'          => $product->name,
                    'system_sku'    => $product->system_sku,
                    'cost_price'    => number_format($product->cost_price, 2),
                    'barcode'       => $product->barcode ?? 'N/A',
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Store Supplier Product Error: ' . $e->getMessage());
            Log::error('Stack Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * SCAN BARCODE METHOD - Para sa Scanner Gun functionality
     */
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

    public function datatable(Request $request)
    {
        // 1. Siguraduhin na kasama ang relationships
        $query = SupplierProduct::with(['supplier', 'category']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('supplier_name', function ($row) {
                // Kinukuha ang pangalan ng Supplier (e.g. Unilever)
                return $row->supplier?->name ?? 'N/A';
            })
            ->editColumn('name', function ($row) {
                // Kinukuha ang pangalan ng Product (e.g. Bear Brand)
                // Ginagawa nating explicit para hindi sya malito
                return $row->name;
            })
            ->editColumn('system_sku', function ($row) {
                return $row->system_sku ?? 'No SKU';
            })
            ->editColumn('cost_price', function ($row) {
                return number_format($row->cost_price, 2);
            })
            ->editColumn('barcode', function ($row) {
                return $row->barcode ?? 'N/A';
            })
            // IMPORTANT: Wag ilagay ang 'name' sa rawColumns kung text lang naman sya
            ->rawColumns(['supplier_name'])
            ->make(true);
    }

    public function showTable(Request $request, $id = null)
    {
        $supplier_id = $id ?? $request->id;

        if (!$supplier_id) {
            return DataTables::of(collect([]))->make(true);
        }

        $request->merge(['supplier_id' => $supplier_id]);
        return $this->datatableService->get_supplier_products_show_table($request); // ✅ WITH 'S'
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
