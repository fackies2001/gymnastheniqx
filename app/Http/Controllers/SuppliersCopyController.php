<?php
/*
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SupplierProduct;
use App\Services\DatatableServices;
use App\Services\SupplierProductServices;
use App\Services\ViewProductServices;
use Illuminate\Http\Request;
use App\Models\Suppliers;
use App\Helpers\TransactionHelper;
use App\Http\Requests\StoreSupplierRequest;
use Laravel\Pennant\Feature;
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
        // Gamitin ang bagong query method
        $query = $this->supplierProductServices->get_all_supplier_query();

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('created_at', $request->month)
                ->whereYear('created_at', $request->year);
        }

        $suppliers = $query->get();

        $years = Supplier::selectRaw('EXTRACT(YEAR FROM created_at)::int as year')
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

    // Sa SuppliersController.php
    public function showTable(Request $request, $id)
    {
        // Idagdag natin ang id sa request para mabasa ng Service
        $request->merge(['id' => $id]);

        // Gamitin ang tamang function name mula sa DatatableServices.php
        return $this->datatableServices->get_supplier_products_show_table($request);
    }

    // App\Http\Controllers\SuppliersController.php

    public function getProductsForPR(Request $request, $id)
    {
        // I-merge ang $id sa request para mabasa ng DatatableServices
        $request->merge(['id' => $id]);

        return $this->datatableServices->get_purchase_requests_table($request);
    }
}

feb 10