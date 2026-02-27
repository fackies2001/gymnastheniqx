@extends('layouts.adminlte')

@section('subtitle', 'User Management')
@section('content_header_title', 'User Management')

@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title text-uppercase" style="letter-spacing: 0.2em;">Employees</h3>
                    <button class="btn btn-sm btn-primary ml-auto" id="create_employee">Create Employee</button>
                </div>
                <div class="card-body">
                    <table id="sampleId" class="table table-bordered w-100">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Photo</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Date Hired</th>
                                <th>Status</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    {{-- Photo --}}
                                    <td class="text-center">
                                        @if ($employee->profile_photo)
                                            <img src="{{ $employee->profile_photo }}" width="40" height="40"
                                                class="img-circle border shadow-sm" style="object-fit: cover;"
                                                onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(substr($employee->full_name, 0, 1)) }}&background=6777ef&color=fff';">
                                        @else
                                            <div class="img-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white shadow-sm"
                                                style="width:40px; height:40px; font-size:16px;">
                                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>{{ $employee->full_name }}</td>
                                    <td>{{ $employee->email ?? 'No Email' }}</td>
                                    <td>{{ $employee->username }}</td>
                                    <td>{{ $employee->contact_number ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($employee->address ?? 'N/A', 20) }}</td>
                                    <td>{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d') : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $employee->role->role_name ?? 'N/A' }}</td>

                                    {{-- ✅ FIXED: Buttons are now properly separated --}}
                                    <td>
                                        <button class="btn btn-xs btn-success edit-employee-btn"
                                            data-id="{{ $employee->id }}" data-full_name="{{ $employee->full_name }}"
                                            data-email="{{ $employee->email }}" data-username="{{ $employee->username }}"
                                            data-role="{{ $employee->role_id }}"
                                            data-department="{{ $employee->department_id }}"
                                            data-warehouse="{{ $employee->assigned_at }}"
                                            data-status="{{ $employee->status }}"
                                            data-contact_number="{{ $employee->contact_number }}"
                                            data-address="{{ $employee->address }}"
                                            data-hired="{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d') : '' }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button class="btn btn-xs btn-warning reset-pin-btn" data-id="{{ $employee->id }}">
                                            <i class="fas fa-undo"></i> Reset PIN
                                        </button>

                                        <button class="btn btn-xs btn-danger delete-employee-btn"
                                            data-id="{{ $employee->id }}" data-name="{{ $employee->full_name }}">
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

    {{-- ========================================== --}}
    {{-- MODAL - CREATE & EDIT                       --}}
    {{-- ========================================== --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="employeeForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="emp_id">

                    <div class="modal-body">
                        <div class="row">

                            {{-- Full Name --}}
                            <div class="col-md-6 form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>

                            {{-- Username --}}
                            <div class="col-md-6 form-group">
                                <label>Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>

                            {{-- Contact Number --}}
                            <div class="col-md-6 form-group">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number" class="form-control">
                            </div>

                            {{-- Role --}}
                            <div class="col-md-6 form-group">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="role_id" id="role_id" class="form-control" required>
                                    <option value="">-- Select Role --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ✅ Department --}}
                            <div class="col-md-6 form-group">
                                <label>Department</label>
                                <select name="department_id" id="department_id" class="form-control">
                                    <option value="">-- Select Department --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Assigned Warehouse --}}
                            <div class="col-md-6 form-group">
                                <label>Assigned Warehouse</label>
                                <select name="assigned_at" id="assigned_at" class="form-control">
                                    <option value="">-- Select Warehouse --</option>
                                    @foreach ($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-6 form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            {{-- Date Hired --}}
                            <div class="col-md-6 form-group">
                                <label>Date Hired</label>
                                <input type="date" name="date_hired" id="date_hired" class="form-control">
                            </div>

                            {{-- Address --}}
                            <div class="col-md-12 form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                            </div>

                            {{-- Profile Photo --}}
                            <div class="col-md-12 form-group">
                                <label>Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control-file">
                                <small class="text-muted">Leave blank if you don't want to change the photo.</small>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="save_btn">Save Employee</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function() {

                // ============================================
                // DATATABLE
                // ============================================
                if ($.fn.DataTable.isDataTable('#sampleId')) {
                    $('#sampleId').DataTable().destroy();
                }
                let table = $('#sampleId').DataTable({
                    responsive: true,
                    autoWidth: false,
                    retrieve: true
                });

                // ============================================
                // CREATE MODE
                // ============================================
                $('#create_employee').on('click', function() {
                    $('#employeeForm')[0].reset();
                    $('#emp_id').val('');
                    $('.modal-title').text('Create New Employee');
                    $('#save_btn').text('Save Employee');
                    $('#createUserModal').modal('show');
                });

                // ============================================
                // EDIT MODE
                // ============================================
                $(document).on('click', '.edit-employee-btn', function() {
                    let btn = $(this);

                    // Reset form first
                    $('#employeeForm')[0].reset();

                    // Fill hidden ID
                    $('#emp_id').val(btn.data('id'));

                    // Fill text fields
                    $('#full_name').val(btn.data('full_name') || '');
                    $('#email').val(btn.data('email') || '');
                    $('#username').val(btn.data('username') || '');
                    $('#contact_number').val(btn.data('contact_number') || '');
                    $('#address').val(btn.data('address') || '');
                    $('#date_hired').val(btn.data('hired') || '');

                    // Fill dropdowns
                    $('#role_id').val(btn.data('role') || '');
                    $('#department_id').val(btn.data('department') || '');
                    $('#assigned_at').val(btn.data('warehouse') || '');
                    $('#status').val(btn.data('status') || 'active');

                    // Update modal UI
                    $('.modal-title').text('Edit Employee Details');
                    $('#save_btn').text('Update Employee');
                    $('#createUserModal').modal('show');
                });

                // ============================================
                // FORM SUBMIT (Store & Update)
                // ============================================
                // DATI:
                $('#employeeForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    ...
                });

                // BAGO — i-trigger manually:
                $('#save_btn').off('click').on('click', function() {
                    let id = $('#emp_id').val();
                    let formData = new FormData($('#employeeForm')[0]);
                    let url = id ?
                        "{{ route('user.management.update') }}" :
                        "{{ route('user.management.store') }}";

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire('Success', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.log('ERROR:', xhr.status, xhr.responseText);
                            let errorMsg = xhr.responseJSON?.message || 'May mali sa server.';
                            if (xhr.responseJSON?.errors) {
                                errorMsg = Object.values(xhr.responseJSON.errors).flat().join(
                                    '<br>');
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: errorMsg
                            });
                        }
                    });
                });

                // ============================================
                // RESET PIN
                // ============================================
                $(document).on('click', '.reset-pin-btn', function() {
                    let id = $(this).data('id');

                    Swal.fire({
                        title: 'Reset PIN?',
                        text: 'This user will need to set a new PIN on their next action.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, Reset it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('admin.reset.pin') }}",
                                type: 'POST',
                                data: {
                                    id: id,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    Swal.fire('Reset!', res.message, 'success');
                                },
                                error: function(xhr) {
                                    let errorMsg = xhr.responseJSON?.message ||
                                        'Failed to reset PIN.';
                                    Swal.fire('Error', errorMsg, 'error');
                                }
                            });
                        }
                    });
                });

                // ============================================
                // DELETE EMPLOYEE
                // ============================================
                $(document).on('click', '.delete-employee-btn', function() {
                    let id = $(this).data('id');
                    let name = $(this).data('name');

                    Swal.fire({
                        title: 'Sigurado ka ba?',
                        text: 'Mabubura ang account ni ' + name + '. Hindi mo na ito maibabalik!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Oo, Burahin na!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/user-management/delete/' + id,
                                type: 'DELETE',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    Swal.fire('Deleted!', res.message, 'success').then(
                                        () => {
                                            location.reload();
                                        });
                                },
                                error: function(xhr) {
                                    let errorMsg = xhr.responseJSON?.message ||
                                        'Hindi mabura ang user.';
                                    Swal.fire('Error!', errorMsg, 'error');
                                }
                            });
                        }
                    });
                });

            }); // end ready
        </script>
    @endpush
@stop
