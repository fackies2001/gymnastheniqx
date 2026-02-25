@extends('layouts.adminlte')

@section('content_header_title', 'Weekly Performance Report')
@section('content_header_subtitle')
    {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
@endsection

@section('content_body')
    <div class="container-fluid">

        {{-- BUTTONS (No Print) --}}
        <div class="row mb-3 no-print">
            <div class="col-12 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Weekly Report
                </button>
            </div>
        </div>

        {{-- FILTER (No Print) --}}
        <div class="card card-outline card-primary shadow-sm mb-4 no-print">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-filter mr-2"></i> Filter By Date
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.weekly') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="font-weight-bold">Select Time Period</label>
                            <select name="filter_type" id="filter_type" class="form-control">
                                <option value="last_7_days"
                                    {{ ($filterType ?? 'last_7_days') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days
                                </option>
                                <option value="this_week" {{ ($filterType ?? '') == 'this_week' ? 'selected' : '' }}>This
                                    Week</option>
                                <option value="last_14_days" {{ ($filterType ?? '') == 'last_14_days' ? 'selected' : '' }}>
                                    Last 14 Days</option>
                                <option value="this_month" {{ ($filterType ?? '') == 'this_month' ? 'selected' : '' }}>This
                                    Month</option>
                                <option value="custom" {{ ($filterType ?? '') == 'custom' ? 'selected' : '' }}>Custom Range
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 custom-range-field" style="display: none;">
                            <label class="font-weight-bold">Start Date</label>
                            <input type="date" name="custom_start" class="form-control"
                                value="{{ request('custom_start') }}">
                        </div>
                        <div class="col-md-3 custom-range-field" style="display: none;">
                            <label class="font-weight-bold">End Date</label>
                            <input type="date" name="custom_end" class="form-control"
                                value="{{ request('custom_end') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- PRINT HEADER --}}
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Weekly Inventory & Sales Performance Report</h4>
            <p class="mb-0"><strong>Period:</strong> {{ $startDate->format('F d, Y') }} -
                {{ $endDate->format('F d, Y') }}</p>
            <p class="small text-muted">Generated on: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
            <hr class="border-dark">
        </div>

        {{-- ROW 1: TOP 5 FAST MOVING --}}
        <div class="card card-primary card-outline shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-crown text-warning mr-2"></i> Top 5 Fast-Moving Products
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-bordered mb-0 text-center">
                    <thead class="bg-light text-uppercase">
                        <tr>
                            <th style="width: 10%;">Rank</th>
                            <th style="width: 40%;" class="text-left pl-4">Product Name</th>
                            <th style="width: 25%;">Items Sold</th>
                            <th style="width: 25%;">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $index => $item)
                            <tr>
                                <td class="align-middle">#{{ $index + 1 }}</td>
                                <td class="text-left pl-4 align-middle font-weight-bold">{{ $item->product_name }}</td>
                                <td class="align-middle text-success font-weight-bold h5">{{ $item->total_sold }}</td>
                                <td class="align-middle font-weight-bold">₱ {{ number_format($item->total_revenue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sales recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ROW 2: STOCK ANALYSIS --}}
        <div class="card card-info card-outline shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-chart-line mr-2"></i> Stock-to-Sales Analysis
                </h3>
                <div class="card-tools no-print">
                    <small class="text-muted">Auto-computed based on Realtime Data</small>
                </div>
            </div>
            <div class="card-body">
                <table id="ratioTable" class="table table-bordered table-hover text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-left pl-3">Product Details</th>
                            <th>Current Stock (Whse)</th>
                            <th>Sold (Selected Period)</th>
                            <th>Supply Ratio</th>
                            <th>System Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventoryAnalysis as $item)
                            <tr>
                                <td class="text-left pl-3 align-middle">
                                    <span class="font-weight-bold">{{ $item['name'] }}</span><br>
                                    <small class="text-muted">SKU: {{ $item['sku'] }}</small>
                                </td>
                                <td class="align-middle font-weight-bold h6">{{ $item['current_stock'] }}</td>
                                <td class="align-middle">{{ $item['weekly_sales'] }}</td>
                                <td class="align-middle">{{ $item['ratio'] }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $item['badge'] }} px-3 py-2 text-uppercase"
                                        style="font-size: 12px;">
                                        {{ $item['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ROW 3: AUDIT TOOL --}}
        <div class="card card-warning card-outline shadow-sm break-before" id="auditCard">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-clipboard-check mr-2"></i> Inventory Accuracy Audit
                </h3>
                {{-- ✅ Audit History Button --}}
                <div class="card-tools no-print">
                    <a href="{{ route('reports.audit.history') }}" class="btn btn-sm btn-info mr-2">
                        <i class="fas fa-history mr-1"></i> View Audit History
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center" id="auditTable">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-left pl-3">Product Name</th>
                            <th class="bg-secondary">System Count</th>
                            <th class="bg-white text-dark border-primary" style="width: 150px;">Actual Count</th>
                            <th>Variance</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventoryAnalysis as $item)
                            <tr data-product-name="{{ $item['name'] }}" data-product-sku="{{ $item['sku'] }}"
                                data-system-count="{{ $item['current_stock'] }}">
                                <td class="text-left pl-3 align-middle">{{ $item['name'] }}</td>
                                <td class="align-middle system-qty h6">{{ $item['current_stock'] }}</td>
                                <td class="align-middle p-1">
                                    <input type="number"
                                        class="form-control text-center font-weight-bold actual-input border-0 bg-light"
                                        placeholder="Enter Qty" min="0">
                                </td>
                                <td class="align-middle font-weight-bold variance-cell">-</td>
                                <td class="align-middle remarks-cell">
                                    <span class="badge badge-secondary">Pending</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- ✅ Save Audit Button --}}
                <div class="row mt-3 no-print">
                    <div class="col-12 text-right">
                        <small class="text-muted mr-3">
                            <i class="fas fa-info-circle"></i>
                            Only rows with entered actual count will be saved.
                        </small>
                        <button id="saveAuditBtn" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Save Audit Results
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRINT FOOTER --}}
        <div class="d-none d-print-block mt-5 pt-5">
            <div class="row text-center">
                <div class="col-4">
                    <div class="border-top border-dark mx-4 pt-2">
                        <p class="font-weight-bold mb-0">WAREHOUSE STAFF</p>
                        <small>Prepared By</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark mx-4 pt-2">
                        <p class="font-weight-bold mb-0">WAREHOUSE MANAGER</p>
                        <small>Verified By</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top border-dark mx-4 pt-2">
                        <p class="font-weight-bold mb-0">SYSTEM ADMIN</p>
                        <small>Noted By</small>
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
            .main-sidebar {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                background: white !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .table {
                width: 100% !important;
                background: white !important;
            }

            .break-before {
                page-break-before: always;
            }

            .actual-input {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                text-align: center;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {

            // ✅ Show/Hide custom date range fields
            $('#filter_type').on('change', function() {
                if ($(this).val() === 'custom') {
                    $('.custom-range-field').show();
                } else {
                    $('.custom-range-field').hide();
                }
            }).trigger('change');

            // ✅ DataTable
            $('#ratioTable').DataTable({
                "retrieve": true,
                "paging": false,
                "info": false,
                "searching": false,
                "order": [
                    [4, "desc"]
                ]
            });

            // ✅ Inventory Accuracy Audit — Real-time variance computation
            $('.actual-input').on('input', function() {
                let row = $(this).closest('tr');
                let systemCount = parseInt(row.find('.system-qty').text()) || 0;
                let actualCount = parseInt($(this).val());
                let varianceCell = row.find('.variance-cell');
                let remarksCell = row.find('.remarks-cell');

                if (isNaN(actualCount)) {
                    varianceCell.text('-');
                    remarksCell.html('<span class="badge badge-secondary">Pending</span>');
                    return;
                }

                let variance = actualCount - systemCount;
                varianceCell.text(variance > 0 ? '+' + variance : variance);

                if (variance === 0) {
                    varianceCell.removeClass('text-danger text-success').addClass('text-dark');
                    remarksCell.html('<span class="badge badge-success">Match</span>');
                } else if (variance < 0) {
                    varianceCell.removeClass('text-success text-dark').addClass('text-danger');
                    remarksCell.html('<span class="badge badge-danger">MISSING (' + Math.abs(variance) +
                        ')</span>');
                } else {
                    varianceCell.removeClass('text-danger text-dark').addClass('text-success');
                    remarksCell.html('<span class="badge badge-warning">SURPLUS (' + variance + ')</span>');
                }
            });

            // ✅ Save Audit Results
            $('#saveAuditBtn').on('click', function() {
                let auditItems = [];
                let auditPeriod = "{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}";

                $('#auditTable tbody tr').each(function() {
                    let actualInput = $(this).find('.actual-input').val();

                    // Only include rows where actual count was entered
                    if (actualInput !== '' && actualInput !== null) {
                        auditItems.push({
                            product_name: $(this).data('product-name'),
                            product_sku: $(this).data('product-sku'),
                            system_count: $(this).data('system-count'),
                            actual_count: parseInt(actualInput),
                        });
                    }
                });

                if (auditItems.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Data',
                        text: 'Please enter at least one actual count before saving.',
                        confirmButtonColor: '#f39c12'
                    });
                    return;
                }

                // Confirm before saving
                Swal.fire({
                    icon: 'question',
                    title: 'Save Audit Results?',
                    text: auditItems.length + ' item(s) will be recorded for period: ' +
                        auditPeriod,
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Save It!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('reports.audit.save') }}',
                            method: 'POST',
                            data: JSON.stringify({
                                audit_items: auditItems,
                                audit_period: auditPeriod,
                                _token: '{{ csrf_token() }}'
                            }),
                            contentType: 'application/json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Audit Saved!',
                                        text: response.message,
                                        confirmButtonColor: '#28a745'
                                    }).then(() => {
                                        // Clear inputs after save
                                        $('.actual-input').val('');
                                        $('.variance-cell').text('-');
                                        $('.remarks-cell').html(
                                            '<span class="badge badge-secondary">Pending</span>'
                                            );
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                let msg = xhr.responseJSON?.message ??
                                    'Something went wrong.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: msg
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush
