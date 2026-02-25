<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SerialNumber;
use App\Models\SupplierProduct;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class DatatableServices
{

    /**
     * Supplier Details Table (Show - List View Only)
     */
    public function get_supplier_products_show_table($request)
    {
        $supplierId = $request->id ?? $request->supplier_id;

        if (!$supplierId) {
            return DataTables::of(collect([]))->make(true);
        }

        $query = \App\Models\SupplierProduct::with(['category', 'supplier'])
            ->where('supplier_id', $supplierId);

        return DataTables::eloquent($query)
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier->name ?? 'N/A';
            })
            ->addColumn('category_name', function ($row) {
                return $row->category->name ?? 'N/A';
            })
            ->addColumn('product_name', function ($row) {
                return $row->name ?? 'N/A';
            })
            ->addColumn('system_sku', function ($row) {
                return $row->system_sku ?? 'N/A';
            })
            ->addColumn('cost_price', function ($row) {
                return number_format($row->cost_price ?? 0, 2);
            })
            ->addColumn('date_created', function ($row) {
                return $row->created_at ? $row->created_at->format('M d, Y') : '-';
            })
            // ✅ ADD THESE FOR SEARCH & SORT SUPPORT
            ->filterColumn('supplier_name', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category_name', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->make(true);
    }

    public function get_pr_index_table($request) // Lagyan mo ng $request parameter
    {
        $query = \DB::table('purchase_request as pr')
            ->leftJoin('employee as u', 'pr.user_id', '=', 'u.id')
            ->leftJoin('department as d', 'pr.department_id', '=', 'd.id')
            ->leftJoin('supplier as s', 'pr.supplier_id', '=', 's.id')
            ->leftJoin('purchase_status_library as ps', 'pr.status_id', '=', 'ps.id')
            ->select(
                'pr.id',
                'pr.request_number',
                'pr.created_at',
                'pr.status_id',
                'u.full_name as requestor',
                'd.name as department_name',
                's.name as supplier_name',
                'ps.name as status_name'
            );

        return DataTables::of($query)
            ->order(function ($query) {
                $query->orderBy('pr.id', 'desc');
            })
            ->setRowId(function ($row) {
                return 'pr_' . $row->id;
            })
            // PINAKAMAHALAGA: Gawin nating 'pr_number' ang key para mag-match sa JS
            ->addColumn('pr_number', function ($row) {
                return '<span class="font-weight-bold" style="color: #333;">' . ($row->request_number ?? 'N/A') . '</span>';
            })
            ->addColumn('requestor', function ($row) {
                return $row->requestor ?? 'Unknown';
            })
            ->addColumn('department', function ($row) {
                return $row->department_name ?? 'N/A';
            })
            ->addColumn('created_at', function ($row) {
                return \Carbon\Carbon::parse($row->created_at)->format('M d, Y');
            })
            ->addColumn('action', function ($row) {
                if ($row->status_id == 1) {
                    return '<button class="btn btn-warning btn-xs review-pr-btn" data-pr-id="' . $row->id . '">
                                <i class="fas fa-clock"></i> PENDING
                            </button>';
                } elseif ($row->status_id == 2) {
                    return '<span class="badge badge-success px-2 py-1">
                                <i class="fas fa-check-circle"></i> APPROVED
                            </span>';
                } elseif ($row->status_id == 3) {
                    return '<span class="badge badge-danger px-2 py-1">
                                <i class="fas fa-times-circle"></i> REJECTED
                            </span>';
                }
                return '<span class="badge badge-secondary">Unknown</span>';
            })
            ->rawColumns(['pr_number', 'action']) // Siguraduhin na kasama ang pr_number dito
            ->make(true);
    }

    /**
     * Generic function para sa Product Lookup Modal base sa Supplier ID
     */
    public function get_products_by_supplier_datatable($supplier_id)
    {
        $query = \App\Models\SupplierProduct::where('supplier_id', $supplier_id);

        return DataTables::eloquent($query)
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-xs btn-primary select-product" 
                        data-id="' . $row->id . '" 
                        data-name="' . $row->name . '" 
                        data-price="' . $row->cost_price . '">
                        <i class="fas fa-plus"></i> Select
                    </button>';
            })
            ->make(true);
    }

    /**
     * Purchase Orders Table
     */
    public function get_purchase_order_table()
    {
        $query = PurchaseOrder::with([
            'supplier',
            'paymentTerms',
            'approvedBy',
            'purchaseRequest.user',
            'purchaseRequest.status',
        ]);

        return DataTables::eloquent($query)
            ->order(function ($query) {
                if (request()->has('order')) {
                    // Allow DataTables default sorting
                } else {
                    // Default view
                    $query->orderBy('order_date', 'desc');
                }
            })
            ->addColumn('po_number', function ($row) {
                $url = route('purchase_orders.show', $row->id);
                return '<a href="' . $url . '" class="text-primary font-weight-bold">'
                    . ($row->po_number ?? 'N/A') . '</a>';
            })
            ->addColumn('supplier', function ($row) {
                return $row->supplier->name ?? 'N/A';
            })
            ->addColumn('payment_term', function ($row) {
                return $row->paymentTerms->description ?? 'N/A';
            })
            ->addColumn('approved_by', function ($row) {
                $approver = $row->approvedBy;
                if ($approver) {
                    return '<div class="text-center">'
                        . '<strong>' . $approver->full_name . '</strong><br>'
                        . '<small class="text-muted">' . ($approver->role->role_name ?? 'N/A') . '</small>'
                        . '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('requested_by', function ($row) {
                $requestor = $row->purchaseRequest?->user;
                if ($requestor) {
                    return '<div class="text-center">'
                        . '<strong>' . $requestor->full_name . '</strong><br>'
                        . '<small class="text-muted">' . ($requestor->role->role_name ?? 'N/A') . '</small>'
                        . '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('order_date', function ($row) {
                return $row->order_date
                    ? \Carbon\Carbon::parse($row->order_date)->format('M d, Y')
                    : 'N/A';
            })
            ->addColumn('delivery_date', function ($row) {
                return $row->delivery_date
                    ? \Carbon\Carbon::parse($row->delivery_date)->format('M d, Y')
                    : 'N/A';
            })
            ->addColumn('action', function ($row) {
                $status = $row->purchaseRequest?->status;
                $statusId = $status?->id ?? 0;

                if ($statusId === 3) {
                    return '<button type="button" class="btn btn-sm btn-primary make-order-btn" 
                        data-po-id="' . $row->id . '">
                        <i class="fas fa-paper-plane"></i> Make Order
                    </button>';
                }

                $viewUrl = route('purchase_orders.show', $row->id);
                return '<a href="' . $viewUrl . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                </a>';
            })
            ->rawColumns(['po_number', 'approved_by', 'requested_by', 'action'])
            ->make(true);
    }

    /**
     * ✅ SIMPLIFIED: Single color badge for quantity display
     */
    public function get_serialized_products_summary_table($query)
    {
        return DataTables::eloquent($query)
            ->addColumn('product_name', function ($row) {
                return '<span class="font-weight-bold text-dark">' . ($row->name ?? 'N/A') . '</span>';
            })
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier->name ?? 'N/A';
            })
            ->addColumn('system_sku', function ($row) {
                return '<span class="font-monospace text-muted">' . ($row->system_sku ?? 'N/A') . '</span>';
            })
            ->addColumn('quantity', function ($row) {
                $available = $row->available_count ?? 0;

                return '<div class="text-center">
                        <span class="badge badge-primary px-3 py-2" 
                              style="font-size: 1rem; font-weight: 600;">
                            ' . $available . ' Units
                        </span>
                    </div>';
            })
            ->addColumn('action', function ($row) {
                return ['id' => $row->id, 'name' => $row->name];
            })
            ->rawColumns(['product_name', 'system_sku', 'quantity'])
            ->make(true);
    }


    /**
     * ✅ UPDATED: Layer 2: Specific Product Serial List
     * Now shows proper status names with color badges
     */
    public function get_serialized_product_table($supplier_product_id = null)
    {
        $query = \App\Models\SerializedProduct::with([
            'supplierProducts',
            'productStatus',
            'purchaseOrder',
            'scannedBy'
        ]);

        if ($supplier_product_id && $supplier_product_id != 0) {
            $query->where('product_id', $supplier_product_id);
        }

        return DataTables::eloquent($query)
            ->addColumn('serial_number', function ($row) {
                return '<span class="font-weight-bold text-primary">' . $row->serial_number . '</span>';
            })
            ->addColumn('status', function ($row) {
                $statusColors = [
                    1 => 'success',
                    2 => 'warning',
                    3 => 'danger',
                    4 => 'dark',
                    5 => 'danger',
                    6 => 'info',
                    7 => 'warning',
                ];

                $statusName = $row->productStatus->name ?? 'Unknown';
                $color = $statusColors[$row->status ?? 1] ?? 'secondary';

                return "<span class='badge badge-{$color} px-2 py-1'>{$statusName}</span>";
            })
            ->addColumn('scanned_by', function ($row) {
                $name = $row->scannedBy->full_name ?? 'System';
                return '<span class="small font-weight-bold">' . $name . '</span>';
            })
            ->addColumn('order_date', function ($row) {
                return $row->purchaseOrder
                    ? \Carbon\Carbon::parse($row->purchaseOrder->order_date)->format('M d, Y')
                    : '-';
            })
            ->addColumn('delivery_date', function ($row) {
                return $row->purchaseOrder
                    ? \Carbon\Carbon::parse($row->purchaseOrder->delivery_date)->format('M d, Y')
                    : '-';
            })
            ->addColumn('scanned_at', function ($row) {
                return $row->scanned_at
                    ? \Carbon\Carbon::parse($row->scanned_at)->format('M d, Y h:i A')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                return $row->serial_number;
            })
            ->orderColumn('serial_number', 'serial_number $1')
            ->orderColumn('status', 'status $1')
            ->orderColumn('scanned_by', 'scanned_by $1')        // ✅ Simple lang, walang join
            ->orderColumn('order_date', 'purchase_order_id $1') // ✅ Simple lang, walang join
            ->orderColumn('delivery_date', 'purchase_order_id $1') // ✅ Simple lang, walang join
            ->orderColumn('scanned_at', 'scanned_at $1')
            ->filter(function ($query) {
                if (request()->has('search') && !empty(request('search')['value'])) {
                    $search = request('search')['value'];
                    $query->where('serial_number', 'like', "%{$search}%");
                }
            })
            ->rawColumns(['serial_number', 'status', 'scanned_by'])
            ->make(true);
    }
    /**
     * All Serialized Products
     */
    public function get_serialized_products_table()
    {
        $query = SerialNumber::serializedProducts();
        $dataTable = DataTables::eloquent($query)
            ->addColumn('product_name', function ($row) {
                return $row->product_name;
            })
            ->addColumn('system_sku', function ($row) {
                return $row->system_sku;
            })
            ->addColumn('category_name', function ($row) {
                return $row->category_name;
            })
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier_name;
            })
            ->addColumn('quantity', function ($row) {
                return $row->quantity;
            })
            ->addColumn('action', function ($row) {
                return ['sp_id' => $row->supplier_product_id, 'sn_product_name' => $row->product_name, 'sn_quantity' => $row->quantity];
            });

        if (Gate::allows('can-see-images')) {
            $dataTable->addColumn('images', function ($row) {
                return view('components.bootstrap.product-image', ['product' => $row->images])->render();
            })->rawColumns(['images']);
        }

        return $dataTable->make(true);
    }

    public function get_purchase_request_table($request)
    {
        $query = PurchaseRequest::with(['user', 'department', 'supplier', 'status']);

        return DataTables::eloquent($query)
            ->addColumn('request_number', function ($row) {
                return $row->request_number;
            })

            ->addColumn('action', function ($row) {
                if ($row->status_id == 1) {
                    return '<button class="btn btn-warning btn-sm view-pr-details" data-id="' . $row->id . '">
                            <i class="fas fa-clock"></i> Pending
                        </button>';
                }
                return '<span class="badge badge-success">Approved</span>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Supplier Products Table - All columns without ID
     */
    public function get_supplier_product_table($request)
    {
        $isStudent = auth()->user()->is_student;
        $source_id = $isStudent ? [2] : [1, 3];

        $query = \App\Models\SupplierProduct::with(['supplier', 'category'])
            ->whereIn('source_id', $source_id)
            ->select('supplier_product.*');

        return DataTables::eloquent($query->orderBy('updated_at', 'desc'))
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier->name ?? 'N/A';
            })
            ->editColumn('name', function ($row) {
                return $row->name ?? 'N/A';
            })
            ->editColumn('system_sku', function ($row) {
                return $row->system_sku ?? 'N/A';
            })
            ->editColumn('cost_price', function ($row) {
                return number_format($row->cost_price ?? 0, 2);
            })
            ->editColumn('barcode', function ($row) {
                return $row->barcode ?? 'N/A';
            })
            ->make(true);
    }
}
