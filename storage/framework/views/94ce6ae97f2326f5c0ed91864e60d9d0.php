

<?php $__env->startSection('subtitle', 'Scan Purchase Order'); ?>
<?php $__env->startSection('content_header_title', 'Purchase Orders'); ?>
<?php $__env->startSection('content_header_subtitle', 'Scan Items - ' . $po->po_number); ?>

<?php $__env->startSection('content_body'); ?>
    <div id="scanContainer" data-po-id="<?php echo e($po->id); ?>">

        
        <div class="top-info-bar">
            <div class="info-group">
                <span class="info-label">PO Number:</span>
                <strong class="info-value"><?php echo e($po->po_number); ?></strong>
            </div>
            <div class="info-divider"></div>
            <div class="info-group">
                <span class="info-label">Supplier:</span>
                <span class="info-value"><?php echo e($po->supplier->name ?? 'N/A'); ?></span>
            </div>
            <div class="info-divider"></div>
            <div class="info-group">
                <span class="info-label">Delivery Date:</span>
                <span class="info-value"><?php echo e($po->delivery_date ? $po->delivery_date->format('M d, Y') : 'N/A'); ?></span>
            </div>
            <div class="info-divider"></div>
            <div class="info-group">
                <span class="info-label">Total:</span>
                <strong class="info-value text-success">₱<?php echo e(number_format($po->grand_total ?? 0, 2)); ?></strong>
            </div>
            <button type="button" class="btn btn-sm btn-secondary ml-auto" id="cancelScanBtn">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <div class="row mt-4">
            
            <div class="col-lg-6">

                
                <div class="card shadow-sm mb-3" id="scannerCard">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="scanner-icon">
                                <i class="fas fa-barcode"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="mb-0">Barcode Scanner</h5>
                                <small class="text-muted" id="scannerStatusText">Press BEGIN SCAN to start</small>
                            </div>
                            <span class="badge badge-secondary ml-auto" id="scannerStatusBadge">
                                <span id="statusDotAnim"></span>
                                <span id="statusBadgeText">IDLE</span>
                            </span>
                        </div>

                        
                        <button type="button" class="btn btn-success btn-block mb-3" id="beginScanBtn">
                            <i class="fas fa-play"></i> BEGIN SCAN
                        </button>
                        <button type="button" class="btn btn-danger btn-block mb-3" id="endScanBtn" style="display:none;">
                            <i class="fas fa-stop"></i> END SCAN
                        </button>

                        
                        <div class="input-group" id="barcodeInputWrap">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-qrcode"></i>
                                </span>
                            </div>
                            <input type="text" id="barcodeInput" class="form-control"
                                placeholder="Scan or type barcode..." disabled autocomplete="off">
                        </div>
                        <small class="text-muted">Supports barcode scanner gun & manual entry</small>
                    </div>
                </div>

                
                <div class="row mb-3">
                    <div class="col-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-number" id="totalScanned">0</div>
                            <div class="stat-label">Scanned</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="stat-number" id="totalOrderedDisplay"><?php echo e($po->items->sum('quantity_ordered')); ?>

                            </div>
                            <div class="stat-label">Ordered</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon text-warning">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-number" id="scanProgress">0%</div>
                            <div class="stat-label">Progress</div>
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Overall Progress</small>
                            <small class="text-muted" id="progressPercent">0%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" id="overallProgressBar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <i class="fas fa-check-circle text-success"></i> Scanned Items
                            </h6>
                            <span class="badge badge-success" id="scannedCount">0 items</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="scannedItemsBody">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <div>No items scanned yet</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>SCANNED TOTAL</strong>
                            <strong class="text-success" id="scanGrandTotal">₱0.00</strong>
                        </div>
                    </div>
                </div>

                
                <button type="button" class="btn btn-primary btn-block btn-lg mt-3" id="completeScanBtn">
                    <i class="fas fa-check-double"></i> COMPLETE SCANNING
                </button>

            </div>

            
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list"></i> Purchase Order Items
                            </h6>
                            <span class="badge badge-info"><?php echo e($po->items->count()); ?> products</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: calc(100vh - 250px);">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30"></th>
                                        <th>Product</th>
                                        <th class="text-center" width="70">Ordered</th>
                                        <th class="text-right" width="90">Unit Cost</th>
                                        <th width="180">Progress</th>
                                    </tr>
                                </thead>
                                <tbody id="poItemsBody">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $po->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="po-item-row" data-product-id="<?php echo e($item->product_id); ?>"
                                            data-quantity-ordered="<?php echo e($item->quantity_ordered); ?>"
                                            data-quantity-scanned="<?php echo e($item->quantity_scanned ?? 0); ?>">
                                            <td>
                                                <i class="far fa-circle text-muted"
                                                    id="icon-<?php echo e($item->product_id); ?>"></i>
                                            </td>
                                            <td>
                                                <strong><?php echo e($item->supplierProduct->name ?? 'Unknown Product'); ?></strong>
                                                <br>
                                                <small
                                                    class="text-muted"><?php echo e($item->supplierProduct->supplier_sku ?? 'No SKU'); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?php echo e($item->quantity_ordered); ?></span>
                                            </td>
                                            <td class="text-right">
                                                ₱<?php echo e(number_format($item->unit_cost, 2)); ?>

                                            </td>
                                            <td>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar" id="progress-<?php echo e($item->product_id); ?>"
                                                        style="width: 0%"></div>
                                                </div>
                                                <small class="text-muted d-flex justify-content-between mt-1">
                                                    <span
                                                        id="pcount-<?php echo e($item->product_id); ?>">0/<?php echo e($item->quantity_ordered); ?></span>
                                                    <span id="ppct-<?php echo e($item->product_id); ?>">0%</span>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div id="toastContainer" style="position: fixed; top: 80px; right: 20px; z-index: 9999;"></div>

        
        <div id="scanFlash" style="display: none;"></div>

    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        /* Light, Clean Theme */
        body {
            background: #f8f9fa !important;
        }

        .content-wrapper {
            background: #f8f9fa !important;
        }

        /* Top Info Bar */
        .top-info-bar {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 14px;
            color: #212529;
        }

        .info-divider {
            width: 1px;
            height: 30px;
            background: #dee2e6;
        }

        /* Scanner Card */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .scanner-icon {
            width: 40px;
            height: 40px;
            background: #e3f2fd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2196F3;
            font-size: 18px;
        }

        #scannerCard.scanning {
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }

        #scannerStatusBadge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        #scannerStatusBadge.active {
            background-color: #d4edda;
            color: #28a745;
        }

        #statusDotAnim {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            margin-right: 5px;
        }

        #statusDotAnim.pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Input Group */
        #barcodeInputWrap.active {
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.2);
        }

        #barcodeInputWrap.success-flash {
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
        }

        #barcodeInputWrap.error-flash {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.3);
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 600;
            color: #212529;
        }

        .stat-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }

        /* Table Improvements */
        .table thead th {
            font-size: 11px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
            font-size: 13px;
        }

        .po-item-row.completed-row {
            background-color: #d4edda;
        }

        .po-item-row.completed-row i {
            color: #28a745;
        }

        /* Toast Notifications */
        .toast-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            border-left: 4px solid #28a745;
        }

        .toast-error {
            border-left: 4px solid #dc3545;
        }

        .toast-warning {
            border-left: 4px solid #ffc107;
        }

        .toast-info {
            border-left: 4px solid #17a2b8;
        }

        /* Scan Flash */
        #scanFlash {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9998;
        }

        #scanFlash.flash-success {
            background: rgba(40, 167, 69, 0.1);
            animation: flash 0.3s ease;
        }

        #scanFlash.flash-error {
            background: rgba(220, 53, 69, 0.1);
            animation: flash 0.3s ease;
        }

        @keyframes flash {

            0%,
            100% {
                opacity: 0;
            }

            50% {
                opacity: 1;
            }
        }

        /* Row Animation */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-row-anim {
            animation: slideUp 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-info-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-divider {
                display: none;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/barcode-scanner/barcode-scanner.js']); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/purchase-order/scan.blade.php ENDPATH**/ ?>