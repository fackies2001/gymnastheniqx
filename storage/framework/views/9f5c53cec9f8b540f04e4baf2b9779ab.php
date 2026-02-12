

<?php $__env->startSection('content_header_title', 'Weekly Performance Report'); ?>
<?php $__env->startSection('content_header_subtitle'); ?>
    Last 7 Days (<?php echo e($startDate->format('M d, Y')); ?> - <?php echo e($endDate->format('M d, Y')); ?>)
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid">

        
        <div class="row mb-3 no-print">
            <div class="col-12 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Weekly Report
                </button>
            </div>
        </div>

        
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Weekly Inventory & Sales Performance Report</h4>
            <p class="mb-0"><strong>Period:</strong> <?php echo e($startDate->format('F d, Y')); ?> - <?php echo e($endDate->format('F d, Y')); ?>

            </p>
            <p class="small text-muted">Generated on: <?php echo e(\Carbon\Carbon::now()->format('F d, Y h:i A')); ?></p>
            <hr class="border-dark">
        </div>

        
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
                            <th style="width: 25%;">Items Sold (7 Days)</th>
                            <th style="width: 25%;">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="align-middle">#<?php echo e($index + 1); ?></td>
                                <td class="text-left pl-4 align-middle font-weight-bold"><?php echo e($item->product_name); ?></td>
                                <td class="align-middle text-success font-weight-bold h5"><?php echo e($item->total_sold); ?></td>
                                <td class="align-middle font-weight-bold">₱ <?php echo e(number_format($item->total_revenue, 2)); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sales recorded this week.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
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
                            <th>Sold (Last 7 Days)</th>
                            <th>Supply Ratio</th>
                            <th>System Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $inventoryAnalysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-left pl-3 align-middle">
                                    <span class="font-weight-bold"><?php echo e($item['name']); ?></span><br>
                                    <small class="text-muted">SKU: <?php echo e($item['sku']); ?></small>
                                </td>
                                <td class="align-middle font-weight-bold h6"><?php echo e($item['current_stock']); ?></td>
                                <td class="align-middle"><?php echo e($item['weekly_sales']); ?></td>
                                <td class="align-middle"><?php echo e($item['ratio']); ?></td>
                                <td class="align-middle">
                                    <span class="badge badge-<?php echo e($item['badge']); ?> px-3 py-2 text-uppercase"
                                        style="font-size: 12px;">
                                        <?php echo e($item['status']); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="card card-warning card-outline shadow-sm break-before">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-clipboard-check mr-2"></i> Inventory Accuracy Audit
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered text-center">
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $inventoryAnalysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="text-left pl-3 align-middle"><?php echo e($item['name']); ?></td>
                                <td class="align-middle system-qty h6"><?php echo e($item['current_stock']); ?></td>
                                <td class="align-middle p-1">
                                    <input type="number"
                                        class="form-control text-center font-weight-bold actual-input border-0 bg-light"
                                        placeholder="Enter Qty">
                                </td>
                                <td class="align-middle font-weight-bold variance-cell">-</td>
                                <td class="align-middle remarks-cell"><span class="badge badge-secondary">Pending</span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
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

            /* Page break after Top 5 & Stock Analysis to separate Audit Tool */
            .break-before {
                page-break-before: always;
            }

            /* Inputs become plain text on print */
            .actual-input {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                text-align: center;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(document).ready(function() {
            $('#ratioTable').DataTable({
                "retrieve": true,
                "paging": false,
                "info": false,
                "searching": false,
                "order": [
                    [4, "desc"]
                ]
            });

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
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/reports/weekly.blade.php ENDPATH**/ ?>