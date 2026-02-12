

<?php $__env->startSection('content_header_title', 'Strategic Business Report'); ?>
<?php $__env->startSection('content_header_subtitle', 'Target Year: ' . $selectedYear); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid">

        
        <div class="row mb-4 no-print align-items-center">
            <div class="col-md-6">
                <form action="<?php echo e(route('reports.strategic')); ?>" method="GET" class="form-inline">
                    <label class="mr-2 font-weight-bold">Select Fiscal Year:</label>
                    <select name="year" class="form-control mr-2 shadow-sm" onchange="this.form.submit()">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year); ?>" <?php echo e($selectedYear == $year ? 'selected' : ''); ?>>
                                <?php echo e($year); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Annual Report
                </button>
            </div>
        </div>

        
        <div class="d-none d-print-block text-center mb-4">
            <h1 class="font-weight-bold text-uppercase m-0">GYMNASTHENIQX WAREHOUSE</h1>
            <h4 class="text-uppercase">Strategic Annual & Quarterly Report</h4>
            <p class="mb-0"><strong>Fiscal Year:</strong> <?php echo e($selectedYear); ?></p>
            <p class="small text-muted">Generated on: <?php echo e(\Carbon\Carbon::now()->format('F d, Y h:i A')); ?></p>
            <hr class="border-dark">
        </div>

        
        <div class="row">
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Yearly Revenue</span>
                        <span class="info-box-number">₱ <?php echo e(number_format($totalYearlyRevenue, 2)); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-shopping-cart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Acquisition Cost</span>
                        <span class="info-box-number">₱ <?php echo e(number_format($totalYearlyCost, 2)); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <div class="info-box shadow-sm print-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-chart-pie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Gross Profit Margin</span>
                        <span class="info-box-number">
                            ₱ <?php echo e(number_format($totalYearlyRevenue - $totalYearlyCost, 2)); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row">
            
            <div class="col-md-8">
                <div class="card card-primary card-outline shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Monthly Performance Trend (<?php echo e($selectedYear); ?>)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="strategyChart" style="height: 300px; max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $quarterlyData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="font-weight-bold">Q<?php echo e($q); ?></td>
                                        <td class="text-success small-text">₱<?php echo e(number_format($data['revenue'])); ?></td>
                                        <td class="text-danger small-text">₱<?php echo e(number_format($data['cost'])); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="row mt-4 break-before">
            
            <div class="col-md-6">
                <div class="card card-outline card-warning h-100 shadow-sm print-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-binoculars mr-2"></i> Forecast for <?php echo e($selectedYear + 1); ?>

                        </h3>
                        <div class="card-tools no-print"><small>Based on <?php echo e($selectedYear); ?> Sales + 10%</small></div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover text-center">
                            <thead>
                                <tr>
                                    <th class="text-left pl-3">Item</th>
                                    <th>Sold (<?php echo e($selectedYear); ?>)</th>
                                    <th>Target (<?php echo e($selectedYear + 1); ?>)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $projectedStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-left pl-3"><?php echo e($proj['product']); ?></td>
                                        <td><?php echo e($proj['sold']); ?></td>
                                        <td class="font-weight-bold"><?php echo e($proj['forecast']); ?></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($proj['current'] >= $proj['forecast']): ?>
                                                <span class="badge badge-success">OK</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Restock</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-muted">No sales data available.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $deadStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="text-left pl-3"><?php echo e($dead['name']); ?></td>
                                        <td class="font-weight-bold text-danger"><?php echo e($dead['stock']); ?></td>
                                        <td>₱<?php echo e(number_format($dead['value'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-muted">No dead stocks found.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            var ctx = document.getElementById('strategyChart').getContext('2d');
            var strategyChart = new Chart(ctx, {
                type: 'line', // Line chart mas maganda for trends
                data: {
                    labels: <?php echo json_encode($months, 15, 512) ?>,
                    datasets: [{
                            label: 'Revenue (Sales)',
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            data: <?php echo json_encode($monthlyRevenue, 15, 512) ?>,
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Cost (Expenses)',
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            data: <?php echo json_encode($monthlyCost, 15, 512) ?>,
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('css'); ?>
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

            /* Hide icons to save ink */
            .info-box-content {
                text-align: center;
            }

            .break-before {
                page-break-inside: avoid;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/reports/strategic.blade.php ENDPATH**/ ?>