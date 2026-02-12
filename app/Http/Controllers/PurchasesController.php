<?php

namespace App\Http\Controllers;

use App\Services\SupplierProductServices;
use Illuminate\Http\Request;
use App\Services\SystemProductServices;
class PurchasesController extends Controller
{
    //
    protected $supplierProductServices;
    protected $systemProdServices;
    public function __construct(SupplierProductServices $supplierProductServices, SystemProductServices $systemProdServices)
    {
        $this->supplierProductServices = $supplierProductServices;
        $this->systemProdServices = $systemProdServices;
    }
    public function index()
    {

        // return view("purchase.index", compact());
    }

}
