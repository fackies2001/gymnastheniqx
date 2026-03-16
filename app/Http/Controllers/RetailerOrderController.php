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
    // ============================================================
    // ✅ HELPER: Get all admin users
    // ============================================================
    private function getAdmins()
    {
        return User::whereHas('role', function ($q) {
            $q->where('role_name', 'Admin')
                ->orWhere('role_name', 'admin');
        })->get();
    }

    // ============================================================
    // ✅ HELPER: Check if current user is admin
    // ============================================================
    private function isAdmin(): bool
    {
        return strtolower(Auth::user()->role?->role_name ?? '') === 'admin';
    }

    // ============================================================
    // ✅ INDEX METHOD — Role-based filtering
    //    Admin       → nakikita LAHAT ng orders
    //    Manager     → sarili lang niyang orders
    //    Staff       → sarili lang niyang orders
    // ============================================================
    public function index(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = $this->isAdmin();

        // ============================================================
        // ✅ DATE FILTER CONDITIONS
        // ============================================================
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
                                $request->end_date . ' 23:59:59',
                            ]);
                        }
                        break;
                }
            }
        };

        // ============================================================
        // ✅ BASE QUERY — Admin: lahat | Manager/Staff: sarili lang
        // ============================================================
        $baseQuery = RetailerOrder::query()
            ->when(!$isAdmin, function ($query) use ($user) {
                // Use created_by_user_id (integer) — mas reliable, walang conflict sa same name
                $query->where('created_by_user_id', $user->id);
            });

        // ============================================================
        // ✅ GET FILTERED ORDERS FOR TABLE
        // ============================================================
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

        // ============================================================
        // ✅ METRICS — Scoped din sa role
        // ============================================================
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

        // ============================================================
        // ✅ GET WAREHOUSE PRODUCTS
        // ============================================================
        $warehouse_products = SupplierProduct::select(
            'supplier_product.*',
            DB::raw('COUNT(CASE WHEN serialized_product.status = 1 THEN 1 END) as available_quantity')
        )
            ->leftJoin('serialized_product', 'supplier_product.id', '=', 'serialized_product.product_id')
            ->groupBy('supplier_product.id')
            ->get();

        return view('orders.index', compact(
            'retailer_orders',
            'warehouse_products',
            'totalSales',
            'pendingOrders',
            'completedOrders'
        ));
    }

    // ============================================================
    // ✅ STORE METHOD
    // ============================================================
    public function store(Request $request)
    {
        $request->validate([
            'retailer_name' => 'required',
            'product_id'    => 'required|exists:supplier_product,id',
            'quantity'      => 'required|integer|min:1',
            'unit_price'    => 'required|numeric',
        ]);

        $product        = SupplierProduct::findOrFail($request->product_id);
        $availableStock = SerializedProduct::where('product_id', $product->id)
            ->where('status', 1)
            ->count();

        // ✅ I-check MUNA bago mag-create ng order
        if ($availableStock === 0) {
            return back()->with('error', '❌ Order failed! No available stock for this product.');
        }

        if ($request->quantity > $availableStock) {
            return back()->with('error', "❌ Insufficient stock! Available: {$availableStock} units only. You ordered: {$request->quantity} units.");
        }

        // ✅ Gumawa ng order PAGKATAPOS ng validation
        $order = RetailerOrder::create([
            'product_id'          => $product->id,
            'retailer_name'       => $request->retailer_name,
            'product_name'        => $product->name,
            'quantity'            => $request->quantity,
            'unit_price'          => $request->unit_price,
            'total_amount'        => $request->quantity * $request->unit_price,
            'status'              => 'Pending',
            'sku'                 => $product->supplier_sku ?? $product->system_sku ?? 'N/A',
            'created_by'          => Auth::user()->full_name ?? 'Unknown User',
            'created_by_user_id'  => Auth::id(),
            'user_role'           => Auth::user()->role?->role_name ?? 'No Role',
        ]);

        // Notify admins
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

    // ============================================================
    // ✅ APPROVE METHOD — Admin only
    // ============================================================
    public function approve($id)
    {
        if (!$this->isAdmin()) {
            return back()->with('error', 'Access Denied! Only Admins can approve orders.');
        }

        $order = RetailerOrder::findOrFail($id);

        if ($order->status === 'Approved') {
            return back()->with('info', 'This order has already been approved.');
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
                ->whereIn('status', [1, 2])
                ->where('remarks', 'LIKE', "%Order ID: {$order->id}%")
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

            DB::commit();

            // Notify creator
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

            $reservedQty = count($serialNumbers);
            if ($reservedQty === 0) {
                return back()->with('warning', 'Order Approved with NO STOCK! Please request stock replenishment immediately.');
            } elseif ($reservedQty < $order->quantity) {
                return back()->with('warning', "Order Approved with PARTIAL stock! Reserved: {$reservedQty}/{$order->quantity}.");
            }

            return back()->with('success', 'Order Approved! Stock reserved via FIFO.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    // ============================================================
    // ✅ REJECT METHOD — Admin only
    // ============================================================
    public function reject($id)
    {
        if (!$this->isAdmin()) {
            return back()->with('error', 'Access Denied! Only Admins can reject orders.');
        }

        $order = RetailerOrder::findOrFail($id);
        $order->update([
            'status'      => 'Rejected',
            'rejected_by' => Auth::user()->full_name,
            'rejected_at' => now(),
        ]);

        // Notify creator
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

    // ============================================================
    // ✅ COMPLETE METHOD — Admin only
    // ============================================================
    public function complete($id)
    {
        if (!$this->isAdmin()) {
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
                if ($order->product_id) {
                    $product = SupplierProduct::find($order->product_id);
                }
                if (!$product && $order->sku) {
                    $product = SupplierProduct::where('supplier_sku', $order->sku)
                        ->orWhere('system_sku', $order->sku)
                        ->first();
                }
                if (!$product && $order->product_name) {
                    $product = SupplierProduct::where('name', $order->product_name)->first();
                }
                if (!$product) {
                    throw new \Exception("Product '{$order->product_name}' not found in inventory");
                }

                $serializedProducts = SerializedProduct::where('product_id', $product->id)
                    ->where('status', 1)
                    ->orderBy('created_at', 'asc')
                    ->limit($order->quantity)
                    ->get();

                $serialNumbers = $serializedProducts->pluck('serial_number')->toArray();
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

            DB::commit();

            // Notify creator
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
