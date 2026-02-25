<?php
/*
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    PurchaseOrderController,
    PurchaseRequestController,
    PurchasesController,
    RetailersController,
    ScanController,
    SupplierProductsController,
    UserManagementController,
    ProductsController,
    WarehouseController,
    SuppliersController,
    InvoiceOutvoiceRecordsController,
    ReportsController,
    DashboardController,
    CategoriesController,
    PusherController,
    NotificationsController,
    SerializedProductsController,
    RetailerOrderController,
    ManpowerController,
    GymEquipmentController,
    PincodeController // ✅ ADD THIS
};
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\CheckPinStatus;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
});

// ✅ PIN VERIFICATION ROUTES (OUTSIDE CheckPinStatus MIDDLEWARE - CRITICAL!)
Route::middleware(['auth'])->group(function () {
    Route::post('/verify_pin', [UserManagementController::class, 'verifyPin'])->name('user.verify.pin');
    Route::put('/update_pin', [UserManagementController::class, 'updatePin'])->name('user.update.pin');
});

// ✅ AUTHENTICATED ROUTES WITH PIN CHECK
Route::middleware(['auth', CheckPinStatus::class])->group(function () {

    // --- DASHBOARD & USER MANAGEMENT ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ DEBUG ROUTES (Remove after testing)
    Route::get('/debug-image', function () {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'full_name' => $user->full_name,
            'profile_photo' => $user->profile_photo,
            'storage_path' => $user->profile_photo ? storage_path('app/public/' . $user->profile_photo) : null,
            'file_exists' => $user->profile_photo ? file_exists(storage_path('app/public/' . $user->profile_photo)) : false,
            'asset_url' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
            'adminlte_image' => $user->adminlte_image(),
            'role' => $user->role?->role_name,
            'warehouse' => $user->warehouse?->name,
            'public_storage_exists' => file_exists(public_path('storage')),
            'is_symlink' => is_link(public_path('storage')),
        ]);
    });

    Route::get('/test-user-methods', function () {
        $user = Auth::user();
        return response()->json([
            'full_name' => $user->full_name,
            'adminlte_image' => $user->adminlte_image(),
            'adminlte_desc' => $user->adminlte_desc(),
            'adminlte_role' => $user->adminlte_role(),
            'adminlte_warehouse' => $user->adminlte_warehouse(),
            'role_object' => $user->role,
            'warehouse_object' => $user->warehouse,
        ]);
    });

    Route::get('/user-management', [UserManagementController::class, 'index'])->name('user.management');
    Route::post('/user-management/store', [UserManagementController::class, 'store'])->name('user.management.store');
    Route::post('/user-management/update', [UserManagementController::class, 'update'])->name('user.management.update');
    Route::delete('/user-management/delete/{id}', [UserManagementController::class, 'destroy'])->name('user.management.delete');
    Route::post('/user-management/reset-pin', [UserManagementController::class, 'resetPin'])->name('admin.reset.pin');
    Route::put('/update_pin', [UserManagementController::class, 'updatePin'])->name('user.update.pin');
    Route::post('/verify_pin', [UserManagementController::class, 'verifyPin'])->name('user.verify.pin');

    // Registered User Form
    Route::get('admin/register/form', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/employee/register', [RegisteredUserController::class, 'store'])->name('employee.register');

    // --- PROFILE MANAGEMENT ---
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    // --- NOTIFICATIONS & REALTIME (PUSHER) ---
    Route::prefix('notifications')->group(function () {
        Route::get('/get',          [NotificationsController::class, 'getNotificationsData'])->name('notifications.get');
        Route::get('/count',        [NotificationsController::class, 'getCount'])->name('notifications.count');
        Route::post('/read/{id}',   [NotificationsController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all',    [NotificationsController::class, 'markAllRead'])->name('notifications.read-all');
    });

    // --- WAREHOUSE & SCANNING ---
    Route::get('/warehouse', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::post('/warehouse/store', [WarehouseController::class, 'store'])->name('warehouse.store');
    Route::post('/warehouse/update', [WarehouseController::class, 'update'])->name('warehouse.update');
    Route::delete('/warehouse/delete', [WarehouseController::class, 'destroy'])->name('warehouse.delete');
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');

    // ✅ Purchase Request Routes
    Route::prefix('purchase-request')->group(function () {
        Route::get('/', [PurchaseRequestController::class, 'index'])->name('pr.index');
        Route::get('/datatable', [PurchaseRequestController::class, 'getPurchaseRequestTable'])->name('pr.datatable');
        Route::get('/generate-number', [PurchaseRequestController::class, 'generatePRNumber'])->name('pr.generate-number');
        Route::get('/supplier-products/{id}', [PurchaseRequestController::class, 'getSupplierProducts'])->name('pr.supplier-products');
        Route::post('/store', [PurchaseRequestController::class, 'store'])->name('pr.store');
        Route::post('/approve/{id}', [PurchaseRequestController::class, 'approve'])->name('pr.approve');
        Route::post('/reject/{id}', [PurchaseRequestController::class, 'reject'])->name('pr.reject');
        Route::get('/{id}', [PurchaseRequestController::class, 'show'])->name('pr.show');
    });

    // Purchase Order 
    Route::prefix('purchase-order')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-order.index');
        Route::get('/generate-number', [PurchaseOrderController::class, 'generatePONumber'])->name('purchase-order.generate-number');
        Route::get('/scan/{id}', [PurchaseOrderController::class, 'scanView'])->name('purchase-order.scan');
        Route::get('/details/{id}', [PurchaseOrderController::class, 'getDetailsJson'])->name('purchase-order.details');
        Route::post('/{id}/scan-item', [PurchaseOrderController::class, 'scanItem'])->name('purchase-order.scan-item');
        Route::post('/{id}/complete-scan', [PurchaseOrderController::class, 'completeScan'])->name('purchase-order.complete-scan');
        Route::post('/receive', [PurchaseOrderController::class, 'receiveItems'])->name('purchase-order.receive');
        Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    });

    Route::get('/suppliers/{id}/products', [PurchaseRequestController::class, 'getSupplierProducts'])->name('suppliers.products');

    // SERIALIZED PRODUCTS
    Route::controller(SerializedProductsController::class)->group(function () {
        Route::get('/serialized_products', 'index')->name('serialized_products.index');
        Route::get('/serialized_products/index-table', 'indexTable')->name('serialized_products.indexTable');
        Route::post('/serialized_products/store', 'store')->name('serialized_products.store');
        Route::get('/serialized_products/show/{id}/{product_name}', 'show')->name('serialized_products.show');
        Route::get('/serialized_products/show-table/{id?}', 'showTable')->name('serialized_products.showTable');
        Route::get('/serialized_products/overview/{serial_number}', 'overview')->name('serialized_products.overview');
        Route::get('/serialized_products/datatable', 'serialized_products_table')->name('serialized_products.datatable');
        Route::get('/serialized_products/datatable/{id}', 'serialized_product_datatable')->name('serialized_products.show_datatable');
        Route::get('/_serialized_products', '_index')->name('serialized_products._index');
        Route::put('/serialized_products/update_status/{id}', 'updateStatus')->name('serialized_products.update_status');
    });

    // SUPPLIER MANAGEMENT
    Route::controller(SuppliersController::class)->group(function () {
        Route::get('/suppliers', 'index')->name('suppliers.index');
        Route::get('/suppliers/create', 'create')->name('suppliers.create');
        Route::post('/suppliers/store', 'store')->name('suppliers.store');
        Route::get('/suppliers/{id}/products-table', 'showTable')->name('suppliers_products.show_table');
        Route::get('/suppliers/{id}', 'show')->name('suppliers.show');
    });

    // SUPPLIER PRODUCTS
    Route::controller(SupplierProductsController::class)->group(function () {
        Route::get('/supplier_products', 'index')->name('supplier_products.index');
        Route::post('/supplier_products/store', 'store')->name('supplier_products.store');
        Route::get('/supplier_products/scan/{barcode}', 'scan')->name('supplier_products.scan');
        Route::get('/supplier_products/data', 'datatable')->name('supplier_products.data');
        Route::get('/supplier_products/initial_table', 'initial_table')->name('supplier_products.api_initial_table');
        Route::get('/supplier_products/list/{supplier_id}', 'getProductsBySupplier');
        Route::get('/supplier_products/show_table/{id}', 'showTable')->name('supplier_products.show_table');
    });

    // RETAILER ORDERS
    Route::controller(RetailerOrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('retailer.orders.index');
        Route::post('/retailer-orders/store', 'store')->name('retailer.orders.store');
        Route::post('/retailer-orders/{id}/approve', 'approve')->name('retailer.orders.approve');
        Route::post('/retailer-orders/{id}/reject', 'reject')->name('retailer.orders.reject');
        Route::post('/retailer-orders/{id}/complete', 'complete')->name('retailer.orders.complete');
    });

    // REPORTS MANAGEMENT
    Route::controller(ReportsController::class)->group(function () {
        Route::post('/reports/damage', 'reportDamage')->name('reports.report.damage');
        Route::post('/reports/update-stock', 'updateStock')->name('reports.update.stock');
        Route::get('/daily-reports', 'dailyIndex')->name('reports.daily');
        Route::get('/reports/daily/data', 'getDailyData')->name('reports.daily.data');
        Route::get('/reports/daily/export', 'exportDaily')->name('reports.daily.export');
        Route::get('/weekly-reports', 'weeklyIndex')->name('reports.weekly');
        Route::get('/weekly-reports/get-data', 'getWeeklyData')->name('reports.weekly.data');
        Route::get('/weekly-reports/export', 'exportWeekly')->name('reports.weekly.export');
        Route::post('/reports/approve/{id}/{type}', 'approve')->name('reports.approve');
        Route::post('/reports/reject/{id}/{type}', 'reject')->name('reports.reject');
        Route::get('/monthly-reports', 'monthlyIndex')->name('reports.monthly');
        Route::get('/quarterly-reports', 'strategicIndex')->name('reports.quarterly');
        Route::get('/yearly-reports', 'strategicIndex')->name('reports.yearly');
        Route::get('/strategic-reports', 'strategicIndex')->name('reports.strategic');
    });

    // MANPOWER MANAGEMENT
    Route::controller(ManpowerController::class)->group(function () {
        Route::get('/manpower', 'index')->name('manpower.index');
        Route::get('/manpower/data', 'get_coaches_data')->name('manpower.data');
        Route::post('/manpower/store', 'store')->name('manpower.store');
        Route::get('/manpower/{id}/edit', 'edit')->name('manpower.edit');
        Route::put('/manpower/{id}', 'update')->name('manpower.update');
        Route::delete('/manpower/{id}', 'destroy')->name('manpower.delete');
    });

    // GYM EQUIPMENT MANAGEMENT
    Route::controller(GymEquipmentController::class)->group(function () {
        Route::get('/gym-equipments', 'index')->name('gym.index');
        Route::get('/gym-equipments/data', 'getEquipments')->name('gym.data');
        Route::get('/gym-equipments/print', 'print')->name('gym.print');
        Route::post('/gym-equipments/store', 'store')->name('gym.store');
        Route::get('/gym-equipments/{id}/edit', 'edit')->name('gym.edit');
        Route::put('/gym-equipments/{id}', 'update')->name('gym.update');
        Route::delete('/gym-equipments/{id}', 'destroy')->name('gym.delete');
    });
});

require __DIR__ . '/auth.php';

feb 16