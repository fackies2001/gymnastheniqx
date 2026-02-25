@extends('layouts.adminlte')

@section('content_header_title', 'Strategic Business Report')
@section('content_header_subtitle', 'Target Year: ' . $selectedYear)

@section('content_body')
    <div class="container-fluid">

        {{-- ACTIONS ROW (Filter & Print) --}}
        <div class="row mb-4 no-print align-items-center">
            <div class="col-md-6">
                <form action="{{ route('reports.strategic') }}" method="GET" class="form-inline">
                    <label class="mr-2 font-weight-bold">Select Fiscal Year:</label>
                    <select name="year" class="form-control mr-2 shadow-sm" onchange="this.form.submit()">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Annual Report
                </button>
            </div>
        </div>

        {{-- PRINT HEADER --}}
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Strategic Annual & Quarterly Report</h4>
            <p class="mb-0"><strong>Fiscal Year:</strong> {{ $selectedYear }}</p>
            <p class="small text-muted">Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
            <hr class="border-dark">
        </div>

        {{-- ROW 1: YEARLY SUMMARY CARDS --}}
        <div class="row">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Yearly Revenue</span>
                        <span class="info-box-number">₱ {{ number_format($totalYearlyRevenue, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Acquisition Cost</span>
                        <span class="info-box-number">₱ {{ number_format($totalYearlyCost, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-chart-pie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Gross Profit Margin</span>
                        <span class="info-box-number">
                            ₱ {{ number_format($totalYearlyRevenue - $totalYearlyCost, 2) }}
                        </span>
                        {{-- ✅ Disclaimer: shown only when margin is negative --}}
                        @if ($totalYearlyRevenue - $totalYearlyCost < 0)
                            <small class="text-white d-block mt-1" style="font-size: 11px; opacity: 0.85;">
                                <i class="fas fa-info-circle mr-1"></i>
                                Note: Acquisition cost reflects all-time purchases. Revenue reflects {{ $selectedYear }}
                                only.
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 2: CHART & QUARTERLY TABLE --}}
        <div class="row">
            {{-- SEASONAL CHART --}}
            <div class="col-md-8">
                <div class="card card-primary card-outline shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Monthly Performance Trend ({{ $selectedYear }})</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="strategyChart" style="height: 300px; max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            {{-- QUARTERLY BREAKDOWN TABLE --}}
            <div class="col-md-4">
                <div class="card card-secondary card-outline shadow-sm h-100 print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Quarterly Breakdown</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped text-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Revenue</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quarterlyData as $q => $data)
                                    <tr>
                                        <td class="font-weight-bold">Q{{ $q }}</td>
                                        <td class="text-success small-text">
                                            @if ($data['revenue'] > 0)
                                                ₱{{ number_format($data['revenue']) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-danger small-text">
                                            @if ($data['cost'] > 0)
                                                ₱{{ number_format($data['cost']) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{-- ✅ Note for empty quarters --}}
                        <div class="px-3 py-2">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Quarters with no activity are shown as —
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: PROJECTIONS (Next Year) & DEAD STOCK --}}
        <div class="row mt-4 break-before">
            {{-- PROJECTION --}}
            <div class="col-md-6">
                <div class="card card-outline card-warning h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-binoculars mr-2"></i> Forecast for {{ $selectedYear + 1 }}
                        </h3>
                        <div class="card-tools no-print"><small>Based on {{ $selectedYear }} Sales + 10%</small></div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="text-left pl-3">Item</th>
                                    <th>Sold ({{ $selectedYear }})</th>
                                    <th>Target ({{ $selectedYear + 1 }})</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectedStocks as $proj)
                                    <tr>
                                        <td class="text-left pl-3">{{ $proj['product'] }}</td>
                                        <td>{{ $proj['sold'] }}</td>
                                        <td class="font-weight-bold">{{ $proj['forecast'] }}</td>
                                        <td>
                                            @if ($proj['current'] >= $proj['forecast'])
                                                <span class="badge badge-success">OK</span>
                                            @else
                                                <span class="badge badge-warning">Restock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted">No sales data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DEAD STOCK --}}
            <div class="col-md-6">
                <div class="card card-outline card-danger h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Dead Stock (Current)
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm text-center">
                            <thead>
                                <tr>
                                    <th class="text-left pl-3">Item</th>
                                    <th>Qty</th>
                                    <th>Tied Capital</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deadStocks as $dead)
                                    <tr>
                                        <td class="text-left pl-3">{{ $dead['name'] }}</td>
                                        <td class="font-weight-bold text-danger">{{ $dead['stock'] }}</td>
                                        <td>₱{{ number_format($dead['value'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">No dead stocks found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRINT SIGNATORIES --}}
        <div class="d-none d-print-block mt-5 pt-5">
            <div class="row text-center">
                <div class="col-6">
                    <div class="border-top border-dark mx-5 pt-2">
                        <p class="font-weight-bold mb-0">OPERATIONS MANAGER</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="border-top border-dark mx-5 pt-2">
                        <p class="font-weight-bold mb-0">CHIEF EXECUTIVE OFFICER</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            var ctx = document.getElementById('strategyChart').getContext('2d');
            var strategyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($months),
                    datasets: [{
                            label: 'Revenue (Sales)',
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            data: @json($monthlyRevenue),
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Cost (Expenses)',
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            data: @json($monthlyCost),
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
@endpush

@push('css')
    <style>
        .small-text {
            font-size: 0.9rem;
        }

        @media print {

            .no-print,
            .main-footer,
            .navbar,
            .main-sidebar {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                background: white !important;
            }

            .print-card,
            .print-box {
                border: 1px solid #333 !important;
                box-shadow: none !important;
            }

            .card-header {
                background-color: #f4f4f4 !important;
                border-bottom: 1px solid #333 !important;
            }

            .info-box-icon {
                display: none !important;
            }

            .info-box-content {
                text-align: center;
            }

            .break-before {
                page-break-inside: avoid;
            }
        }
    </style>
@endpush
