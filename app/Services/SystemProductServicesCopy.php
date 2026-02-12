<?php

/*
namespace App\Services;

use App\Helpers\TransactionHelper;
use App\Models\PaymentTerms;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseStatusLibrary;
use App\Models\SerialNumber;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


// class SystemProductServices
// {
//     /**
//      * Get all purchase requests with filters
//      */
//     public function get_all_purchase_request()
//     {
//         $query = PurchaseRequest::query();
//         return $query->filterByStudent()->filterByWarehouse();
//     }

//     /**
//      * Get single purchase request
//      */
//     public function get_purchase_request($id = null)
//     {
//         return PurchaseRequest::find($id);
//     }

//     /**
//      * Get purchase request by request number
//      */
//     public function get_purchase_request_by_request_number($request_number)
//     {
//         return PurchaseRequest::withRequestNumber($request_number)->get();
//     }

//     /**
//      * Update purchase request status
//      */
//     public function update_purchase_request_status($id, $status)
//     {
//         $pr = PurchaseRequest::findOrFail($id);

//         $pr->update([
//             'status_id' => $status
//         ]);

//         return $pr;
//     }

//     /**
//      * Generate latest PR number
//      */
//     public function get_latest_request_number(): string
//     {
//         $today = now()->format('
//         Ymd');

//         $latest = PurchaseRequest::latest('id')->first();

//         if (!$latest || !preg_match('/(\d+)$/', $latest->request_number, $matches)) {
//             $nextSequence = str_pad(1, 3, '0', STR_PAD_LEFT);
//             return "PR-{$today}-{$nextSequence}";
//         }

//         $lastSequence = (int) $matches[1];
//         $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);

//         return "PR-{$today}-{$nextSequence}";
//     }

//     /**
//      * Get purchase statuses (filtered or all)
//      */
//     public function get_all_purchase_status(array $statusIncluded = [])
//     {
//         $query = PurchaseStatusLibrary::query();

//         if (!empty($statusIncluded)) {
//             $query->whereIn('id', $statusIncluded);
//         }

//         return $query->get();
//     }

//     /**
//      * Get all purchase orders with filters
//      */
//     public function get_all_purchase_order()
//     {
//         $statusFilter = [2, 3, 5, 6];
//         $query = PurchaseOrder::query();
//         return $query->filterByStudent()->filterByWarehouse()->filterByStatus($statusFilter);
//     }

//     /**
//      * Get purchase orders excluding certain statuses
//      */
//     public function get_purchase_order_filter_by_exclusion($statusExcluded)
//     {
//         return PurchaseOrder::withExclusion($statusExcluded)->get();
//     }

//     /**
//      * Store new Purchase Request (receives full FormRequest)
//      */
//     public function store_purchase_request($request)
//     {
//         $validated = $request->validated();

//         $result = TransactionHelper::run(function () use ($validated) {
//             $items = collect($validated['products'] ?? [])->map(function ($product) {
//                 $discount = $product['discount'] ?? 0;
//                 return [
//                     'supplier_product_id' => $product['supplier_product_id'],
//                     'quantity'            => $product['quantity'],
//                     'cost_price'          => $product['cost_price'],
//                     'discount'            => $discount,
//                     'subtotal'            => ($product['quantity'] * $product['cost_price']) - $discount,
//                     'barcode'             => $product['barcode'] ?? '',
//                 ];
//             })->toArray();

//             if (empty($items)) {
//                 throw ValidationException::withMessages(['products' => 'Please add at least one product.']);
//             }

//             return PurchaseRequest::create([
//                 'request_number' => $validated['request_number'],
//                 'supplier_id'    => $validated['supplier_id'],
//                 'items'          => $items,
//                 'requested_by'   => auth()->user()->employee_id,
//                 'status_id'      => 1, // Pending
//             ]);
//         });

//         return response()->json([
//             'success' => true,
//             'message' => 'Purchase Request saved successfully!',
//             'data'    => $result,
//         ]);
//     }

//     /**
//      * Store new Purchase Order (receives full FormRequest)
//      */
//     public function store_purchase_order($request)
//     {
//         $validated = $request->validated();

//         Log::info('Validated PO Data:', $validated);

//         $result = TransactionHelper::run(function () use ($validated) {
//             $po = PurchaseOrder::create([
//                 'po_number'       => $validated['po_number'],
//                 'supplier_id'     => $validated['supplier_id'] ?? null,
//                 'payment_term_id' => $validated['payment_term_id'] ?? null,
//                 'approved_by'     => auth()->user()->employee_id,
//                 'request_id'      => $validated['request_id'] ?? null,
//                 'order_date'      => $validated['order_date'],
//                 'delivery_date'   => $validated['delivery_date'] ?? null,
//                 'remarks'         => $validated['remarks'] ?? null,
//                 'total_amount'    => 0,
//                 'received_items'  => $validated['received_items'] ?? [],
//             ]);

//             // Auto-create serial numbers from received_items
//             foreach ($validated['received_items'] ?? [] as $item) {
//                 $productId = $item['supplier_product_id'] ?? null;
//                 $qty = (int) ($item['received_qty'] ?? 0);
//                 $serials = $item['serial_numbers'] ?? [];

