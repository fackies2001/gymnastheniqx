<?php
/*
namespace App\Services;

use App\Helpers\SkuHelper;
use App\Helpers\TransactionHelper;
use App\Http\Requests\StoreSupplierProductRequest;
use App\Models\Categories;
use App\Models\SupplierProduct;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;

class SupplierProductServices
{
    public function get_all_category()
    {
        return Category::all();
    }

    public function get_category_pluck_name_id()
    {
        return Category::pluck('name', 'id');
    }

    public function get_category_with_description()
    {
        return Category::select('id', 'name', 'description')->get();
    }

    public function get_all_supplier()
    {
        return Supplier::orderBy('name', 'asc')->get();
    }


    public function get_supplier($id)
    {
        return Supplier::withId($id)->first();
    }

    public function get_supplier_pluck_name_id()
    {
        return Supplier::pluck('name', 'id');
    }

    public function store_supplier($request)
    {
        $source_id = auth()->user()->is_student ? 2 : 3;
        $validatedData = $request->validated();

        TransactionHelper::run(function () use ($validatedData, $request, $source_id) {
            $supplier = Supplier::create([
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'] ?? null,
                'email' => $validatedData['email'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'created_by' => auth()->user()->employee->id,
                'source_id' => $source_id,
            ]);

            if (Gate::allows('can-create-supplier-api')) {
                if ($request->filled('api_url')) {
                    $supplier->supplierApis()->create([
                        'api_url' => $validatedData['api_url'],
                        'headers' => $validatedData['headers'] ?? null,
                        'service_class' => $validatedData['service_class'] ?? null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function get_all_supplier_product()
    {
        $source_id = auth()->user()->is_student ? [2] : [1, 3];
        return SupplierProduct::filterBySource($source_id)->get();
    }

    public function selected_supplier_products($supplier_id = null)
    {
        $query = SupplierProduct::query();
        if ($supplier_id) {
            $query->where('supplier_id', $supplier_id);
        }
        return $query;
    }

    public function get_supplier_product_name($supplier_id)
    {
        return SupplierProduct::where("supplier_id", $supplier_id)->pluck("name");
    }

    public function get_all_barcode_products($search_term)
    {
        return SupplierProduct::where(function ($query) use ($search_term) {
            $query->where('barcode', $search_term)
                ->orWhere('system_sku', $search_term)
                ->orWhere('supplier_sku', $search_term)
                ->orWhere('name', 'LIKE', "%{$search_term}%");
        })->first();
    }

    public function get_all_barcodes()
    {
        return SupplierProduct::pluck('barcode');
    }

    public function store_supplier_product(StoreSupplierProductRequest $request)
    {
        $validated = $request->validated();
        $isStudent = auth()->user()->is_student;
        $source_id = $isStudent ? 2 : 3;
        $abbrv = 'SYS';

        // 🟢 FIX: Handle Category (Check if it's ID or Name)
        $categoryId = $validated['category'];
        if (!is_numeric($categoryId)) {
            $category = Categories::firstOrCreate(
                ['name' => $categoryId],
                ['description' => $validated['description'] ?? 'Auto-generated via product creation']
            );
            $categoryId = $category->id;
        }

        if ($isStudent) {
            TransactionHelper::run(function () use ($validated, $abbrv, $source_id, $categoryId) {
                return SupplierProduct::create([
                    'supplier_id' => $validated['supplier_id'],
                    'category_id' => $categoryId,
                    'name' => $validated['name'],
                    'supplier_sku' => $validated['sku'] ?? null,
                    'system_sku' => SkuHelper::generateSystemSku($abbrv),
                    'cost_price' => $validated['cost_price'] ?? null,
                    'barcode' => $validated['barcode'] ?? null,
                    'source_id' => $source_id,
                ]);
            });

            return redirect()
                ->back()
                ->with('success', 'Supplier product created successfully.');
        } else {
            $supplierProduct = TransactionHelper::run(function () use ($validated, $abbrv, $categoryId, $source_id) {
                return SupplierProduct::create([
                    'supplier_id' => $validated['supplier_id'],
                    'category_id' => $categoryId,
                    'name' => $validated['name'],
                    'supplier_sku' => $validated['sku'] ?? null,
                    'system_sku' => SkuHelper::generateSystemSku($abbrv),
                    'cost_price' => $validated['cost_price'] ?? null,
                    'discount' => $validated['discount'] ?? null,
                    'stock' => $validated['stock'] ?? null,
                    'availability_status' => $validated['availability_status'] ?? null,
                    'shipping_information' => $validated['shipping_information'] ?? null,
                    'warranty_information' => $validated['warranty_information'] ?? null,
                    'return_policy' => $validated['return_policy'] ?? null,
                    'dimensions' => [
                        'weight' => $validated['weight'] ?? null,
                        'width' => $validated['dimensions_width'] ?? null,
                        'height' => $validated['dimensions_height'] ?? null,
                        'depth' => $validated['dimensions_depth'] ?? null,
                    ],
                    'barcode' => $validated['barcode'] ?? null,
                    'thumbnail' => $validated['thumbnail'] ?? null,
                    'images' => $validated['images'] ?? [],
                    'source_id' => $source_id,
                ]);
            });

            return response()->json([
                'message' => 'Supplier product created successfully.',
                'supplier_product' => $supplierProduct,
            ]);
        }
    }
}


feb 10