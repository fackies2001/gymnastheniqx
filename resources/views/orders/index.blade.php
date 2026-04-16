{{-- 📁 resources/views/orders/index.blade.php --}}
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

    {{-- 2. DATE FILTER --}}
    <div class="card shadow mb-4 no-print">
        <div class="card-header bg-gradient-primary">
            <h3 class="card-title font-weight-bold text-white">
                <i class="fas fa-filter"></i> FILTER BY DATE
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('retailer.orders.index') }}" id="dateFilterForm">
                <div class="row">
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
                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRange"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold"><i class="fas fa-calendar-day text-success"></i> From</label>
                        <input type="date" name="start_date" id="startDate"
                            class="form-control form-control-lg shadow-sm" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRangeEnd"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold"><i class="fas fa-calendar-check text-danger"></i> To</label>
                        <input type="date" name="end_date" id="endDate" class="form-control form-control-lg shadow-sm"
                            value="{{ request('end_date') }}">
                    </div>
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

    {{-- 3. TABLE CARD --}}
    <div class="card card-outline card-primary shadow">
        <div class="card-header no-print">
            <h3 class="card-title font-weight-bold">RETAILER TRANSACTIONS</h3>
            <div class="card-tools">
                @php $roleNorm = auth()->user()->normalizedRoleName(); @endphp
                @if ($roleNorm !== 'account staff')
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#createOrderModal">
                        <i class="fas fa-plus"></i> CREATE RETAILER ORDER
                    </button>
                @endif
                <button class="btn btn-info btn-sm shadow-sm ml-2" onclick="handleOrderPrint()">
                    <i class="fas fa-print"></i> PRINT ALL
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover" id="retailerTable">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Order #</th>
                        <th>Retailer Name</th>
                        <th>Product</th>
                        <th>Condition</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($retailer_orders as $order)
                        <tr data-order-id="{{ $order->id }}">
                            <td class="font-weight-bold text-muted">
                                ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>{{ $order->retailer_name }}</td>
                            <td>{{ $order->product_name }}</td>
                            <td class="text-center">
                                @if ($order->product_condition === 'Defective')
                                    <span class="badge badge-danger px-2"><i class="fas fa-exclamation-triangle mr-1"></i>
                                        DEFECTIVE</span>
                                @else
                                    <span class="badge badge-secondary px-2">STANDARD</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $order->quantity }}</td>
                            <td>₱ {{ number_format($order->unit_price ?? 0, 2) }}</td>
                            <td class="text-primary font-weight-bold">₱ {{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @if ($order->status == 'Pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif ($order->status == 'Approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif ($order->status == 'Completed')
                                    <span class="badge badge-dark">Completed</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="no-print">
                                @if ($order->status == 'Pending')
                                    @if ($canManageRetailerOrders)
                                        <span class="badge badge-warning view-pending-order"
                                            style="cursor: pointer; font-size: 0.9rem;" data-id="{{ $order->id }}"
                                            data-retailer="{{ $order->retailer_name }}"
                                            data-sku="{{ $order->sku ?? 'N/A' }}"
                                            data-product="{{ $order->product_name }}" data-qty="{{ $order->quantity }}"
                                            data-price="{{ number_format($order->unit_price, 2) }}"
                                            data-total="{{ number_format($order->total_amount, 2) }}"
                                            data-total-raw="{{ $order->total_amount }}"
                                            data-cost="{{ $order->product->cost_price ?? 0 }}"
                                            data-condition="{{ $order->product_condition }}">
                                            <i class="fas fa-hourglass-half"></i> PENDING (Click to Review)
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-hourglass-half"></i> Awaiting Admin Approval
                                        </span>
                                        <br><small class="text-muted">By: {{ $order->created_by ?? 'You' }}</small>
                                    @endif
                                @elseif ($order->status == 'Approved')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                    @if ($order->approved_by)
                                        <br><small class="text-muted">by {{ $order->approved_by }}</small>
                                    @endif
                                    @if ($canManageRetailerOrders)
                                        <br>
                                        <button class="btn btn-sm btn-primary mt-1 ship-order-btn"
                                            data-order-id="{{ $order->id }}"
                                            data-retailer="{{ $order->retailer_name }}"
                                            data-qty="{{ $order->quantity }}">
                                            <i class="fas fa-shipping-fast"></i> Ship Order
                                        </button>
                                    @endif
                                @elseif ($order->status == 'Completed')
                                    <span class="badge badge-dark">
                                        <i class="fas fa-check-double"></i> Completed
                                    </span>
                                    <br><small class="text-muted">Shipped & Sold</small>
                                    @if ($order->shipped_at)
                                        <br><small class="text-info">
                                            <i class="fas fa-truck mr-1"></i>
                                            Shipped:
                                            {{ \Carbon\Carbon::parse($order->shipped_at)->format('M d, Y h:i A') }}
                                        </small>
                                    @endif
                                    @if ($order->received_at)
                                        <br><small class="text-success" style="font-weight: 500;">
                                            <i class="fas fa-hands-helping mr-1"></i>
                                            Received:
                                            {{ \Carbon\Carbon::parse($order->received_at)->format('M d, Y h:i A') }}
                                        </small>
                                    @else
                                        @if ($order->shipped_at && $canManageRetailerOrders)
                                            <br>
                                            <button class="btn btn-sm btn-outline-success mt-1 receive-order-btn"
                                                data-order-id="{{ $order->id }}"
                                                data-retailer="{{ $order->retailer_name }}">
                                                <i class="fas fa-check"></i> Mark Received
                                            </button>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ✅ PRINT AREA --}}
    <div id="printArea" style="display:none;">
        <div style="font-family: 'Courier New', monospace; padding: 20px; color: black;">
            <div style="text-align:center; margin-bottom:20px;">
                <h2 style="font-weight:bold; margin:0;">GYMNASTHENIQX INVENTORY SYSTEM</h2>
                <p style="margin:4px 0; text-transform:uppercase;">Warehouse:
                    {{ auth()->user()->adminlte_warehouse() ?? 'Main Warehouse' }}</p>
                <h4
                    style="border-bottom: 2px solid #000; display:inline-block; padding-bottom:5px; text-transform:uppercase; font-weight:bold;">
                    RETAILER ORDERS REPORT
                </h4>
                <p style="font-size:12px; margin-top:8px;">
                    Generated: {{ date('F d, Y h:i A') }}
                    @if (request('filter_type'))
                        | Filter:
                        @switch(request('filter_type'))
                            @case('today')
                                Today
                            @break

                            @case('yesterday')
                                Yesterday
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
                    @endif
                </p>
            </div>

            <table style="width:100%; border-collapse:collapse; font-size:11px; border: 2px solid black;">
                <thead>
                    <tr style="background:#eee;">
                        <th style="border:1px solid black; padding:6px;">ORDER #</th>
                        <th style="border:1px solid black; padding:6px;">RETAILER NAME</th>
                        <th style="border:1px solid black; padding:6px;">PRODUCT</th>
                        <th style="border:1px solid black; padding:6px; text-align:center;">QTY</th>
                        <th style="border:1px solid black; padding:6px; text-align:right;">UNIT PRICE</th>
                        <th style="border:1px solid black; padding:6px; text-align:right;">TOTAL AMOUNT</th>
                        <th style="border:1px solid black; padding:6px; text-align:center;">STATUS</th>
                    </tr>
                </thead>
                <tbody id="printOrdersBody"></tbody>
                <tfoot>
                    <tr style="background:#f0f0f0; font-weight:bold;">
                        <td colspan="5" style="border:1px solid black; padding:6px; text-align:right;">GRAND TOTAL:
                        </td>
                        <td style="border:1px solid black; padding:6px; text-align:right;" id="printGrandTotal">₱0.00</td>
                        <td style="border:1px solid black; padding:6px;"></td>
                    </tr>
                </tfoot>
            </table>

            {{-- ✅ SUMMARY BOX --}}
            <table style="width:100%; margin-top:15px; font-size:11px; border-collapse:collapse;">
                <tr>
                    <td style="width:33%; border:1px solid black; padding:8px; text-align:center;">
                        <strong>Total Orders</strong><br>
                        <span id="printTotalOrders" style="font-size:18px; font-weight:bold;">0</span>
                    </td>
                    <td style="width:33%; border:1px solid black; padding:8px; text-align:center;">
                        <strong>Total Items Sold</strong><br>
                        <span id="printTotalItems" style="font-size:18px; font-weight:bold;">0</span>
                    </td>
                    <td style="width:33%; border:1px solid black; padding:8px; text-align:center;">
                        <strong>Total Sales Amount</strong><br>
                        <span id="printTotalAmount" style="font-size:18px; font-weight:bold; color:#000;">₱0.00</span>
                    </td>
                </tr>
            </table>

            {{-- ✅ SIGNATURE --}}
            <div style="display:flex; margin-top:60px; text-align:center;">
                <div style="flex:1;">
                    <div style="border-top:1px solid black; width:80%; margin:40px auto 5px auto;"></div>
                    <strong>ADMIN</strong><br>
                    <small>Prepared By</small>
                </div>
                <div style="flex:1;">
                    <div style="border-top:1px solid black; width:80%; margin:40px auto 5px auto;"></div>
                    <strong>MANAGER</strong><br>
                    <small>Noted By</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- 4. MODAL: PENDING ORDER REVIEW                               --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="pendingOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-warning">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-clipboard-check"></i> REVIEW PENDING ORDER
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Review the order details carefully before approving or
                        rejecting.
                    </div>
                    <div class="mb-4">
                        <h5 class="text-primary font-weight-bold border-bottom pb-2">RETAILER INFORMATION</h5>
                        <p class="mb-1"><strong>Retailer Name:</strong> <span id="modal-retailer"
                                class="text-uppercase"></span></p>
                    </div>
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
                                <th>Subtotal (excl. VAT)</th>
                                <td class="font-weight-bold">₱ <span id="modal-subtotal-excl"></span></td>
                            </tr>
                            <tr>
                                <th>VAT (12%)</th>
                                <td>₱ <span id="modal-vat"></span></td>
                            </tr>
                            <tr class="bg-warning">
                                <th>Total (incl. VAT)</th>
                                <td class="font-weight-bold text-dark" style="font-size: 1.2rem;">₱ <span
                                        id="modal-total-incl"></span></td>
                            </tr>
                        </table>
                    </div>
                    <input type="hidden" id="modal-order-id">
                </div>
                <div class="modal-footer bg-light flex-column align-items-stretch">
                    <div id="below_cost_modal_warn" class="alert alert-danger mb-2" style="display:none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Cannot be approved </strong> The selling price is less than the cost to the supplier.
                        This order will result in a loss for the company.
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        @if ($canManageRetailerOrders)
                            <button type="button" class="btn btn-danger mr-2" id="btn-reject-order">
                                <i class="fas fa-ban"></i> REJECT ORDER
                            </button>
                            <button type="button" class="btn btn-success" id="btn-approve-order">
                                <i class="fas fa-check-circle"></i> APPROVE ORDER
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ✅ END OF pendingOrderModal --}}

    {{-- ============================================================ --}}
    {{-- 5. MODAL: CREATE ORDER                                       --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="createOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white font-weight-bold">
                    <h5 class="modal-title">CREATE RETAILERS ORDER FORM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
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
                                            $std = $p->is_consumable
                                                ? ($p->consumableStocks?->current_qty ?? 0)
                                                : ($p->available_quantity ?? 0);
                                            $def = $p->defective_quantity ?? 0;
                                        @endphp
                                        <option value="{{ $p->id }}" data-cost="{{ $p->cost_price ?? 0 }}"
                                            data-selling="{{ $p->selling_price ?? 0 }}"
                                            data-has-selling="{{ $p->selling_price ? '1' : '0' }}"
                                            data-sku="{{ $displaySku }}" data-name="{{ $p->name }}"
                                            data-std-qty="{{ $std }}" data-def-qty="{{ $def }}">
                                            {{ $p->name }} (Standard: {{ $std }}, Defective:
                                            {{ $def }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-danger">Item Condition</label>
                                <select name="product_condition" id="product_condition"
                                    class="form-control shadow-sm border-danger" required>
                                    <option value="Standard">Standard (New Stock)</option>
                                    <option value="Defective">Defective (Damaged Stock)</option>
                                </select>
                                <div class="mt-1 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Pull from damaged stock.</small>
                                    <span id="condition_stock_badge" class="badge badge-pill badge-info shadow-sm"
                                        style="display:none;">
                                        Stock: 0
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-success">
                                    <i class="fas fa-edit"></i> Selling Price
                                    <small class="text-muted font-weight-normal">(editable)</small>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold bg-success text-white">₱</span>
                                    </div>
                                    <input type="number" step="0.01" name="unit_price" id="inp_price"
                                        class="form-control shadow-sm border-success" required
                                        placeholder="Enter selling price">
                                </div>

                                <div id="price_info_box" class="mt-2 p-2 rounded"
                                    style="display:none; background:#f8f9fa; border:1px solid #dee2e6;">
                                    <small>
                                        <i class="fas fa-tag text-secondary mr-1"></i>
                                        <strong>Original Price:</strong>
                                        <span class="text-danger font-weight-bold" id="orig_cost_display">₱0.00</span>
                                        &nbsp;|&nbsp;
                                        <strong>Suggested:</strong>
                                        <span class="text-success font-weight-bold" id="suggested_price_display">Not
                                            set</span>
                                    </small>
                                </div>

                                <div id="below_cost_warn" class="mt-1" style="display:none;">
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Price is below supplier cost! You may be selling at a loss.
                                    </small>
                                </div>

                                <div id="markup_info" class="mt-1" style="display:none;">
                                    <small class="text-info">
                                        <i class="fas fa-chart-line"></i>
                                        Markup: ₱<span id="markup_amount">0.00</span>
                                        (<span id="markup_pct">0</span>%)
                                    </small>
                                </div>
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
    {{-- ✅ END OF createOrderModal --}}

    <form id="approveForm" method="POST" style="display: none;">@csrf</form>
    <form id="rejectForm" method="POST" style="display: none;">@csrf</form>

@endsection

@push('css')
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        #inp_price.border-success {
            border-color: #28a745 !important;
            border-width: 2px;
        }

        .view-pending-order:hover,
        .ship-order-btn:hover {
            opacity: 0.8;
            transform: scale(1.02);
            transition: all 0.2s;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ✅ Declared only ONCE — fixes the "already been declared" SyntaxError
        window.CAN_MANAGE_RETAILER_ORDERS = {{ $canManageRetailerOrders ? 'true' : 'false' }};

        // ✅ PRINT FUNCTION
        function handleOrderPrint() {
            const rows = document.querySelectorAll('#retailerTable tbody tr');
            if (!rows.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'No orders to print.'
                });
                return;
            }

            let html = '';
            let grandTotal = 0;
            let totalItems = 0;
            let totalOrders = 0;

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length < 7) return;

                const orderNo = cells[0].innerText.trim();
                const retailer = cells[1].innerText.trim();
                const product = cells[2].innerText.trim();
                const qty = cells[3].innerText.trim();
                const unitPrice = cells[4].innerText.trim();
                const total = cells[5].innerText.trim();
                const status = cells[6].innerText.trim();

                const totalNum = parseFloat(total.replace(/[₱,]/g, '')) || 0;
                grandTotal += totalNum;
                totalItems += parseInt(qty) || 0;
                totalOrders++;

                html += `<tr>
                    <td style="border:1px solid black;padding:5px;">${orderNo}</td>
                    <td style="border:1px solid black;padding:5px;">${retailer}</td>
                    <td style="border:1px solid black;padding:5px;">${product}</td>
                    <td style="border:1px solid black;padding:5px;text-align:center;">${qty}</td>
                    <td style="border:1px solid black;padding:5px;text-align:right;">${unitPrice}</td>
                    <td style="border:1px solid black;padding:5px;text-align:right;">${total}</td>
                    <td style="border:1px solid black;padding:5px;text-align:center;">${status}</td>
                </tr>`;
            });

            document.getElementById('printOrdersBody').innerHTML = html;
            document.getElementById('printGrandTotal').innerText = '₱' + grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
            document.getElementById('printTotalOrders').innerText = totalOrders;
            document.getElementById('printTotalItems').innerText = totalItems;
            document.getElementById('printTotalAmount').innerText = '₱' + grandTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });

            window.print();
        }

        $(document).ready(function() {

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

            if ($.fn.DataTable.isDataTable('#retailerTable')) $('#retailerTable').DataTable().destroy();
            $('#retailerTable').DataTable({
                responsive: true,
                autoWidth: false,
                destroy: true,
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                language: {
                    emptyTable: 'No records found'
                },
                columnDefs: [{
                    targets: -1,
                    orderable: false,
                    searchable: false
                }]
            });

            (function focusOrderFromQuery() {
                const params = new URLSearchParams(window.location.search);
                const oid = params.get('focus_order');
                if (!oid) return;
                setTimeout(() => {
                    const pending = $(`.view-pending-order[data-id="${oid}"]`);
                    if (pending.length) {
                        pending.trigger('click');
                    } else {
                        const row = $(`#retailerTable tbody tr[data-order-id="${oid}"]`);
                        if (row.length) {
                            row.addClass('table-info');
                            row[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                    params.delete('focus_order');
                    const q = params.toString();
                    window.history.replaceState({}, '', window.location.pathname + (q ? '?' + q : ''));
                }, 550);
            })();

            function calculate() {
                let q = parseFloat($('#inp_qty').val()) || 0;
                let p = parseFloat($('#inp_price').val()) || 0;
                $('#disp_total').val((q * p).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            function updateMarkup() {
                try {
                    var entered = parseFloat($('#inp_price').val()) || 0;
                    var cost = parseFloat($('#sel_prod').find(':selected').data('cost')) || 0;
                    var selling = parseFloat($('#sel_prod').find(':selected').data('selling')) || 0;
                    var condition = $('#product_condition').val() || 'Standard';
                    var baseline = selling > 0 ? selling : cost; // fallback to cost kung walang selling price

                    if (entered > 0 && baseline > 0) {
                        var diff = entered - baseline;
                        var pct = 0;
                        if (baseline > 0) {
                            pct = ((Math.abs(diff) / baseline) * 100).toFixed(1);
                        }

                        var htmlContent = '';
                        if (diff < 0) {
                            htmlContent = '<small class="text-danger font-weight-bold">' +
                                '<i class="fas fa-arrow-down mr-1"></i> Discount: ₱' + Math.abs(diff).toFixed(2) +
                                ' (' + pct + '%)' +
                                '</small>';
                        } else if (diff > 0) {
                            htmlContent = '<small class="text-success font-weight-bold">' +
                                '<i class="fas fa-arrow-up mr-1"></i> Markup: ₱' + diff.toFixed(2) + ' (' + pct +
                                '%)' +
                                '</small>';
                        }

                        if (htmlContent !== '') {
                            $('#markup_info').html(htmlContent).show();
                        } else {
                            $('#markup_info').hide();
                        }

                        // Below cost warning (Standard Items Only)
                        if (entered < cost && condition !== 'Defective') {
                            $('#below_cost_warn').show();
                        } else {
                            $('#below_cost_warn').hide();
                        }
                    } else {
                        $('#markup_info').hide();
                        $('#below_cost_warn').hide();
                    }
                } catch (e) {
                    console.error("Error in updateMarkup:", e);
                }

                // ✅ NEW: Update Condition Stock Badge
                try {
                    var selected = $('#sel_prod').find(':selected');
                    var condition = $('#product_condition').val();
                    var stdQty = parseInt(selected.data('std-qty')) || 0;
                    var defQty = parseInt(selected.data('def-qty')) || 0;

                    if (selected.val()) {
                        var finalQty = (condition === 'Defective') ? defQty : stdQty;
                        var colorClass = (finalQty > 0) ? 'badge-success' : 'badge-danger';

                        $('#condition_stock_badge')
                            .attr('class', 'badge badge-pill shadow-sm ' + colorClass)
                            .html('Available: ' + finalQty + ' units')
                            .show();
                    } else {
                        $('#condition_stock_badge').hide();
                    }
                } catch (err) {
                    console.error("Error in stock badge update:", err);
                }

                calculate();
            }

            $('#sel_prod').on('change', function() {
                let selected = $(this).find(':selected');
                let cost = parseFloat(selected.data('cost')) || 0;
                let selling = parseFloat(selected.data('selling')) || 0;

                $('#inp_price').val(selling > 0 ? selling.toFixed(2) : '');

                if (cost > 0 || selling > 0) {
                    $('#orig_cost_display').text('₱' + cost.toFixed(2));
                    $('#suggested_price_display').text(selling > 0 ? '₱' + selling.toFixed(2) : 'Not set');
                    $('#price_info_box').show();
                } else {
                    $('#price_info_box').hide();
                }

                updateMarkup();
            });

            $('#inp_price').on('input', updateMarkup);
            $('#product_condition').on('change', updateMarkup);
            $('#inp_qty').on('input', calculate);

            $('#createOrderForm').on('submit', function(e) {
                e.preventDefault();
                let enteredPrice = parseFloat($('#inp_price').val()) || 0;
                let costPrice = parseFloat($('#sel_prod').find(':selected').data('cost')) || 0;

                const proceed = () => {
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
                        if (result.isConfirmed) this.submit();
                    });
                };

                const condition = $('#product_condition').val();

                if (costPrice > 0 && enteredPrice < costPrice && condition !== 'Defective') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Price Below Cost!',
                        html: `The entered price <b>₱${enteredPrice.toFixed(2)}</b> is less than the supplier cost. <b>₱${costPrice.toFixed(2)}</b>.<br><br>You will lose money on this transaction. Will proceed nonetheless.?`,
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Will continue anyway',
                        cancelButtonText: 'Change the price'
                    }).then((result) => {
                        if (result.isConfirmed) proceed();
                    });
                } else {
                    proceed();
                }
            });

            $(document).on('click', '.view-pending-order', function() {
                $('#modal-order-id').val($(this).data('id'));
                $('#modal-retailer').text($(this).data('retailer'));
                $('#modal-sku').text($(this).data('sku'));
                $('#modal-product').text($(this).data('product'));
                $('#modal-qty').text($(this).data('qty'));
                $('#modal-price').text($(this).data('price'));

                // ✅ Below cost check (Allowed for Defective items)
                let unitPrice = parseFloat(String($(this).data('price')).replace(/,/g, '')) || 0;
                let costPrice = parseFloat($(this).data('cost') || 0);
                let condition = $(this).data('condition');

                if (costPrice > 0 && unitPrice < costPrice && condition !== 'Defective') {
                    $('#btn-approve-order')
                        .prop('disabled', true)
                        .removeClass('btn-success')
                        .addClass('btn-secondary')
                        .html('<i class="fas fa-ban"></i> Hindi Pwede (Below Cost)');
                    $('#below_cost_modal_warn').show();
                } else {
                    $('#btn-approve-order')
                        .prop('disabled', false)
                        .removeClass('btn-secondary')
                        .addClass('btn-success')
                        .html('<i class="fas fa-check-circle"></i> APPROVE ORDER');
                    $('#below_cost_modal_warn').hide();
                }

                let subExcl = parseFloat($(this).data('total-raw'));
                if (Number.isNaN(subExcl)) {
                    subExcl = parseFloat(String($(this).data('total')).replace(/,/g, '')) || 0;
                }
                const vat = subExcl * 0.12;
                const incl = subExcl + vat;
                const fmt = (n) => n.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                $('#modal-subtotal-excl').text(fmt(subExcl));
                $('#modal-vat').text(fmt(vat));
                $('#modal-total-incl').text(fmt(incl));
                $('#pendingOrderModal').modal('show');
            });

            $('#btn-approve-order').on('click', function() {
                let orderId = $('#modal-order-id').val();
                Swal.fire({
                    title: 'Approve Order?',
                    text: 'This will reserve stock for this retailer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#pendingOrderModal').modal('hide');
                        $('#approveForm').attr('action', '/retailer-orders/' + orderId + '/approve')
                            .submit();
                    }
                });
            });

            $('#btn-reject-order').on('click', function() {
                let orderId = $('#modal-order-id').val();
                Swal.fire({
                    title: 'Reject Order?',
                    text: 'This order will be marked as rejected.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#pendingOrderModal').modal('hide');
                        $('#rejectForm').attr('action', '/retailer-orders/' + orderId + '/reject')
                            .submit();
                    }
                });
            });

            $(document).on('click', '.ship-order-btn', function() {
                let orderId = $(this).data('order-id');
                let retailer = $(this).data('retailer');
                let qty = $(this).data('qty');
                Swal.fire({
                    title: 'Ship this order?',
                    html: `This will:<br>• Mark <b>${qty} items</b> as <b class="text-success">SOLD</b><br>• Complete transaction for <b>${retailer}</b>`,
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
                                    })
                                    .then(() => location.reload());
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

            $(document).on('click', '.receive-order-btn', function() {
                let orderId = $(this).data('order-id');
                let retailer = $(this).data('retailer');
                Swal.fire({
                    title: 'Mark as Received?',
                    html: `Confirm that <b>${retailer}</b> has successfully received this shipment.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Yes, Mark Received!',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/retailer-orders/${orderId}/received`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Received!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Action Failed',
                                    text: xhr.responseJSON?.message ||
                                        'Failed to update order status',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });

            $('#filterType').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#customDateRange, #customDateRangeEnd').show();
                } else {
                    $('#customDateRange, #customDateRangeEnd').hide();
                    if ($(this).val() !== '') $('#dateFilterForm').submit();
                }
            });

            $('#startDate, #endDate').on('change', function() {
                const s = new Date($('#startDate').val());
                const e = new Date($('#endDate').val());
                if (s && e && s > e) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Date Range',
                        text: 'Start date cannot be after end date!',
                        confirmButtonColor: '#d33'
                    });
                    $(this).val('');
                }
            });

        });
    </script>
@endpush
