<?php
// 📁 app/Http/Controllers/RetailerOrderController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerOrder;
use App\Models\SupplierProduct;
use App\Models\SerializedProduct;
use App\Models\User;
use App\Notifications\RetailerOrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RetailerOrderController extends Controller
{
    private function getAdmins()
    {
        return User::whereHas('role', function ($q) {
            $q->whereRaw('LOWER(TRIM(role_name)) IN (?, ?, ?)', [
                'admin',
                'manager',
                'account staff',
            ]);
        })->get();
    }

    private function canSeeAllOrders(): bool
    {
        $user = Auth::user();

        return $user && ($user->hasPrivilegedAccess() || $user->isViewOnlyStaff());
    }

    // ============================================================
    //  INDEX — Role-based filtering
    //    Admin / Manager → sees ALL orders
    //    Staff           → sees only their own orders
    //
    //  FIX: Staff filter now uses BOTH created_by_user_id AND
    //    created_by name as fallback — fixes cases where older
    //    orders had null created_by_user_id
    // ============================================================
    public function index(Request $request)
    {
        $user           = Auth::user();
        $seeAllOrders   = $this->canSeeAllOrders();

        $dateConditions = function ($query) use ($request) {
            if ($request->filled('filter_type')) {
                $filterType = $request->filter_type;
                $today      = now()->startOfDay();

                switch ($filterType) {
                    case 'today':
                        $query->whereDate('created_at', $today);
                        break;
                    case 'yesterday':
                        $query->whereDate('created_at', $today->copy()->subDay());
                        break;
                    case 'last_7_days':
                        $query->whereBetween('created_at', [
                            $today->copy()->subDays(6)->startOfDay(),
                            now()->endOfDay(),
                        ]);
                        break;
                    case 'last_30_days':
                        $query->whereBetween('created_at', [
                            $today->copy()->subDays(29)->startOfDay(),
                            now()->endOfDay(),
                        ]);
                        break;
                    case 'this_month':
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                        break;
                    case 'last_month':
                        $lastMonth = now()->subMonth();
                        $query->whereMonth('created_at', $lastMonth->month)
                            ->whereYear('created_at', $lastMonth->year);
                        break;
                    case 'this_year':
                        $query->whereYear('created_at', now()->year);
                        break;
                    case 'custom':
                        if ($request->filled('start_date') && $request->filled('end_date')) {
                            $query->whereBetween('created_at', [
                                $request->start_date . ' 00:00:00',
                                $request->end_date   . ' 23:59:59',
                            ]);
                        }
                        break;
                }
            }
        };

        //  FIX: Staff filter — check BOTH user_id AND name as fallback
        // This fixes orders created before created_by_user_id was added
        $baseQuery = RetailerOrder::query()
            ->when(!$seeAllOrders, function ($query) use ($user) {
                $userName = $user->full_name
                    ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    ?: null;
                $query->where(function ($q) use ($user, $userName) {
                    $q->where('created_by_user_id', $user->id);
                    if ($userName) {
                        $q->orWhere('created_by', $userName);
                    }
                });
            });

        $retailer_orders = (clone $baseQuery)
            ->where($dateConditions)
            ->orderByRaw("
                CASE
                    WHEN status = 'Pending'   THEN 1
                    WHEN status = 'Approved'  THEN 2
                    WHEN status = 'Completed' THEN 3
                    WHEN status = 'Rejected'  THEN 4
                END
            ")
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = (clone $baseQuery)
            ->where($dateConditions)
            ->whereIn('status', ['Approved', 'Completed'])
            ->sum('total_amount');

        $pendingOrders = (clone $baseQuery)
            ->where($dateConditions)
            ->where('status', 'Pending')
            ->count();

        $completedOrders = (clone $baseQuery)
            ->where($dateConditions)
            ->where('status', 'Completed')
            ->count();

        $warehouse_products = SupplierProduct::query()
            ->withCount([
                'serializedProducts as available_quantity' => function ($q) {
                    $q->where('status', 1);
                },
            ])
            ->orderBy('name')
            ->get();

        $canManageRetailerOrders = Auth::user()->hasPrivilegedAccess();

        return view('orders.index', compact(
            'retailer_orders',
            'warehouse_products',
            'totalSales',
            'pendingOrders',
            'completedOrders',
            'canManageRetailerOrders'
        ));
    }

    /**
     * Admin / Manager — same listing as index (already shows all orders for those roles).
     * Route kept for bookmarks and sidebar links to retailer.orders.all.
     */
    public function indexAll(Request $request)
    {
        return $this->index($request);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->normalizedRoleName() === 'account staff') {
            return back()->with('error', 'Access Denied! You are not allowed to submit retailer orders.');
        }

        $request->validate([
            'retailer_name' => 'required',
            'product_id'    => 'required|exists:supplier_product,id',
            'quantity'      => 'required|integer|min:1',
            'unit_price'    => 'required|numeric|min:0',
        ]);

        $product = SupplierProduct::findOrFail($request->product_id);

        // ✅ FIX — Kung consumable, kuhanin sa consumable_stocks
        // Kung non-consumable, kuhanin sa serialized_product
        if ($product->is_consumable) {
            $availableStock = \App\Models\ConsumableStock::where('product_id', $product->id)
                ->value('current_qty') ?? 0;
        } else {
            $availableStock = SerializedProduct::where('product_id', $product->id)
                ->where('status', 1)
                ->count();
        }

        if ($availableStock === 0) {
            return back()->with('error', '❌ Order failed! No available stock for this product.');
        }

        if ($request->quantity > $availableStock) {
            return back()->with('error', "❌ Insufficient stock! Available: {$availableStock} units only. You ordered: {$request->quantity} units.");
        }

        $order = RetailerOrder::create([
            'product_id'         => $product->id,
            'retailer_name'      => $request->retailer_name,
            'product_name'       => $product->name,
            'quantity'           => $request->quantity,
            'unit_price'         => $request->unit_price,
            'total_amount'       => $request->quantity * $request->unit_price,
            'status'             => 'Pending',
            'sku'                => $product->supplier_sku ?? $product->system_sku ?? 'N/A',
            'created_by'         => Auth::user()->full_name ?? 'Unknown User',
            'created_by_user_id' => Auth::id(),
            'user_role'          => Auth::user()->role?->role_name ?? 'No Role',
        ]);

        try {
            $admins = $this->getAdmins();
            foreach ($admins as $admin) {
                if ($admin->id !== Auth::id()) {
                    $admin->notify(new RetailerOrderNotification(
                        'created',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        $request->retailer_name,
                        (int) $order->id
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Retailer order notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Order submitted successfully! Awaiting admin approval.');
    }

    public function approve($id)
    {
        if (!Auth::user()->hasPrivilegedAccess()) {
            return back()->with('error', 'Access Denied! You cannot approve orders.');
        }

        $order = RetailerOrder::findOrFail($id);

        if ($order->status === 'Approved') {
            return back()->with('info', 'This order has already been approved.');
        }

        // ✅ Block approve kung below cost
        $product = SupplierProduct::find($order->product_id);
        if ($product && $product->cost_price > 0 && $order->unit_price < $product->cost_price) {
            return back()->with('error', '❌ Cannot approve! The selling price. (₱' . number_format($order->unit_price, 2) . ') 
            is lower than supplier cost (₱' . number_format($product->cost_price, 2) . '). Change the price first.');
        }

        $product = null;
        if ($order->product_id) {
            $product = SupplierProduct::find($order->product_id);
        }
        if (!$product && $order->sku) {
            $product = SupplierProduct::where('supplier_sku', $order->sku)
                ->orWhere('system_sku', $order->sku)
                ->first();
        }
        if (!$product) {
            return back()->with('error', "Product not found in inventory. (SKU: {$order->sku})");
        }

        DB::beginTransaction();
        try {
            $serializedProducts = SerializedProduct::where('product_id', $product->id)
                ->where('status', 1)
                ->orderBy('created_at', 'asc')
                ->limit($order->quantity)
                ->get();

            $serialNumbers = [];
            foreach ($serializedProducts as $sp) {
                $sp->update([
                    'status'  => 2,
                    'remarks' => "Reserved for Retailer: {$order->retailer_name} (Order ID: {$order->id})",
                ]);
                $serialNumbers[] = $sp->serial_number;
            }

            $order->update([
                'status'                   => 'Approved',
                'approved_by'              => Auth::user()->full_name,
                'approved_at'              => now(),
                'allocated_serial_numbers' => json_encode($serialNumbers),
            ]);

            //  DAGDAG — i-record ang stock movement para sa consumable products
            if ($product && $product->is_consumable) {
                $warehouseId = \App\Models\ConsumableStock::where('product_id', $product->id)
                    ->value('warehouse_id') ?? 9;

                \App\Models\StockMovement::record([
                    'product_id'        => $product->id,
                    'warehouse_id'      => $warehouseId,
                    'type'              => \App\Models\StockMovement::TYPE_OUT,
                    'quantity'          => $order->quantity,
                    'reason_type'       => \App\Models\StockMovement::REASON_SOLD,
                    'remarks'           => "Sold to Retailer: {$order->retailer_name} (Order ID: {$order->id})",
                    'retailer_order_id' => $order->id,
                    'created_by'        => auth()->id(),
                ]);
            }

            DB::commit();

            try {
                $creator = User::find($order->created_by_user_id);
                if ($creator) {
                    $creator->notify(new RetailerOrderNotification(
                        'approved',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        $order->retailer_name,
                        (int) $order->id
                    ));
                }
            } catch (\Exception $e) {
                Log::warning('Retailer order approval notification failed: ' . $e->getMessage());
            }

            if ($product->is_consumable) {
                return back()->with('success', "Order Approved! {$order->quantity} items deducted from consumable stock.");
            }

            // Non-consumable — check serialized products
            $reservedQty = count($serialNumbers);
            if ($reservedQty === 0) {
                return back()->with('warning', 'Order Approved with NO STOCK! Please request stock replenishment immediately.');
            } elseif ($reservedQty < $order->quantity) {
                return back()->with('warning', "Order Approved with PARTIAL stock! Reserved: {$reservedQty}/{$order->quantity}.");
            }

            return back()->with('success', "Order Approved! {$reservedQty} items reserved via FIFO.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        if (!Auth::user()->hasPrivilegedAccess()) {
            return back()->with('error', 'Access Denied! You cannot reject orders.');
        }

        $order = RetailerOrder::findOrFail($id);
        $order->update([
            'status'      => 'Rejected',
            'rejected_by' => Auth::user()->full_name,
            'rejected_at' => now(),
        ]);

        try {
            $creator = User::find($order->created_by_user_id);
            if ($creator) {
                $creator->notify(new RetailerOrderNotification(
                    'rejected',
                    'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    $order->retailer_name,
                    (int) $order->id
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Retailer order rejection notification failed: ' . $e->getMessage());
        }

        return back()->with('info', 'Order rejected.');
    }

    public function complete($id)
    {
        if (!Auth::user()->hasPrivilegedAccess()) {
            return response()->json(['success' => false, 'message' => 'Access Denied!'], 403);
        }

        DB::beginTransaction();
        try {
            $order = RetailerOrder::findOrFail($id);

            if ($order->status !== 'Approved') {
                throw new \Exception('Order must be approved first');
            }

            $serialNumbers = json_decode($order->allocated_serial_numbers, true);

            if (!$serialNumbers || count($serialNumbers) === 0) {
                $product = null;
                if ($order->product_id)  $product = SupplierProduct::find($order->product_id);
                if (!$product && $order->sku) {
                    $product = SupplierProduct::where('supplier_sku', $order->sku)
                        ->orWhere('system_sku', $order->sku)->first();
                }
                if (!$product && $order->product_name) {
                    $product = SupplierProduct::where('name', $order->product_name)->first();
                }
                if (!$product) {
                    throw new \Exception("Product '{$order->product_name}' not found in inventory");
                }

                $serialNumbers = SerializedProduct::where('product_id', $product->id)
                    ->where('status', 1)
                    ->orderBy('created_at', 'asc')
                    ->limit($order->quantity)
                    ->pluck('serial_number')
                    ->toArray();
            }

            if (!empty($serialNumbers)) {
                SerializedProduct::whereIn('serial_number', $serialNumbers)
                    ->update([
                        'status'  => 3,
                        'remarks' => "Sold to Retailer: {$order->retailer_name} (Order ID: {$order->id})",
                    ]);
            }

            $shippedQty = count($serialNumbers);
            $message    = $shippedQty < $order->quantity
                ? "Order shipped with PARTIAL fulfillment! Shipped: {$shippedQty}/{$order->quantity} items."
                : "Order shipped successfully! {$shippedQty} items marked as SOLD.";

            $order->update([
                'status'                   => 'Completed',
                'shipped_by'               => Auth::user()->full_name,
                'shipped_at'               => now(),
                'allocated_serial_numbers' => json_encode($serialNumbers),
            ]);

            //  DAGDAG — i-record ang stock movement para sa consumable products
            $completedProduct = null;
            if ($order->product_id) $completedProduct = \App\Models\SupplierProduct::find($order->product_id);
            if (!$completedProduct && $order->product_name) {
                $completedProduct = \App\Models\SupplierProduct::where('name', $order->product_name)->first();
            }

            if ($completedProduct && $completedProduct->is_consumable) {
                $warehouseId = \App\Models\ConsumableStock::where('product_id', $completedProduct->id)
                    ->value('warehouse_id') ?? 9;

                //  Kung nag-approve na — bawasan na lang yung hindi pa nababawas
                // (avoid double deduct kung nag-record na sa approve)
                $alreadyRecorded = \App\Models\StockMovement::where('retailer_order_id', $order->id)
                    ->where('type', 'out')
                    ->exists();

                if (!$alreadyRecorded) {
                    \App\Models\StockMovement::record([
                        'product_id'        => $completedProduct->id,
                        'warehouse_id'      => $warehouseId,
                        'type'              => \App\Models\StockMovement::TYPE_OUT,
                        'quantity'          => $shippedQty,
                        'reason_type'       => \App\Models\StockMovement::REASON_SOLD,
                        'remarks'           => "Completed — Sold to Retailer: {$order->retailer_name} (Order ID: {$order->id})",
                        'retailer_order_id' => $order->id,
                        'created_by'        => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            try {
                $creator = User::find($order->created_by_user_id);
                if ($creator) {
                    $creator->notify(new RetailerOrderNotification(
                        'completed',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                        $order->retailer_name,
                        (int) $order->id
                    ));
                }
            } catch (\Exception $e) {
                Log::warning('Retailer order complete notification failed: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => $message], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
