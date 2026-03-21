<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use App\Models\Supplier;
use App\Services\DatatableServices;
use App\Services\SupplierProductServices;
use Illuminate\Http\Request;
use App\Helpers\TransactionHelper;
use App\Http\Requests\StoreSupplierRequest;
use Log;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

class SuppliersController extends Controller
{
    public $supplierProductServices;
    public $datatableServices;

    public function __construct(SupplierProductServices $supplierProductServices, DatatableServices $datatableServices)
    {
        $this->supplierProductServices = $supplierProductServices;
        $this->datatableServices = $datatableServices;
    }

    /**
     * ⭐ ENHANCED: Display suppliers with product counts and search/sort
     */
    public function index(Request $request)
    {
        // Get all suppliers with product counts
        $query = Supplier::withCount('supplierProducts')
            ->with(['createdBy.user', 'source']);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'name_asc'); // default: name A-Z
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_products':
                $query->orderBy('supplier_products_count', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $suppliers = $query->get();

        // Get unique years from suppliers (for filtering if needed later)
        $years = Supplier::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('suppliers.index', compact('suppliers', 'years'));
    }

    public function show($id)
    {
        $supplier = $this->supplierProductServices->get_supplier($id);
        return view('suppliers.show', compact('supplier'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        // ✅ Handle null email properly
        $email = $request->email ? strtolower($request->email) : null;

        $query = Supplier::whereRaw('LOWER(name) = ?', [strtolower($request->name)]);

        if ($email) {
            $query->whereRaw('LOWER(email) = ?', [$email]);
        } else {
            $query->whereNull('email');
        }

        if ($query->exists()) {
            return back()->withErrors([
                'name' => 'A supplier with this name and email already exists.'
            ])->withInput();
        }

        return $this->supplierProductServices->store_supplier($request);
    }

    public function showTable(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->datatableServices->get_supplier_products_show_table($request);
    }

    public function getProductsForPR(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->datatableServices->get_purchase_requests_table($request);
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            // 1. Kunin lahat ng product IDs ng supplier
            $productIds = $supplier->supplierProducts()->pluck('id');

            if ($productIds->isNotEmpty()) {
                // 2. I-delete muna ang purchase_request_items (walang cascade sa migration)
                \App\Models\PurchaseRequestItem::whereIn('product_id', $productIds)->delete();

                // NOTE: serialized_product at purchase_order_items
                // ay may onDelete('cascade') na sa migration — auto-delete na sila
            }

            // 3. I-delete ang supplier products
            $supplier->supplierProducts()->delete();

            // 4. I-delete na ang supplier
            // (purchase_order ay may cascade sa supplier_id — auto-delete na rin)
            $supplier->delete();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Delete Supplier Error: ' . $e->getMessage());

            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier: ' . $e->getMessage());
        }
    }

    public function checkDuplicate(Request $request)
    {
        // ✅ Handle null email properly
        $email = $request->email ? strtolower($request->email) : null;

        $query = Supplier::whereRaw('LOWER(name) = ?', [strtolower($request->name)]);

        if ($email) {
            $query->whereRaw('LOWER(email) = ?', [$email]);
        } else {
            $query->whereNull('email');
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
