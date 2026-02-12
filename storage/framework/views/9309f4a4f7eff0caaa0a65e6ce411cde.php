

<?php $__env->startSection('subtitle', 'Purchase Requests'); ?>
<?php $__env->startSection('content_header_subtitle', 'Purchase Requests'); ?>

<?php $__env->startSection('plugins.Datatables', true); ?>
<?php $__env->startSection('plugins.Select2', true); ?>
<?php $__env->startSection('plugins.Sweetalert2', true); ?>

<?php $__env->startSection('content_body'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 font-weight-bold text-dark">
                            <i class="fas fa-shopping-cart mr-2 text-primary"></i> PURCHASE REQUESTS
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                            data-target="#createPRModal">
                            <i class="fas fa-plus-circle mr-1"></i> CREATE PURCHASE REQUEST
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="prTable" class="table table-bordered table-striped table-hover w-100">
                                <thead class="bg-dark text-white text-uppercase small">
                                    <tr>
                                        <th>PR Number</th>
                                        <th>Requestor</th>
                                        <th>Department</th>
                                        <th>Date Created</th>
                                        <th class="text-center no-print">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="small"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div class="modal fade" id="createPRModal" tabindex="-1" role="dialog" aria-labelledby="createPRModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createPRModalLabel">
                        <i class="fas fa-file-alt mr-2"></i> PR Creation Form
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="prForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="font-weight-bold text-muted small">PR NUMBER:</label>
                            <input type="text" name="request_number" id="modal_pr_number"
                                class="form-control bg-light border-0" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 pr-md-3">
                                <div class="mb-4">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-truck mr-2"></i> 1. Supplier Selection
                                    </h6>
                                    <select name="supplier_id" id="supplierSelect" class="form-control" required>
                                        <option value="">Choose Supplier...</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>

                                <div>
                                    <h6 class="font-weight-bold text-info mb-3">
                                        <i class="fas fa-box-open mr-2"></i> 2. Available Products
                                    </h6>

                                    <div class="table-responsive border rounded"
                                        style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover mb-0" id="availableProductsTable">
                                            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th class="text-center" width="100">Price</th>
                                                    <th class="text-center" width="80">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center">
                                                    <td colspan="3" class="text-muted py-4">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        <em>Please select a supplier first</em>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 pl-md-3">
                                <div>
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-clipboard-check mr-2"></i> 3. Review Selected Items
                                    </h6>

                                    <div class="table-responsive border rounded"
                                        style="max-height: 440px; overflow-y: auto;">
                                        <table class="table table-sm mb-0" id="selectedItemsTable">
                                            <thead class="thead-dark" style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-center" width="90">Unit Cost</th>
                                                    <th class="text-center" width="70">Qty</th>
                                                    <th class="text-center" width="100">Subtotal</th>
                                                    <th class="text-center" width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="empty-row">
                                                    <td colspan="5" class="text-center text-muted py-4">
                                                        <i class="fas fa-shopping-basket mr-2"></i>
                                                        <em>No items added yet</em>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div
                                        class="bg-dark text-white p-3 mt-3 rounded d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 font-weight-bold">GRAND TOTAL:</h5>
                                        <h5 class="mb-0 font-weight-bold text-success">₱<span id="grandTotal">0.00</span>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="savePRBtn">
                            <i class="fas fa-check-circle mr-1"></i> Submit Purchase Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    
    
    <div class="modal fade" id="viewPRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-file-invoice mr-2"></i> Purchase Request Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                
                <div class="modal-body p-4" style="background-color: #fafafa;">
                    
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <p class="text-uppercase text-muted small mb-1" style="letter-spacing: 1px;">PURCHASE REQUEST OF
                        </p>
                        <h4 class="font-weight-bold text-danger mb-0" id="receipt_customer_name">LOADING...</h4>
                    </div>

                    
                    <div class="bg-white p-3 rounded shadow-sm mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="font-weight-bold mb-1 text-primary" id="receipt_pr_number">PR-XXXXXX</h5>
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-user mr-1"></i> Requested By:
                                    <span class="font-weight-bold text-dark" id="receipt_requestor">N/A</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-warning" id="receipt_status">PENDING</span>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="bg-white p-3 rounded shadow-sm h-100">
                                <h6 class="font-weight-bold text-info mb-2">
                                    <i class="fas fa-calendar-alt mr-1"></i> Dates
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted small" width="140">Date Ordered:</td>
                                        <td class="small font-weight-bold" id="receipt_date_ordered">N/A</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Estimated Delivery:</td>
                                        <td class="small font-weight-bold" id="receipt_delivery_date">N/A</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Date Created:</td>
                                        <td class="small font-weight-bold" id="receipt_requested_date">N/A</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-white p-3 rounded shadow-sm h-100">
                                <h6 class="font-weight-bold text-success mb-2">
                                    <i class="fas fa-truck mr-1"></i> Supplier Details
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted small" width="80">Name:</td>
                                        <td class="small font-weight-bold" id="receipt_supplier_name">N/A</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Contact:</td>
                                        <td class="small" id="receipt_supplier_contact">N/A</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted small">Address:</td>
                                        <td class="small" id="receipt_supplier_address">N/A</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white p-3 rounded shadow-sm mb-3" id="receipt_remarks_container">
                        <h6 class="font-weight-bold text-warning mb-2">
                            <i class="fas fa-comment-alt mr-1"></i> Remarks / Notes
                        </h6>
                        <p class="small mb-0 text-muted" id="receipt_remarks">No remarks provided</p>
                    </div>

                    
                    <div class="bg-white p-3 rounded shadow-sm mb-3">
                        <h6 class="font-weight-bold text-dark mb-3">
                            <i class="fas fa-boxes mr-1"></i> Products Requested
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="small" width="100">SKU</th>
                                        <th class="small">Product Name</th>
                                        <th class="small text-center" width="80">Qty</th>
                                        <th class="small text-right" width="120">Unit Cost</th>
                                        <th class="small text-right" width="120">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="receipt_items_body">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading items...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    
                    <div class="bg-dark text-white p-3 rounded shadow">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold">GRAND TOTAL:</h5>
                            <h4 class="mb-0 font-weight-bold text-success" id="receipt_grand_total">₱0.00</h4>
                        </div>
                    </div>
                </div>

                
                <div class="modal-footer bg-light d-flex justify-content-between">
                    
                    <div id="viewPRActionButtons" style="display: none;">
                        <button type="button" class="btn btn-danger btn-sm" id="viewModalRejectBtn">
                            <i class="fas fa-times-circle mr-1"></i> Reject
                        </button>
                        <button type="button" class="btn btn-success btn-sm ml-2" id="viewModalApproveBtn">
                            <i class="fas fa-check-circle mr-1"></i> Approve
                        </button>
                    </div>
                    
                    <div class="ml-auto">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div class="modal fade" id="approvePRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-clipboard-check mr-2"></i> Review Purchase Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="approvePRForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="approve_pr_id">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-primary">
                                    <i class="fas fa-file-alt mr-2"></i> REQUEST DETAILS
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" width="140">PR Number:</td>
                                        <td class="font-weight-bold text-primary" id="approve_pr_number"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Requested By:</td>
                                        <td class="font-weight-bold" id="approve_requestor"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Department:</td>
                                        <td id="approve_department"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Date Created:</td>
                                        <td id="approve_date_created"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold text-success">
                                    <i class="fas fa-truck mr-2"></i> SUPPLIER DETAILS
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" width="100">Supplier:</td>
                                        <td class="font-weight-bold" id="approve_supplier"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Contact:</td>
                                        <td id="approve_contact"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email:</td>
                                        <td id="approve_email"></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Address:</td>
                                        <td id="approve_address"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h6 class="font-weight-bold text-info mb-2">
                            <i class="fas fa-boxes mr-2"></i> PRODUCTS REQUESTED
                        </h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Product Name</th>
                                        <th class="text-center" width="100">Quantity</th>
                                        <th class="text-center" width="120">Unit Cost</th>
                                        <th class="text-center" width="120">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="approve_items_list"></tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="4" class="text-right">GRAND TOTAL:</td>
                                        <td class="text-center text-primary" id="approve_grand_total">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <h6 class="font-weight-bold text-warning mb-3">
                            <i class="fas fa-clipboard-check mr-2"></i> APPROVAL DETAILS
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" id="approve_order_date" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Estimated Delivery Date</label>
                                    <input type="date" name="estimated_delivery_date"
                                        id="approve_estimated_delivery_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Payment Terms <span class="text-danger">*</span></label>
                                    <select name="payment_terms" class="form-control" required>
                                        <option value="">-- Select Payment Term --</option>
                                        <option value="cash_on_delivery">Cash on Delivery</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Remarks / Notes</label>
                            <textarea name="remarks" id="approve_remarks" class="form-control" rows="3"
                                placeholder="Enter any additional notes..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Approved By</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo e(auth()->user()->full_name ?? 'N/A'); ?>" readonly>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmRejectBtn">
                            <i class="fas fa-times-circle mr-1"></i> Reject Request
                        </button>
                        <button type="submit" class="btn btn-success" id="confirmApproveBtn">
                            <i class="fas fa-check-circle mr-1"></i> Approve Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Rejection</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="rejectPRForm">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="reject_pr_id_confirm">

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            You are about to reject PR <strong id="reject_pr_number_confirm"></strong>
                        </div>

                        <div class="form-group">
                            <label>Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="remarks" id="reject_remarks" class="form-control" rows="4" required
                                placeholder="Please provide a clear reason..."></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label>Rejected By</label>
                            <input type="text" class="form-control bg-light"
                                value="<?php echo e(auth()->user()->full_name ?? 'N/A'); ?>" readonly>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-arrow-left mr-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger" id="confirmRejectSubmitBtn">
                            <i class="fas fa-times-circle mr-1"></i> Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(document).ready(function() {
            let selectedItems = [];
            let supplierProducts = [];
            let table;

            // ✅ Track current PR ID and status for the view modal
            let currentViewPRId = null;
            let currentViewStatus = null;

            // ============================================
            // GRAND TOTAL
            // ============================================
            function updateGrandTotal() {
                let grandTotal = 0;
                selectedItems.forEach(item => {
                    grandTotal += parseFloat(item.subtotal) || 0;
                });
                $('#grandTotal').text(grandTotal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            // ============================================
            // DATATABLE
            // ============================================
            if ($.fn.DataTable.isDataTable('#prTable')) {
                $('#prTable').DataTable().destroy();
            }

            table = $('#prTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "<?php echo e(route('pr.datatable')); ?>",
                order: [
                    [3, "desc"]
                ],
                columns: [{
                        data: 'request_number',
                        name: 'request_number'
                    },
                    {
                        data: 'requestor',
                        name: 'requestor'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // ============================================
            // ✅ OPEN RECEIPT VIEW MODAL
            // ============================================
            function openPRDetailsModal(prId) {
                currentViewPRId = prId;
                currentViewStatus = null;

                // Reset action buttons while loading
                $('#viewPRActionButtons').hide();

                $('#receipt_items_body').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
                        </td>
                    </tr>
                `);

                $('#viewPRModal').modal('show');

                $.ajax({
                    url: `/purchase-request/${prId}`,
                    type: 'GET',
                    success: function(res) {
                        // Store status for footer buttons
                        currentViewStatus = res.status_id;

                        // Customer name
                        let customerName = res.user?.full_name || res.requestor_name || 'N/A';
                        $('#receipt_customer_name').text(customerName.toUpperCase());
                        $('#receipt_pr_number').text(res.request_number || 'N/A');
                        $('#receipt_requestor').text(customerName);

                        // Status badge
                        let statusClass = 'badge-secondary';
                        let statusText = 'N/A';
                        if (res.status_id == 1) {
                            statusClass = 'badge-warning';
                            statusText = 'PENDING';
                        } else if (res.status_id == 2) {
                            statusClass = 'badge-success';
                            statusText = 'APPROVED';
                        } else if (res.status_id == 3) {
                            statusClass = 'badge-danger';
                            statusText = 'REJECTED';
                        }

                        $('#receipt_status')
                            .removeClass('badge-warning badge-success badge-danger badge-secondary')
                            .addClass(statusClass)
                            .text(statusText);

                        // ✅ Show Approve/Reject buttons ONLY if status is PENDING (status_id == 1)
                        if (res.status_id == 1) {
                            $('#viewPRActionButtons').show();
                        } else {
                            $('#viewPRActionButtons').hide();
                        }

                        // Dates
                        $('#receipt_date_ordered').text(
                            res.order_date ?
                            new Date(res.order_date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            }) :
                            'N/A'
                        );
                        $('#receipt_delivery_date').text(
                            res.estimated_delivery_date ?
                            new Date(res.estimated_delivery_date).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            }) :
                            'N/A'
                        );
                        $('#receipt_requested_date').text(
                            res.created_at ?
                            new Date(res.created_at).toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            }) :
                            'N/A'
                        );

                        // Supplier
                        $('#receipt_supplier_name').text(res.supplier?.name || 'N/A');
                        $('#receipt_supplier_contact').text(res.supplier?.contact_number || res.supplier
                            ?.contact_person || 'N/A');
                        $('#receipt_supplier_address').text(res.supplier?.address || 'N/A');

                        // Remarks
                        $('#receipt_remarks').text(res.remarks || 'No remarks provided');
                        if (!res.remarks || res.remarks.trim() === '') {
                            $('#receipt_remarks_container').hide();
                        } else {
                            $('#receipt_remarks_container').show();
                        }

                        // Items
                        let itemsHtml = '';
                        let grandTotal = 0;
                        if (res.items && res.items.length > 0) {
                            res.items.forEach(item => {
                                let subtotal = parseFloat(item.subtotal || 0);
                                grandTotal += subtotal;
                                let productName = item.product_name || item.supplier_product
                                    ?.name || 'Unknown';
                                let sku = item.sku || item.supplier_product?.system_sku || item
                                    .supplier_product?.supplier_sku || 'N/A';
                                itemsHtml += `
                                    <tr>
                                        <td class="small text-muted">${sku}</td>
                                        <td class="small font-weight-bold">${productName}</td>
                                        <td class="small text-center"><span class="badge badge-info badge-pill">${item.quantity || 0}</span></td>
                                        <td class="small text-right">₱${parseFloat(item.unit_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                        <td class="small text-right font-weight-bold text-success">₱${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                    </tr>`;
                            });
                        } else {
                            itemsHtml =
                                `<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox mr-2"></i> No items found</td></tr>`;
                        }

                        $('#receipt_items_body').html(itemsHtml);
                        $('#receipt_grand_total').text('₱' + grandTotal.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    },
                    error: function(xhr) {
                        console.error('❌ Error loading PR:', xhr.responseText);
                        $('#receipt_items_body').html(`
                            <tr><td colspan="5" class="text-center text-danger py-4">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Failed to load details.
                            </td></tr>`);
                        Swal.fire('Error', 'Failed to load purchase request details', 'error');
                    }
                });
            }

            // ============================================
            // ✅ VIEW MODAL: APPROVE BUTTON → Open Approval Form
            // ============================================
            $('#viewModalApproveBtn').on('click', function() {
                if (!currentViewPRId) return;
                $('#viewPRModal').modal('hide');
                // Small delay so modals don't overlap
                setTimeout(function() {
                    openApprovalModal(currentViewPRId);
                }, 300);
            });

            // ============================================
            // ✅ VIEW MODAL: REJECT BUTTON → Open Reject Confirm
            // ============================================
            $('#viewModalRejectBtn').on('click', function() {
                if (!currentViewPRId) return;
                let prNumber = $('#receipt_pr_number').text();
                $('#viewPRModal').modal('hide');
                setTimeout(function() {
                    $('#reject_pr_id_confirm').val(currentViewPRId);
                    $('#reject_pr_number_confirm').text(prNumber);
                    $('#reject_remarks').val('');
                    $('#rejectConfirmModal').modal('show');
                }, 300);
            });

            // ============================================
            // TABLE ROW CLICK
            // ============================================
            $('#prTable tbody').on('click', 'tr', function(e) {
                // Don't trigger if clicking the action column (index 4) or a button
                if ($(e.target).closest('td').index() === 4 || $(e.target).is('button, a')) {
                    return;
                }
                let data = table.row(this).data();
                if (data && data.id) {
                    openPRDetailsModal(data.id);
                }
            });

            // ============================================
            // ACTION BUTTONS IN TABLE (view-pr-badge, approve-pr-btn)
            // ============================================
            $(document).on('click', '.view-pr-badge, .view-pr-btn', function(e) {
                e.stopPropagation();
                let prId = $(this).data('id');
                if (prId) openPRDetailsModal(prId);
            });

            $(document).on('click', '.approve-pr-btn', function(e) {
                e.stopPropagation();
                let prId = $(this).data('id');
                if (prId) openApprovalModal(prId);
            });

            $(document).on('click', '.reject-pr-btn', function(e) {
                e.stopPropagation();
                let prId = $(this).data('id');
                let prNumber = $(this).data('number') || '';
                if (prId) {
                    $('#reject_pr_id_confirm').val(prId);
                    $('#reject_pr_number_confirm').text(prNumber);
                    $('#reject_remarks').val('');
                    $('#rejectConfirmModal').modal('show');
                }
            });

            // ============================================
            // OPEN APPROVAL MODAL (populates form)
            // ============================================
            function openApprovalModal(prId) {
                $.ajax({
                    url: `/purchase-request/${prId}`,
                    type: 'GET',
                    success: function(res) {
                        $('#approve_pr_id').val(prId);
                        $('#approve_order_date').val('');
                        $('#approve_estimated_delivery_date').val('');
                        $('#approve_remarks').val('');
                        $('select[name="payment_terms"]').val('');

                        $('#approve_pr_number').text(res.request_number || 'N/A');
                        $('#approve_requestor').text(res.user?.full_name || 'N/A');
                        $('#approve_department').text(res.department?.name || 'N/A');
                        $('#approve_date_created').text(res.created_at ? new Date(res.created_at)
                            .toLocaleDateString() : 'N/A');

                        $('#approve_supplier').text(res.supplier?.name || 'N/A');
                        $('#approve_contact').text(res.supplier?.contact_number || 'N/A');
                        $('#approve_email').text(res.supplier?.email || 'N/A');
                        $('#approve_address').text(res.supplier?.address || 'N/A');

                        let itemsHtml = '';
                        let grandTotal = 0;
                        if (res.items && res.items.length > 0) {
                            res.items.forEach((item, index) => {
                                let subtotal = parseFloat(item.subtotal || 0);
                                grandTotal += subtotal;
                                let productName = item.product_name || item.supplier_product
                                    ?.name || 'Unknown';
                                let sku = item.sku || item.supplier_product?.system_sku ||
                                'N/A';
                                itemsHtml += `
                                    <tr>
                                        <td class="text-center">${index + 1}</td>
                                        <td><strong>${productName}</strong><br><small class="text-muted">SKU: ${sku}</small></td>
                                        <td class="text-center"><span class="badge badge-info">${item.quantity}</span></td>
                                        <td class="text-right">₱${parseFloat(item.unit_cost).toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                        <td class="text-right font-weight-bold">₱${subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                                    </tr>`;
                            });
                        }
                        $('#approve_items_list').html(itemsHtml);
                        $('#approve_grand_total').text('₱' + grandTotal.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        }));

                        $('#approvePRModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load PR details', 'error');
                    }
                });
            }

            // ============================================
            // APPROVE FORM SUBMIT
            // ============================================
            $('#approvePRForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                let prId = $('#approve_pr_id').val();
                let btn = $('#confirmApproveBtn');
                if (btn.prop('disabled')) return false;

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: `/purchase-request/approve/${prId}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#approvePRModal').modal('hide');
                        Swal.fire('Success!', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to approve PR',
                            'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-circle mr-1"></i> Approve Request');
                    }
                });
            });

            // ============================================
            // REJECT BUTTON inside Approve Modal → goes to Reject Confirm
            // ============================================
            $('#confirmRejectBtn').off('click').on('click', function() {
                let prId = $('#approve_pr_id').val();
                let prNumber = $('#approve_pr_number').text();
                $('#approvePRModal').modal('hide');
                setTimeout(function() {
                    $('#reject_pr_id_confirm').val(prId);
                    $('#reject_pr_number_confirm').text(prNumber);
                    $('#reject_remarks').val('');
                    $('#rejectConfirmModal').modal('show');
                }, 300);
            });

            // ============================================
            // REJECT FORM SUBMIT
            // ============================================
            $('#rejectPRForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                let prId = $('#reject_pr_id_confirm').val();
                let btn = $('#confirmRejectSubmitBtn');

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: `/purchase-request/reject/${prId}`,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#rejectConfirmModal').modal('hide');
                        Swal.fire('Success!', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject PR',
                            'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-times-circle mr-1"></i> Confirm Rejection');
                    }
                });
            });

            // ============================================
            // GENERATE PR NUMBER ON MODAL OPEN
            // ============================================
            $('#createPRModal').on('show.bs.modal', function() {
                $.get("<?php echo e(route('pr.generate-number')); ?>", function(res) {
                    $('#modal_pr_number').val(res.request_number);
                });
                selectedItems = [];
                supplierProducts = [];
                $('#supplierSelect').val('');
                $('#availableProductsTable tbody').html(
                    '<tr><td colspan="3" class="text-center py-4">Please select a supplier</td></tr>'
                );
                renderSelectedItems();
                updateGrandTotal();
            });

            // ============================================
            // LOAD PRODUCTS WHEN SUPPLIER SELECTED
            // ============================================
            $('#supplierSelect').on('change', function() {
                let supplierId = $(this).val();
                if (!supplierId) {
                    $('#availableProductsTable tbody').html(
                        '<tr><td colspan="3" class="text-center py-4">Please select a supplier</td></tr>'
                    );
                    return;
                }
                $.get(`/purchase-request/supplier-products/${supplierId}`, function(products) {
                    supplierProducts = products;
                    renderAvailableProducts(products);
                }).fail(function() {
                    Swal.fire('Error', 'Failed to load products', 'error');
                });
            });

            // ============================================
            // RENDER AVAILABLE PRODUCTS
            // ============================================
            function renderAvailableProducts(products) {
                const tbody = $('#availableProductsTable tbody');
                tbody.empty();
                if (!products || products.length === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">No products found.</td></tr>');
                    return;
                }
                let html = '';
                products.forEach(product => {
                    let isAdded = selectedItems.some(item => parseInt(item.id) === parseInt(product.id));
                    let btn = isAdded ?
                        `<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check"></i> Added</button>` :
                        `<button class="btn btn-sm btn-primary add-product-btn"
                            data-id="${product.id}"
                            data-name="${product.name}"
                            data-price="${product.cost_price}">
                            <i class="fas fa-plus"></i> Add
                        </button>`;
                    html += `<tr>
                        <td class="small">${product.name}</td>
                        <td class="text-right">₱${parseFloat(product.cost_price).toFixed(2)}</td>
                        <td class="text-center">${btn}</td>
                    </tr>`;
                });
                tbody.append(html);
            }

            // ============================================
            // ADD PRODUCT
            // ============================================
            $(document).off('click', '.add-product-btn').on('click', '.add-product-btn', function(e) {
                e.preventDefault();
                let productId = parseInt($(this).data('id'));
                let name = $(this).data('name');
                let price = parseFloat($(this).data('price'));
                if (selectedItems.some(item => parseInt(item.id) === productId)) return;
                selectedItems.push({
                    id: productId,
                    name,
                    price,
                    quantity: 1,
                    subtotal: price
                });
                renderSelectedItems();
                updateGrandTotal();
                $(this).removeClass('btn-primary').addClass('btn-secondary').prop('disabled', true)
                    .html('<i class="fas fa-check"></i> Added');
            });

            // ============================================
            // RENDER SELECTED ITEMS
            // ============================================
            function renderSelectedItems() {
                let tbody = $('#selectedItemsTable tbody');
                tbody.empty();
                if (selectedItems.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center py-4">No items added</td></tr>');
                    return;
                }
                selectedItems.forEach((item, index) => {
                    tbody.append(`
                        <tr>
                            <td class="small">${item.name}</td>
                            <td><input type="number" name="products[${item.id}][unit_cost]" class="form-control form-control-sm text-center unit-cost-input" data-index="${index}" value="${item.price.toFixed(2)}" step="0.01" min="0"></td>
                            <td><input type="number" name="products[${item.id}][quantity]" class="form-control form-control-sm text-center qty-input" data-index="${index}" value="${item.quantity}" min="1"></td>
                            <td class="text-center font-weight-bold">₱${item.subtotal.toFixed(2)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>`);
                });
            }

            // UPDATE QTY/COST
            $(document).on('input', '.qty-input, .unit-cost-input', function() {
                let index = $(this).data('index');
                if (!selectedItems[index]) return;
                let qty = parseInt($(`.qty-input[data-index="${index}"]`).val()) || 0;
                let cost = parseFloat($(`.unit-cost-input[data-index="${index}"]`).val()) || 0;
                selectedItems[index].quantity = qty;
                selectedItems[index].price = cost;
                selectedItems[index].subtotal = qty * cost;
                renderSelectedItems();
                updateGrandTotal();
            });

            // REMOVE ITEM
            $(document).off('click', '.remove-item').on('click', '.remove-item', function() {
                selectedItems.splice($(this).data('index'), 1);
                renderSelectedItems();
                updateGrandTotal();
                renderAvailableProducts(supplierProducts);
            });

            // ============================================
            // SUBMIT PR FORM
            // ============================================
            $('#savePRBtn').off('click').on('click', function(e) {
                e.preventDefault();
                if (selectedItems.length === 0) {
                    Swal.fire('Warning', 'Please add items first.', 'warning');
                    return;
                }
                let btn = $(this);
                if (btn.prop('disabled')) return;
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                let formData = new FormData();
                formData.append('_token', $('input[name="_token"]').val());
                formData.append('supplier_id', $('#supplierSelect').val());
                selectedItems.forEach(item => {
                    formData.append(`products[${item.id}][quantity]`, item.quantity);
                    formData.append(`products[${item.id}][unit_cost]`, item.price);
                });

                $.ajax({
                    url: "<?php echo e(route('pr.store')); ?>",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#createPRModal').modal('hide');
                        Swal.fire('Success', 'Purchase Request Saved!', 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error saving PR',
                            'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-circle mr-1"></i> Submit Purchase Request'
                            );
                    }
                });
            });

        }); // end ready
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('css'); ?>
    <style>
        #viewPRModal .modal-content {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        #viewPRModal .modal-header {
            border-bottom: 3px solid #007bff;
        }

        #viewPRModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        #viewPRModal .table-sm td,
        #viewPRModal .table-sm th {
            padding: 0.5rem;
            vertical-align: middle;
        }

        #viewPRModal .badge {
            font-size: 0.75rem;
            padding: 0.4em 0.8em;
            font-weight: 600;
        }

        #receipt_items_body tr:hover {
            background-color: #f1f3f5;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .modal-lg {
            max-width: 900px;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.5rem;
            font-size: 0.875rem;
        }

        .thead-dark {
            background-color: #343a40 !important;
            color: white !important;
        }

        .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        input[type="number"].text-center {
            text-align: center;
        }

        .table-borderless td {
            border: none;
            padding: 0.25rem 0;
        }

        .modal-body h6 {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        /* ✅ Approve/Reject button area in view modal footer */
        #viewPRModal .modal-footer {
            align-items: center;
        }

        #viewModalApproveBtn {
            min-width: 100px;
        }

        #viewModalRejectBtn {
            min-width: 80px;
        }

        @media (max-width: 768px) {
            #viewPRModal .modal-dialog {
                margin: 0.5rem;
            }

            #viewPRModal .table-responsive {
                font-size: 0.8rem;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.adminlte', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\inventoryyy\resources\views/purchase-request/index.blade.php ENDPATH**/ ?>