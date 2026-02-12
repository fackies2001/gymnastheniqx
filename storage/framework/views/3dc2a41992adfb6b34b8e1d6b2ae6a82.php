

<?php $__env->startSection('content_header_title', 'Monthly Financial & Planning Report'); ?>
<?php $__env->startSection('content_header_subtitle', 'Period: ' . $now->format('F Y')); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid">

        
        <div class="row mb-3 no-print">
            <div class="col-12 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Monthly Report
                </button>
            </div>
        </div>

        
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Monthly Financial & Planning Report</h4>
            <p class="mb-0"><strong>Period:</strong> <?php echo e($now->format('F Y')); ?></p>
            
            <p class="small text-muted">Generated on: <?php echo e(\Carbon\Carbon::now()->format('F d, Y h:i A')); ?></p>
            <hr class="border-dark">
        </div>

        
        <div class="row">
            
            <div class="col-md-6 col-12">
                
                <div class="small-box bg-gradient-primary shadow-sm print-box">
                    <div class="inner">
                        <h3>₱ <?php echo e(number_format($totalInventoryValue, 2)); ?></h3>
                        <p class="font-weight-bold text-uppercase">Total Inventory Asset Value</p>
                        <small class="d-block">Sum of (Stock × Cost Price)</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>

            
            <div class="col-md-6 col-12">
                <div
                    class="small-box bg-gradient-<?php echo e($growthStatus == 'increase' ? 'success' : ($growthStatus == 'decrease' ? 'danger' : 'secondary')); ?> shadow-sm print-box">
                    <div class="inner">
                        <h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($growthStatus == 'increase'): ?>
                                +
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php echo e(number_format($growthPercentage, 1)); ?>%
                        </h3>
                        <p class="font-weight-bold text-uppercase">Monthly Sales Growth</p>
                        <small class="d-block">
                            Current: ₱<?php echo e(number_format($currentMonthSales, 2)); ?> vs
                            Last Month: ₱<?php echo e(number_format($lastMonthSales, 2)); ?>

                        </small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>#<?php echo e($index + 1); ?></td>
                                        <td class="text-left font-weight-bold"><?php echo e($item->product_name); ?></td>
                                        <td><?php echo e($item->total_sold); ?></td>
                                        <td class="text-success font-weight-bold">₱
                                            <?php echo e(number_format($item->total_revenue, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-muted">No sales data for this month.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            
            <div class="col-md-6">
                <div class="card card-outline card-info h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-truck text-info mr-2"></i> Top Suppliers (By Volume)
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $supplierPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-left font-weight-bold">
                                            <?php echo e($supplier->supplier->name ?? 'Unknown Supplier'); ?></td>
                                        <td><span class="badge badge-info text-lg px-3"><?php echo e($supplier->total_pos); ?></span>
                                        </td>
                                        <td>₱ <?php echo e(number_format($supplier->total_spent, 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-muted">No purchase orders recorded.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border print-card">
                    <div class="card-body bg-light">
                        <h5 class="font-weight-bold"><i class="fas fa-info-circle text-primary"></i> Executive Summary &
                            Analysis</h5>
                        <p class="text-muted mb-0" style="font-size: 14px;">
                            • <strong>Inventory Value:</strong> Currently at ₱<?php echo e(number_format($totalInventoryValue, 2)); ?>.
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($growthStatus == 'decrease'): ?>
                                High inventory with declining sales may indicate overstocking.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <br>
                            • <strong>Sales Growth:</strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($growthStatus == 'increase'): ?>
                                Positive growth of <?php echo e(number_format($growthPercentage, 1)); ?>%. Strategy is effective.
                            <?php elseif($growthStatus == 'decrease'): ?>
                                Declined by <?php echo e(number_format(abs($growthPercentage), 1)); ?>%. Review marketing or pricing
                                strategy.
                            <?php else: ?>
                                Stable performance.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <br>
                            • <strong>Supplier Strategy:</strong> Focus negotiations on top suppliers to improve margins.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        @media print {

            /* Hide unwanted elements */
            .no-print,
            .main-footer,
            .navbar,
            .main-sidebar,
            .card-header .card-tools {
                display: none !important;
            }

            /* Layout Adjustments */
            .content-wrapper {
                margin-left: 0 !important;
                background: white !important;
                padding: 0 !important;
            }

            body {
                background: white !important;
                font-size: 12pt;
            }

            /* Ensure columns sit side-by-side */
            .col-md-6 {
                width: 50% !important;
                float: left !important;
                padding: 0 10px !important;
            }

            /* Styling for Cards/Boxes on Print */
            .print-box,
            .print-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }

            /* Force Small Boxes to look cleaner on B&W print */
            .small-box {
                color: black !important;
                border: 1px solid #000 !important;
            }

            .small-box .icon {
                display: none !important;
            }

            /* Hide big icons to save ink/space */

            /* Table Styling */
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

            /* Badge/Colors adjust for print */
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
                /* Force black text for readability */
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/reports/monthly.blade.php ENDPATH**/ ?>