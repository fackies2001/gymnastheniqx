@extends('layouts.adminlte')

@section('subtitle', 'Purchase Orders')
@section('content_header_subtitle', 'Purchase Orders')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)

@section('content_body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 card-po-custom">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                            <i class="fas fa-file-invoice mr-2 text-primary"></i> PURCHASE ORDER LIST
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="poTable" class="table table-bordered table-striped table-hover w-100">
                                <thead class="bg-dark text-white text-uppercase small">
                                    <tr>
                                        <th>PO Number</th>
                                        <th>Supplier</th>
                                        <th>Approved By</th>
                                        <th>Order Date</th>
                                        <th>Status</th>
                                        <th class="text-center no-print">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    @foreach ($purchaseOrders as $po)
                                        <tr>
                                            <td class="font-weight-bold">{{ $po->po_number }}</td>
                                            <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                            <td>{{ $po->approvedBy->full_name ?? 'N/A' }}</td>
                                            <td>{{ $po->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @php
                                                    $statusClass = match (strtolower($po->status)) {
                                                        'pending' => 'badge-warning',
                                                        'pending_scan' => 'badge-warning',
                                                        'approved' => 'badge-info',
                                                        'completed' => 'badge-success',
                                                        'cancelled' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $po->status)) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-info btn-xs shadow-sm view-po-details"
                                                    data-id="{{ $po->id }}" title="View Details">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PO DETAILS MODAL - IMPROVED VERSION --}}
    <div class="modal fade" id="poDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow">
                <div class="modal-header bg-dark text-white py-2">
                    <h5 class="modal-title font-weight-bold small">PURCHASE ORDER DETAILS</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3 bg-light">
                    {{-- PO Header Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i>Order Information</h6>
                                </div>
                                <div class="card-body bg-white">
                                    <div class="info-row">
                                        <div class="info-label">PO Number</div>
                                        <div id="detail_po_number" class="info-value text-primary"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Order Date</div>
                                        <div id="detail_order_date" class="info-value"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Delivery Date</div>
                                        <div id="detail_delivery_date" class="info-value"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Payment Terms</div>
                                        <div id="detail_payment_terms" class="info-value"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Requested By</div>
                                        <div id="detail_requested_by" class="info-value"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Department</div>
                                        <div id="detail_department" class="info-value"></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Approved By</div>
                                        <div id="detail_approved_by" class="info-value"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ⭐ IMPROVED: Supplier Details Section --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0"><i class="fas fa-truck mr-2"></i>Supplier Information</h6>
                                </div>
                                <div class="card-body bg-white">
                                    <div class="info-row">
                                        <div class="info-label">Supplier Name</div>
                                        <div id="detail_supplier_name" class="info-value text-success font-weight-bold">
                                        </div>
                                    </div>
                                    <div class="info-row" id="contact_person_row">
                                        <div class="info-label"><i class="fas fa-user mr-1"></i> Contact Person</div>
                                        <div id="detail_supplier_contact_person" class="info-value"></div>
                                    </div>
                                    <div class="info-row" id="contact_number_row">
                                        <div class="info-label"><i class="fas fa-phone mr-1"></i> Contact Number</div>
                                        <div id="detail_supplier_contact_number" class="info-value"></div>
                                    </div>
                                    <div class="info-row" id="email_row">
                                        <div class="info-label"><i class="fas fa-envelope mr-1"></i> Email</div>
                                        <div id="detail_supplier_email" class="info-value"></div>
                                    </div>
                                    <div class="info-row" id="address_row">
                                        <div class="info-label"><i class="fas fa-map-marker-alt mr-1"></i> Address</div>
                                        <div id="detail_supplier_address" class="info-value"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Remarks Section --}}
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-comment mr-2"></i>Remarks</h6>
                        </div>
                        <div class="card-body bg-white">
                            <p id="detail_remarks" class="mb-0 text-muted"></p>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-info text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-list mr-2"></i>Order Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" id="poItemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="50" class="text-center">#</th>
                                            <th>Item Description</th>
                                            <th class="text-center" width="100px">Qty Ordered</th>
                                            <th class="text-right" width="120px">Unit Cost</th>
                                            <th class="text-right" width="120px">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        {{-- Dynamically Loaded --}}
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right py-2">GRAND TOTAL:</td>
                                            <td class="text-right text-success py-2" id="detail_grand_total"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm px-4 shadow-sm"
                        data-dismiss="modal">Close</button>
                    <button type="button" id="scanOrderBtn" class="btn btn-primary btn-sm px-4 shadow-sm">
                        <i class="fas fa-barcode mr-1"></i> SCAN ORDER
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let currentPOId = null;

            // ✅ FIX: Disable sorting on ACTION column
            if (!$.fn.DataTable.isDataTable('#poTable')) {
                var table = $('#poTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "order": [
                        [3, "desc"]
                    ],
                    "columnDefs": [{
                        "orderable": false,
                        "targets": 5 // ✅ ACTION column (0-indexed)
                    }],
                    "language": {
                        "paginate": {
                            "previous": "Previous",
                            "next": "Next"
                        }
                    }
                });
            }

            // ⭐ IMPROVED: VIEW PO DETAILS with better empty field handling
            $(document).on('click', '.view-po-details', function() {
                let poId = $(this).data('id');
                currentPOId = poId;

                $.ajax({
                    url: `/purchase-order/details/${poId}`,
                    type: 'GET',
                    success: function(res) {
                        if (res.success) {
                            // Fill PO header info
                            $('#detail_po_number').text(res.po_number);
                            $('#detail_order_date').text(res.order_date);
                            $('#detail_delivery_date').text(res.delivery_date);
                            $('#detail_payment_terms').text(res.payment_terms);
                            $('#detail_requested_by').text(res.requested_by.name);
                            $('#detail_department').text(res.department);
                            $('#detail_approved_by').text(res.approved_by.name);
                            $('#detail_remarks').text(res.remarks);

                            // ⭐ IMPROVED: Fill supplier information with smart hiding
                            $('#detail_supplier_name').text(res.supplier.name);

                            // Contact Person - hide row if empty
                            if (res.supplier.contact_person && res.supplier.contact_person !==
                                'N/A') {
                                $('#detail_supplier_contact_person').text(res.supplier
                                    .contact_person);
                                $('#contact_person_row').show();
                            } else {
                                $('#contact_person_row').hide();
                            }

                            // Contact Number - hide row if empty
                            if (res.supplier.contact_number && res.supplier.contact_number !==
                                'N/A') {
                                $('#detail_supplier_contact_number').text(res.supplier
                                    .contact_number);
                                $('#contact_number_row').show();
                            } else {
                                $('#contact_number_row').hide();
                            }

                            // Email - hide row if empty
                            if (res.supplier.email && res.supplier.email !== 'N/A') {
                                $('#detail_supplier_email').text(res.supplier.email);
                                $('#email_row').show();
                            } else {
                                $('#email_row').hide();
                            }

                            // Address - hide row if empty
                            if (res.supplier.address && res.supplier.address !== 'N/A') {
                                $('#detail_supplier_address').text(res.supplier.address);
                                $('#address_row').show();
                            } else {
                                $('#address_row').hide();
                            }

                            // Fill items table with numbering
                            let itemsHtml = '';
                            let itemNumber = 1;
                            res.items.forEach(item => {
                                itemsHtml += `
                                    <tr>
                                        <td class="text-center">${itemNumber++}</td>
                                        <td>${item.product.name}</td>
                                        <td class="text-center"><span class="badge badge-primary">${item.quantity}</span></td>
                                        <td class="text-right">₱${parseFloat(item.unit_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        <td class="text-right">₱${parseFloat(item.subtotal).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    </tr>
                                `;
                            });
                            $('#poItemsTable tbody').html(itemsHtml);

                            // Display grand total
                            $('#detail_grand_total').text('₱' + parseFloat(res.grand_total)
                                .toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }));

                            // Show modal
                            $('#poDetailModal').modal('show');
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Failed to load PO details';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });

            // ✅ SCAN ORDER BUTTON - Redirect to scan page
            $('#scanOrderBtn').on('click', function() {
                if (currentPOId) {
                    window.location.href = `/purchase-order/scan/${currentPOId}`;
                }
            });
        });
    </script>
@endpush

@push('css')
    <style>
        /* ✅ Table Sorting UI Fix */
        table.dataTable thead .sorting:before,
        table.dataTable thead .sorting:after {
            bottom: .5em !important;
        }

        /* ✅ Remove sorting arrows from ACTION column */
        table.dataTable thead th:nth-child(6).sorting_disabled:before,
        table.dataTable thead th:nth-child(6).sorting_disabled:after {
            display: none !important;
        }

        .card-po-custom {
            border-radius: 8px;
            border-top: 3px solid #343a40 !important;
        }

        /* Modal Refinement */
        .modal-content {
            border-radius: 8px;
            overflow: hidden;
        }

        /* ⭐ IMPROVED: Info Row Styling */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex: 0 0 140px;
        }

        .info-value {
            font-size: 14px;
            color: #212529;
            font-weight: 500;
            flex: 1;
            text-align: right;
        }

        /* Card styling */
        .card-header h6 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Table improvements */
        #poItemsTable thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            background-color: #f8f9fa !important;
            color: #495057;
        }

        #poItemsTable tbody td {
            vertical-align: middle;
            font-size: 13px;
        }

        #poItemsTable tfoot td {
            font-size: 14px;
        }

        .badge {
            font-size: 85%;
            padding: 0.4em 0.6em;
        }

        /* Smooth transitions */
        .card {
            transition: all 0.3s ease;
        }

        .info-row {
            transition: background-color 0.2s ease;
        }

        .info-row:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush
