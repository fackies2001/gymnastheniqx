

<?php $__env->startSection('subtitle', 'Daily Report'); ?>
<?php $__env->startSection('content_header_title', 'Reports'); ?>
<?php $__env->startSection('content_header_subtitle', 'Daily Report'); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid">
        
        <div class="row no-print mb-3">
            <div class="col-lg-3 col-6" onclick="filterByStatus('low_stock')" style="cursor: pointer;">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php echo e($lowStockCount); ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('received')" style="cursor: pointer;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo e($newArrivals); ?></h3>
                        <p>Daily Received</p>
                    </div>
                    <div class="icon"><i class="fas fa-download"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('outflow')" style="cursor: pointer;">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?php echo e($dailyOutflow); ?></h3>
                        <p>Daily Outflow</p>
                    </div>
                    <div class="icon"><i class="fas fa-upload"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6" onclick="filterByStatus('damage')" style="cursor: pointer;">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?php echo e($damagedCount ?? 0); ?></h3>
                        <p>Damaged/Return</p>
                    </div>
                    <div class="icon"><i class="fas fa-tools"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center bg-white no-print">
                        <div class="card-title mb-0 text-uppercase font-weight-bold">
                            <i class="fas fa-clipboard-list mr-1"></i> Inventory Activity
                            <span id="filterBadge" class="badge badge-secondary ml-2" style="display:none;"></span>
                        </div>
                        <div class="ml-auto">
                            <div class="form-inline">
                                <button onclick="clearFilter()" class="btn btn-sm btn-outline-secondary mr-2"
                                    id="clearFilterBtn" style="display:none;">
                                    <i class="fas fa-times"></i> Clear Filter
                                </button>
                                <input type="date" id="reportDate" class="form-control form-control-sm mr-2"
                                    value="<?php echo e($date); ?>">
                                <button onclick="handlePrint()" class="btn btn-dark btn-sm shadow-sm">
                                    <i class="fas fa-print"></i> PRINT
                                </button>
                            </div>
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

        
        <div id="printArea" class="d-none d-print-block"
            style="font-family: 'Courier New', Courier, monospace; color: black; padding: 10px;">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold mb-0">GYMNASTHENIQX INVENTORY SYSTEM</h2>
                <p class="mb-0 text-uppercase">Warehouse: <?php echo e(auth()->user()->adminlte_warehouse() ?? 'Main Warehouse'); ?></p>
                <h4 class="mt-2 text-uppercase font-weight-bold"
                    style="border-bottom: 2px solid #000; display: inline-block; padding-bottom: 5px;">
                    DAILY OPERATIONAL & TRACEABILITY REPORT
                </h4>
                <p class="small mt-2">
                    Report Date: <?php echo e(\Carbon\Carbon::parse($date)->format('F d, Y')); ?> |
                    Generated: <?php echo e(date('F d, Y h:i A')); ?>

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
                    <p class="small text-uppercase mb-0"><?php echo e(auth()->user()->name); ?></p>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        let activeFilter = 'all';
        let currentData = [];

        // ✅ FILTER BY STATUS
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
            const clearBtn = $('#clearFilterBtn');

            if (status === 'all') {
                badge.hide();
                clearBtn.hide();
            } else {
                const labels = {
                    'low_stock': '⚠️ Low Stock',
                    'received': '📥 Received',
                    'outflow': '📤 Outflow',
                    'damage': '❌ Damaged'
                };
                badge.html(labels[status] || status).show();
                clearBtn.show();
            }
        }

        // ✅ LOAD DATA FROM SERVER
        function loadData() {
            const date = $('#reportDate').val();
            const type = activeFilter === 'all' ? null : activeFilter;

            console.log('📤 Loading:', {
                date,
                type
            });

            // Show loading
            $('#loadingSpinner').show();
            $('#dailyReportTable tbody').html(
                '<tr><td colspan="4" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><br><small>Loading...</small></td></tr>'
                );

            $.ajax({
                url: "<?php echo e(route('reports.daily.data')); ?>",
                type: 'GET',
                data: {
                    date,
                    type
                },
                success: function(response) {
                    console.log('📥 Received:', response.data?.length || 0, 'rows');
                    currentData = response.data || [];
                    renderTable(currentData);
                    $('#loadingSpinner').hide();
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

        // ✅ RENDER TABLE
        function renderTable(data) {
            let html = '';

            if (data && data.length > 0) {
                data.forEach(item => {
                    // Clean HTML for display
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

            // Load initial data
            loadData();

            // Date change handler
            $('#reportDate').on('change', function() {
                console.log('📅 Date changed:', $(this).val());
                activeFilter = 'all';
                updateFilterBadge('all');
                loadData();
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        .small-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        #dailyReportTable tbody tr:hover {
            background-color: #f8f9fa;
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/reports/daily.blade.php ENDPATH**/ ?>