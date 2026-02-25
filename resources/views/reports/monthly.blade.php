@extends('layouts.adminlte')

@section('content_header_title', 'Monthly Financial & Planning Report')
@section('content_header_subtitle', 'Period: ' . $selectedDate->format('F Y'))

@section('content_body')
    <div class="container-fluid">

        {{-- PRINT BUTTON (No Print) --}}
        <div class="row mb-3 no-print">
            <div class="col-12 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Monthly Report
                </button>
            </div>
        </div>

        {{-- FILTER BY DATE (No Print) — same style as Daily Report --}}
        <div class="card card-primary shadow-sm mb-4 no-print">
            <div class="card-header" style="background-color: #1a73e8;">
                <h3 class="card-title font-weight-bold text-white">
                    <i class="fas fa-filter mr-2"></i> FILTER BY DATE
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url('/monthly-reports') }}">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i> Select Month
                            </label>
                            <select name="month" class="form-control">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $selectedDate->month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="font-weight-bold">Select Year</label>
                            <select name="year" class="form-control">
                                @foreach ($availableYears as $y)
                                    <option value="{{ $y }}" {{ $selectedDate->year == $y ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Apply
                            </button>
                            <a href="{{ url('/monthly-reports') }}" class="btn btn-secondary">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- PRINT HEADER (Hidden on Screen, Visible on Print) --}}
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Monthly Financial & Planning Report</h4>
            <p class="mb-0"><strong>Period:</strong> {{ $selectedDate->format('F Y') }}</p>
            <p class="small text-muted">Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
            <hr class="border-dark">
        </div>

        {{-- ROW 1: KEY FINANCIAL METRICS --}}
        <div class="row">
            {{-- 1. TOTAL INVENTORY VALUE --}}
            <div class="col-md-6 col-12">
                <div class="small-box bg-gradient-primary shadow-sm print-box">
                    <div class="inner">
                        <h3>₱ {{ number_format($totalInventoryValue, 2) }}</h3>
                        <p class="font-weight-bold text-uppercase">Total Inventory Asset Value</p>
                        <small class="d-block">Sum of (Stock × Cost Price)</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>

            {{-- 2. SALES GROWTH COMPARISON --}}
            <div class="col-md-6 col-12">
                <div
                    class="small-box bg-gradient-{{ $growthStatus == 'increase' ? 'success' : ($growthStatus == 'decrease' ? 'danger' : 'secondary') }} shadow-sm print-box">
                    <div class="inner">
                        <h3>
                            @if ($growthStatus == 'increase')
                                +
                            @endif
                            {{ number_format($growthPercentage, 1) }}%
                        </h3>
                        <p class="font-weight-bold text-uppercase">Monthly Sales Growth</p>
                        <small class="d-block">
                            Current: ₱{{ number_format($currentMonthSales, 2) }} vs
                            Last Month: ₱{{ number_format($lastMonthSales, 2) }}
                        </small>
                        {{-- ✅ FIX #2: Disclaimer when no previous month data --}}
                        @if ($lastMonthSales == 0 && $currentMonthSales > 0)
                            <small class="d-block mt-1" style="opacity: 0.85;">
                                <i class="fas fa-info-circle"></i>
                                No data available for the previous month. Growth rate may not reflect actual performance.
                            </small>
                        @endif
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: BEST SELLERS & SUPPLIER STATS --}}
        <div class="row">
            {{-- TOP 5 BEST SELLERS --}}
            <div class="col-md-6">
                <div class="card card-outline card-warning h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-trophy text-warning mr-2"></i> Top 5 Revenue Generators
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped text-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Rank</th>
                                    <th class="text-left">Product</th>
                                    <th>Qty Sold</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $index => $item)
                                    <tr>
                                        <td>#{{ $index + 1 }}</td>
                                        <td class="text-left font-weight-bold">{{ $item->product_name }}</td>
                                        <td>{{ $item->total_sold }}</td>
                                        <td class="text-success font-weight-bold">₱
                                            {{ number_format($item->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">No sales data for this month.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SUPPLIER PERFORMANCE --}}
            <div class="col-md-6">
                <div class="card card-outline card-info h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-truck text-info mr-2"></i> Top Suppliers (By Volume)
                            <small class="text-muted font-weight-normal ml-1">— {{ $selectedDate->format('F Y') }}</small>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover text-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-left">Supplier Name</th>
                                    <th>Total POs</th>
                                    <th>Total Amount Purchased</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierPerformance as $supplier)
                                    <tr>
                                        <td class="text-left font-weight-bold">
                                            {{ $supplier->supplier->name ?? 'Unknown Supplier' }}</td>
                                        <td><span class="badge badge-info text-lg px-3">{{ $supplier->total_pos }}</span>
                                        </td>
                                        <td>₱ {{ number_format($supplier->total_spent, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">No purchase orders recorded for this month.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- EXECUTIVE SUMMARY --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border print-card">
                    <div class="card-body bg-light">
                        <h5 class="font-weight-bold"><i class="fas fa-info-circle text-primary"></i> Executive Summary &
                            Analysis</h5>
                        <p class="text-muted mb-0" style="font-size: 14px;">
                            - <strong>Inventory Value:</strong> Currently at ₱{{ number_format($totalInventoryValue, 2) }}.
                            @if ($growthStatus == 'decrease' && $totalInventoryValue > 10000)
                                ⚠️ Overstocked alert! High inventory with declining sales. Consider reducing purchase
                                orders.
                            @elseif ($totalInventoryValue < 5000)
                                🔴 Low inventory! Consider restocking immediately.
                            @else
                                ✅ Inventory level is stable.
                            @endif
                            <br>
                            - <strong>Sales Growth:</strong>
                            @if ($lastMonthSales == 0 && $currentMonthSales > 0)
                                📊 No previous month data available for comparison. Current sales:
                                ₱{{ number_format($currentMonthSales, 2) }}.
                            @elseif ($growthStatus == 'increase' && $growthPercentage >= 50)
                                🚀 Excellent performance! Sales grew by {{ number_format($growthPercentage, 1) }}%.
                                Strategy is effective!
                            @elseif ($growthStatus == 'increase')
                                ✅ Positive growth of {{ number_format($growthPercentage, 1) }}%. Keep it up!
                            @elseif ($growthStatus == 'decrease' && abs($growthPercentage) >= 50)
                                🔴 Warning! Sales declined by {{ number_format(abs($growthPercentage), 1) }}%. Consider
                                promotions or discounts immediately.
                            @elseif ($growthStatus == 'decrease')
                                ⚠️ Sales declined by {{ number_format(abs($growthPercentage), 1) }}%. Review marketing or
                                pricing strategy.
                            @else
                                📊 Stable performance. No significant changes this month.
                            @endif
                            <br>
                            - <strong>Supplier Strategy:</strong>
                            @if ($supplierPerformance->count() > 0)
                                Focus negotiations with
                                <strong>{{ $supplierPerformance->first()->supplier->name ?? 'top supplier' }}</strong>
                                who has <strong>{{ $supplierPerformance->first()->total_pos }}</strong> POs worth
                                ₱{{ number_format($supplierPerformance->first()->total_spent, 2) }} to improve margins.
                            @else
                                No supplier activity recorded for {{ $selectedDate->format('F Y') }}.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRINT FOOTER: SIGNATORIES --}}
        <div class="d-none d-print-block mt-5 pt-5">
            <div class="row text-center">
                <div class="col-6">
                    <div class="border-top border-dark mx-5 pt-2">
                        <p class="font-weight-bold mb-0">FINANCE MANAGER</p>
                        <small>Analyzed & Prepared By</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark mx-5 pt-2">
                        <p class="font-weight-bold mb-0">GENERAL MANAGER</p>
                        <small>Approved By</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('css')
    <style>
        @media print {

            .no-print,
            .main-footer,
            .navbar,
            .main-sidebar,
            .card-header .card-tools {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                background: white !important;
                padding: 0 !important;
            }

            body {
                background: white !important;
                font-size: 12pt;
            }

            .col-md-6 {
                width: 50% !important;
                float: left !important;
                padding: 0 10px !important;
            }

            .print-box,
            .print-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }

            .small-box {
                color: black !important;
                border: 1px solid #000 !important;
            }

            .small-box .icon {
                display: none !important;
            }

            .table {
                width: 100% !important;
                background: white !important;
                border-collapse: collapse !important;
            }

            .table th,
            .table td {
                border: 1px solid #ddd !important;
                color: black !important;
            }

            .badge {
                border: 1px solid #000;
                color: black !important;
                background: transparent !important;
            }

            .text-success,
            .text-danger,
            .text-warning,
            .text-info {
                color: black !important;
            }
        }
    </style>
@endpush
