<?php
/*
namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\DatatableServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PurchaseRequestController extends Controller
{
    protected $datatableServices;

    public function __construct(DatatableServices $datatableServices)
    {
        $this->datatableServices = $datatableServices;
    }

    public function getPurchaseRequestTable(Request $request)
    {
        if ($request->ajax()) {
            $query = PurchaseRequest::with(['user', 'department', 'supplier', 'status'])
                ->select('purchase_request.*');

            return DataTables::eloquent($query)
                ->addColumn('requestor', function ($pr) {
                    return $pr->user->full_name ?? 'N/A';
                })
                ->addColumn('department', function ($pr) {
                    return $pr->department->name ?? 'N/A';
                })
                ->addColumn('action', function ($pr) {
                    $statusId = $pr->status_id;

                    if ($statusId == 1) {
                        return '<span class="badge badge-warning view-pr-badge" data-id="' . $pr->id . '" style="cursor: pointer; font-size: 0.875rem; padding: 0.4em 0.8em;">PENDING</span>';
                    }

                    if ($statusId == 2) {
                        return '<span class="badge badge-success" style="font-size: 0.875rem; padding: 0.4em 0.8em;">APPROVED</span>';
                    }

                    if ($statusId == 3) {
                        return '<span class="badge badge-danger" style="font-size: 0.875rem; padding: 0.4em 0.8em;">REJECTED</span>';
                    }

                    return '<span class="badge badge-secondary">N/A</span>';
                })
                ->addColumn('id', function ($pr) {
                    return $pr->id;
                })
                ->editColumn('created_at', function ($pr) {
                    return $pr->created_at->format('M d, Y');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function index()
    {
        $purchaseRequests = PurchaseRequest::with(['user', 'department', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::all();
        return view('purchase-request.index', compact('purchaseRequests', 'suppliers'));
    }


    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'supplier_id'    => 'required|exists:supplier,id',
            'products'       => 'required|array|min:1',
            'products.*.quantity'   => 'required|integer|min:1',
            'products.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Generate PR number
            $prefix = "PR-" . date('Ym');

            $lastPR = PurchaseRequest::where('request_number', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastPR) {
                $lastSequence = intval(substr($lastPR->request_number, -3));
                $newNumber = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $prNumber = $prefix . '-' . $newNumber;

            // ✅ Create PR - CHECK THIS CAREFULLY
            $pr = PurchaseRequest::create([
                'request_number' => $prNumber,
                'user_id'        => $user->id,
                'department_id'  => $user->department_id,
                'supplier_id'    => $validated['supplier_id'],
                'status_id'      => 1,
                'order_date'     => now(),
            ]);

            // ✅ Verify PR was created
            if (!$pr || !$pr->id) {
                throw new \Exception('Failed to create Purchase Request');
            }

            Log::info('🔍 Creating PR items:', [
                'pr_id' => $pr->id,
                'pr_number' => $pr->request_number,
                'products' => $request->products
            ]);

            // ✅ Create items with duplicate protection
            $processedProducts = []; // Para sa tracking sa loob ng loop

            foreach ($request->products as $productId => $details) {
                // 1. Skip kung naproseso na itong product_id sa loop na ito
                if (in_array($productId, $processedProducts)) {
                    continue;
                }

                // 2. Double check sa database kung nag-exist na (optional but safer)
                $exists = PurchaseRequestItem::where('purchase_request_id', $pr->id)
                    ->where('product_id', $productId)
                    ->exists();

                if (!$exists) {
                    $pr->items()->create([
                        'product_id' => $productId,
                        'quantity'   => $details['quantity'],
                        'unit_cost'  => $details['unit_cost'],
                        'subtotal'   => ($details['quantity'] * $details['unit_cost']),
                    ]);

                    $processedProducts[] = $productId; // Mark as done
                }
            }

            Log::info('✅ PR created successfully:', [
                'pr_id' => $pr->id,
                'pr_number' => $pr->request_number,
                'items_count' => $pr->items()->count()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Request created successfully!',
                'pr_id'   => $pr->id,
                'pr_number' => $pr->request_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ PR Store Error:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save PR: ' . $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $pr = PurchaseRequest::with([
                'supplier',
                'user.department',
                'department',
                'status',
                'items.supplierProduct' // ✅ Eager load with correct relationship
            ])->findOrFail($id);

            // ✅ NO NEED for manual mapping anymore - use accessors!
            // But we'll add it for safety
            foreach ($pr->items as $item) {
                // Double-check product info is loaded
                if (!isset($item->product_name)) {
                    $item->product_name = $item->supplierProduct->name ?? 'Unknown Product';
                }
                if (!isset($item->sku)) {
                    $item->sku = $item->supplierProduct->system_sku ??
                        $item->supplierProduct->supplier_sku ??
                        'No SKU';
                }
            }

            // Department fallback
            if (!$pr->department && $pr->user && $pr->user->department) {
                $pr->setRelation('department', $pr->user->department);
            }

            // Set requestor name
            $pr->requestor_name = $pr->user->full_name ?? 'Unknown';

            // Supplier contact details
            if ($pr->supplier) {
                $pr->supplier_contact_person = $pr->supplier->contact_person ?? 'N/A';
                $pr->supplier_contact_number = $pr->supplier->contact_number ?? 'N/A';
                $pr->supplier_email = $pr->supplier->email ?? 'N/A';
            }

            return response()->json($pr);
        } catch (\Exception $e) {
            Log::error('PR Show Error:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading PR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'order_date' => 'nullable|date',
            'estimated_delivery_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|in:cash_on_delivery,bank_transfer',
            'remarks' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $pr = PurchaseRequest::with('items.supplierProduct')->findOrFail($id);

            Log::info('🔍 Approving PR:', [
                'pr_id' => $pr->id,
                'pr_number' => $pr->request_number,
                'items_count' => $pr->items->count()
            ]);

            // Update PR status
            $pr->update([
                'status_id' => 2,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $validated['remarks'] ?? null
            ]);

            // Calculate Grand Total
            $grandTotal = $pr->items->sum('subtotal');

            // Generate PO Number
            $poNumber = 'PO-' . date('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT);

            // Create PO
            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'purchase_request_id' => $pr->id,
                'supplier_id' => $pr->supplier_id,
                'approved_by' => auth()->id(),
                'requested_by' => $pr->user_id,
                'order_date' => $validated['order_date'] ?? now(),
                'delivery_date' => $validated['estimated_delivery_date'] ?? now()->addDays(7),
                'payment_terms' => $validated['payment_terms'] ?? 'cash_on_delivery',
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'pending_scan',
                'grand_total' => $grandTotal,
            ]);

            Log::info('📦 Creating PO items from PR items...');

            // ✅ Copy PR items to PO items (no duplicates, just direct copy)
            foreach ($pr->items as $item) {
                Log::info('  → Adding item:', [
                    'product_id' => $item->product_id,
                    'product_name' => $item->supplierProduct->name ?? 'Unknown',
                    'quantity' => $item->quantity
                ]);

                $po->items()->create([
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                    'quantity_scanned' => 0,
                    'unit_cost' => $item->unit_cost,
                    'subtotal' => $item->subtotal,
                ]);
            }

            Log::info('✅ PO created successfully:', [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'items_count' => $po->items()->count()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Request approved and PO created successfully!',
                'po_id' => $po->id,
                'po_number' => $po->po_number
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ PR Approve Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $pr = PurchaseRequest::findOrFail($id);

            $pr->update([
                'status_id' => 3,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase request rejected successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSupplierProducts($id)
    {
        try {
            $products = SupplierProduct::where('supplier_id', $id)
                ->select('id', 'name', 'cost_price', 'supplier_sku', 'system_sku')
                ->get()
                ->unique('name') // <--- DITO MO GAWIN ANG UNIQUE
                ->values();      // Reset array keys para maging JSON array pa rin

            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generatePRNumber()
    {
        try {
            DB::beginTransaction();

            $prefix = "PR-" . date('Ym');

            $lastPR = PurchaseRequest::where('request_number', 'LIKE', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            if ($lastPR) {
                $lastSequence = intval(substr($lastPR->request_number, -3));
                $newNumber = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $prNumber = $prefix . '-' . $newNumber;

            DB::commit();

            return response()->json([
                'success' => true,
                'request_number' => $prNumber
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}


feb 11