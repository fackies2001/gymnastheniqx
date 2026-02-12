class PurchaseRequestManager {
    /*
    constructor() {
        this.selectedItems = [];
        this.reviewTableInstance = null;
        this.init();
    }

    init() {
        // ✅ Wait for DOM and jQuery to be ready
        if (typeof $ === 'undefined') {
            console.error('❌ jQuery is not loaded!');
            return;
        }

        // ✅ Check if DataTable plugin is available
        if (!$.fn.DataTable) {
            console.error('❌ DataTables plugin is not loaded!');
            return;
        }

        this.setupEventListeners();
        this.initializeMainTable();
    }

    setupEventListeners() {
        const createBtn = document.getElementById('createPRBtn');
        if (createBtn) {
            createBtn.addEventListener('click', () => this.showCreateModal());
        }

        $('#supplier_id').on('change', (e) => {
            this.loadSupplierProducts(e.target.value);
        });

        $(document).on('click', '.select-product-btn', (e) => {
            const btn = $(e.currentTarget);
            this.addProductToRequest(
                btn.data('product-id'),
                btn.data('product-name'),
                btn.data('cost-price')
            );
        });

        $(document).on('change', '.change-qty', (e) => {
            const input = $(e.currentTarget);
            this.updateQty(input.data('id'), input.val());
        });

        $(document).on('click', '.remove-item-btn', (e) => {
            const btn = $(e.currentTarget);
            this.removeItem(btn.data('id'));
        });

        $('#savePRForm').on('submit', (e) => {
            e.preventDefault();
            this.savePurchaseRequest();
        });

        $(document).on('click', '.view-pr-details', (e) => {
            const id = $(e.currentTarget).data('id');
            this.showApprovalModal(id);
        });

        $(document).on('click', '.view-po-details, .view-po', (e) => {
            const id = $(e.currentTarget).data('id');
            if(id) {
                this.showPODetailsAfterApproval(id);
            }
        });

        $(document).on('click', '#makeOrderBtn', function() {
            const poId = $(this).attr('data-id');
            if(poId) {
                window.location.href = `/purchase-order/scan/${poId}`;
            } else {
                Swal.fire('Error', 'Purchase Order ID not found.', 'error');
            }
        });

        $('#btnApprove').on('click', () => this.processApproval());
        $('#btnReject').on('click', () => this.processRejection());
    }

    initializeMainTable() {
        if ($('#prTable').length && $.fn.DataTable) {
            // ✅ Destroy existing instance if any
            if ($.fn.DataTable.isDataTable('#prTable')) {
                $('#prTable').DataTable().destroy();
            }

            $('#prTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/purchase-request/datatable',
                columns: [
                    { data: 'request_number', name: 'request_number' },
                    { data: 'requestor', name: 'requestor' },
                    { data: 'department', name: 'department' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']],
                responsive: true,
                autoWidth: false,
                searching: true,
                drawCallback: () => {
                    $('.review-pr-btn').off('click').on('click', (e) => {
                        const prId = $(e.currentTarget).data('pr-id');
                        this.showApprovalModal(prId);
                    });
                }
            });
        }
    }

    async showCreateModal() {
        try {
            const response = await fetch('/purchase-request/generate-number');
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}. Check PurchaseRequestController::generatePRNumber()`);
            }

            const data = await response.json();
            
            // ✅ Better error handling
            if (!data.success) {
                throw new Error(data.message || 'Failed to generate PR number');
            }

            const pr_val = data.request_number || data.pr_number;
            
            if (!pr_val) {
                throw new Error('PR number not returned from server');
            }
            
            $('#pr_number').val(pr_val);
            $('#supplier_id').val('').trigger('change');
            this.selectedItems = [];
            this.renderReviewTable();
            
            // ✅ Use Bootstrap 4 modal syntax
            $('#createPRModal').modal('show');
        } catch (error) {
            console.error('❌ Error details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hindi makagawa ng PR Number: ' + error.message,
                confirmButtonText: 'OK'
            });
        }
    }

    async loadSupplierProducts(supplierId) {
        const list = $('#supplier-products-list');
        if (!supplierId) {
            list.html('<tr><td colspan="3" class="text-center text-muted py-4 small">Select a supplier first</td></tr>');
            return;
        }

        try {
            list.html('<tr><td colspan="3" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading products...</td></tr>');
            
            const response = await fetch(`/suppliers/${supplierId}/products`);
            
            if (!response.ok) {
                throw new Error('Failed to load products');
            }

            const products = await response.json();
            this.displaySupplierProducts(products);
        } catch (error) {
            console.error('❌ Error loading products:', error);
            list.html('<tr><td colspan="3" class="text-center text-danger py-4 small">Error loading products</td></tr>');
        }
    }

    displaySupplierProducts(products) {
        // ✅ Destroy existing DataTable instance first
        if ($.fn.DataTable.isDataTable('#modalProductsTable')) {
            $('#modalProductsTable').DataTable().destroy();
        }

        const list = $('#supplier-products-list').empty();

        if (!products || products.length === 0) {
            list.append('<tr><td colspan="3" class="text-center text-muted py-4">No products available</td></tr>');
            return;
        }

        products.forEach(p => {
            list.append(`
                <tr>
                    <td class="small font-weight-bold align-middle">${p.name || 'Unknown'}</td>
                    <td class="small align-middle text-center">₱${parseFloat(p.cost_price || 0).toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-success select-product-btn" 
                                data-product-id="${p.id}" 
                                data-product-name="${p.name}" 
                                data-cost-price="${p.cost_price}">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // ✅ Re-initialize DataTable
        $('#modalProductsTable').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 25],
            autoWidth: false,
            ordering: true,
            language: {
                emptyTable: "No products available",
                zeroRecords: "No matching products found"
            }
        });
    }

    addProductToRequest(id, name, price) {
        const existing = this.selectedItems.find(item => item.id == id);
        if (existing) {
            existing.qty++;
        } else {
            this.selectedItems.push({ id, name, price: parseFloat(price), qty: 1 });
        }
        this.renderReviewTable();
    }

    updateQty(id, newQty) {
        const item = this.selectedItems.find(i => i.id == id);
        if (item) {
            item.qty = Math.max(1, parseInt(newQty) || 1);
            this.renderReviewTable();
        }
    }

    removeItem(id) {
        this.selectedItems = this.selectedItems.filter(i => i.id != id);
        this.renderReviewTable();
    }

    renderReviewTable() {
        if (this.reviewTableInstance) {
            this.reviewTableInstance.destroy();
            this.reviewTableInstance = null;
        }

        const body = $('#selectedProductsBody').empty();
        let grandTotal = 0;

        if (this.selectedItems.length === 0) {
            body.append('<tr><td colspan="5" class="text-center text-muted py-5 small">No products added yet.</td></tr>');
            $('#grandTotal').text('₱0.00');
        } else {
            this.selectedItems.forEach(item => {
                const subtotal = item.price * item.qty;
                grandTotal += subtotal;
                body.append(`
                    <tr class="small">
                        <td class="align-middle pl-3 font-weight-bold">${item.name}</td>
                        <td class="text-right align-middle">₱${item.price.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="align-middle text-center">
                            <input type="number" class="form-control form-control-sm text-center change-qty mx-auto" 
                                   style="width: 70px;" min="1" value="${item.qty}" data-id="${item.id}">
                        </td>
                        <td class="text-right align-middle font-weight-bold">₱${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-xs btn-danger remove-item-btn" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            $('#grandTotal').text(`₱${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`);
        }
    }

    async savePurchaseRequest() {
        if (this.selectedItems.length === 0) {
            Swal.fire('Warning', 'Please add at least one product.', 'warning');
            return;
        }

        const supplierId = $('#supplier_id').val();
        if (!supplierId) {
            Swal.fire('Warning', 'Please select a supplier.', 'warning');
            return;
        }

        const requestNumber = $('#pr_number').val();
        if (!requestNumber) {
            Swal.fire('Error', 'PR Number is missing.', 'error');
            return;
        }

        const products = this.selectedItems.map(item => ({
            product_id: item.id,
            quantity: item.qty,
            unit_cost: item.price
        }));

        const payload = {
            supplier_id: supplierId,
            request_number: requestNumber,
            products: products
        };

        console.log('📤 Sending PR Data:', payload);

        try {
            const response = await fetch('/purchase-request/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            console.log('📥 Server Response:', result);

            if (result.success) {
                $('#createPRModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Purchase Request created successfully!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#prTable').DataTable().ajax.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to save Purchase Request',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {
            console.error('❌ Save PR Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving: ' + error.message,
                confirmButtonText: 'OK'
            });
        }
    }

    async showApprovalModal(prId) {
        try {
            console.log('📋 Fetching PR details for ID:', prId);

            const response = await fetch(`/purchase-request/${prId}`);
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }

            const pr = await response.json();

            console.log('📥 PR Data:', pr);

            $('#approval_pr_id').val(pr.id);
            $('#view_pr_number').text(pr.request_number || 'N/A');
            
            $('#view_supplier').text(pr.supplier?.name || 'N/A');
            
            const contactPerson = pr.supplier?.contact_person 
                || pr.supplier_contact_person 
                || 'N/A';
            $('#view_contact').text(contactPerson);
            
            const email = pr.supplier?.email 
                || pr.supplier_email 
                || 'N/A';
            $('#view_email').text(email);

            let requestorName = 'Unknown';
            if (pr.user?.full_name) {
                requestorName = pr.user.full_name;
            } else if (pr.requestor_name) {
                requestorName = pr.requestor_name;
            }
            $('#view_requestor').text(requestorName);
            
            let departmentName = 'N/A';
            if (pr.department?.name) {
                departmentName = pr.department.name;
            } else if (pr.department_name) {
                departmentName = pr.department_name;
            } else if (pr.user?.department?.name) {
                departmentName = pr.user.department.name;
            }
            $('#view_dept').text(departmentName);

            const itemsBody = $('#modalItemsTable tbody').empty();
            let total = 0;
            
            if (pr.items && pr.items.length > 0) {
                pr.items.forEach(item => {
                    const subtotal = parseFloat(item.subtotal || 0);
                    total += subtotal;
                    itemsBody.append(`
                        <tr>
                            <td class="small">${item.supplier_product?.name || item.product_name || 'Unknown'}</td>
                            <td class="text-center small">${item.quantity || 0}</td>
                            <td class="text-right small">₱${parseFloat(item.unit_cost || 0).toLocaleString()}</td>
                            <td class="text-right small font-weight-bold">₱${subtotal.toLocaleString()}</td>
                        </tr>
                    `);
                });
            } else {
                itemsBody.append('<tr><td colspan="4" class="text-center text-muted">No items</td></tr>');
            }
            
            $('#view_total').text(`₱${total.toLocaleString()}`);

            // Reset form fields
            $('#admin_remarks').val('');
            $('#order_date').val('');
            $('#estimated_delivery_date').val('');
            $('#payment_terms').val('');

            // ✅ Show modal using Bootstrap 4 syntax
            $('#approvalModal').modal('show');
        } catch (error) {
            console.error('❌ Error fetching PR details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch details: ' + error.message,
                confirmButtonText: 'OK'
            });
        }
    }

    async processApproval() {
        const id = $('#approval_pr_id').val();
        
        if (!id) {
            Swal.fire('Error', 'PR ID is missing', 'error');
            return;
        }

        // ✅ Collect form data manually to ensure correct types
        const data = {
            order_date: $('#order_date').val() || null,
            estimated_delivery_date: $('#estimated_delivery_date').val() || null,
            payment_terms: $('#payment_terms').val() || null,
            remarks: $('#admin_remarks').val() || null
        };

        console.log('✅ Approval Data Being Sent:', data);

        const result = await Swal.fire({
            title: 'Confirm Approval',
            text: "Approve this request and generate PO?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/purchase-request/approve/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(data)
                });

                const res = await response.json();
                console.log('📥 Approval Response:', res);

                if (res.success) {
                    $('#approvalModal').modal('hide');
                    
                    Swal.fire({
                        title: 'Approved!',
                        text: res.message || 'Purchase Request approved successfully!',
                        icon: 'success',
                        confirmButtonText: 'View PO Details'
                    }).then(() => {
                        if(res.po_id) {
                            this.showPODetailsAfterApproval(res.po_id);
                        } else {
                            $('#prTable').DataTable().ajax.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', res.message || 'Approval failed.', 'error');
                }
            } catch (error) {
                console.error('❌ Approval error:', error);
                Swal.fire('Error', 'An internal error occurred: ' + error.message, 'error');
            }
        }
    }

    async processRejection() {
        const id = $('#approval_pr_id').val();
        
        if (!id) {
            Swal.fire('Error', 'PR ID is missing', 'error');
            return;
        }

        const remarks = $('#admin_remarks').val().trim();

        const result = await Swal.fire({
            title: 'Reject Request?',
            text: remarks ? `Reason: ${remarks}` : "Are you sure you want to reject this request?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/purchase-request/reject/${id}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
                    },
                    body: JSON.stringify({ remarks: remarks || null })
                });

                const res = await response.json();

                if (res.success) {
                    $('#approvalModal').modal('hide');
                    Swal.fire('Rejected', res.message || 'Request has been rejected.', 'info');
                    $('#prTable').DataTable().ajax.reload();
                } else {
                    Swal.fire('Error', res.message || 'Rejection failed.', 'error');
                }
            } catch (error) {
                console.error('❌ Rejection error:', error);
                Swal.fire('Error', 'Rejection failed: ' + error.message, 'error');
            }
        }
    }

    async showPODetailsAfterApproval(poId) {
        try {
            const response = await fetch(`/purchase-order/details/${poId}`);
            if (!response.ok) throw new Error('PO Not Found');
            
            const po = await response.json();

            $('#disp_po_number').text(po.po_number);
            $('#disp_supplier').text(po.supplier?.name || 'N/A');
            $('#disp_delivery').text(po.delivery_date);
            $('#makeOrderBtn').attr('data-id', po.id);

            const itemsBody = $('#poItemsTable tbody').empty();
            if (po.items && po.items.length > 0) {
                po.items.forEach(item => {
                    itemsBody.append(`
                        <tr>
                            <td>${item.product?.name || 'Unknown'}</td>
                            <td class="text-center">${item.quantity || 0}</td>
                            <td class="text-center"><span class="badge badge-warning">0</span></td>
                            <td class="text-right">₱${parseFloat(item.subtotal || 0).toLocaleString()}</td>
                        </tr>
                    `);
                });
            }

            $('#poDetailsModal').modal('show');
        } catch (error) {
            console.error('❌ Error loading PO:', error);
            Swal.fire('Error', 'Could not load PO details: ' + error.message, 'error');
        }
    }
}

// ✅ Wait for DOM and all scripts to be ready
$(document).ready(function() {
    console.log('✅ Document Ready');
    console.log('✅ jQuery Version:', $.fn.jquery);
    console.log('✅ DataTables Available:', typeof $.fn.DataTable !== 'undefined');
    console.log('✅ Bootstrap Available:', typeof $.fn.modal !== 'undefined');
    
    // ✅ Initialize the manager
    window.manager = new PurchaseRequestManager();
    console.log('✅ PurchaseRequestManager initialized');
});

feb 12