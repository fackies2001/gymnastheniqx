// ============================================
// DAILY REPORTS - INITIALIZATION
// ============================================
$(document).ready(function() {
    console.log('📊 Daily Reports Initialized');

    // Initialize DataTable
    if ($('#dailyReportTable').length) {
        initializeDailyReportTable();
    }

    // Date picker handler
    $('#reportDate').on('change', function() {
        const selectedDate = $(this).val();
        console.log('📅 Date changed:', selectedDate);
        loadDailyData(selectedDate);
    });

    // Print report button
    $('#printReport').on('click', function() {
        window.print();
    });

    // Export button
    $('#exportReport').on('click', function() {
        const date = $('#reportDate').val();
        window.location.href = `/reports/daily/export?date=${date}`;
    });

    console.log('✅ Daily Reports Ready');
});

// ============================================
// FILTER BY STATUS FUNCTION (MISSING!)
// ============================================
function filterByStatus(status) {
    console.log('🔍 Filtering by status:', status);
    
    const table = $('#dailyReportTable').DataTable();
    
    // Clear previous search
    table.search('').draw();
    
    // Apply filter based on status
    if (status === 'all') {
        // Show all records
        table.column(5).search('').draw(); // Column 5 is status
    } else {
        // Filter by specific status
        table.column(5).search(status, true, false).draw();
    }
    
    // Update active button
    $('.filter-btn-group .btn').removeClass('active');
    $(`.filter-btn-group .btn[onclick*="${status}"]`).addClass('active');
}

// ============================================
// INITIALIZE DAILY REPORT DATATABLE
// ============================================
function initializeDailyReportTable() {
    const date = $('#reportDate').val() || new Date().toISOString().split('T')[0];
    
    $('#dailyReportTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/reports/daily/data',
            type: 'GET',
            data: function(d) {
                d.date = $('#reportDate').val();
                return d;
            },
            error: function(xhr, error, code) {
                console.error('❌ DataTable Error:', error);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            {
                data: 'image',
                name: 'image',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (data) {
                        return `<img src="${data}" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">`;
                    }
                    return '<div style="width: 50px; height: 50px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-muted"></i></div>';
                }
            },
            {
                data: 'product_name',
                name: 'product_name',
                render: function(data) {
                    return data; // Already formatted from backend
                }
            },
            {
                data: 'category_name',
                name: 'category_name',
                render: function(data) {
                    return data; // Already has badge HTML
                }
            },
            {
                data: 'traceability',
                name: 'traceability',
                render: function(data) {
                    return data; // Already formatted
                }
            },
            {
                data: 'quantity',
                name: 'quantity',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge badge-info" style="font-size: 14px; padding: 6px 12px;">${data}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    let badgeClass = 'badge-secondary';
                    let icon = 'fa-info-circle';
                    
                    if (data === 'Received') {
                        badgeClass = 'badge-success';
                        icon = 'fa-check-circle';
                    } else if (data === 'Outflow') {
                        badgeClass = 'badge-primary';
                        icon = 'fa-arrow-circle-right';
                    } else if (data === 'Damaged') {
                        badgeClass = 'badge-danger';
                        icon = 'fa-times-circle';
                    } else if (data === 'Low Stock') {
                        badgeClass = 'badge-warning';
                        icon = 'fa-exclamation-triangle';
                    }
                    
                    return `<span class="badge ${badgeClass}"><i class="fas ${icon}"></i> ${data}</span>`;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...',
            emptyTable: 'No inventory activity found for this date',
            zeroRecords: 'No matching records found'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        responsive: true,
        autoWidth: false
    });
}

// ============================================
// RELOAD DATA WHEN DATE CHANGES
// ============================================
function loadDailyData(date) {
    if ($('#dailyReportTable').length) {
        const table = $('#dailyReportTable').DataTable();
        table.ajax.reload();
    }
}

// ============================================
// APPROVE/REJECT FUNCTIONS
// ============================================
function approvePR(prId) {
    if (!confirm('Are you sure you want to approve this Purchase Request?')) return;
    
    $.ajax({
        url: `/reports/approve/${prId}/pr`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#dailyReportTable').DataTable().ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error approving request');
        }
    });
}

function rejectPR(prId) {
    if (!confirm('Are you sure you want to reject this Purchase Request?')) return;
    
    $.ajax({
        url: `/reports/reject/${prId}/pr`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.warning(response.message);
                $('#dailyReportTable').DataTable().ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error rejecting request');
        }
    });
}

function approvePO(poId) {
    if (!confirm('Are you sure you want to approve this Purchase Order?')) return;
    
    $.ajax({
        url: `/reports/approve/${poId}/po`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#dailyReportTable').DataTable().ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error approving order');
        }
    });
}

function rejectPO(poId) {
    if (!confirm('Are you sure you want to reject this Purchase Order?')) return;
    
    $.ajax({
        url: `/reports/reject/${poId}/po`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.warning(response.message);
                $('#dailyReportTable').DataTable().ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error rejecting order');
        }
    });
}

// Make functions globally available
window.filterByStatus = filterByStatus;
window.approvePR = approvePR;
window.rejectPR = rejectPR;
window.approvePO = approvePO;
window.rejectPO = rejectPO;