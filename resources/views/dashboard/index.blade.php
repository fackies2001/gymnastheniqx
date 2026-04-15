{{-- 📁 resources/views/dashboard/index.blade.php --}}
@extends('layouts.adminlte')

@section('subtitle', 'Dashboard')
@section('content_header_title', 'Dashboard')

@php
    $product_status_counts = $doughnut['product_status_counts'];
    $purchase_request_status_counts = $doughnut['purchase_request_status_counts'];
    $monthly_products_in = $bar['monthly_products_in'];
    $monthly_sales_income = $monthly_sales_income ?? ['year' => (int) date('Y'), 'amounts' => array_fill(0, 12, 0)];
    $low_stock_products = $low_stock_products ?? [];
    $recent_activities = $recent_activities ?? [];
    $retailer_orders = $retailer_orders ?? collect();
    $isManager = auth()->user()->isManager();
    $isAdmin = auth()->user()->isAdmin();
@endphp

@push('css')
    <style>
        .stat-box {
            border-radius: 10px;
            padding: 20px 18px 14px;
            color: #fff;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.18) !important;
        }

        .stat-box .stat-bg-icon {
            position: absolute;
            right: -10px;
            top: -10px;
            font-size: 5rem;
            opacity: 0.12;
        }

        .stat-box .stat-number {
            font-size: 2.4rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-box .stat-label {
            font-size: 0.85rem;
            opacity: 0.88;
            margin-top: 4px;
        }

        .stat-box .stat-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            margin-top: 14px;
            padding-top: 8px;
            font-size: 0.78rem;
            opacity: 0.9;
        }

        .stat-box .stat-footer a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .stat-box .stat-footer a:hover {
            text-decoration: underline;
        }

        .stat-box-suppliers {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-box-pr {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-box-po {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-box-stock {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-box-sales-today {
            background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
        }

        .stat-box-sales-total {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .activity-item--clickable {
            transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
        }

        .activity-item--clickable:hover {
            border-color: #667eea !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-1px);
            text-decoration: none;
        }

        .activity-item-link:focus {
            outline: 2px solid rgba(102, 126, 234, 0.5);
            outline-offset: 1px;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .report-shortcut-card {
            border-radius: 10px;
            padding: 18px 16px;
            color: #fff;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none !important;
        }

        .report-shortcut-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2) !important;
            color: #fff;
        }

        .report-shortcut-card .shortcut-bg-icon {
            position: absolute;
            right: -8px;
            top: -8px;
            font-size: 4.5rem;
            opacity: 0.12;
        }

        .report-shortcut-card .shortcut-label {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .report-shortcut-card .shortcut-sub {
            font-size: 0.75rem;
            opacity: 0.85;
            margin-top: 4px;
        }

        .report-shortcut-card .shortcut-arrow {
            font-size: 0.78rem;
            opacity: 0.9;
            font-weight: 600;
            margin-top: 10px;
        }

        .shortcut-yearly {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .activity-item {
            padding: 10px 12px;
            border-left: 3px solid #667eea;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .activity-item:hover {
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .activity-time {
            font-size: 0.72rem;
            opacity: 0.7;
            margin-top: 2px;
        }

        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .dark-mode-toggle i {
            font-size: 18px;
            color: white;
        }

        /* ========== FONT SIZE BOOST ========== */

        /* General content text */
        .content-wrapper {
            font-size: 16px;
        }

        /* Stat box numbers & labels */
        .stat-box .stat-number {
            font-size: 2.8rem;
        }
        .stat-box .stat-label {
            font-size: 1.05rem;
        }
        .stat-box .stat-footer {
            font-size: 0.9rem;
        }

        /* Card titles */
        .card-title {
            font-size: 1.15rem !important;
        }

        /* Filter labels & form elements */
        label,
        .font-weight-bold {
            font-size: 1rem;
        }
        .form-control,
        .form-control-lg {
            font-size: 1.05rem;
        }

        /* Buttons */
        .btn {
            font-size: 1rem;
        }

        /* Badges */
        .badge {
            font-size: 0.9rem;
        }

        /* Alerts */
        .alert {
            font-size: 1rem;
        }

        /* Low Stock table */
        .card-body .table th,
        .card-body .table td {
            font-size: 0.95rem;
        }

        /* Activity items */
        .activity-item .font-weight-bold,
        .activity-item-wrap .font-weight-bold {
            font-size: 0.95rem !important;
        }
        .activity-item-wrap div[style*="font-size:0.78rem"] {
            font-size: 0.9rem !important;
        }
        .activity-time {
            font-size: 0.82rem;
        }

        /* Report shortcut cards */
        .report-shortcut-card .shortcut-label {
            font-size: 1.1rem;
        }
        .report-shortcut-card .shortcut-sub {
            font-size: 0.85rem;
        }
        .report-shortcut-card .shortcut-arrow {
            font-size: 0.9rem;
        }

        /* Content header */
        .content-header h1,
        .content-header .content_header_title {
            font-size: 1.8rem !important;
        }

        /* ========== END FONT SIZE BOOST ========== */
    </style>
@endpush

@section('content_body')

    {{-- DARK MODE TOGGLE --}}
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon" id="darkModeIcon"></i>
    </button>

    {{-- DATE FILTER BAR --}}
    <div class="card shadow mb-3 no-print">
        <div class="card-header bg-gradient-primary">
            <h3 class="card-title font-weight-bold text-white">
                <i class="fas fa-filter"></i> FILTER BY DATE
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" id="dateFilterForm">
                <div class="row">
                    <div class="col-lg-5 col-md-6 mb-3">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar text-primary"></i> Select Time Period
                        </label>
                        <select name="filter_type" id="filterType" class="form-control form-control-lg shadow-sm">
                            <option value="">All Time Records</option>
                            <option value="today" {{ request('filter_type') == 'today' ? 'selected' : '' }}>Today
                            </option>
                            <option value="yesterday" {{ request('filter_type') == 'yesterday' ? 'selected' : '' }}>
                                Yesterday</option>
                            <option value="last_7_days" {{ request('filter_type') == 'last_7_days' ? 'selected' : '' }}>Last
                                7 Days</option>
                            <option value="last_30_days"{{ request('filter_type') == 'last_30_days' ? 'selected' : '' }}>
                                Last
                                30 Days</option>
                            <option value="this_month" {{ request('filter_type') == 'this_month' ? 'selected' : '' }}>This
                                Month</option>
                            <option value="last_month" {{ request('filter_type') == 'last_month' ? 'selected' : '' }}>Last
                                Month</option>
                            <option value="this_year" {{ request('filter_type') == 'this_year' ? 'selected' : '' }}>This
                                Year</option>
                            <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Custom
                                Range</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRange"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar-day text-success"></i> From
                        </label>
                        <input type="date" name="start_date" id="startDate"
                            class="form-control form-control-lg shadow-sm" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-lg-2 col-md-6 mb-3" id="customDateRangeEnd"
                        style="display: {{ request('filter_type') == 'custom' ? 'block' : 'none' }};">
                        <label class="font-weight-bold">
                            <i class="fas fa-calendar-check text-danger"></i> To
                        </label>
                        <input type="date" name="end_date" id="endDate" class="form-control form-control-lg shadow-sm"
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="d-block">&nbsp;</label>
                        <div class="btn-group btn-block">
                            <button type="submit" class="btn btn-primary btn-lg shadow">
                                <i class="fas fa-search"></i> Apply
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg shadow">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>

                @if (request('filter_type'))
                    <div class="alert alert-info mt-3 mb-0">
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
                @endif
            </form>
        </div>
    </div>

    {{-- ============================================
         STAT BOXES — ADMIN / STAFF
    ============================================ --}}
    @if (!$isManager)
        <div class="row mb-3">

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-suppliers shadow-sm">
                    <i class="fas fa-truck stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">{{ $small_boxes['supplier_counts'] }}</div>
                        <div class="stat-label">Suppliers</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-users mr-1"></i> All time</span>
                        <a href="{{ route('suppliers.index') }}">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-pr shadow-sm">
                    <i class="fas fa-file-alt stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">{{ $small_boxes['purchase_request_counts'] }}</div>
                        <div class="stat-label">Purchase Requests</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-filter mr-1"></i> Filtered period</span>
                        <a href="{{ route('pr.index') }}">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-po shadow-sm">
                    <i class="fas fa-shopping-cart stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">{{ $small_boxes['purchase_order_counts'] }}</div>
                        <div class="stat-label">Purchase Orders</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-filter mr-1"></i> Filtered period</span>
                        <a href="{{ route('purchase-order.index') }}">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-stock shadow-sm">
                    <i class="fas fa-boxes stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">{{ $small_boxes['serial_number_counts'] }}</div>
                        <div class="stat-label">Total Stock</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-boxes mr-1"></i> Available Units</span>
                        <a href="{{ route('consumables.index') }}">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

        </div>
    @endif
    @if ($isManager)
        <div class="row mb-3">

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-stock shadow-sm">
                    <i class="fas fa-boxes stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">{{ $small_boxes['serial_number_counts'] }}</div>
                        <div class="stat-label">Total Stock</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-boxes mr-1"></i> Available Units</span>
                        <a href="{{ route('consumables.index') }}">More info <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-sales-today shadow-sm">
                    <i class="fas fa-coins stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">₱{{ number_format($small_boxes['total_sales_today'], 2) }}</div>
                        <div class="stat-label">
                            {{ request('filter_type') ? 'Filtered Sales' : 'Sales Today' }}
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-calendar-day mr-1"></i>
                            {{ request('filter_type') ? ucfirst(str_replace('_', ' ', request('filter_type'))) : 'Today only' }}
                        </span>
                        <span>Completed orders</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="stat-box stat-box-sales-total shadow-sm">
                    <i class="fas fa-chart-line stat-bg-icon"></i>
                    <div>
                        <div class="stat-number">₱{{ number_format($small_boxes['total_sales_alltime'], 2) }}</div>
                        <div class="stat-label">Total Sales (All Time)</div>
                    </div>
                    <div class="stat-footer">
                        <span><i class="fas fa-infinity mr-1"></i> All time</span>
                        <a href="{{ route('retailer.orders.index') }}">View Orders <i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <a href="{{ route('reports.daily') }}" class="report-shortcut-card shortcut-yearly shadow-sm d-block">
                    <i class="fas fa-file-export shortcut-bg-icon"></i>
                    <div>
                        <div class="shortcut-label"><i class="fas fa-file-export mr-1"></i> View Reports</div>
                        <div class="shortcut-sub">Daily, Weekly, Monthly & Yearly reports available</div>
                    </div>
                    <div class="shortcut-arrow">Go to Reports <i class="fas fa-arrow-right"></i></div>
                </a>
            </div>

        </div>
    @endif

    {{-- ✅ ADDED: Phase 3 Stock Status Legend (Under Summary Boxes) --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="small font-weight-bold text-uppercase text-muted mr-3">
                            <i class="fas fa-info-circle mr-1"></i> Stock Health Guide:
                        </div>
                        <div class="d-flex flex-wrap">
                            <div class="mr-4 small">
                                <span class="badge badge-danger mr-1"><i class="fas fa-exclamation-circle"></i></span> 
                                <span class="font-weight-bold">Critical (0-5)</span>
                            </div>
                            <div class="mr-4 small">
                                <span class="badge bg-orange text-white mr-1"><i class="fas fa-exclamation-triangle"></i></span> 
                                <span class="font-weight-bold">Low (6-15)</span>
                            </div>
                            <div class="mr-4 small">
                                <span class="badge badge-warning mr-1"><i class="fas fa-clock"></i></span> 
                                <span class="font-weight-bold">Warning (16-25)</span>
                            </div>
                            <div class="small">
                                <span class="badge badge-success mr-1"><i class="fas fa-check-circle"></i></span> 
                                <span class="font-weight-bold">Healthy (>25)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================

    {{-- ============================================
         CHARTS + SIDEBAR — LAHAT NG ROLES
    ============================================ --}}
    <div class="row">

        {{-- LEFT: CHARTS --}}
        <div class="col-md-9 col-sm-12">
            <div class="row">

                @if (!$isManager)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-1 text-primary"></i> Purchase Request Status
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyProductReceived"
                                    style="min-height:250px; height:250px; max-height:250px; max-width:100%;"></canvas>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1 text-success"></i> Product Status
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyProductRelease"
                                style="min-height:250px; height:250px; max-height:250px; max-width:100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" id="dashboard-monthly-sales-card">
                    <div class="card border-left-success shadow-sm" style="border-left-width:4px;">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-coins mr-1 text-success"></i> Monthly Sales (Income) —
                                <small class="text-muted font-weight-normal">{{ $monthly_sales_income['year'] ?? date('Y') }}</small>
                            </h3>
                            <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group" aria-label="Sort sales chart">
                                <button type="button" class="btn btn-outline-secondary active" id="salesSortChrono"
                                    title="January → December">
                                    <i class="fas fa-calendar-alt mr-1"></i> By month
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="salesSortAmount"
                                    title="Highest sales first">
                                    <i class="fas fa-sort-amount-down mr-1"></i> By amount
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="MonthlySalesIncomeChart"
                                style="min-height:280px; height:280px; max-height:280px; max-width:100%;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1 text-info"></i> Monthly Products Scanned
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="MonthlyProductsScanned"
                                style="min-height:300px; height:300px; max-height:300px; max-width:100%;"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- RIGHT: LOW STOCK + RECENT ACTIVITY — LAHAT NG ROLES ✅ --}}
        <div class="col-md-3 col-sm-12">

            {{-- ✅ Low Stock Alert — LAHAT NG ROLES (admin, manager, staff) --}}
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock Alert
                    </h3>
                    @if (count($low_stock_products) > 0)
                        <span class="badge badge-danger float-right">{{ count($low_stock_products) }} items</span>
                    @endif
                </div>
                <div class="card-body p-0" style="max-height:280px; overflow-y:auto;">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th class="pl-3" style="width:60%">Product</th>
                                <th class="text-center" style="width:40%">
                                    Qty <small class="text-muted d-block"
                                        style="font-size:0.68rem; font-weight:normal;">(Below 20)</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($low_stock_products as $item)
                                <tr>
                                    <td class="pl-3 small align-middle" style="line-height:1.2;">
                                        <strong>{{ $item->name }}</strong><br>
                                        <small class="text-muted">{{ $item->system_sku ?? 'N/A' }}</small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge {{ $item->status_color === 'orange' ? 'bg-orange text-white' : 'badge-'.$item->status_color }}"
                                            style="font-size:0.9rem; padding:0.35rem 0.55rem;">
                                            <i class="fas fa-{{ $item->status_icon }}" style="font-size:0.7rem;"></i>
                                            {{ $item->available_count }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success mb-2"
                                            style="font-size:2rem; display:block;"></i>
                                        <div class="font-weight-bold">All stocks are good!</div>
                                        <small>No items below 20 units</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (count($low_stock_products) > 0)
                    <div class="card-footer text-center small text-muted py-1 bg-white border-top-0">
                        <small><i class="fas fa-info-circle mr-1"></i> Top 15 most urgent items shown</small>
                    </div>
                @endif
            </div>

            {{-- Recent Activity --}}
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-1"></i> Recent Activity
                    </h3>
                </div>
                <div class="card-body p-2" style="max-height:380px; overflow-y:auto;">
                    @forelse($recent_activities as $activity)
                        @if (auth()->user()->hasPrivilegedAccess() || auth()->user()->isViewOnlyStaff() || $activity->user_name === auth()->user()->full_name)
                            <div class="activity-item-wrap mb-2">
                                <a href="{{ $activity->url ?? '#' }}"
                                    class="activity-item-link d-block text-reset rounded border border-light bg-white p-2 {{ !empty($activity->url) ? 'activity-item--clickable' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div
                                            class="activity-icon bg-{{ $activity->type_color ?? 'primary' }} text-white mr-2 flex-shrink-0">
                                            <i class="fas fa-{{ $activity->icon ?? 'info' }}"></i>
                                        </div>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="font-weight-bold" style="font-size:0.83rem;">
                                                {{ $activity->user_name }}</div>
                                            <div style="font-size:0.78rem;">{{ $activity->description }}</div>
                                            <div class="activity-time text-muted">
                                                <i class="far fa-clock"></i> {{ $activity->time_ago ?? 'Just now' }}
                                                @if (!empty($activity->url))
                                                    <span class="float-right small text-primary"><i
                                                            class="fas fa-external-link-alt"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @if (($activity->kind ?? '') === 'retailer_order' && isset($activity->sales_month_index))
                                    <button type="button"
                                        class="btn btn-link btn-xs text-success p-0 mt-1 activity-jump-sales px-2"
                                        data-sales-month="{{ (int) $activity->sales_month_index }}"
                                        title="Show this month on the sales chart">
                                        <i class="fas fa-chart-line mr-1"></i>Income chart
                                    </button>
                                @endif
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox mb-2" style="font-size:2rem; display:block; opacity:0.4;"></i>
                            <div>No recent activities</div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

@stop

@push('js')
    <script>
        window.monthly_products_in = @json($monthly_products_in);
        window.monthly_sales_income = @json($monthly_sales_income);
        window.product_status_counts = @json($product_status_counts);
        window.purchase_request_status_counts = @json($purchase_request_status_counts);
        window.isManager = {{ $isManager ? 'true' : 'false' }};
    </script>

    @vite(['resources/js/dashboard.js'])

    <script>
        $(document).ready(function() {
            console.log('🚀 Dashboard Initialized');

            $('#filterType').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('#customDateRange, #customDateRangeEnd').slideDown();
                } else {
                    $('#customDateRange, #customDateRangeEnd').slideUp();
                    if ($(this).val() !== '') {
                        $('#dateFilterForm').submit();
                    }
                }
            });

            $('#startDate, #endDate').on('change', function() {
                const startDate = new Date($('#startDate').val());
                const endDate = new Date($('#endDate').val());
                if (startDate && endDate && startDate > endDate) {
                    alert('Start date cannot be after end date!');
                    $(this).val('');
                }
            });

            function checkMidnightReset() {
                const now = new Date();
                if (now.getHours() === 0 && now.getMinutes() === 0) {
                    if ($('#filterType').val() === 'today') {
                        console.log('🌙 Midnight detected! Auto-refreshing...');
                        location.reload();
                    }
                }
            }
            setInterval(checkMidnightReset, 60000);
        });
    </script>
@endpush
