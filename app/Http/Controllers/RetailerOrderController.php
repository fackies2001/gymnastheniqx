<?php
// 📁 app/Http/Controllers/RetailerOrderController.php
// ✅ FIXED: Correct notification parameters

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

    public function index()
    {
        $retailer_orders = RetailerOrder::orderByRaw("
            CASE 
                WHEN status = 'Pending'  THEN 1
                WHEN status = 'Approved' THEN 2
                WHEN status = 'Rejected' THEN 3
            END
        ")
            ->orderBy('created_at', 'desc')
            ->get();

        $warehouse_products = SupplierProduct::select(
            'supplier_product.*',
            DB::raw('COUNT(CASE WHEN serialized_product.status = 1 THEN 1 END) as available_quantity')
        )
            ->leftJoin('serialized_product', 'supplier_product.id', '=', 'serialized_product.product_id')
            ->groupBy('supplier_product.id')
            ->get();

        $totalSales       = RetailerOrder::where('status', 'Approved')->sum('total_amount');
        $pendingOrders    = RetailerOrder::where('status', 'Pending')->count();
        $completedOrders  = RetailerOrder::where('status', 'Approved')->count();

        return view('orders.index', compact('retailer_orders', 'warehouse_products', 'totalSales', 'pendingOrders', 'completedOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'retailer_name' => 'required',
            'product_id'    => 'required|exists:supplier_product,id',
            'quantity'      => 'required|integer|min:1',
            'unit_price'    => 'required|numeric',
        ]);

        $product        = SupplierProduct::findOrFail($request->product_id);
        $availableStock = SerializedProduct::where('product_id', $product->id)->where('status', 1)->count();

        $order = RetailerOrder::create([
            'product_id'    => $product->id,
            'retailer_name' => $request->retailer_name,
            'product_name'  => $product->name,
            'quantity'      => $request->quantity,
            'unit_price'    => $request->unit_price,
            'total_amount'  => $request->quantity * $request->unit_price,
            'status'        => 'Pending',
            'sku'           => $product->supplier_sku ?? $product->system_sku ?? 'N/A',
            'created_by'    => Auth::user()->full_name ?? 'Unknown User',
            'user_role'     => Auth::user()->role?->role_name ?? 'No Role',
        ]);

        // ============================================================
        // ✅ NOTIFY ALL ADMINS: New Retailer Order
        // ============================================================
        try {
            $admins = $this->getAdmins();

            foreach ($admins as $admin) {
                if ($admin->id !== Auth::id()) {
                    $admin->notify(new RetailerOrderNotification(
                        'created',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // ✅ Order Number
                        $request->retailer_name,
                        (int) $order->id  // ✅ Order ID as integer
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Retailer order notification failed: ' . $e->getMessage());
        }

        if ($availableStock === 0) {
            return back()->with('warning', 'Order submitted successfully! ⚠️ NO STOCK AVAILABLE - Please request stock replenishment.');
        } elseif ($availableStock < $request->quantity) {
            return back()->with('warning', "Order submitted successfully! ⚠️ LOW STOCK - Available: {$availableStock}, Ordered: {$request->quantity}");
        }

        return back()->with('success', 'Order submitted successfully! Awaiting admin approval.');
    }

    public function approve($id)
    {
        $userRole = Auth::user()->role?->role_name;
        if (strtolower($userRole) !== 'admin') {
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
                'status'                    => 'Approved',
                'approved_by'               => Auth::user()->full_name,
                'approved_at'               => now(),
                'allocated_serial_numbers'  => json_encode($serialNumbers),
            ]);

            DB::commit();

            // ============================================================
            // ✅ NOTIFY THE CREATOR that order was approved
            // ============================================================
            try {
                // Find user by created_by name (best effort)
                $creator = User::where('full_name', $order->created_by)->first();
                if ($creator) {
                    $creator->notify(new RetailerOrderNotification(
                        'approved',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // ✅ Order Number
                        $order->retailer_name,
                        (int) $order->id  // ✅ Order ID as integer
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
            DB::rollback();
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $userRole = Auth::user()->role?->role_name;
        if (strtolower($userRole) !== 'admin') {
            return back()->with('error', 'Access Denied! Only Admins can reject orders.');
        }

        $order = RetailerOrder::findOrFail($id);
        $order->update([
            'status'      => 'Rejected',
            'rejected_by' => Auth::user()->full_name,
            'rejected_at' => now(),
        ]);

        // ============================================================
        // ✅ NOTIFY CREATOR: Order rejected
        // ============================================================
        try {
            $creator = User::where('full_name', $order->created_by)->first();
            if ($creator) {
                $creator->notify(new RetailerOrderNotification(
                    'rejected',
                    'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // ✅ Order Number
                    $order->retailer_name,
                    (int) $order->id  // ✅ Order ID as integer
                ));
            }
        } catch (\Exception $e) {
            Log::warning('Retailer order rejection notification failed: ' . $e->getMessage());
        }

        return back()->with('info', 'Order rejected.');
    }

    public function complete($id)
    {
        $userRole = Auth::user()->role?->role_name;
        if (strtolower($userRole) !== 'admin') {
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
                'status'                    => 'Completed',
                'shipped_by'                => Auth::user()->full_name,
                'shipped_at'                => now(),
                'allocated_serial_numbers'  => json_encode($serialNumbers),
            ]);

            DB::commit();

            // ============================================================
            // ✅ NOTIFY CREATOR: Order shipped/completed
            // ============================================================
            try {
                $creator = User::where('full_name', $order->created_by)->first();
                if ($creator) {
                    $creator->notify(new RetailerOrderNotification(
                        'completed',
                        'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // ✅ Order Number
                        $order->retailer_name,
                        (int) $order->id  // ✅ Order ID as integer
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
