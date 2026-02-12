<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use App\Models\Supplier; // ✅ TAMA NA - Supplier (walang S)
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

    public function index(Request $request)
    {
        $query = $this->supplierProductServices->get_all_supplier_query();

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                ->whereYear('created_at', $request->year);
        }

        $suppliers = $query->get();

        // ✅ TAMA NA - Supplier (walang S)
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
