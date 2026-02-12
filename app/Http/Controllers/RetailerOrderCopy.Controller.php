<?php
/*
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RetailerOrder;
use App\Models\SupplierProduct;
use App\Models\SerializedProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RetailerOrderController extends Controller
{
    public function index()
    {
        $retailer_orders = RetailerOrder::orderByRaw("
            CASE 
                WHEN status = 'Pending' THEN 1
                WHEN status = 'Approved' THEN 2
                WHEN status = 'Rejected' THEN 3
            END
        ")
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ Get products with available stock
        $warehouse_products = SupplierProduct::select(
            'supplier_product.*',
            DB::raw('COUNT(CASE WHEN serialized_product.status = 1 THEN 1 END) as available_quantity')
        )
            ->leftJoin('serialized_product', 'supplier_product.id', '=', 'serialized_product.product_id')
            ->groupBy('supplier_product.id')
            ->get();

        $totalSales = RetailerOrder::where('status', 'Approved')->sum('total_amount');
        $pendingOrders = RetailerOrder::where('status', 'Pending')->count();
        $completedOrders = RetailerOrder::where('status', 'Approved')->count();

        return view('orders.index', compact('retailer_orders', 'warehouse_products', 'totalSales', 'pendingOrders', 'completedOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'retailer_name' => 'required',
            'product_id' => 'required|exists:supplier_product,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric',
        ]);

        $product = SupplierProduct::findOrFail($request->product_id);

        // ✅ Check stock (for info only, no blocking)
        $availableStock = SerializedProduct::where('product_id', $product->id)
            ->where('status', 1)
            ->count();

        // ✅ Use actual product data, not form input
        RetailerOrder::create([
            'product_id' => $product->id, // ✅ STORE PRODUCT ID - eliminates SKU mismatch issues!
            'retailer_name' => $request->retailer_name,
            'product_name' => $product->name,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_amount' => $request->quantity * $request->unit_price,
            'status' => 'Pending',
            'sku' => $product->supplier_sku ?? $product->system_sku ?? 'N/A', // Keep for display only
            'created_by' => Auth::user()->full_name ?? 'Unknown User',
            'user_role' => Auth::user()->role?->role_name ?? 'No Role',
        ]);

        // ✅ Show warning if low/no stock
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

        // ✅ IMPROVED: Get product by product_id (with SKU fallback for old orders)
        $product = null;

        if ($order->product_id) {
            // New way: Direct product_id lookup
            $product = SupplierProduct::find($order->product_id);
        }

        if (!$product && $order->sku) {
            // Fallback for old orders: SKU-based lookup
            $product = SupplierProduct::where('supplier_sku', $order->sku)
                ->orWhere('system_sku', $order->sku)
                ->first();
        }

        if (!$product) {
            return back()->with('error', "Product not found in inventory. (SKU: {$order->sku})");
        }

        // ✅ Check available stock (no validation, just info)
        $availableStock = SerializedProduct::where('product_id', $product->id)
            ->where('status', 1)
            ->count();

        DB::beginTransaction();
        try {
            // ✅ Get available items (FIFO) - reserve kung meron, if wala ok lang
            $serializedProducts = SerializedProduct::where('product_id', $product->id)
                ->where('status', 1)
                ->orderBy('created_at', 'asc')
                ->limit($order->quantity)
                ->get();

            // ✅ Reserve them (kung meron man)
            $serialNumbers = [];
            foreach ($serializedProducts as $serialProduct) {
                $serialProduct->update([
                    'status' => 2, // Reserved
                    'remarks' => "Reserved for Retailer: {$order->retailer_name} (Order ID: {$order->id})"
                ]);
                $serialNumbers[] = $serialProduct->serial_number;
            }

            // ✅ Update order
            $order->update([
                'status' => 'Approved',
                'approved_by' => Auth::user()->full_name,
                'approved_at' => now(),
                'allocated_serial_numbers' => json_encode($serialNumbers),
            ]);

            DB::commit();

            // ✅ Build message based on stock availability
            $reservedQty = count($serialNumbers);
            if ($reservedQty === 0) {
                return back()->with('warning', 'Order Approved with NO STOCK! Please request stock replenishment immediately.');
            } elseif ($reservedQty < $order->quantity) {
                return back()->with('warning', "Order Approved with PARTIAL stock! Reserved: {$reservedQty}/{$order->quantity}. Please request additional stock.");
            } else {
                return back()->with('success', 'Order Approved! Stock reserved via FIFO.');
            }
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
            'status' => 'Rejected',
            'rejected_by' => Auth::user()->full_name,
            'rejected_at' => now(),
        ]);

        return back()->with('info', 'Order rejected.');
    }


    /**
     * ✅ MODIFIED: Complete order - ALLOWS shipping even with low/insufficient stock
     * - Ships whatever is available
     * - Marks order as "Completed" with partial fulfillment note
     */
    /*
    public function complete($id)
    {
        $userRole = Auth::user()->role?->role_name;

        if (strtolower($userRole) !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied!'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $order = RetailerOrder::findOrFail($id);

            if ($order->status !== 'Approved') {
                throw new \Exception('Order must be approved first');
            }

            // ✅ TRY to get allocated serial numbers
            $serialNumbers = json_decode($order->allocated_serial_numbers, true);

            // ✅ FALLBACK: If old order without allocated serial numbers
            if (!$serialNumbers || count($serialNumbers) === 0) {

                // ✅ IMPROVED: Find product by product_id first, then SKU fallback
                $product = null;

                if ($order->product_id) {
                    // New way: Direct product_id lookup
                    $product = SupplierProduct::find($order->product_id);
                }

                if (!$product && $order->sku) {
                    // Fallback for old orders: SKU-based lookup
                    $product = SupplierProduct::where('supplier_sku', $order->sku)
                        ->orWhere('system_sku', $order->sku)
                        ->first();
                }

                // ✅ Last resort: Try by product name
                if (!$product && $order->product_name) {
                    $product = SupplierProduct::where('name', $order->product_name)->first();
                }

                if (!$product) {
                    throw new \Exception("Product '{$order->product_name}' (SKU: {$order->sku}) not found in inventory");
                }

                // ✅ MODIFIED: Get available stock NOW (FIFO) - pero wala nang validation!
                $serializedProducts = SerializedProduct::where('product_id', $product->id)
                    ->where('status', 1) // Available
                    ->orderBy('created_at', 'asc')
                    ->limit($order->quantity)
                    ->get();

                // ✅ REMOVED STOCK VALIDATION - Ship whatever is available!
                // Para kahit 19 lang available pero 25 ang order, i-proceed pa rin!

                // Get serial numbers (kung wala man, empty array lang)
                $serialNumbers = $serializedProducts->pluck('serial_number')->toArray();
            }

            // ✅ Mark serialized products as Sold (status = 3) - kung meron man
            if (!empty($serialNumbers)) {
                $updateCount = SerializedProduct::whereIn('serial_number', $serialNumbers)
                    ->update([
                        'status' => 3, // Sold
                        'remarks' => "Sold to Retailer: {$order->retailer_name} (Order ID: {$order->id})"
                    ]);

                // ✅ Log if update failed
                if ($updateCount === 0) {
                    \Log::warning("No serialized products updated for order {$order->id}. Serial numbers: " . json_encode($serialNumbers));
                }
            }

            // ✅ Build success message with actual shipped quantity
            $shippedQty = count($serialNumbers);
            $message = "Order shipped successfully!";

            if ($shippedQty < $order->quantity) {
                $message = "Order shipped with PARTIAL fulfillment! Shipped: {$shippedQty}/{$order->quantity} items. (Low stock)";
            } else {
                $message = "Order shipped successfully! {$shippedQty} items marked as SOLD.";
            }

            // ✅ Update order status to Completed
            $order->update([
                'status' => 'Completed',
                'shipped_by' => Auth::user()->full_name,
                'shipped_at' => now(),
                'allocated_serial_numbers' => json_encode($serialNumbers),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

feb 11