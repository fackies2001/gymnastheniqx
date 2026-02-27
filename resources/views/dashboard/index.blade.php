{{-- 📁 resources/views/dashboard/index.blade.php --}}
@extends('layouts.adminlte')

@section('subtitle', 'Dashboard')
@section('content_header_title', 'Dashboard')

@php
    $product_status_counts = $doughnut['product_status_counts'];
    $purchase_request_status_counts = $doughnut['purchase_request_status_counts'];
    $monthly_products_in = $bar['monthly_products_in'];
    $low_stock_products = $low_stock_products ?? [];
    $recent_activities = $recent_activities ?? [];
@endphp

@push('css')
    <style>
        /* ============================================
               SMALL STAT BOXES
            ============================================ */
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

        /* ============================================
               ACTIVITY FEED
            ============================================ */
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

        /* ============================================
               FILTER BUTTONS
            ============================================ */
        .filter-btn-group .btn {
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.85rem;
        }

        .filter-btn-group .btn.active {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }

        /* ============================================
               DARK MODE TOGGLE
            ============================================ */
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
            transition: transform 0.3s ease;
        }
    </style>
@endpush

@section('content_body')

    {{-- ============================================
         DARK MODE TOGGLE
    ============================================ --}}
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon" id="darkModeIcon"></i>
    </button>

    {{-- ============================================
         DATE FILTER BAR
    ============================================ --}}
    <div class="card shadow mb-4 no-print">
        <div class="card-header bg-gradient-primary">
            <h3 class="card-title font-weight-bold text-white">
                <i class="fas fa-filter"></i> FILTER BY DATE
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" id="dateFilterForm">
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
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg shadow">
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
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- ============================================
         SMALL STAT BOXES
    ============================================ --}}
    <div class="row mb-3">

        {{-- Suppliers --}}
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

        {{-- Purchase Requests --}}
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

        {{-- Purchase Orders --}}
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-po shadow-sm">
                <i class="fas fa-shopping-cart stat-bg-icon"></i>
                <div>
                    <div class="stat-number">{{ $small_boxes['purchase_order_counts'] }}</div>
                    <div class="stat-label">Purchase Orders</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-filter mr-1"></i> Filtered period</span>
                    <a href="{{ route('purchase-order.index') }}">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- Available Stock --}}
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-stock shadow-sm">
                <i class="fas fa-boxes stat-bg-icon"></i>
                <div>
                    <div class="stat-number">{{ $small_boxes['serial_number_counts'] }}</div>
                    <div class="stat-label">Serialized Products</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-barcode mr-1"></i> Status: available</span>
                    <a href="{{ route('serialized_products.index') }}">More info <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

    </div>

    {{-- ============================================
         CHARTS + SIDEBAR
    ============================================ --}}
    <div class="row">

        {{-- LEFT: CHARTS --}}
        <div class="col-md-9 col-sm-12">
            <div class="row">

                {{-- Doughnut: Purchase Request Status --}}
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

                {{-- Doughnut: Product Status --}}
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

                {{-- Bar: Monthly Products Scanned --}}
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

        {{-- RIGHT: LOW STOCK + RECENT ACTIVITY --}}
        <div class="col-md-3 col-sm-12">

            {{-- Low Stock Alert --}}
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
                                        @php
                                            $qty = $item->available_count ?? 0;
                                            $badgeClass =
                                                $qty <= 5
                                                    ? 'badge-danger'
                                                    : ($qty <= 10
                                                        ? 'badge-warning'
                                                        : 'badge-info');
                                            $icon =
                                                $qty <= 5
                                                    ? 'fas fa-exclamation-circle'
                                                    : ($qty <= 10
                                                        ? 'fas fa-exclamation-triangle'
                                                        : 'fas fa-info-circle');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}"
                                            style="font-size:0.9rem; padding:0.35rem 0.55rem;">
                                            <i class="{{ $icon }}" style="font-size:0.7rem;"></i>
                                            {{ $qty }}
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
                    <div class="card-footer text-center small text-muted py-1">
                        <div class="d-flex justify-content-around">
                            <span><i class="fas fa-circle text-danger" style="font-size:0.55rem;"></i> ≤5</span>
                            <span><i class="fas fa-circle text-warning" style="font-size:0.55rem;"></i> 6-10</span>
                            <span><i class="fas fa-circle text-info" style="font-size:0.55rem;"></i> 11-19</span>
                        </div>
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
                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon bg-{{ $activity->type_color ?? 'primary' }} text-white mr-2">
                                    <i class="fas fa-{{ $activity->icon ?? 'info' }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold" style="font-size:0.83rem;">{{ $activity->user_name }}
                                    </div>
                                    <div style="font-size:0.78rem;">{{ $activity->description }}</div>
                                    <div class="activity-time">
                                        <i class="far fa-clock"></i> {{ $activity->time_ago ?? 'Just now' }}
                                    </div>
                                </div>
                            </div>
                        </div>
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

    {{--
        ❌ TINANGGAL: @include('components.bootstrap.pincode')
        ✅ DAHIL: Ang PIN modal ay nasa layouts/adminlte.blade.php na — hindi na kailangan dito
    --}}

@stop

@push('js')
    {{-- ============================================
         GLOBAL DATA FOR CHARTS
    ============================================ --}}
    <script>
        window.monthly_products_in = @json($monthly_products_in);
        window.product_status_counts = @json($product_status_counts);
        window.purchase_request_status_counts = @json($purchase_request_status_counts);
    </script>

    {{-- ============================================
         LOAD DASHBOARD SCRIPTS
    ============================================ --}}
    @vite(['resources/js/dashboard.js'])

    {{-- ============================================
         DATE FILTER FUNCTIONALITY ONLY
         ❌ TINANGGAL: PIN modal JS — nasa layouts/adminlte.blade.php na
    ============================================ --}}
    <script>
        $(document).ready(function() {
            console.log('🚀 Dashboard Initialized');

            // ============================================
            // DATE FILTER FUNCTIONALITY
            // ============================================
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

            // Date range validation
            $('#startDate, #endDate').on('change', function() {
                const startDate = new Date($('#startDate').val());
                const endDate = new Date($('#endDate').val());
                if (startDate && endDate && startDate > endDate) {
                    alert('Start date cannot be after end date!');
                    $(this).val('');
                }
            });

            // Auto-reset at midnight
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
