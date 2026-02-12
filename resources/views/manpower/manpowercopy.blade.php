@extends('layouts.adminlte')

@section('subtitle', 'Manpower')
@section('content_header_title', 'Manpower')
@section('content_header_subtitle', 'Coach Management')

@section('content_body')

    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="card-title mb-0 text-uppercase font-weight-bold"
                            style="letter-spacing: 0.3rem; font-size: 1.2rem;">
                            MANPOWER
                        </div>
                        <button class="btn btn-sm btn-primary ml-auto" id="btnCreateCoach" style="border-radius: 2px;">
                            <i class="fas fa-plus mr-1"></i> Create Coach
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="coachTable" class="table table-bordered table-hover w-100">
                        <thead class="bg-dark text-white">
                            <tr class="text-center">
                                <th>Full Name</th>
                                <th>Contact No.</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Position</th>
                                <th>Date Hired</th>
                                <th>Status</th>
                                <th width="150px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- MODAL --}}
            <div class="modal fade" id="createCoachModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form id="coachForm" action="{{ route('manpower.store') }}" method="POST">
                            @csrf
                            <div id="methodField"></div>
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title font-weight-bold text-white" id="modalTitle">Create New Coach
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-bold">Complete Name</label>
                                        <input type="text" name="full_name" id="full_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-bold">Date of Birth</label>
                                        <input type="date" name="birth_date" id="birth_date" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="small font-weight-bold">Address</label>
                                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-bold">Contact No.</label>
                                        <input type="text" name="contact_no" id="contact_no" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small font-weight-bold">Email Address</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="small font-weight-bold">Position</label>
                                        <select name="position" id="position" class="form-control">
                                            <option value="Head Coach">Head Coach</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="small font-weight-bold">Date Hired</label>
                                        <input type="date" name="date_hired" id="date_hired" class="form-control"
                                            required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="small font-weight-bold">Current Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="On Leave">On Leave</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm">Save Details</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // STEP 1: Iwas sa reinitialise error
            if ($.fn.DataTable.isDataTable('#coachTable')) {
                $('#coachTable').DataTable().destroy();
            }

            // STEP 2: Initialize DataTable
            var table = $('#coachTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: "{{ route('manpower.data') }}",
                columns: [{
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'contact_no',
                        name: 'contact_no'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'position',
                        name: 'position'
                    },
                    {
                        data: 'date_hired',
                        name: 'date_hired'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data) {
                            let badge = data === 'Active' ? 'success' : (data === 'On Leave' ?
                                'warning' : 'danger');
                            return `<span class="badge badge-${badge}">${data}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // CREATE BUTTON
            $('#btnCreateCoach').on('click', function() {
                $('#coachForm')[0].reset();
                $('#modalTitle').text('Create New Coach').removeClass('text-warning').addClass(
                    'text-white');
                $('.modal-header').removeClass('bg-warning').addClass('bg-primary');

                // ✅ RESET FORM ACTION TO STORE
                $('#coachForm').attr('action', "{{ route('manpower.store') }}");
                $('#methodField').empty();

                $('#createCoachModal').modal('show');
            });

            // FORM SUBMIT (Save & Update)
            $('#coachForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                let form = $(this);
                let url = form.attr('action');
                let formData = form.serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#createCoachModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Tagumpay!',
                            text: response.success,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value) {
                                errorMsg += value[0] + '<br>';
                            });
                            Swal.fire('Validation Error', errorMsg, 'error');
                        } else {
                            Swal.fire('Error', 'May mali sa pag-save ng data.', 'error');
                        }
                    }
                });
            });

            // ✅ EDIT ACTION - FIXED NA ITO!
            $(document).on('click', '.edit-coach', function() {
                var id = $(this).data('id');
                let editUrl = "{{ route('manpower.edit', ':id') }}".replace(':id', id);
                let updateUrl = "{{ route('manpower.update', ':id') }}".replace(':id', id);

                $.get(editUrl, function(data) {
                    $('#modalTitle').text('Edit Coach Details').removeClass('text-white').addClass(
                        'text-white');
                    $('.modal-header').removeClass('bg-primary').addClass('bg-warning');

                    // ✅ SET FORM ACTION ATTRIBUTE - ITO YUNG KULANG SA CODE MO!
                    $('#coachForm').attr('action', updateUrl);
                    $('#methodField').html('<input type="hidden" name="_method" value="PUT">');

                    // Fill fields
                    $('#full_name').val(data.full_name);
                    $('#birth_date').val(data.birth_date);
                    $('#address').val(data.address);
                    $('#contact_no').val(data.contact_no);
                    $('#email').val(data.email);
                    $('#position').val(data.position);
                    $('#date_hired').val(data.date_hired);
                    $('#status').val(data.status);

                    $('#createCoachModal').modal('show');
                });
            });

            // DELETE ACTION
            $(document).on('click', '.delete-coach', function() {
                var id = $(this).data('id');
                let deleteUrl = "{{ route('manpower.delete', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Sigurado ka ba?',
                    text: "Hindi mo na ito mababawi!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oo, burahin!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: "DELETE",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                table.ajax.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
