<?php $__env->startSection('subtitle', 'Dashboard'); ?>
<?php $__env->startSection('content_header_title', 'Dashboard'); ?>

<?php
    $product_status_counts = $doughnut['product_status_counts'];
    $purchase_request_status_counts = $doughnut['purchase_request_status_counts'];
    $monthly_products_in = $bar['monthly_products_in'];
    $low_stock_products = $low_stock_products ?? [];
    $recent_activities = $recent_activities ?? [];
?>

<?php $__env->startPush('css'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content_body'); ?>


    
    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark/Light Mode">
        <i class="fas fa-moon" id="darkModeIcon"></i>
    </button>

    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-0">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                        <span class="font-weight-bold">
                            <i class="fas fa-calendar-alt mr-1 text-primary"></i> Date Filter:
                        </span>
                        <div class="filter-btn-group btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-filter="today">
                                <i class="fas fa-calendar-day"></i> Today
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-filter="week">
                                <i class="fas fa-calendar-week"></i> This Week
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-filter="month">
                                <i class="fas fa-calendar-alt"></i> This Month
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-filter="custom">
                                <i class="fas fa-calendar"></i> Custom
                            </button>
                        </div>
                    </div>

                    
                    <div id="customDateRange" class="mt-2" style="display:none;">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="startDate">
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="endDate">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary w-100" id="applyCustomFilter">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row mb-3">

        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-suppliers shadow-sm">
                <i class="fas fa-truck stat-bg-icon"></i>
                <div>
                    <div class="stat-number"><?php echo e($small_boxes['supplier_counts']); ?></div>
                    <div class="stat-label">Suppliers</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-users mr-1"></i> All time</span>
                    <a href="<?php echo e(route('suppliers.index')); ?>">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-pr shadow-sm">
                <i class="fas fa-file-alt stat-bg-icon"></i>
                <div>
                    <div class="stat-number"><?php echo e($small_boxes['purchase_request_counts']); ?></div>
                    <div class="stat-label">Purchase Requests</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-filter mr-1"></i> Filtered period</span>
                    <a href="<?php echo e(route('pr.index')); ?>">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-po shadow-sm">
                <i class="fas fa-shopping-cart stat-bg-icon"></i>
                <div>
                    <div class="stat-number"><?php echo e($small_boxes['purchase_order_counts']); ?></div>
                    <div class="stat-label">Purchase Orders</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-filter mr-1"></i> Filtered period</span>
                    <a href="<?php echo e(route('purchase-order.index')); ?>">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="stat-box stat-box-stock shadow-sm">
                <i class="fas fa-boxes stat-bg-icon"></i>
                <div>
                    <div class="stat-number"><?php echo e($small_boxes['serial_number_counts']); ?></div>
                    <div class="stat-label">Serialized Products</div>
                </div>
                <div class="stat-footer">
                    <span><i class="fas fa-barcode mr-1"></i> Status: available</span>
                    <a href="<?php echo e(route('serialized_products.index')); ?>">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row">

        
        <div class="col-md-9 col-sm-12">
            <div class="row">

                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1 text-primary"></i>
                                Purchase Request Status
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

                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1 text-success"></i>
                                Product Status
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

                
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1 text-info"></i>
                                Monthly Products Scanned
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

        
        <div class="col-md-3 col-sm-12">

            
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock Alert
                    </h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($low_stock_products) > 0): ?>
                        <span class="badge badge-danger float-right"><?php echo e(count($low_stock_products)); ?> items</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $low_stock_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="pl-3 small align-middle" style="line-height:1.2;">
                                        <strong><?php echo e($item->name); ?></strong><br>
                                        <small class="text-muted"><?php echo e($item->system_sku ?? 'N/A'); ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php
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
                                        ?>
                                        <span class="badge <?php echo e($badgeClass); ?>"
                                            style="font-size:0.9rem; padding:0.35rem 0.55rem;">
                                            <i class="<?php echo e($icon); ?>" style="font-size:0.7rem;"></i>
                                            <?php echo e($qty); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">
                                        <i class="fas fa-check-circle text-success mb-2"
                                            style="font-size:2rem; display:block;"></i>
                                        <div class="font-weight-bold">All stocks are good!</div>
                                        <small>No items below 20 units</small>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($low_stock_products) > 0): ?>
                    <div class="card-footer text-center small text-muted py-1">
                        <div class="d-flex justify-content-around">
                            <span><i class="fas fa-circle text-danger" style="font-size:0.55rem;"></i> ≤5</span>
                            <span><i class="fas fa-circle text-warning" style="font-size:0.55rem;"></i> 6-10</span>
                            <span><i class="fas fa-circle text-info" style="font-size:0.55rem;"></i> 11-19</span>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-1"></i> Recent Activity
                    </h3>
                </div>
                <div class="card-body p-2" style="max-height:380px; overflow-y:auto;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recent_activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon bg-<?php echo e($activity->type_color ?? 'primary'); ?> text-white mr-2">
                                    <i class="fas fa-<?php echo e($activity->icon ?? 'info'); ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold" style="font-size:0.83rem;">
                                        <?php echo e($activity->user_name); ?>

                                    </div>
                                    <div style="font-size:0.78rem;"><?php echo e($activity->description); ?></div>
                                    <div class="activity-time">
                                        <i class="far fa-clock"></i> <?php echo e($activity->time_ago); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox mb-2" style="font-size:2rem; display:block; opacity:0.4;"></i>
                            <div>No recent activities</div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('show_pin_modal')): ?>
        <?php echo $__env->make('components.bootstrap.pincode', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/reports.js']); ?>
    
    <script>
        window.monthly_products_in = <?php echo json_encode($monthly_products_in, 15, 512) ?>;
        window.product_status_counts = <?php echo json_encode($product_status_counts, 15, 512) ?>;
        window.purchase_request_status_counts = <?php echo json_encode($purchase_request_status_counts, 15, 512) ?>;
    </script>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/dashboard.js']); ?>

    
    <script>
        $(document).ready(function() {
            console.log('🚀 Dashboard Initialized');

            <?php if(session('show_pin_modal')): ?>
                console.log('🔐 PIN Modal Required');
                console.log('   Mode:', '<?php echo e(session('pin_mode')); ?>');
                console.log('   Verified:', <?php echo e(session('pin_verified') ? 'true' : 'false'); ?>);

                // ✅ Show PIN Modal
                setTimeout(function() {
                    if ($('#pincodeModal').length) {
                        console.log('✅ PIN Modal Found - Showing...');

                        $('#pincodeModal').modal({
                            backdrop: 'static',
                            keyboard: false,
                            show: true
                        });

                        // ✅ Prevent modal from closing
                        $('#pincodeModal').on('hide.bs.modal', function(e) {
                            console.log('⚠️ Modal close blocked');
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        });

                        // ✅ Disable ESC key
                        $(document).on('keydown', function(e) {
                            if (e.key === 'Escape' || e.keyCode === 27) {
                                e.preventDefault();
                                return false;
                            }
                        });

                        // ✅ Auto-focus first PIN input
                        setTimeout(function() {
                            $('.pin-digit').first().focus();
                        }, 600);

                        // ✅ PIN digit auto-navigation
                        $(document).on('input', '.pin-digit', function() {
                            const val = $(this).val();
                            if (val.length === 1 && /\d/.test(val)) {
                                $(this).next('.pin-digit').focus();
                            }
                        });

                        // ✅ Backspace navigation
                        $(document).on('keydown', '.pin-digit', function(e) {
                            if (e.key === 'Backspace' && $(this).val() === '') {
                                $(this).prev('.pin-digit').focus();
                            }
                        });

                        console.log('✅ PIN Modal Fully Initialized');
                    } else {
                        console.error('❌ ERROR: #pincodeModal not found!');
                    }
                }, 400);
            <?php else: ?>
                console.log('ℹ️ No PIN verification required');
            <?php endif; ?>

            // ============================================
            // DATE FILTER FUNCTIONALITY
            // ============================================
            const urlParams = new URLSearchParams(window.location.search);
            const activeFilter = urlParams.get('filter') || 'today';

            // Set active filter button
            $('.filter-btn-group .btn').removeClass('active');
            $(`.filter-btn-group .btn[data-filter="${activeFilter}"]`).addClass('active');

            // Show custom date range if needed
            if (activeFilter === 'custom') {
                $('#customDateRange').show();
            }

            // Filter button click handler
            $('.filter-btn-group .btn').on('click', function() {
                $('.filter-btn-group .btn').removeClass('active');
                $(this).addClass('active');

                const filter = $(this).data('filter');

                if (filter === 'custom') {
                    $('#customDateRange').slideDown();
                } else {
                    $('#customDateRange').slideUp();
                    applyDateFilter(filter);
                }
            });

            // Apply custom date filter
            $('#applyCustomFilter').on('click', function() {
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();

                if (startDate && endDate) {
                    applyDateFilter('custom', startDate, endDate);
                } else {
                    alert('Please select both start and end dates');
                }
            });

            // Date filter application function
            function applyDateFilter(filter, startDate = null, endDate = null) {
                const params = new URLSearchParams({
                    filter
                });

                if (startDate) params.append('start_date', startDate);
                if (endDate) params.append('end_date', endDate);

                window.location.href = '<?php echo e(route('dashboard')); ?>?' + params.toString();
            }

            console.log('✅ Dashboard Ready');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/dashboard/index.blade.php ENDPATH**/ ?>