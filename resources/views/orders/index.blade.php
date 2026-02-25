@extends('layouts.adminlte')

@section('subtitle', 'Retailer Orders')
@section('content_header_title', 'Retailer Orders')

@section('content_body')
    {{-- 1. SUMMARY CARDS --}}
    <div class="row no-print">
        <div class="col-md-4">
            <div class="small-box bg-info shadow">
                <div class="inner">
                    <h3>₱ {{ number_format($totalSales, 2) }}</h3>
                    <p>Total Approved Sales</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning shadow text-dark">
                <div class="inner">
                    <h3>{{ $pendingOrders }}</h3>
                    <p>Pending Approvals</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success shadow">
                <div class="inner">
                    <h3>{{ $completedOrders }}</h3>
                    <p>Completed Transactions</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    {{-- ✅ 2. CLEANER DATE FILTER --}}
    <div class="card shadow mb-4 no-print">
        <div class="card-header bg-gradient-primary">
            <h3 class="card-title font-weight-bold text-white">
                <i class="fas fa-filter"></i> FILTER BY DATE
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('retailer.orders.index') }}" id="dateFilterForm">
                <div class="row">
                    {{-- Filter Dropdown --}}
                    <div class="col-lg-5 col-md-6 mb-3">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar text-primary"></i> Select Time Period
                        </label>
                        <select name="filter_type" id="filterType" class="form-control form-control-lg shadow-sm">
                            <option value="">All Time Records</option>
                            <option value="today" {{ request('filter_type') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('filter_type') == 'yesterday' ? 'selected' : '' }}>
                                Yesterday</option>
                            <option value="last_7_days" {{ request('filter_type') == 'last_7_days' ? 'selected' : '' }}>Last
                                7 Days</option>
                            <option value="last_30_days" {{ request('filter_type') == 'last_30_days' ? 'selected' : '' }}>
                                Last 30 Days</option>
                            <option value="this_month" {{ request('filter_type') == 'this_month' ? 'selected' : '' }}>This
                                Month</option>
                            <option value="last_month" {{ request('filter_type') == 'last_month' ? 'selected' : '' }}>Last
                                Month</option>
                            <option value="this_year" {{ request('filter_type') == 'this_year' ? 'selected' : '' }}>This
                                Year</option>
                            <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Custom Range
                            </option>
                        </select>
                    </div>

                    {{-- Custom Start Date --}}
                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRange"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar-day text-success"></i> From
                        </label>
                        <input type="date" name="start_date" id="startDate"
                            class="form-control form-control-lg shadow-sm" value="{{ request('start_date') }}">
                    </div>

                    {{-- Custom End Date --}}
                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRangeEnd"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar-check text-danger"></i> To
                        </label>
                        <input type="date" name="end_date" id="endDate" class="form-control form-control-lg shadow-sm"
                            value="{{ request('end_date') }}">
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="d-block">&nbsp;</label>
                        <div class="btn-group btn-block">
                            <button type="submit" class="btn btn-primary btn-lg shadow">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('retailer.orders.index') }}" class="btn btn-secondary btn-lg shadow">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Active Filter Badge --}}
                @if (request('filter_type'))
                    <div class="alert alert-info mt-3 mb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-info-circle"></i>
                                <strong>Active Filter:</strong>
                                @switch(request('filter_type'))
                                    @case('today')
                                        Today's Records
                                    @break

                                    @case('yesterday')
                                        Yesterday's Records
                                    @break

                                    @case('last_7_days')
                                        Last 7 Days
                                    @break

                                    @case('last_30_days')
                                        Last 30 Days
                                    @break

                                    @case('this_month')
                                        This Month
                                    @break

                                    @case('last_month')
                                        Last Month
                                    @break

                                    @case('this_year')
                                        This Year
                                    @break

                                    @case('custom')
                                        {{ request('start_date') }} to {{ request('end_date') }}
                                    @break
                                @endswitch
                            </div>
                            <span class="badge badge-primary badge-lg">
                                {{ $retailer_orders->count() }} records
                            </span>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>



    {{-- 2. TABLE CARD --}}
    <div class="card card-outline card-primary shadow">
        <div class="card-header no-print">
            <h3 class="card-title font-weight-bold">RETAILER TRANSACTIONS</h3>
            <div class="card-tools">
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#createOrderModal">
                    <i class="fas fa-plus"></i> CREATE RETAILER ORDER
                </button>
                <button class="btn btn-info btn-sm shadow-sm ml-2" onclick="printTable()">
                    <i class="fas fa-print"></i> PRINT ALL
                </button>
            </div>
        </div>
        <div class="card-body" id="printableTable">
            {{-- PRINT HEADER (Hidden on Screen, Visible on Print) --}}
            <div class="d-none d-print-block text-center mb-4">
                <h2 class="font-weight-bold mb-0">GYMNASTHENIQX WAREHOUSE</h2>
                <p class="text-uppercase mb-0">Retailer Transactions Report</p>
                <p class="small">Date Printed: <span id="print-date-table"></span></p>
                <hr class="border-dark">
            </div>

            <table class="table table-bordered table-hover" id="retailerTable">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Retailer Name</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Total Amount</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($retailer_orders as $order)
                        <tr>
                            <td>{{ $order->retailer_name }}</td>
                            <td>{{ $order->product_name }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td class="text-primary font-weight-bold">₱ {{ number_format($order->total_amount, 2) }}</td>
                            <td class="no-print">
                                @if ($order->status == 'Pending')
                                    {{-- PENDING: Admin can click to review --}}
                                    @if (Auth::user()->role && strtolower(Auth::user()->role->role_name) === 'admin')
                                        <span class="badge badge-warning view-pending-order"
                                            style="cursor: pointer; font-size: 0.9rem;" data-id="{{ $order->id }}"
                                            data-retailer="{{ $order->retailer_name }}"
                                            data-sku="{{ $order->sku ?? 'N/A' }}"
                                            data-product="{{ $order->product_name }}" data-qty="{{ $order->quantity }}"
                                            data-price="{{ number_format($order->unit_price, 2) }}"
                                            data-total="{{ number_format($order->total_amount, 2) }}">
                                            <i class="fas fa-hourglass-half"></i> PENDING (Click to Review)
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-hourglass-half"></i> Awaiting Admin
                                        </span>
                                    @endif
                                @elseif ($order->status == 'Approved')
                                    {{-- APPROVED: Show who approved + Ship button --}}
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                    @if ($order->approved_by)
                                        <br><small class="text-muted">by {{ $order->approved_by }}</small>
                                        <br><small
                                            class="text-muted">{{ $order->approved_at ? $order->approved_at->format('M d, Y h:i A') : '' }}</small>
                                    @endif

                                    {{-- ✅ SHIP ORDER BUTTON (Admin only) --}}
                                    @if (Auth::user()->role && strtolower(Auth::user()->role->role_name) === 'admin')
                                        <br>
                                        <button class="btn btn-sm btn-primary mt-2 ship-order-btn"
                                            data-order-id="{{ $order->id }}"
                                            data-retailer="{{ $order->retailer_name }}"
                                            data-qty="{{ $order->quantity }}">
                                            <i class="fas fa-shipping-fast"></i> Ship Order
                                        </button>
                                    @endif
                                @elseif ($order->status == 'Completed')
                                    {{-- COMPLETED: Show completion info --}}
                                    <span class="badge badge-dark">
                                        <i class="fas fa-check-double"></i> Completed
                                    </span>
                                    <br><small class="text-muted">Shipped & Sold</small>
                                @else
                                    {{-- REJECTED: Show who rejected --}}
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times-circle"></i> Rejected
                                    </span>
                                    @if ($order->rejected_by)
                                        <br><small class="text-muted">by {{ $order->rejected_by }}</small>
                                        <br><small
                                            class="text-muted">{{ $order->rejected_at ? $order->rejected_at->format('M d, Y h:i A') : '' }}</small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- PRINT FOOTER --}}
            <div class="d-none d-print-block mt-5">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-4">Prepared by:</p>
                        <div class="border-bottom border-dark w-75 mb-1"></div>
                        <p class="font-weight-bold text-uppercase">{{ Auth::user()->full_name ?? 'Warehouse Staff' }}</p>
                        <p class="small text-muted">{{ Auth::user()->role?->role_name ?? 'Staff' }}</p>
                    </div>
                    <div class="col-6 text-right">
                        <p class="mb-4">Verified by:</p>
                        <div class="border-bottom border-dark w-100 mb-1"></div>
                        <p class="small text-muted">(Authorized Signature)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. MODAL: PENDING ORDER REVIEW (Admin Only) --}}
    <div class="modal fade" id="pendingOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-clipboard-check"></i> REVIEW PENDING ORDER
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Review the order details carefully before approving or
                        rejecting.
                    </div>

                    {{-- Retailer Info --}}
                    <div class="mb-4">
                        <h5 class="text-primary font-weight-bold border-bottom pb-2">RETAILER INFORMATION</h5>
                        <p class="mb-1"><strong>Retailer Name:</strong> <span id="modal-retailer"
                                class="text-uppercase"></span></p>
                    </div>

                    {{-- Order Details --}}
                    <div class="mb-4">
                        <h5 class="text-primary font-weight-bold border-bottom pb-2">ORDER DETAILS</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">SKU</th>
                                <td id="modal-sku" class="font-weight-bold text-uppercase"></td>
                            </tr>
                            <tr>
                                <th>Product Name</th>
                                <td id="modal-product"></td>
                            </tr>
                            <tr>
                                <th>Quantity Ordered</th>
                                <td id="modal-qty" class="font-weight-bold text-primary"></td>
                            </tr>
                            <tr>
                                <th>Unit Price</th>
                                <td>₱ <span id="modal-price"></span></td>
                            </tr>
                            <tr class="bg-light">
                                <th>Total Amount</th>
                                <td class="font-weight-bold text-danger" style="font-size: 1.2rem;">
                                    ₱ <span id="modal-total"></span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <input type="hidden" id="modal-order-id">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-danger" id="btn-reject-order">
                        <i class="fas fa-ban"></i> REJECT ORDER
                    </button>
                    <button type="button" class="btn btn-success" id="btn-approve-order">
                        <i class="fas fa-check-circle"></i> APPROVE ORDER
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MODAL: CREATE ORDER --}}
    <div class="modal fade" id="createOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white font-weight-bold">
                    <h5 class="modal-title">CREATE RETAILERS ORDER FORM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="createOrderForm" action="{{ route('retailer.orders.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold text-uppercase">Retailer's Name</label>
                                <input type="text" name="retailer_name" class="form-control shadow-sm"
                                    placeholder="Enter Retailer's Full Name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Select Product</label>
                                <select name="product_id" id="sel_prod" class="form-control shadow-sm" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach ($warehouse_products as $p)
                                        @php
                                            $displaySku =
                                                $p->system_sku ?? ($p->supplier_sku ?? ($p->barcode ?? 'No SKU'));
                                        @endphp
                                        <option value="{{ $p->id }}" data-price="{{ $p->cost_price }}"
                                            data-sku="{{ $displaySku }}">
                                            {{ $p->name }} (SKU: {{ $displaySku }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-success">Auto-computed Unit Price</label>
                                <input type="number" step="0.01" name="unit_price" id="inp_price"
                                    class="form-control shadow-sm" required readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">Quantity</label>
                                <input type="number" name="quantity" id="inp_qty" class="form-control shadow-sm"
                                    min="1" placeholder="0" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-primary">Total Price</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold bg-primary text-white">₱</span>
                                    </div>
                                    <input type="text" id="disp_total" class="form-control font-weight-bold bg-light"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success shadow-sm font-weight-bold">Save Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden Forms for Approval/Rejection --}}
    <form id="approveForm" method="POST" style="display: none;">
        @csrf
    </form>
    <form id="rejectForm" method="POST" style="display: none;">
        @csrf
    </form>
@endsection

@push('css')
    <style>
        @media print {
            body * {
                visibility: hidden;
            }

            #printableTable,
            #printableTable * {
                visibility: visible;
            }

            #printableTable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print,
            .card-header,
            .btn,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                display: none !important;
            }

            .d-print-block {
                display: block !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 8px !important;
                visibility: visible !important;
            }

            thead {
                display: table-header-group !important;
                visibility: visible !important;
                background-color: #343a40 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead tr th {
                background-color: #343a40 !important;
                color: black !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                visibility: visible !important;
                display: table-cell !important;
                font-weight: bold !important;
            }

            /* Hide DataTable generated elements but keep our table */
            .dataTables_scrollHead {
                display: none !important;
            }
        }

        .view-pending-order:hover,
        .ship-order-btn:hover {
            opacity: 0.8;
            transform: scale(1.02);
            transition: all 0.2s;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .form-control-lg {
            height: 48px;
            font-size: 1rem;
        }

        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }

        .badge-lg {
            font-size: 1rem;
            padding: 8px 15px;
        }

        #customDateRange,
        #customDateRangeEnd {
            transition: all 0.3s ease-in-out;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function printTable() {
            let now = new Date();
            let dateString = now.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            let timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
            document.getElementById('print-date-table').textContent = dateString + ' | ' + timeString;

            // Destroy DataTable
            if ($.fn.DataTable.isDataTable('#retailerTable')) {
                $('#retailerTable').DataTable().destroy();
            }

            // Manually restore headers
            $('#retailerTable thead tr').html(`
                <th>Retailer Name</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Total Amount</th>
            `);

            window.print();

            // Re-initialize DataTable after print
            window.onafterprint = function() {
                $('#retailerTable thead tr').html(`
            <th>Retailer Name</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Total Amount</th>
            <th class="no-print">Action</th>
        `);
                $('#retailerTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "destroy": true,
                    "order": [
                        [0, "asc"]
                    ],
                    "pageLength": 10,
                    "language": {
                        "emptyTable": "No records found for the selected period"
                    },
                    "columnDefs": [{
                        "targets": -1,
                        "orderable": false,
                        "searchable": false
                    }]
                });
            };
        }

        $(document).ready(function() {

            // ✅ SWEETALERT MESSAGES
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#d33'
                });
            @endif

            @if (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Notice',
                    text: '{{ session('info') }}',
                    confirmButtonColor: '#3085d6'
                });
            @endif

            // ✅ INITIALIZE DATATABLE
            if ($.fn.DataTable.isDataTable('#retailerTable')) {
                $('#retailerTable').DataTable().destroy();
            }

            var table = $('#retailerTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "destroy": true,
                "order": [
                    [0, "asc"]
                ],
                "pageLength": 10,
                "language": {
                    "emptyTable": "No records found for the selected period"
                },
                // ✅ DISABLE SORTING ON ACTION COLUMN (Last column)
                "columnDefs": [{
                    "targets": -1, // Last column (Action)
                    "orderable": false, // Disable sorting
                    "searchable": false // Also disable search on this column
                }]
            });

            // ✅ AUTO-COMPUTE LOGIC
            function calculate() {
                let q = parseFloat($('#inp_qty').val()) || 0;
                let p = parseFloat($('#inp_price').val()) || 0;
                let total = q * p;

                $('#disp_total').val(total.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#sel_prod').on('change', function() {
                let price = $(this).find(':selected').data('price');
                $('#inp_price').val(price);
                calculate();
            });

            $('#inp_qty, #inp_price').on('input', calculate);

            // ✅ FORM SUBMISSION WITH SWEETALERT
            $('#createOrderForm').on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Submit Order?',
                    text: 'This order will be marked as Pending and require admin approval.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Submit!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });

            // ✅ CLICK PENDING BADGE TO OPEN MODAL
            $(document).on('click', '.view-pending-order', function() {
                let orderId = $(this).data('id');
                let retailer = $(this).data('retailer');
                let sku = $(this).data('sku');
                let product = $(this).data('product');
                let qty = $(this).data('qty');
                let price = $(this).data('price');
                let total = $(this).data('total');

                $('#modal-order-id').val(orderId);
                $('#modal-retailer').text(retailer);
                $('#modal-sku').text(sku);
                $('#modal-product').text(product);
                $('#modal-qty').text(qty);
                $('#modal-price').text(price);
                $('#modal-total').text(total);

                $('#pendingOrderModal').modal('show');
            });

            // ✅ APPROVE ORDER FROM MODAL
            $('#btn-approve-order').on('click', function() {
                let orderId = $('#modal-order-id').val();

                Swal.fire({
                    title: 'Approve Order?',
                    text: 'This will reserve stock for this retailer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#pendingOrderModal').modal('hide');
                        let form = $('#approveForm');
                        form.attr('action', '/retailer-orders/' + orderId + '/approve');
                        form.submit();
                    }
                });
            });

            // ✅ REJECT ORDER FROM MODAL
            $('#btn-reject-order').on('click', function() {
                let orderId = $('#modal-order-id').val();

                Swal.fire({
                    title: 'Reject Order?',
                    text: 'This order will be marked as rejected.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#pendingOrderModal').modal('hide');
                        let form = $('#rejectForm');
                        form.attr('action', '/retailer-orders/' + orderId + '/reject');
                        form.submit();
                    }
                });
            });

            // ✅ SHIP ORDER (Mark as Completed/Sold)
            $(document).on('click', '.ship-order-btn', function() {
                let orderId = $(this).data('order-id');
                let retailer = $(this).data('retailer');
                let qty = $(this).data('qty');

                Swal.fire({
                    title: 'Ship this order?',
                    html: `This will:<br>
                           • Mark <b>${qty} items</b> as <b class="text-success">SOLD</b><br>
                           • Update inventory counts<br>
                           • Complete transaction for <b>${retailer}</b>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-shipping-fast"></i> Yes, Ship It!',
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/retailer-orders/${orderId}/complete`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Order Shipped!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Shipping Failed',
                                    text: xhr.responseJSON?.message ||
                                        'Failed to ship order',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });


            // ============================================================
            // ✅ DATE FILTER HANDLERS
            // ============================================================

            // Show/hide custom date range inputs
            $('#filterType').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#customDateRange, #customDateRangeEnd').show();
                } else {
                    $('#customDateRange, #customDateRangeEnd').hide();
                }
            });

            // ============================================================
            // ✅ AUTO-RESET AT MIDNIGHT (12:00 AM)
            // ============================================================
            function checkMidnightReset() {
                const now = new Date();
                const hours = now.getHours();
                const minutes = now.getMinutes();

                // If it's 12:00 AM and filter is set to "today", auto-refresh
                if (hours === 0 && minutes === 0) {
                    const currentFilter = $('#filterType').val();
                    if (currentFilter === 'today') {
                        console.log('🌙 Midnight detected! Auto-refreshing for new day...');
                        location.reload();
                    }
                }
            }

            // Check every minute for midnight
            setInterval(checkMidnightReset, 60000);

            // ============================================================
            // ✅ SMART DATE RANGE VALIDATION
            // ============================================================
            $('#startDate, #endDate').on('change', function() {
                const startDate = new Date($('#startDate').val());
                const endDate = new Date($('#endDate').val());

                if (startDate && endDate && startDate > endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date Range',
                        text: 'Start date cannot be after end date!',
                        confirmButtonColor: '#d33'
                    });
                    $(this).val('');
                }
            });

            // ============================================================
            // ✅ KEYBOARD SHORTCUT: Press "F" to focus filter
            // ============================================================
            $(document).on('keypress', function(e) {
                if (e.key === 'f' || e.key === 'F') {
                    if (!$('input, textarea').is(':focus')) {
                        e.preventDefault();
                        $('#filterType').focus();
                    }
                }
            });

            // ============================================================
            // ✅ AUTO-SUBMIT ON PRESET SELECTION (Optional)
            // ============================================================
            $('#filterType').on('change', function() {
                const selectedValue = $(this).val();

                // Auto-submit for non-custom filters
                if (selectedValue !== 'custom' && selectedValue !== '') {
                    $('#dateFilterForm').submit();
                }
            });

        });
    </script>
@endpush
