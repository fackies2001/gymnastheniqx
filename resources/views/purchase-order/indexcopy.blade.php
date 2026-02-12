@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content_body')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 card-po-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                    <i class="fas fa-file-invoice mr-2 text-primary"></i>PURCHASE ORDER LIST
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
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach ($purchaseOrders as $po)
                                <tr>
                                    <td>
                                        <a href="javascript:void(0)" class="view-po-details font-weight-bold text-primary"
                                            data-id="{{ $po->id }}">
                                            {{ $po->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $po->approvedBy->first_name ?? 'N/A' }} {{ $po->approvedBy->last_name ?? '' }}
                                    </td>
                                    <td>{{ $po->order_date ? $po->order_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = 'badge-secondary';
                                            if ($po->status == 'pending') {
                                                $badgeClass = 'badge-warning';
                                            }
                                            if ($po->status == 'completed') {
                                                $badgeClass = 'badge-success';
                                            }
                                            if ($po->status == 'processing') {
                                                $badgeClass = 'badge-info';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-2 py-1">
                                            {{ strtoupper($po->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm view-po-details shadow-sm"
                                            data-id="{{ $po->id }}">
                                            <i class="fas fa-eye"></i> VIEW
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

    <div class="modal fade" id="poDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg shadow-lg" role="document">
            <div class="modal-content border-0" style="border-radius: 12px;">
                <div class="modal-header bg-dark text-white border-bottom-0" style="border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title font-weight-bold text-uppercase small">
                        <i class="fas fa-info-circle mr-2 text-warning"></i>PO Details: <span id="disp_po_number"
                            class="text-warning"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light p-4">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-md-6 border-right">
                                            <small
                                                class="text-muted d-block text-uppercase font-weight-bold">Supplier</small>
                                            <span id="disp_supplier" class="small font-weight-bold"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block text-uppercase font-weight-bold">Delivery
                                                Date</small>
                                            <span id="disp_delivery" class="small font-weight-bold"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive bg-white rounded shadow-sm border" style="max-height: 250px;">
                                <table class="table table-sm table-striped mb-0" id="poItemsTable">
                                    <thead class="bg-secondary text-white small">
                                        <tr>
                                            <th class="pl-3">Product Name</th>
                                            <th class="text-center">Order Qty</th>
                                            <th class="text-center">Scanned</th>
                                            <th class="text-right pr-3">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 d-flex justify-content-between p-3"
                    style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light btn-sm px-4" data-dismiss="modal">CLOSE</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" id="makeOrderBtn">
                        <i class="fas fa-barcode mr-1"></i> MAKE ORDER (SCAN)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <style>
        .card-po-custom {
            border-radius: 12px;
            border: none;
        }

        /* DataTables Custom UI para maging kamukha ng PR */
        #poTable_wrapper .dataTables_length {
            float: left;
            margin-bottom: 10px;
        }

        #poTable_wrapper .dataTables_filter {
            float: right;
            margin-bottom: 10px;
        }

        #poTable_wrapper .dataTables_info {
            float: left;
            margin-top: 10px;
        }

        #poTable_wrapper .dataTables_paginate {
            float: right;
            margin-top: 10px;
        }

        .table thead th {
            vertical-align: middle;
            text-align: center;
        }

        .view-po-details {
            text-decoration: none !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#poTable')) {
                $('#poTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "order": [
                        [3, "desc"]
                    ], // Sort sa Date Created
                    "language": {
                        "search": "Search PO:",
                        "lengthMenu": "Show _MENU_ entries"
                    }
                });
            }
        });
    </script>
    @vite(['resources/js/purchase-order/purchase-order.js'])
@endsection

24-01-2026