@extends('layouts.adminlte')

@section('subtitle', 'Daily Report')
@section('content_header_title', 'Reports')
@section('content_header_subtitle', 'Daily Report')

@section('content_body')
    <div class="container-fluid">
        {{-- Statistics Cards --}}
        <div class="row no-print mb-3">
            <div class="col-lg-3 col-6" onclick="filterByStatus('low_stock')" style="cursor: pointer;">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $lowStockCount }}</h3>
                        <p>Low Stock Items</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('received')" style="cursor: pointer;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $newArrivals }}</h3>
                        <p>Daily Received</p>
                    </div>
                    <div class="icon"><i class="fas fa-download"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('outflow')" style="cursor: pointer;">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dailyOutflow }}</h3>
                        <p>Daily Outflow</p>
                    </div>
                    <div class="icon"><i class="fas fa-upload"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('damage')" style="cursor: pointer;">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $damagedCount ?? 0 }}</h3>
                        <p>Damaged/Return</p>
                    </div>
                    <div class="icon"><i class="fas fa-tools"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        {{-- ✅ NEW: CLEANER DATE FILTER (Same as Retailer Orders) --}}
        <div class="card shadow mb-4 no-print">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title font-weight-bold text-white">
                    <i class="fas fa-filter"></i> FILTER BY DATE
                </h3>
            </div>
            <div class="card-body">
                <form method="GET" id="dateFilterForm">
                    <div class="row">
                        {{-- Filter Dropdown --}}
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

                        {{-- Custom Date Input --}}
                        <div class="col-lg-4 col-md-6 mb-3" id="customDateDiv"
                            style="display:{{ ($filterType ?? '') == 'custom' ? 'block' : 'none' }};">
                            <label class="font-weight-bold">
                                <i class="fas fa-calendar-day text-success"></i> Select Date
                            </label>
                            <input type="date" name="custom_date" id="customDate"
                                class="form-control form-control-lg shadow-sm" value="{{ $date ?? '' }}">
                        </div>

                        {{-- Action Buttons --}}
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

                    {{-- Active Filter Badge --}}
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

        {{-- Print Template --}}
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
                    Report Date: {{ \Carbon\Carbon::parse($date)->format('F d, Y') }} |
                    Generated: {{ date('F d, Y h:i A') }}
                </p>
            </div>

            <table class="table table-bordered w-100" style="border: 2px solid black !important; font-size: 12px;">
                <thead style="background-color: #eee !important;">
                    <tr>
                        <th style="border: 1px solid black !important;">PRODUCT NAME</th>
                        <th style="border: 1px solid black !important;">CATEGORY</th>
                        <th style="border: 1px solid black !important;">SERIAL/TRACE</th>
                        <th style="border: 1px solid black !important; text-align: center;">QTY</th>
                    </tr>
                </thead>
                <tbody id="printTableBody"></tbody>
            </table>

            <div class="row mt-5">
                <div class="col-4 text-center">
                    <p class="mb-0"><strong>Prepared/Filed by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">{{ auth()->user()->name }}</p>
                    <p style="font-size: 10px;">(Employee Name & Signature)</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-0"><strong>Verified/Received by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">____________________</p>
                    <p style="font-size: 10px;">(Warehouse Staff On-Duty)</p>
                </div>
                <div class="col-4 text-center">
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
        let activeFilter = 'all';
        let currentData = [];
        let dataTable = null;

        // ✅ FILTER BY STATUS (from cards)
        function filterByStatus(status) {
            console.log('🔍 Filter:', status);
            activeFilter = status;
            updateFilterBadge(status);
            loadData();
        }

        // ✅ CLEAR FILTER
        function clearFilter() {
            console.log('🔄 Clearing filter');
            activeFilter = 'all';
            updateFilterBadge('all');
            loadData();
        }

        // ✅ UPDATE FILTER BADGE
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

        // ✅ LOAD DATA FROM SERVER
        function loadData() {
            const filterType = $('#filterType').val() || 'today';
            const customDate = $('#customDate').val();
            const type = activeFilter === 'all' ? null : activeFilter;

            console.log('📤 Loading:', {
                filterType,
                customDate,
                type
            });

            // Show loading
            $('#loadingSpinner').show();

            // Destroy existing DataTable if it exists
            if (dataTable) {
                dataTable.destroy();
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
                    console.log('📥 Received:', response.data?.length || 0, 'rows');
                    currentData = response.data || [];
                    renderTable(currentData);
                    $('#loadingSpinner').hide();

                    // Update record count
                    $('#recordCount').text(currentData.length + ' records');
                },
                error: function(xhr, status, error) {
                    console.error('❌ Error:', error);
                    $('#dailyReportTable tbody').html(
                        '<tr><td colspan="4" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error loading data. Please try again.</td></tr>'
                    );
                    $('#loadingSpinner').hide();
                }
            });
        }

        // ✅ RENDER TABLE WITH DATATABLES
        function renderTable(data) {
            let html = '';

            if (data && data.length > 0) {
                data.forEach(item => {
                    let productName = item.product_name || '';
                    let categoryName = item.category_name || '';
                    let traceability = item.traceability || '-';
                    let quantity = item.quantity || 0;

                    html += `<tr>
                        <td>${productName}</td>
                        <td>${categoryName}</td>
                        <td>${traceability}</td>
                        <td class="text-center font-weight-bold">${quantity}</td>
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

            // ✅ Initialize DataTable with sorting arrows
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

        // ✅ PRINT FUNCTION
        function handlePrint() {
            if (!currentData || currentData.length === 0) {
                alert('No data to print');
                return;
            }

            let html = '';
            currentData.forEach(item => {
                let cleanName = $('<div>').html(item.product_name).text();
                let cleanCat = $('<div>').html(item.category_name).text();
                let cleanTrace = $('<div>').html(item.traceability).text();

                html += `<tr>
                    <td style="border: 1px solid black; padding: 5px;">${cleanName}</td>
                    <td style="border: 1px solid black; padding: 5px;">${cleanCat}</td>
                    <td style="border: 1px solid black; padding: 5px;">${cleanTrace}</td>
                    <td style="border: 1px solid black; padding: 5px; text-align: center;">${item.quantity}</td>
                </tr>`;
            });

            $('#printTableBody').html(html || '<tr><td colspan="4" class="text-center">No data</td></tr>');
            window.print();
        }

        // ✅ INITIALIZE
        $(document).ready(function() {
            console.log('📊 Daily Reports Ready');

            // ✅ Show active filter badge on page load
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

            // Load initial data
            loadData();

            // ✅ Show/hide custom date input
            $('#filterType').on('change', function() {
                const selectedValue = $(this).val();

                if (selectedValue === 'custom') {
                    $('#customDateDiv').show();
                    $('#activeFilterBadge').hide();
                } else {
                    $('#customDateDiv').hide();

                    if (selectedValue && selectedValue !== 'all_time') {
                        $('#activeFilterBadge').show();

                        // Set filter label
                        const labels = {
                            'today': "Today's Records",
                            'yesterday': "Yesterday's Records",
                            'last_7_days': 'Last 7 Days',
                            'last_30_days': 'Last 30 Days',
                            'this_month': 'This Month',
                            'last_month': 'Last Month',
                            'this_year': 'This Year'
                        };
                        $('#filterLabel').text(labels[selectedValue] || selectedValue);
                    } else {
                        $('#activeFilterBadge').hide();
                    }
                }
            });

            // ✅ Apply Filter Button
            $('#applyFilterBtn').on('click', function() {
                console.log('📅 Applying filter');
                activeFilter = 'all';
                updateFilterBadge('all');
                loadData();
            });

            // ✅ Reset Filter Button
            $('#resetFilterBtn').on('click', function() {
                console.log('🔄 Resetting filter to today');
                $('#filterType').val('today');
                $('#customDate').val('');
                $('#customDateDiv').hide();
                $('#activeFilterBadge').show();
                $('#filterLabel').text("Today's Records");
                activeFilter = 'all';
                updateFilterBadge('all');
                loadData();
            });

            // ✅ Custom date change handler
            $('#customDate').on('change', function() {
                const selectedDate = $(this).val();
                console.log('📅 Custom date changed:', selectedDate);
                $('#activeFilterBadge').show();
                $('#filterLabel').text(selectedDate);
            });

            // ✅ Auto-submit on preset selection
            $('#filterType').on('change', function() {
                const selectedValue = $(this).val();

                // Auto-load for non-custom filters
                if (selectedValue !== 'custom') {
                    activeFilter = 'all';
                    updateFilterBadge('all');
                    loadData();
                }
            });

            // ============================================================
            // ✅ AUTO-RESET AT MIDNIGHT (12:00 AM)
            // ============================================================
            function checkMidnightReset() {
                const now = new Date();
                const hours = now.getHours();
                const minutes = now.getMinutes();
                const currentFilter = $('#filterType').val();

                // If it's midnight and filter is "today", auto-refresh
                if (hours === 0 && minutes === 0 && currentFilter === 'today') {
                    console.log('🌙 Midnight detected! Auto-refreshing for new day...');

                    // Reset to today's view
                    $('#filterType').val('today');
                    $('#activeFilterBadge').show();
                    $('#filterLabel').text("Today's Records");
                    activeFilter = 'all';
                    updateFilterBadge('all');

                    // Show notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'New Day!',
                            text: 'Daily report has been reset for the new day.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }

                    loadData();
                }
            }

            // Check every minute for midnight
            setInterval(checkMidnightReset, 60000);

            // Check immediately on page load if it's a new day
            checkMidnightReset();
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

        /* ✅ DataTables Sorting Arrow Styles */
        table.dataTable thead .sorting:before,
        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:before,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:before,
        table.dataTable thead .sorting_desc:after {
            opacity: 1 !important;
            color: #fff !important;
        }

        table.dataTable thead th {
            position: relative;
            cursor: pointer;
        }

        /* ✅ Filter Design (Same as Retailer Orders) */
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

        #customDateDiv {
            transition: all 0.3s ease-in-out;
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
