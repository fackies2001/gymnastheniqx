// Purchase Order Management
/*
class PurchaseOrderManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // ✅ FIXED: Match sa blade class at data attribute
        $(document).on('click', '.view-po-details', (e) => {
            e.preventDefault();
            const poId = $(e.currentTarget).data('id');
            this.viewPODetails(poId);
        });

        // Make Order Button
        $(document).on('click', '#makeOrderBtn', () => {
            const poId = $('#poDetailsModal').data('po-id');
            window.location.href = `/purchase-order/scan/${poId}`;
        });
    }

    async viewPODetails(poId) {
        try {
            // ✅ FIXED: Use correct endpoint
            const response = await fetch(`/purchase-order/details/${poId}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayPODetails(data, poId);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error('Error loading PO details:', error);
            alert('Error loading purchase order details');
        }
    }

    displayPODetails(data, poId) {
        // ✅ FIXED: Use correct element IDs from blade
        $('#disp_po_number').text(data.po_number);
        $('#disp_supplier').text(data.supplier.name);
        $('#disp_delivery').text(data.delivery_date);
        
        // Store PO ID for Make Order button
        $('#poDetailsModal').data('po-id', poId);
        
        // Populate items table
        const tbody = $('#poItemsTable tbody');
        tbody.empty();
        
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const row = `
                    <tr>
                        <td class="pl-3">${item.product.name}</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-center">0</td>
                        <td class="text-right pr-3">₱${item.subtotal}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        } else {
            tbody.html('<tr><td colspan="4" class="text-center">No items found</td></tr>');
        }
        
        // Show modal
        $('#poDetailsModal').modal('show');
    }
}

// Initialize on page load
$(document).ready(function() {
    new PurchaseOrderManager();
});