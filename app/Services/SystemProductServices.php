<?php

namespace App\Services;

use App\Helpers\TransactionHelper;
use App\Models\PaymentTerms;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseStatusLibrary;
use App\Models\SerializedProduct;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SystemProductServices
{
    /**
     * Get all purchase requests with filters
     */
    public function get_all_purchase_request()
    {
        $query = PurchaseRequest::query();
        return $query->filterByStudent()->filterByWarehouse();
    }

    /**
     * Get single purchase request
     */
    public function get_purchase_request($id = null)
    {
        return PurchaseRequest::find($id);
    }

    /**
     * Get purchase request by request number
     */
    public function get_purchase_request_by_request_number($request_number)
    {
        return PurchaseRequest::withRequestNumber($request_number)->get();
    }

    /**
     * Update purchase request status
     */
    public function update_purchase_request_status($id, $status)
    {
        $pr = PurchaseRequest::findOrFail($id);

        $pr->update([
            'status_id' => $status
        ]);

        return $pr;
    }

    /**
     * Generate latest PR number
     */
    public function get_latest_request_number(): string
    {
        $today = now()->format('Ymd');

        $latest = PurchaseRequest::latest('id')->first();

        if (!$latest || !preg_match('/(\d+)$/', $latest->request_number, $matches)) {
            $nextSequence = str_pad(1, 3, '0', STR_PAD_LEFT);
            return "PR-{$today}-{$nextSequence}";
        }

        $lastSequence = (int) $matches[1];
        $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);

        return "PR-{$today}-{$nextSequence}";
    }

    /**
     * Get purchase statuses (filtered or all)
     */
    public function get_all_purchase_status(array $statusIncluded = [])
    {
        $query = PurchaseStatusLibrary::query();

        if (!empty($statusIncluded)) {
            $query->whereIn('id', $statusIncluded);
        }

        return $query->get();
    }

    /**
     * ✅ FIXED: Get all purchase orders with filters
     */
    public function get_all_purchase_order()
    {
        $query = PurchaseOrder::query();
        return $query->filterByStudent()->filterByWarehouse();
    }

    /**
     * Get purchase orders excluding certain statuses
     */
    public function get_purchase_order_filter_by_exclusion($statusExcluded)
    {
        return PurchaseOrder::withExclusion($statusExcluded)->get();
    }

    /**
     * Store new Purchase Request (receives full FormRequest)
     */
    public function store_purchase_request($request)
    {
        $validated = $request->validated();

        $result = TransactionHelper::run(function () use ($validated) {
            $items = collect($validated['products'] ?? [])->map(function ($product) {
                $discount = $product['discount'] ?? 0;
                return [
                    'supplier_product_id' => $product['supplier_product_id'],
                    'quantity'            => $product['quantity'],
                    'cost_price'          => $product['cost_price'],
                    'discount'            => $discount,
                    'subtotal'            => ($product['quantity'] * $product['cost_price']) - $discount,
                    'barcode'             => $product['barcode'] ?? '',
                ];
            })->toArray();

            if (empty($items)) {
                throw ValidationException::withMessages(['products' => 'Please add at least one product.']);
            }

            return PurchaseRequest::create([
                'request_number' => $validated['request_number'],
                'supplier_id'    => $validated['supplier_id'],
                'items'          => $items,
                'requested_by'   => auth()->user()->employee_id,
                'status_id'      => 1, // Pending
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Purchase Request saved successfully!',
            'data'    => $result,
        ]);
    }

    /**
     * Store new Purchase Order (receives full FormRequest)
     */
    public function store_purchase_order($request)
    {
        $validated = $request->validated();

        Log::info('Validated PO Data:', $validated);

        $result = TransactionHelper::run(function () use ($validated) {
            $po = PurchaseOrder::create([
                'po_number'       => $validated['po_number'],
                'supplier_id'     => $validated['supplier_id'] ?? null,
                'payment_term_id' => $validated['payment_term_id'] ?? null,
                'approved_by'     => auth()->user()->employee_id,
                'request_id'      => $validated['request_id'] ?? null,
                'order_date'      => $validated['order_date'],
                'delivery_date'   => $validated['delivery_date'] ?? null,
                'remarks'         => $validated['remarks'] ?? null,
                'total_amount'    => 0,
                'received_items'  => $validated['received_items'] ?? [],
            ]);

            foreach ($validated['received_items'] ?? [] as $item) {
                $productId = $item['supplier_product_id'] ?? null;
                $qty = (int) ($item['received_qty'] ?? 0);
                $serials = $item['serial_numbers'] ?? [];

                if (!$productId || $qty <= 0) continue;

                if ($po->request_id) {
                    $pr = PurchaseRequest::find($po->request_id);
                    $prItem = collect($pr->items)->firstWhere('supplier_product_id', $productId);
                    if ($prItem && $qty > ($prItem['quantity'] ?? 0)) {
                        throw new \Exception("Received quantity exceeds ordered quantity for product ID {$productId}");
                    }
                }

                for ($i = 0; $i < $qty; $i++) {
                    SerializedProduct::create([
                        'product_id'        => $productId,
                        'serial_number'     => $serials[$i] ?? 'AUTO-' . uniqid(),
                        'purchase_order_id' => $po->id,
                        'status'            => 1,
                        'scanned_by'        => auth()->user()->employee_id,
                    ]);
                }
            }

            if ($po->request_id) {
                $pr = PurchaseRequest::find($po->request_id);
                if ($pr) {
                    $pr->update(['status_id' => 6]);
                }
            }

            return $po;
        });

        return $result;
    }

    /**
     * Get single purchase order with relationships
     */
    public function get_purchase_order($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'approvedBy',
            'purchaseRequest.requestedBy',
            'serializedProducts.supplierProducts'
        ])->findOrFail($id);

        $userEmployeeId = auth()->user()->employee_id;

        $requestorId = $purchaseOrder->purchaseRequest->requested_by ?? null;
        $reviewedByMe = ($requestorId && $userEmployeeId) && ($requestorId == $userEmployeeId);

        $isPending = $purchaseOrder->purchaseRequest->status_id === 1;

        if (!$reviewedByMe && $isPending && Gate::allows('can-review-purchase-order')) {
            Log::info("User (Emp ID: {$userEmployeeId}) is reviewing Purchase Order ID: {$id}");

            TransactionHelper::run(function () use ($purchaseOrder) {
                $purchaseOrder->purchaseRequest()->update([
                    'status_id' => 2
                ]);
            });

            $purchaseOrder->refresh();
        }

        return $purchaseOrder;
    }

    /**
     * Update purchase order status
     */
    public function set_purchase_status($id, $status)
    {
        $purchase_order = PurchaseOrder::findOrFail($id);

        $purchase_order->purchaseRequest()->update([
            'status_id' => $status
        ]);

        return $purchase_order;
    }

    /**
     * Get payment terms for dropdown
     */
    public function get_payment_terms_pluck_name_id()
    {
        return PaymentTerms::pluck('name', 'id');
    }

    /**
     * Get warehouses for dropdown
     */
    public function get_warehouse_pluck_name_id()
    {
        return Warehouse::pluck('name', 'id');
    }

    /**
     * ✅ FIXED: Get all serial numbers with filters
     */
    public function get_all_serial_numbers()
    {
        return SerializedProduct::filterByStudent()->filterByWarehouse()->get();
    }

    /**
     * ✅ FIXED: Get serial number counts per status - DYNAMIC COLUMN DETECTION
     */
    public function get_count_serial_numbers_per_status()
    {
        // 🔥 Detect the correct column name in product_status table
        $columns = Schema::getColumnListing('product_status');

        $possibleNames = ['status_name', 'name', 'product_status_name', 'status', 'label', 'title'];

        $nameColumn = null;
        foreach ($possibleNames as $possible) {
            if (in_array($possible, $columns)) {
                $nameColumn = $possible;
                break;
            }
        }

        if (!$nameColumn) {
            Log::warning('Could not find name column in product_status table');
            return [];
        }

        try {
            return SerializedProduct::join('product_status', 'serialized_product.status', '=', 'product_status.id')
                ->select("product_status.{$nameColumn} as name", DB::raw('count(serialized_product.id) as total'))
                ->groupBy("product_status.{$nameColumn}")
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error in get_count_serial_numbers_per_status: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ FIXED: Get purchase request counts per status
     */
    public function get_count_purchase_request_per_status()
    {
        return PurchaseRequest::join('purchase_status_library', 'purchase_request.status_id', '=', 'purchase_status_library.id')
            ->select('purchase_status_library.name', DB::raw('count(purchase_request.id) as total'))
            ->groupBy('purchase_status_library.name')
            ->get()
            ->toArray();
    }

    /**
     * ✅ FIXED: Monthly products scanned in
     */
    public function monthly_products_in()
    {
        return SerializedProduct::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', date('Y'))
            ->filterByStudent()
            ->filterByWarehouse()
            ->groupBy('month')
            ->pluck('total', 'month');
    }

    /**
     * ✅ FIXED: Get serialized product overview by serial number
     */
    public function get_all_serialized_product_overview($serial_number)
    {
        return SerializedProduct::where('serial_number', $serial_number)
            ->filterByStudent()
            ->filterByWarehouse()
            ->first();
    }

    /**
     * ✅ FIXED: Get serialized products by product ID
     */
    public function get_serialized_product($id)
    {
        return SerializedProduct::where('product_id', $id)
            ->filterByStudent()
            ->filterByWarehouse()
            ->get();
    }
}
