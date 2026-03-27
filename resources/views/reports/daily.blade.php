@extends('layouts.adminlte')

@section('subtitle', 'Daily Report')
@section('content_header_title', 'Reports')
@section('content_header_subtitle', 'Daily Report')

@section('content_body')
    <div class="container-fluid">
        {{-- Statistics Cards --}}
        {{-- Statistics Cards --}}
        <div class="row no-print mb-3">
            <div class="col-lg-3 col-6" onclick="filterByStatus('low_stock')" style="cursor: pointer;">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="cardLowStock">{{ $lowStockCount }}</h3>
                        <p>Low Stock Items</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('received')" style="cursor: pointer;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="cardReceived">{{ $newArrivals }}</h3>
                        <p>Daily Received</p>
                    </div>
                    <div class="icon"><i class="fas fa-download"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- ✅ FIX: id=cardOutflow, $dailyOutflow, label=Daily Outflow --}}
            <div class="col-lg-3 col-6" onclick="filterByStatus('outflow')" style="cursor: pointer;">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="cardOutflow">{{ $dailyOutflow }}</h3>
                        <p>Daily Outflow</p>
                    </div>
                    <div class="icon"><i class="fas fa-upload"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            {{-- ✅ FIX: id=cardDamaged --}}
            <div class="col-lg-3 col-6" onclick="filterByStatus('damage')" style="cursor: pointer;">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="cardDamaged">{{ $damagedCount ?? 0 }}</h3>
                        <p>Damaged/Lost</p>
                    </div>
                    <div class="icon"><i class="fas fa-tools"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- DATE FILTER --}}
        <div class="card shadow mb-4 no-print">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title font-weight-bold text-white">
                    <i class="fas fa-filter"></i> FILTER BY DATE
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" id="dateFilterForm">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 mb-3">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar text-primary"></i> Select Time Period
                            </label>
                            <select name="filter_type" id="filterType" class="form-control form-control-lg shadow-sm">
                                <option value="all_time" {{ ($filterType ?? 'today') == 'all_time' ? 'selected' : '' }}>All
                                    Time Records</option>
                                <option value="today" {{ ($filterType ?? 'today') == 'today' ? 'selected' : '' }}>Today
                                </option>
                                <option value="yesterday" {{ ($filterType ?? '') == 'yesterday' ? 'selected' : '' }}>
                                    Yesterday</option>
                                <option value="last_7_days" {{ ($filterType ?? '') == 'last_7_days' ? 'selected' : '' }}>
                                    Last 7 Days</option>
                                <option value="last_30_days" {{ ($filterType ?? '') == 'last_30_days' ? 'selected' : '' }}>
                                    Last 30 Days</option>
                                <option value="this_month" {{ ($filterType ?? '') == 'this_month' ? 'selected' : '' }}>This
                                    Month</option>
                                <option value="last_month" {{ ($filterType ?? '') == 'last_month' ? 'selected' : '' }}>Last
                                    Month</option>
                                <option value="this_year" {{ ($filterType ?? '') == 'this_year' ? 'selected' : '' }}>This
                                    Year</option>
                                <option value="custom" {{ ($filterType ?? '') == 'custom' ? 'selected' : '' }}>Custom Date
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-6 mb-3" id="customDateDiv"
                            style="display:{{ ($filterType ?? '') == 'custom' ? 'block' : 'none' }};">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar-day text-success"></i> Select Date
                            </label>
                            <input type="date" name="custom_date" id="customDate"
                                class="form-control form-control-lg shadow-sm" value="{{ $date ?? '' }}">
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="d-block">&nbsp;</label>
                            <div class="btn-group btn-block">
                                <button type="button" id="applyFilterBtn" class="btn btn-primary btn-lg shadow">
                                    <i class="fas fa-search"></i> Apply
                                </button>
                                <button type="button" id="resetFilterBtn" class="btn btn-secondary btn-lg shadow">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="activeFilterBadge" style="display:none;">
                        <div class="alert alert-info mt-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Active Filter:</strong>
                                    <span id="filterLabel"></span>
                                </div>
                                <span class="badge badge-primary badge-lg" id="recordCount">0 records</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center bg-white no-print">
                        <div class="card-title mb-0 text-uppercase font-weight-bold">
                            <i class="fas fa-clipboard-list mr-1"></i> Inventory Activity
                            <span id="filterBadge" class="badge badge-secondary ml-2" style="display:none;"></span>
                        </div>
                        <div class="ml-auto">
                            <button onclick="handlePrint()" class="btn btn-dark btn-sm shadow-sm">
                                <i class="fas fa-print"></i> PRINT
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="loadingSpinner" class="text-center py-5" style="display:none;">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                            <p class="mt-3">Loading data...</p>
                        </div>
                        <div class="table-responsive">
                            <table id="dailyReportTable" class="table table-bordered table-hover w-100">
                                <thead class="bg-dark text-white text-uppercase">
                                    <tr>
                                        <th width="35%">Product Name</th>
                                        <th width="20%">Category</th>
                                        <th width="30%">Traceability</th>
                                        <th width="15%" class="text-center">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-2" style="opacity:0.3;"></i>
                                            <div>Loading...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ PRINT AREA --}}
        <div id="printArea" class="d-none d-print-block"
            style="font-family: 'Courier New', Courier, monospace; color: black; padding: 10px;">

            <div class="text-center mb-4">
                <h2 class="font-weight-bold mb-0">GYMNASTHENIQX INVENTORY SYSTEM</h2>
                <p class="mb-0 text-uppercase">Warehouse: {{ auth()->user()->adminlte_warehouse() ?? 'Main Warehouse' }}
                </p>
                <h4 class="mt-2 text-uppercase font-weight-bold"
                    style="border-bottom: 2px solid #000; display: inline-block; padding-bottom: 5px;">
                    DAILY OPERATIONAL & TRACEABILITY REPORT
                </h4>
                <p class="small mt-2">
                    Report Date: {{ \Carbon\Carbon::parse($date ?? now())->format('F d, Y') }} |
                    Generated: {{ date('F d, Y h:i A') }}
                </p>
            </div>

            {{-- ✅ Dynamic table — columns change based on filter type --}}
            <table class="table table-bordered w-100" style="border: 2px solid black !important; font-size: 11px;">
                <thead style="background-color: #eee !important;">
                    <tr id="printTableHeader"></tr>
                </thead>
                <tbody id="printTableBody"></tbody>
            </table>

            {{-- ✅ SIGNATURE — Prepared/Filed by at Acknowledged by LANG --}}
            <div class="row mt-5">
                <div class="col-6 text-center">
                    <p class="mb-0"><strong>Prepared/Filed by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">{{ auth()->user()->name }}</p>
                    <p style="font-size: 10px;">(Employee Name & Signature)</p>
                </div>
                <div class="col-6 text-center">
                    <p class="mb-0"><strong>Acknowledged by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">____________________</p>
                    <p style="font-size: 10px;">(Warehouse Manager)</p>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var activeFilter = 'all';
        var currentData = [];
        var dataTable = null;

        function filterByStatus(status) {
            activeFilter = status;
            updateFilterBadge(status);
            loadData();
        }

        function clearFilter() {
            activeFilter = 'all';
            updateFilterBadge('all');
            loadData();
        }

        function updateFilterBadge(status) {
            const badge = $('#filterBadge');
            if (status === 'all') {
                badge.hide();
            } else {
                const labels = {
                    'low_stock': '⚠️ Low Stock',
                    'received': '📥 Received',
                    'outflow': '📤 Outflow',
                    'damage': '❌ Damaged'
                };
                badge.html(labels[status] || status).show();
            }
        }

        function loadData() {
            const filterType = $('#filterType').val() || 'today';
            const customDate = $('#customDate').val();
            const type = activeFilter === 'all' ? null : activeFilter;

            $('#loadingSpinner').show();

            if ($.fn.DataTable.isDataTable('#dailyReportTable')) {
                $('#dailyReportTable').DataTable().destroy();
                dataTable = null;
            }

            $('#dailyReportTable tbody').html(
                '<tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Loading...</small></td></tr>'
            );

            $.ajax({
                url: "{{ route('reports.daily.data') }}",
                type: 'GET',
                data: {
                    filter_type: filterType,
                    custom_date: customDate,
                    type: type
                },
                success: function(response) {
                    currentData = response.data || [];
                    renderTable(currentData);
                    $('#loadingSpinner').hide();
                    $('#recordCount').text(currentData.length + ' records');

                    // ✅ FIX: I-recount ang cards based sa CURRENT data
                    // Para in-sync ang cards sa table filter
                    let receivedCount = 0,
                        outflowCount = 0,
                        damagedCount = 0,
                        lowStockCount = 0;

                    currentData.forEach(function(item) {
                        const status = (item.status || '').toLowerCase();
                        const qty = parseInt(item.quantity) || 0; // ✅ kuhanin ang qty

                        if (status === 'received') receivedCount += qty;
                        else if (status === 'outflow') outflowCount += qty;
                        else if (status === 'damaged' || status === 'lost') damagedCount += qty;
                        else if (status === 'low stock') lowStockCount++;
                    });

                    // ✅ Only update cards if NOT filtering by specific type
                    // (para kung naka-click ka ng specific card, hindi mabago ang ibang cards)
                    if (activeFilter === 'all') {
                        $('#cardReceived').text(receivedCount);
                        $('#cardOutflow').text(outflowCount);
                        $('#cardDamaged').text(damagedCount);
                        $('#cardLowStock').text(lowStockCount);
                    }

                },
                error: function(xhr, status, error) {
                    $('#dailyReportTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error loading data.</td></tr>'
                    );
                    $('#loadingSpinner').hide();
                }
            });

        }

        function renderTable(data) {
            // ✅ Destroy muna LAGI bago mag-innerHTML
            if ($.fn.DataTable.isDataTable('#dailyReportTable')) {
                $('#dailyReportTable').DataTable().destroy();
                dataTable = null;
            }

            let html = '';

            if (data && data.length > 0) {
                data.forEach(item => {
                    html += `<tr>
                <td>${item.product_name || ''}</td>
                <td>${item.category_name || ''}</td>
                <td>${item.traceability || '-'}</td>
                <td class="text-center font-weight-bold">${item.quantity || 0}</td>
            </tr>`;
                });
            } else {
                html = `<tr>
            <td colspan="4" class="text-center text-muted py-5">
                <i class="fas fa-inbox fa-3x mb-3" style="opacity:0.3;"></i>
                <div class="font-weight-bold">No inventory activity found</div>
                <small>Try selecting a different filter or date</small>
            </td>
        </tr>`;
            }

            $('#dailyReportTable tbody').html(html);

            // ✅ Init PAGKATAPOS ng innerHTML, walang setTimeout
            if (data && data.length > 0) {
                dataTable = $('#dailyReportTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "destroy": true,
                    "order": [
                        [0, "asc"]
                    ],
                    "pageLength": 10,
                    "language": {
                        "emptyTable": "No records found for the selected period"
                    }
                });
            }
        }

        // ✅ UPDATED handlePrint() — dynamic columns per filter type
        function handlePrint() {
            if (!currentData || currentData.length === 0) {
                alert('No data to print');
                return;
            }

            const isLowStock = activeFilter === 'low_stock';
            const isDamage = activeFilter === 'damage';

            // ✅ Dynamic table headers
            let headerHtml = '';
            if (isLowStock) {
                // LOW STOCK — 3 columns lang
                headerHtml = `
                    <th style="border:1px solid black;padding:5px;">PRODUCT NAME</th>
                    <th style="border:1px solid black;padding:5px;">CATEGORY</th>
                    <th style="border:1px solid black;padding:5px;text-align:center;">QTY</th>
                `;
            } else if (isDamage) {
                // DAMAGE — 4 columns + sariling Serial Number column
                headerHtml = `
                    <th style="border:1px solid black;padding:5px;">PRODUCT NAME</th>
                    <th style="border:1px solid black;padding:5px;">CATEGORY</th>
                    <th style="border:1px solid black;padding:5px;">SERIAL NUMBER</th>
                    <th style="border:1px solid black;padding:5px;text-align:center;">QTY</th>
                `;
            } else {
                // RECEIVED / OUTFLOW — 4 columns with Traceability
                headerHtml = `
                    <th style="border:1px solid black;padding:5px;">PRODUCT NAME</th>
                    <th style="border:1px solid black;padding:5px;">CATEGORY</th>
                    <th style="border:1px solid black;padding:5px;">TRACEABILITY</th>
                    <th style="border:1px solid black;padding:5px;text-align:center;">QTY</th>
                `;
            }
            document.getElementById('printTableHeader').innerHTML = headerHtml;

            // ✅ Build rows
            let html = '';
            currentData.forEach(item => {
                let cleanName = $('<div>').html(item.product_name).text();
                let cleanCat = $('<div>').html(item.category_name).text();
                let cleanTrace = $('<div>').html(item.traceability).text();
                let cleanSN = item.serial_number ? item.serial_number : 'N/A';

                if (isLowStock) {
                    html += `<tr>
                        <td style="border:1px solid black;padding:5px;">${cleanName}</td>
                        <td style="border:1px solid black;padding:5px;">${cleanCat}</td>
                        <td style="border:1px solid black;padding:5px;text-align:center;">${item.quantity}</td>
                    </tr>`;
                } else if (isDamage) {
                    html += `<tr>
                        <td style="border:1px solid black;padding:5px;">${cleanName}</td>
                        <td style="border:1px solid black;padding:5px;">${cleanCat}</td>
                        <td style="border:1px solid black;padding:5px;">${cleanSN}</td>
                        <td style="border:1px solid black;padding:5px;text-align:center;">${item.quantity}</td>
                    </tr>`;
                } else {
                    html += `<tr>
                        <td style="border:1px solid black;padding:5px;">${cleanName}</td>
                        <td style="border:1px solid black;padding:5px;">${cleanCat}</td>
                        <td style="border:1px solid black;padding:5px;">${cleanTrace}</td>
                        <td style="border:1px solid black;padding:5px;text-align:center;">${item.quantity}</td>
                    </tr>`;
                }
            });

            document.getElementById('printTableBody').innerHTML =
                html || '<tr><td colspan="4" class="text-center">No data</td></tr>';

            window.print();
        }

        // ✅ INITIALIZE
        $(document).ready(function() {
            const initialFilter = $('#filterType').val();
            if (initialFilter && initialFilter !== 'all_time') {
                $('#activeFilterBadge').show();
                const labels = {
                    'today': "Today's Records",
                    'yesterday': "Yesterday's Records",
                    'last_7_days': 'Last 7 Days',
                    'last_30_days': 'Last 30 Days',
                    'this_month': 'This Month',
                    'last_month': 'Last Month',
                    'this_year': 'This Year'
                };
                $('#filterLabel').text(labels[initialFilter] || initialFilter);
            }

            loadData();

            $('#filterType').on('change', function() {
                const val = $(this).val();
                if (val === 'custom') {
                    $('#customDateDiv').show();
                    $('#activeFilterBadge').hide();
                } else {
                    $('#customDateDiv').hide();
                    if (val && val !== 'all_time') {
                        $('#activeFilterBadge').show();
                        const labels = {
                            'today': "Today's Records",
                            'yesterday': "Yesterday's Records",
                            'last_7_days': 'Last 7 Days',
                            'last_30_days': 'Last 30 Days',
                            'this_month': 'This Month',
                            'last_month': 'Last Month',
                            'this_year': 'This Year'
                        };
                        $('#filterLabel').text(labels[val] || val);
                    } else {
                        $('#activeFilterBadge').hide();
                    }
                    activeFilter = 'all';
                    updateFilterBadge('all');
                    loadData();
                }
            });

            $('#applyFilterBtn').on('click', function() {
                activeFilter = 'all';
                updateFilterBadge('all');
                loadData();
            });

            $('#resetFilterBtn').on('click', function() {
                $('#filterType').val('today');
                $('#customDate').val('');
                $('#customDateDiv').hide();
                $('#activeFilterBadge').show();
                $('#filterLabel').text("Today's Records");
                activeFilter = 'all';
                updateFilterBadge('all');
                loadData();
            });

            $('#customDate').on('change', function() {
                $('#activeFilterBadge').show();
                $('#filterLabel').text($(this).val());
            });

            // Auto-reset at midnight
            function checkMidnightReset() {
                const now = new Date();
                const hours = now.getHours();
                const minutes = now.getMinutes();
                if (hours === 0 && minutes === 0 && $('#filterType').val() === 'today') {
                    activeFilter = 'all';
                    updateFilterBadge('all');
                    loadData();
                }
            }
            setInterval(checkMidnightReset, 60000);
        });
    </script>
@endpush

@push('css')
    <style>
        .small-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        #dailyReportTable tbody tr:hover {
            background-color: #f8f9fa;
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

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
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
