<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SerializedProductsController;
use App\Http\Controllers\SupplierProductsController;
use App\Http\Controllers\WarehouseController;

// Supplier products table
Route::get('/suppliers/{id}/products-table', [PurchaseRequestController::class, 'getSupplierProducts'])
    ->middleware('auth:sanctum')
    ->name('api.suppliers_products.show_table');

Route::get('/supplier_products/initial_table', [SupplierProductsController::class, 'initial_table'])
    ->middleware('auth:sanctum')
    ->name('api.suppliers_products.initial_table'); // ✅ Added 'api.' prefix

Route::get('/purchase_request', [PurchaseRequestController::class, 'purchase_request_table'])
    ->middleware('auth:sanctum')
    ->name('api.purchase_request.indexTable'); // ✅ Added 'api.' prefix

Route::get('/purchase_order', [PurchaseOrderController::class, 'purchase_orders_table'])
    ->middleware('auth:sanctum')
    ->name('api.purchase_order.indexTable'); // ✅ Added 'api.' prefix

Route::post('/purchase_request/store', [PurchaseRequestController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.purchase_requests.store'); // ✅ Added 'api.' prefix

// Warehouses
Route::get('/warehouses', [WarehouseController::class, 'display'])
    ->name('api.warehouses.display'); // ✅ Added 'api.' prefix

// ✅ FIXED: Changed route names to avoid conflicts
Route::get('/serialized_products', [SerializedProductsController::class, 'indexTable'])
    ->middleware('auth:sanctum')
    ->name('api.serialized_products.indexTable'); // ✅ Changed name!

Route::get('/serialized_products/show/{id?}', [SerializedProductsController::class, 'showTable'])
    ->middleware('auth:sanctum')
    ->name('api.serialized_products.showTable'); // ✅ Changed name!

Route::post('/serialized_products/store', [SerializedProductsController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.serialized_products.store'); // ✅ Changed name!