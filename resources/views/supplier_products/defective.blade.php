@extends('layouts.adminlte')

@section('subtitle', 'Defective Inventory')
@section('content_header_title', 'Inventory Accountability')
@section('content_header_subtitle', 'Defective & Damaged Items')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-danger shadow-sm">
                    <div class="card-header d-flex align-items-center bg-white">
                        <div class="card-title mb-0 text-uppercase font-weight-bold text-danger" style="letter-spacing: 0.05em;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Defective Inventory List
                        </div>
                        <div class="ml-auto">
                            <span class="badge badge-danger px-3 py-2">Total Defective Items: <span id="defectiveCount">-</span></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info border-left-info shadow-sm mb-4">
                            <i class="fas fa-info-circle mr-2"></i> <strong>Accountability Note:</strong> 
                            This list contains items marked as damaged. Traceability links back to the original Purchase Order and Supplier for warranty or refund claims.
                        </div>

                        <div class="table-responsive">
                            <table id="defectiveTable" class="table table-bordered table-hover w-100">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Serial Number</th>
                                        <th>Product Name</th>
                                        <th>Supplier Source</th>
                                        <th>PO Number</th>
                                        <th>Damage Remarks</th>
                                        <th>Date Flagged</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var tableId = '#defectiveTable';

            var table = $(tableId).DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('inventory.defective.data') }}",
                columns: [
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'supplier_name', name: 'supplier_name' },
                    { data: 'po_number', name: 'po_number', className: 'text-center' },
                    { data: 'remarks', name: 'remarks' },
                    { data: 'reported_at', name: 'reported_at', className: 'text-center' },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center' 
                    }
                ],
                order: [[5, 'desc']],
                drawCallback: function(settings) {
                    $('#defectiveCount').text(settings._iRecordsTotal);
                }
            });

            // ✅ Restore Logic
            $(document).on('click', '.restore-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Restore this item?',
                    text: "The item will be moved back to standard available stock.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, restore it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('/inventory/defective') }}/" + id + "/restore",
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Restored!', res.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'System error while restoring item.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
