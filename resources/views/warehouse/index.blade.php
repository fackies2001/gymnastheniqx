@extends('layouts.adminlte')

@section('subtitle', 'Warehouse')
@section('content_header_title', 'Warehouse')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title mb-0" style="letter-spacing: 0.1ch; text-transform: uppercase;">Warehouse Details
                    </div>
                    <button class="btn btn-sm btn-primary ml-auto" data-toggle="modal" data-target="#createwarehouse">
                        Create Warehouse
                    </button>
                </div>
                <div class="card-body">
                    <table id="sampleId" class="table table-bordered table-striped dataTable w-100">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Warehouse</th>
                                <th>Address</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Assignee</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($warehouses as $warehouse)
                                <tr>
                                    <td>{{ $warehouse->name }}</td>
                                    <td>{{ $warehouse->address }}</td>
                                    <td>{{ $warehouse->email }}</td>
                                    <td>{{ $warehouse->phone }}</td>
                                    <td>{{ $warehouse->assignee }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-success edit-warehouse" data-toggle="modal"
                                            data-target="#warehouseModal" data-id="{{ $warehouse->id }}"
                                            data-name="{{ $warehouse->name }}" data-email="{{ $warehouse->email }}"
                                            data-phone="{{ $warehouse->phone }}" data-address="{{ $warehouse->address }}"
                                            data-assignee="{{ $warehouse->assignee }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-warehouse"
                                            data-id="{{ $warehouse->id }}" data-name="{{ $warehouse->name }}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: CREATE --}}
    <div class="modal fade" id="createwarehouse" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create Warehouse</h5>
                </div>
                <div class="modal-body">
                    <form id="createwarehouseForm">
                        @csrf
                        <div class="form-group"><label>Warehouse Name</label><input type="text" id="createName"
                                class="form-control" required></div>
                        <div class="form-group"><label>Email</label><input type="email" id="createEmail"
                                class="form-control"></div>
                        <div class="form-group"><label>Contact</label><input type="text" id="createPhone"
                                class="form-control"></div>
                        <div class="form-group"><label>Address</label><input type="text" id="createAddress"
                                class="form-control"></div>
                        <div class="form-group"><label>Assignee</label><input type="text" id="createAssignee"
                                class="form-control"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="createwarehouseSubmit">Submit</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: EDIT --}}
    <div class="modal fade" id="warehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Warehouse Details</h5>
                </div>
                <div class="modal-body">
                    <form id="editWarehouseForm">
                        @csrf
                        <input type="hidden" id="editId">
                        <div class="form-group"><label>Warehouse Name</label><input type="text" class="form-control"
                                id="editName"></div>
                        <div class="form-group"><label>Email</label><input type="email" class="form-control"
                                id="editEmail"></div>
                        <div class="form-group"><label>Contact</label><input type="text" class="form-control"
                                id="editPhone"></div>
                        <div class="form-group"><label>Address</label><input type="text" class="form-control"
                                id="editAddress"></div>
                        <div class="form-group"><label>Assignee</label><input type="text" class="form-control"
                                id="editAssignee"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="updateWarehouseSubmit">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // --- 1. DATATABLES INITIALIZATION ---
            if ($.fn.DataTable.isDataTable('#sampleId')) {
                $('#sampleId').DataTable().destroy();
            }

            var table = $('#sampleId').DataTable({
                "responsive": true,
                "autoWidth": false,
                "ordering": true,
                "order": [
                    [0, "asc"]
                ],
                "columnDefs": [{
                    "orderable": false,
                    "targets": 5
                }]
            });

            // --- 2. CREATE WAREHOUSE AJAX ---
            $('#createwarehouseSubmit').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                let btn = $(this);

                if (!$('#createName').val().trim()) {
                    Swal.fire('Error', 'Warehouse name is required', 'error');
                    return;
                }

                btn.prop('disabled', true).text('Saving...');

                let data = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: $('#createName').val().trim(),
                    email: $('#createEmail').val().trim(),
                    phone: $('#createPhone').val().trim(),
                    address: $('#createAddress').val().trim(),
                    assignee: $('#createAssignee').val().trim(),
                };

                $.ajax({
                    url: "{{ route('warehouse.store') }}",
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Warehouse created successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Create Error:', xhr.responseJSON);

                        let errorMsg = 'Failed to save warehouse';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }

                        Swal.fire('Error', errorMsg, 'error');
                        btn.prop('disabled', false).text('Submit');
                    }
                });
            });

            // --- 3. POPULATE EDIT MODAL ---
            $(document).on('click', '.edit-warehouse', function() {
                $('#editId').val($(this).data('id'));
                $('#editName').val($(this).data('name'));
                $('#editEmail').val($(this).data('email'));
                $('#editPhone').val($(this).data('phone'));
                $('#editAddress').val($(this).data('address'));
                $('#editAssignee').val($(this).data('assignee'));
            });

            // --- 4. UPDATE WAREHOUSE AJAX ---
            $('#updateWarehouseSubmit').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                let btn = $(this);

                if (!$('#editName').val().trim()) {
                    Swal.fire('Error', 'Warehouse name is required', 'error');
                    return;
                }

                btn.prop('disabled', true).text('Updating...');

                let data = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: $('#editId').val(),
                    name: $('#editName').val().trim(),
                    email: $('#editEmail').val().trim(),
                    phone: $('#editPhone').val().trim(),
                    address: $('#editAddress').val().trim(),
                    assignee: $('#editAssignee').val().trim(),
                };

                $.ajax({
                    url: "{{ route('warehouse.update') }}",
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: 'Warehouse updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#warehouseModal').modal('hide');
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Update Error:', xhr.responseJSON);

                        let errorMsg = 'Update failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        }

                        Swal.fire('Error', errorMsg, 'error');
                        btn.prop('disabled', false).text('Save Changes');
                    }
                });
            });

            // --- 5. DELETE WAREHOUSE WITH CONFIRMATION ---
            $(document).on('click', '.delete-warehouse', function(e) {
                e.preventDefault();

                let warehouseId = $(this).data('id');
                let warehouseName = $(this).data('name');

                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete warehouse: <br><strong>${warehouseName}</strong>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('warehouse.delete') }}",
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                id: warehouseId
                            },
                            dataType: 'json',
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Warehouse has been deleted successfully',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                console.error('Delete Error:', xhr.responseJSON);

                                let errorMsg = 'Failed to delete warehouse';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMsg = xhr.responseJSON.error;
                                }

                                Swal.fire('Error', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            // --- RESET FORM ON MODAL CLOSE ---
            $('#createwarehouse').on('hidden.bs.modal', function() {
                $('#createwarehouseForm')[0].reset();
                $('#createwarehouseSubmit').prop('disabled', false).text('Submit');
            });

            $('#warehouseModal').on('hidden.bs.modal', function() {
                $('#updateWarehouseSubmit').prop('disabled', false).text('Save Changes');
            });
        });
    </script>
@endpush
