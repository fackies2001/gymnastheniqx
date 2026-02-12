@extends('layouts.adminlte')

@section('subtitle', 'Purchase Requests')
@section('content_header_subtitle', 'Purchase Requests')

@section('plugins.Datatables', true)
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_body')
    {{-- <div class="container-fluid">
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
                    </div> --}}
                    {{-- <div class="card-body">
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
    </div> --}}

    {{-- PR CREATION MODAL --}}
    {{-- <div class="modal fade" id="createPRModal" tabindex="-1" role="dialog" aria-labelledby="createPRModalLabel"
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
                </div> --}}

                {{-- <form id="prForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="font-weight-bold text-muted small">PR NUMBER:</label>
                            <input type="text" name="request_number" id="modal_pr_number"
                                class="form-control bg-light border-0" readonly>
                        </div> --}}

                        {{-- <div class="row">
                            <div class="col-md-6 pr-md-3">
                                <div class="mb-4">
                                    <h6 class="font-weight-bold text-primary mb-3">
                                        <i class="fas fa-truck mr-2"></i> 1. Supplier Selection
                                    </h6>
                                    <select name="supplier_id" id="supplierSelect" class="form-control" required>
                                        <option value="">Choose Supplier...</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
{{-- 
                                <div>
                                    <h6 class="font-weight-bold text-info mb-3">
                                        <i class="fas fa-box-open mr-2"></i> 2. Available Products
                                    </h6> --}}

                                    {{-- <div class="table-responsive border rounded"
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
                            </div> --}}

                            {{-- <div class="col-md-6 pl-md-3">
                                <div>
                                    <h6 class="font-weight-bold text-success mb-3">
                                        <i class="fas fa-clipboard-check mr-2"></i> 3. Review Selected Items
                                    </h6> --}}

                                    {{-- <div class="table-responsive border rounded"
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
                                    </div> --}}

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

    {{-- ✅ VIEW DETAILS MODAL --}}
    <div class="modal fade" id="viewPRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice mr-2"></i> PR DETAILS</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">PR Number</label>
                            <p id="view_pr_number" class="font-weight-bold text-primary"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">Requestor</label>
                            <p id="view_requestor" class="font-weight-bold"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-0">Department</label>
                            <p id="view_department" class="font-weight-bold"></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-secondary text-white">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center" width="100">Qty</th>
                                    <th class="text-center" width="120">Unit Cost</th>
                                    <th class="text-center" width="120">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="view_pr_items"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ APPROVE MODAL - FIXED VERSION --}}
    <div class="modal fade" id="approvePRModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-clipboard-check mr-2"></i> Review Purchase Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="approvePRForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="approve_pr_id">

                        {{-- REQUEST DETAILS SECTION --}}
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

                        {{-- PRODUCTS REQUESTED --}}
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

                        {{-- APPROVAL DETAILS - FIXED SECTION --}}
                        <h6 class="font-weight-bold text-warning mb-3">
                            <i class="fas fa-clipboard-check mr-2"></i> APPROVAL DETAILS
                        </h6>
                        <div class="row">
                            {{-- Order Date Section --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Order Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" id="approve_order_date" class="form-control"
                                        required>
                                </div>
                            </div>

                            {{-- Estimated Delivery Date Section --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Estimated Delivery Date</label>
                                    <input type="date" name="estimated_delivery_date"
                                        id="approve_estimated_delivery_date" class="form-control">
                                </div>
                            </div>

                            {{-- Payment Terms Section - NOW PROPERLY INSIDE THE ROW --}}
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

                        {{-- Remarks Section --}}
                        <div class="form-group">
                            <label>Remarks / Notes</label>
                            <textarea name="remarks" id="approve_remarks" class="form-control" rows="3"
                                placeholder="Enter any additional notes or instructions..."></textarea>
                        </div>

                        {{-- Approved By Section --}}
                        <div class="form-group">
                            <label>Approved By</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ auth()->user()->full_name ?? 'N/A' }}" readonly>
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

    {{-- ✅ REJECT CONFIRMATION MODAL --}}
    <div class="modal fade" id="rejectConfirmModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Rejection</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="rejectPRForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="reject_pr_id_confirm">

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            You are about to reject PR <strong id="reject_pr_number_confirm"></strong>
                        </div>

                        <div class="form-group">
                            <label>Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="remarks" id="reject_remarks" class="form-control" rows="4" required
                                placeholder="Please provide a clear reason for rejecting this purchase request..."></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label>Rejected By</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ auth()->user()->full_name ?? 'N/A' }}" readonly>
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
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let selectedItems = [];
            let supplierProducts = [];
            let table;

            // ============================================
            // UPDATE GRAND TOTAL FUNCTION - IDAGDAG MO DITO! ✅
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

            if ($.fn.DataTable.isDataTable('#prTable')) {
                $('#prTable').DataTable().destroy();
            }

            table = $('#prTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('pr.datatable') }}",
                order: [
                    [3, "desc"]
                ],
                columns: [{
                        data: 'request_number',
                        name: 'request_number',
                        orderable: true
                    },
                    {
                        data: 'requestor',
                        name: 'requestor',
                        orderable: true
                    },
                    {
                        data: 'department',
                        name: 'department',
                        orderable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true
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
            // 2. TABLE ROW CLICK - Open PR Details
            // ============================================
            $('#prTable tbody').on('click', 'tr', function(e) {
                // Ignore clicks on action column or badges
                if ($(e.target).closest('td').index() === 4 || $(e.target).closest('.badge').length) {
                    return;
                }

                let data = table.row(this).data();
                if (data && data.id) {
                    openPRDetailsModal(data.id);
                }
            });

            // ============================================
            // 3. BADGE/BUTTON CLICK - Open PR Details
            // ============================================
            $(document).on('click', '.view-pr-badge, .approve-pr-btn', function(e) {
                e.stopPropagation();
                let prId = $(this).data('id');
                openPRDetailsModal(prId);
            });


            // ============================================
            // 4. Open PR Details Modal Function
            // ============================================
            function openPRDetailsModal(prId) {
                $.ajax({
                    url: `/purchase-request/${prId}`,
                    type: 'GET',
                    success: function(res) {
                        console.log('PR Data:', res);

                        // ✅ 1. RESET LAHAT NG FIELDS MUNA
                        $('#approve_pr_id').val(prId);
                        $('#approve_order_date').val('');
                        $('#approve_estimated_delivery_date').val('');
                        $('#approve_remarks').val('');
                        $('select[name="payment_terms"]').val('').trigger('change');

                        // ✅ CLEAR THE TABLE FIRST - ITO YUNG IMPORTANTE!
                        $('#approve_items_list').empty(); // o .html('')

                        // ✅ 2. FILL HEADERS
                        $('#approve_pr_number').text(res.request_number || 'N/A');
                        $('#approve_requestor').text(res.user?.full_name || res.requestor || 'N/A');
                        $('#approve_department').text(res.department?.name || res.department_name ||
                            'N/A');

                        let dateCreated = res.created_at ? new Date(res.created_at)
                            .toLocaleDateString() : 'N/A';
                        $('#approve_date_created').text(dateCreated);

                        // ✅ 3. FILL SUPPLIER DETAILS
                        $('#approve_supplier').text(res.supplier?.name || 'N/A');
                        $('#approve_contact').text(res.supplier?.contact_number || 'N/A');
                        $('#approve_email').text(res.supplier?.email || 'N/A');
                        $('#approve_address').text(res.supplier?.address || 'N/A');

                        // ✅ 4. BUILD PRODUCTS TABLE - FIX PARA SA SKU/NAME
                        let itemsHtml = '';
                        let grandTotal = 0;

                        if (res.items && res.items.length > 0) {
                            res.items.forEach((item, index) => {
                                let subtotal = parseFloat(item.subtotal || 0);
                                grandTotal += subtotal;

                                // ✅ FIX PARA SA "N/A" PRODUCT NAME AT SKU
                                let productName = item.product_name ||
                                    item.supplier_product?.name ||
                                    'Unknown Product';

                                let sku = item.sku ||
                                    item.supplier_product?.system_sku ||
                                    item.supplier_product?.supplier_sku ||
                                    'No SKU';

                                itemsHtml += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>
                                <strong>${productName}</strong>
                                <br><small class="text-muted">SKU: ${sku}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">${item.quantity}</span>
                            </td>
                            <td class="text-right">₱${parseFloat(item.unit_cost).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                            <td class="text-right font-weight-bold">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        </tr>`;
                            });
                        } else {
                            itemsHtml =
                                '<tr><td colspan="5" class="text-center text-muted">No items found</td></tr>';
                        }

                        // ✅ 5. INJECT HTML AT SHOW MODAL
                        $('#approve_items_list').html(itemsHtml);
                        $('#approve_grand_total').text('₱' + grandTotal.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        }));

                        $('#approvePRModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('AJAX Error:', xhr.responseText);
                        Swal.fire('Error', 'Failed to load PR details', 'error');
                    }
                });
            }


            // ============================================
            // 5. APPROVE PR FORM SUBMIT
            // ============================================
            $('#approvePRForm').off('submit').on('submit', function(e) {
                e.preventDefault();

                let prId = $('#approve_pr_id').val();
                let btn = $('#confirmApproveBtn');

                if (btn.prop('disabled')) {
                    return false;
                }

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
                        let msg = xhr.responseJSON?.message || 'Failed to approve PR';
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-circle mr-1"></i> Approve Request');
                    }
                });
            });

            // ============================================
            // 6. REJECT PR - Open confirmation modal
            // ============================================
            $('#confirmRejectBtn').off('click').on('click', function() {
                let prId = $('#approve_pr_id').val();
                let prNumber = $('#approve_pr_number').text();

                $('#approvePRModal').modal('hide');
                $('#reject_pr_id_confirm').val(prId);
                $('#reject_pr_number_confirm').text(prNumber);
                $('#reject_remarks').val('');
                $('#rejectConfirmModal').modal('show');
            });

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
                        let msg = xhr.responseJSON?.message || 'Failed to reject PR';
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-times-circle mr-1"></i> Confirm Rejection');
                    }
                });
            });

            // ============================================
            // 7. GENERATE PR NUMBER ON MODAL OPEN
            // ============================================
            $('#createPRModal').on('show.bs.modal', function() {
                $.get("{{ route('pr.generate-number') }}", function(res) {
                    $('#modal_pr_number').val(res.request_number);
                });
                selectedItems = [];
                supplierProducts = [];
                $('#supplierSelect').val('').trigger('change');
                $('#availableProductsTable tbody').html(
                    '<tr><td colspan="3" class="text-center py-4">Please select a supplier</td></tr>');
                renderSelectedItems();
                updateGrandTotal();
            });

            // ============================================
            // 8. LOAD PRODUCTS WHEN SUPPLIER SELECTED
            // ============================================
            $('#supplierSelect').on('change', function() {
                let supplierId = $(this).val();
                if (!supplierId) {
                    $('#availableProductsTable tbody').html(
                        '<tr><td colspan="3" class="text-center py-4">Please select a supplier</td></tr>'
                    );
                    return;
                }

                // PALITAN MO ITONG $.get PART:
                $.get(`/purchase-request/supplier-products/${supplierId}`, function(products) {
                    // 1. I-save ang data sa global variable
                    supplierProducts = products;

                    // 2. Tawagin ang function na may tbody.empty() sa loob
                    renderAvailableProducts(products);

                }).fail(function(xhr) {
                    console.error('Failed to load products:', xhr);
                    Swal.fire('Error', 'Failed to load products. Please try again.', 'error');
                    $('#availableProductsTable tbody').html(
                        '<tr><td colspan="3" class="text-center text-danger py-4">Failed to load products</td></tr>'
                    );
                });
            });

            // 1. FUNCTION PARA SA PAG-DISPLAY NG PRODUCTS SA KALIWA (MODAL)
            function renderAvailableProducts(products) {
                const tbody = $('#availableProductsTable tbody');

                // LINISIN ANG TABLE BODY PARA WALANG DUPLICATE DISPLAY
                tbody.empty();

                if (!products || products.length === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">No products found.</td></tr>');
                    return;
                }

                let html = '';
                products.forEach(product => {
                    // Check kung ang ID na ito ay nasa selectedItems na
                    let isAdded = selectedItems.some(item => parseInt(item.id) === parseInt(product.id));

                    let btn = isAdded ?
                        `<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check"></i> Added</button>` :
                        `<button class="btn btn-sm btn-primary add-product-btn" 
                data-id="${product.id}" 
                data-name="${product.name}" 
                data-price="${product.cost_price}">
                <i class="fas fa-plus"></i> Add
            </button>`;

                    html += `
            <tr>
                <td class="small">${product.name}</td>
                <td class="text-right">₱${parseFloat(product.cost_price).toFixed(2)}</td>
                <td class="text-center">${btn}</td>
            </tr>
        `;
                });
                tbody.append(html);
            }

            $(document).off('click', '.add-product-btn').on('click', '.add-product-btn', function(e) {
                e.preventDefault(); // Iwasan ang default behavior

                let btn = $(this);
                let productId = parseInt(btn.data('id'));
                let name = btn.data('name');
                let price = parseFloat(btn.data('price'));

                // A. DOUBLE CHECK: Kung nandoon na sa array, huwag na ituloy
                if (selectedItems.some(item => parseInt(item.id) === productId)) {
                    return;
                }

                // B. ADD SA ARRAY
                selectedItems.push({
                    id: productId,
                    name: name,
                    price: price,
                    quantity: 1,
                    subtotal: price
                });

                // C. UPDATE ANG MGA DISPLAY
                renderSelectedItems(); // Update kanang table
                updateGrandTotal(); // Update total amount

                // D. I-DISABLE LANG YUNG PININDOT NA BUTTON (Imbes na i-render ulit lahat agad)
                btn.removeClass('btn-primary').addClass('btn-secondary').prop('disabled', true).html(
                    '<i class="fas fa-check"></i> Added');
            });

            // ============================================
            // 10. RENDER SELECTED ITEMS
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
                        </tr>
                    `);
                });
            }


            // ============================================
            // 11. UPDATE QUANTITY/COST
            // ============================================
            $(document).on('input', '.qty-input, .unit-cost-input', function() {
                let index = $(this).data('index');

                // ✅ ADD THIS: Safety Check
                // Kung wala yung item sa array (halimbawa na-clear na), stop na agad para iwas error
                if (!selectedItems[index]) {
                    return;
                }

                // Ito yung dating code mo, ituloy lang sa baba...
                let qty = parseInt($(`.qty-input[data-index="${index}"]`).val()) || 0;
                let cost = parseFloat($(`.unit-cost-input[data-index="${index}"]`).val()) || 0;

                selectedItems[index].quantity = qty;
                selectedItems[index].price = cost;
                selectedItems[index].subtotal = qty * cost;

                // Optional: Update display values without re-rendering everything constantly if causes lag
                // Pero sa ngayon, okay na yung logic mo sa baba:
                renderSelectedItems();
                updateGrandTotal();
            });

            // ============================================
            // 12. REMOVE ITEM
            // ============================================

            $(document).off('click', '.remove-item').on('click', '.remove-item', function() {
                let index = $(this).data('index');

                // Alisin sa array
                selectedItems.splice(index, 1);

                // I-refresh ang mga display
                renderSelectedItems();
                updateGrandTotal();

                // ✅ I-refresh ang listahan sa kaliwa para bumalik sa "Add" yung "Added"
                renderAvailableProducts(supplierProducts);
            });

            // ============================================
            // 13. SUBMIT PR FORM
            // ============================================
            $('#savePRBtn').off('click').on('click', function(e) {
                e.preventDefault();

                if (selectedItems.length === 0) {
                    Swal.fire('Warning', 'Please add items first.', 'warning');
                    return false;
                }

                let btn = $(this);

                if (btn.prop('disabled')) {
                    return false;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                let formData = new FormData();
                formData.append('_token', $('input[name="_token"]').val());
                formData.append('supplier_id', $('#supplierSelect').val());

                selectedItems.forEach(item => {
                    formData.append(`products[${item.id}][quantity]`, item.quantity);
                    formData.append(`products[${item.id}][unit_cost]`, item.price);
                });

                $.ajax({
                    url: "{{ route('pr.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#createPRModal').modal('hide');
                        Swal.fire('Success', 'Purchase Request Saved!', 'success');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON?.message || 'Error saving PR';
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-check-circle mr-1"></i> Submit Purchase Request'
                        );
                    }
                });
            });
        }); // ✅ SINGLE CLOSING FOR $(document).ready()
    </script>
@endpush

@push('css')
    <style>
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

        #availableProductsTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        input[type="number"].text-center {
            text-align: center;
        }

        .border {
            border: 1px solid #dee2e6 !important;
        }

        .rounded {
            border-radius: 0.25rem !important;
        }

        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Action buttons styling */
        .btn-group {
            display: inline-flex;
            gap: 4px;
        }

        .btn-group .btn {
            white-space: nowrap;
        }

        /* Table borderless in modals */
        .table-borderless td {
            border: none;
            padding: 0.25rem 0;
        }

        /* Modal section headers */
        .modal-body h6 {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
    </style>
@endpush

feb 12