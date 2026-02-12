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

    {{-- PO DETAILS MODAL --}}
    <div class="modal fade" id="poDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow">
                <div class="modal-header bg-dark text-white py-2">
                    <h5 class="modal-title font-weight-bold small">PURCHASE ORDER DETAILS</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-3 bg-light">
                    {{-- PO Header Info --}}
                    <div class="row mb-3 bg-white p-2 rounded shadow-sm mx-0 border">
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">PO Number</label>
                            <p id="detail_po_number" class="font-weight-bold mb-0 text-primary"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">Supplier</label>
                            <p id="detail_supplier" class="font-weight-bold mb-0"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">Order Date</label>
                            <p id="detail_date" class="font-weight-bold mb-0"></p>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="table-responsive bg-white rounded border shadow-sm">
                        <table class="table table-sm table-hover mb-0" id="poItemsTable">
                            <thead class="bg-secondary text-white small">
                                <tr>
                                    <th>Item Description</th>
                                    <th class="text-center" width="100px">Qty Ordered</th>
                                    <th class="text-center" width="100px">Received</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                {{-- Dynamically Loaded --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
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

            // ✅ FIX: Check if DataTable already exists before initializing
            if (!$.fn.DataTable.isDataTable('#poTable')) {
                var table = $('#poTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "order": [
                        [3, "desc"]
                    ],
                    "language": {
                        "paginate": {
                            "previous": "Previous",
                            "next": "Next"
                        }
                    }
                });
            }

            // ✅ VIEW PO DETAILS - With AJAX implementation
            $(document).on('click', '.view-po-details', function() {
                let poId = $(this).data('id');
                currentPOId = poId;

                $.ajax({
                    url: `/purchase-order/details/${poId}`,
                    type: 'GET',
                    success: function(res) {
                        if (res.success) {
                            // Fill header info
                            $('#detail_po_number').text(res.po_number);
                            $('#detail_supplier').text(res.supplier.name);
                            $('#detail_date').text(res.order_date);

                            // Fill items table
                            let itemsHtml = '';
                            res.items.forEach(item => {
                                itemsHtml += `
                                    <tr>
                                        <td>${item.product.name}</td>
                                        <td class="text-center">${item.quantity}</td>
                                        <td class="text-center">${item.quantity}</td>
                                    </tr>
                                `;
                            });
                            $('#poItemsTable tbody').html(itemsHtml);

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

        .card-po-custom {
            border-radius: 8px;
            border-top: 3px solid #343a40 !important;
        }

        /* Modal Refinement */
        .modal-content {
            border-radius: 8px;
            overflow: hidden;
        }

        #poItemsTable thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }

        .badge {
            font-size: 85%;
            padding: 0.4em 0.6em;
        }
    </style>
@endpush
