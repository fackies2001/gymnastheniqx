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
}