//                 if (!$productId || $qty <= 0) continue;

//                 // Optional: Validate received qty <= ordered qty
//                 if ($po->request_id) {
//                     $pr = PurchaseRequest::find($po->request_id);
//                     $prItem = collect($pr->items)->firstWhere('supplier_product_id', $productId);
//                     if ($prItem && $qty > ($prItem['quantity'] ?? 0)) {
//                         throw new \Exception("Received quantity exceeds ordered quantity for product ID {$productId}");
//                     }
//                 }

//                 for ($i = 0; $i < $qty; $i++) {
//                     SerialNumber::create([
//                         'sku_id'            => $productId,
//                         // DITO YUNG LOGIC:
//                         'serial_number'     => $serials[$i] ?? 'AUTO-' . uniqid(),
//                         'purchase_order_id' => $po->id,
//                         'product_status_id' => 1, // Available
//                         'scanned_by'        => auth()->user()->employee_id,
//                     ]);
//                 }
//             }

//             // Optional: Update PR status to "Ordered" (adjust ID based on your status table)
//             if ($po->request_id) {
//                 $pr = PurchaseRequest::find($po->request_id);
//                 if ($pr) {
//                     $pr->update(['status_id' => 6]); // e.g., 6 = Ordered
//                 }
//             }

//             return $po;
//         });

//         return $result;
//     }

//     /**
//      * Get single purchase order with relationships
//      */
//     public function get_purchase_order($id)
//     {
//         $purchaseOrder = PurchaseOrder::with([
//             'supplier',
//             'paymentTerms',
//             'approvedBy',
//             'purchaseRequest.requestedBy',
//             'serialNumbers.supplierProducts'
//         ])->findOrFail($id);

//         $userEmployeeId = auth()->user()->employee_id;

//         $requestorId = $purchaseOrder->purchaseRequest->requested_by ?? null;
//         $reviewedByMe = ($requestorId && $userEmployeeId) && ($requestorId == $userEmployeeId);

//         $isPending = $purchaseOrder->purchaseRequest->status_id === 1;

//         if (!$reviewedByMe && $isPending && Gate::allows('can-review-purchase-order')) {
//             Log::info("User (Emp ID: {$userEmployeeId}) is reviewing Purchase Order ID: {$id}");

//             TransactionHelper::run(function () use ($purchaseOrder) {
//                 $purchaseOrder->purchaseRequest()->update([
//                     'status_id' => 2 // Reviewed
//                 ]);
//             });

//             $purchaseOrder->refresh();
//         }

//         return $purchaseOrder;
//     }

//     /**
//      * Update purchase order status
//      */
//     public function set_purchase_status($id, $status)
//     {
//         $purchase_order = PurchaseOrder::findOrFail($id);

//         $purchase_order->purchaseRequest()->update([
//             'status_id' => $status
//         ]);

//         return $purchase_order;
//     }

//     /**
//      * Get payment terms for dropdown
//      */
//     public function get_payment_terms_pluck_name_id()
//     {
//         return PaymentTerms::pluck('name', 'id');
//     }

//     /**
//      * Get warehouses for dropdown
//      */
//     public function get_warehouse_pluck_name_id()
//     {
//         return Warehouse::pluck('name', 'id');
//     }

//     /**
//      * Get all serial numbers with filters
//      */
//     public function get_all_serial_numbers()
//     {
//         return SerialNumber::filterByStudent()->filterByWarehouse()->get();
//     }

//     /**
//      * Get serial number counts per status
//      */
//     public function get_count_serial_numbers_per_status()
//     {
//         return SerialNumber::countsPerStatus()->filterByStudent()->filterByWarehouse()->get();
//     }

//     /**
//      * Get purchase request counts per status
//      */
//     public function get_count_purchase_request_per_status()
//     {
//         // Mas mabilis ito dahil isang query lang sa DB gamit ang Join
//         return PurchaseRequest::join('purchase_status_library', 'purchase_request.status_id', '=', 'purchase_status_library.id')
//             ->select('purchase_status_library.name', DB::raw('count(purchase_request.id) as total'))
//             ->groupBy('purchase_status_library.name')
//             ->get()
//             ->toArray();
//     }

//     /**
//      * Monthly products scanned in
//      */
//     public function monthly_products_in()
//     {
//         return SerialNumber::monthlyProductScannedIn()
//             ->filterByStudent()
//             ->filterByWarehouse()
//             ->pluck('total', 'month');
//     }

//     /**
//      * Get serialized product overview by serial number
//      */
//     public function get_all_serialized_product_overview($serial_number)
//     {
//         return SerialNumber::where('serial_number', $serial_number)
//             ->filterByStudent()
//             ->filterByWarehouse()
//             ->first();
//     }

//     /**
//      * Get serialized products by SKU ID
//      */
//     public function get_serialized_product($id)
//     {
//         return SerialNumber::where('sku_id', $id)
//             ->filterByStudent()
//             ->filterByWarehouse()
//             ->get();
//     }
// }

27-01-2026