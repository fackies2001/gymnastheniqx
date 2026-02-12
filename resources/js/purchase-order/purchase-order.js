// Purchase Order Management
class PurchaseOrderManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // ✅ View PO Details - Using jQuery delegate for dynamic elements
        $(document).on('click', '.view-po-details', (e) => {
            e.preventDefault();
            const poId = $(e.currentTarget).data('id');
            this.viewPODetails(poId);
        });

        // Make Order (Scan) Button
        $(document).on('click', '#makeOrderBtn', () => {
            const poId = $('#poDetailsModal').data('po-id');
            if (poId) {
                window.location.href = `/purchase-order/scan/${poId}`;
            }
        });
    }

    async viewPODetails(poId) {
        try {
            // Show loading state
            this.showLoadingModal();

            // ✅ Use correct endpoint
            const response = await fetch(`/purchase-order/details/${poId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayPODetails(data, poId);
            } else {
                alert('Error: ' + data.message);
                $('#poDetailsModal').modal('hide');
            }
        } catch (error) {
            console.error('Error loading PO details:', error);
            alert('Error loading purchase order details');
            $('#poDetailsModal').modal('hide');
        }
    }

    showLoadingModal() {
        $('#disp_po_number').text('Loading...');
        $('#disp_supplier').text('...');
        $('#disp_contact_person').text('...');
        $('#disp_email').text('...');
        $('#disp_requested_by').text('...');
        $('#disp_department').text('...');
        $('#disp_approved_by').text('...');
        $('#poProductsBody').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
        $('#poDetailsModal').modal('show');
    }

    displayPODetails(data, poId) {
        // Store PO ID for Make Order button
        $('#poDetailsModal').data('po-id', poId);

        // ✅ Populate Header Info
        $('#disp_po_number').text(data.po_number || 'N/A');
        
        // ✅ Request Details Section
        $('#disp_supplier').text(data.supplier?.name || 'N/A');
        $('#disp_contact_person').text(data.supplier?.contact_person || 'N/A');
        $('#disp_email').text(data.supplier?.email || 'N/A');
        
        $('#disp_requested_by').text(data.requested_by?.name || 'N/A');
        $('#disp_department').text(data.department || 'General');
        $('#disp_approved_by').text(data.approved_by?.name || 'N/A');
        
        // ✅ Products Table
        const tbody = $('#poProductsBody');
        tbody.empty();
        
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const row = `
                    <tr>
                        <td>${item.product?.name || 'Unknown Product'}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">${item.quantity}</span>
                        </td>
                        <td class="text-center">₱${this.formatNumber(item.unit_cost)}</td>
                        <td class="text-right">₱${this.formatNumber(item.subtotal)}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        } else {
            tbody.html('<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>');
        }
        
        // ✅ Grand Total
        $('#disp_grand_total').text('₱' + this.formatNumber(data.grand_total));
        
        // ✅ PO Generation Details
        $('#disp_order_date').text(data.order_date || 'N/A');
        $('#disp_delivery_date').text(data.delivery_date || 'N/A');
        $('#disp_payment_terms').text(data.payment_terms || 'N/A');
        $('#disp_remarks').text(data.remarks || 'No remarks provided.');
        
        // Show modal (already shown in loading state)
    }

    formatNumber(num) {
        if (!num) return '0.00';
        return parseFloat(num).toFixed(2);
    }
}

// Initialize on page load
$(document).ready(function() {
    new PurchaseOrderManager();
});