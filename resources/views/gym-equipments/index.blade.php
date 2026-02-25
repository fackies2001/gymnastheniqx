@extends('layouts.adminlte')

@section('content_body')
    <div class="container-fluid">
        <h4 class="mb-3">Gym Equipment > Equipment Management</h4>

        <div class="card shadow">
            <div class="card-header bg-navy">
                <h3 class="card-title" style="letter-spacing: 2px; font-weight: 600;">GYM EQUIPMENT</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="form-inline">
                            <label class="mr-2">Filter by:</label>
                            <select id="filterType" class="form-control mr-2" style="width: 150px;">
                                <option value="date">Specific Date</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>

                            <input type="date" id="dateFilter" class="form-control mr-2" style="width: 180px;"
                                value="{{ date('Y-m-d') }}">

                            <select id="monthFilter" class="form-control mr-2" style="width: 150px; display: none;">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>

                            <select id="yearFilter" class="form-control mr-2" style="width: 120px;">
                                @for ($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                        {{ $y }}</option>
                                @endfor
                            </select>

                            <button id="applyFilter" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                            <button id="resetFilter" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <button id="printBtn" class="btn btn-info mr-2">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#equipmentModal">
                            <i class="fas fa-plus"></i> Create Equipment
                        </button>
                    </div>
                </div>

                <table id="gymTable" class="table table-bordered table-striped w-100">
                    <thead class="bg-dark">
                        <tr>
                            <th>Equipment Name</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Add/Edit Equipment Modal -->
    <div class="modal fade" id="equipmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="equipmentForm">
                @csrf
                <input type="hidden" id="equipment_id" name="id">
                <div class="modal-content">
                    <div class="modal-header bg-navy">
                        <h5 class="modal-title" id="modalTitle">Add New Equipment</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="e.g. Treadmill, Dumbbells" required>
                        </div>
                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="Available">Available</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                                <option value="Out of Order">Out of Order</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Save Equipment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let table;
            let currentFilter = {};
            let isSubmitting = false;

            function initDataTable() {
                if ($.fn.dataTable.isDataTable('#gymTable')) {
                    $('#gymTable').DataTable().destroy();
                }

                table = $('#gymTable').DataTable({
                    destroy: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('gym.data') }}",
                        data: function(d) {
                            d.filter = currentFilter;
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'quantity',
                            name: 'quantity',
                            className: 'text-center'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            className: 'text-center'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            className: 'text-center'
                        },
                        {
                            data: 'updated_at', // ✅ Last Updated
                            name: 'updated_at',
                            className: 'text-center'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [3, 'desc']
                    ]
                });
            }

            initDataTable();

            $('#filterType').on('change', function() {
                const type = $(this).val();
                $('#dateFilter, #monthFilter').hide();

                if (type === 'date') {
                    $('#dateFilter').show();
                } else if (type === 'month') {
                    $('#monthFilter').show();
                }
            });

            $('#applyFilter').on('click', function() {
                const filterType = $('#filterType').val();
                currentFilter = {
                    type: filterType
                };

                if (filterType === 'date') {
                    currentFilter.value = $('#dateFilter').val();
                } else if (filterType === 'month') {
                    currentFilter.month = $('#monthFilter').val();
                    currentFilter.year = $('#yearFilter').val();
                } else if (filterType === 'year') {
                    currentFilter.year = $('#yearFilter').val();
                }

                table.ajax.reload();
            });

            $('#resetFilter').on('click', function() {
                currentFilter = {};
                $('#filterType').val('date');
                $('#dateFilter').val('{{ date('Y-m-d') }}').show();
                $('#monthFilter').hide();
                table.ajax.reload();
            });

            $('#printBtn').on('click', function() {
                let url = "{{ route('gym.print') }}?";

                if (currentFilter.type) {
                    url += `type=${currentFilter.type}`;
                    if (currentFilter.value) url += `&value=${currentFilter.value}`;
                    if (currentFilter.month) url += `&month=${currentFilter.month}`;
                    if (currentFilter.year) url += `&year=${currentFilter.year}`;
                }

                window.open(url, '_blank');
            });

            $('[data-target="#equipmentModal"]').on('click', function() {
                $('#equipmentForm')[0].reset();
                $('#equipment_id').val('');
                $('#modalTitle').text('Add New Equipment');
                $('#submitBtn').html('<i class="fas fa-save"></i> Save Equipment');
                isSubmitting = false;
            });

            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: "{{ route('gym.edit', ':id') }}".replace(':id', id),
                    method: 'GET',
                    success: function(data) {
                        $('#equipment_id').val(data.id);
                        $('#name').val(data.name);
                        $('#quantity').val(data.quantity);
                        $('#status').val(data.status);
                        $('#modalTitle').text('Edit Equipment');
                        $('#submitBtn').html('<i class="fas fa-save"></i> Update Equipment');
                        $('#equipmentModal').modal('show');
                        isSubmitting = false;
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load equipment data'
                        });
                    }
                });
            });

            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('gym.delete', ':id') }}".replace(':id', id),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to delete equipment'
                                });
                            }
                        });
                    }
                });
            });

            $('#equipmentForm').off('submit').on('submit', function(e) { // ✅ I-add ang .off('submit')
                e.preventDefault();
                e.stopImmediatePropagation();

                if (isSubmitting) {
                    console.log('Already submitting, ignoring...');
                    return false;
                }

                isSubmitting = true;

                const id = $('#equipment_id').val();
                const url = id ? "{{ route('gym.update', ':id') }}".replace(':id', id) :
                    "{{ route('gym.store') }}";
                const method = id ? 'PUT' : 'POST';

                let formData = $(this).serializeArray();
                if (method === 'PUT') {
                    formData.push({
                        name: '_method',
                        value: 'PUT'
                    });
                }

                $('#submitBtn').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $.param(formData),
                    success: function(response) {
                        $('#equipmentModal').modal('hide');

                        // ✅ I-add ang setTimeout para hindi mag-double trigger
                        setTimeout(function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                table.ajax.reload(null, false);
                            });
                        }, 300);
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        let errorMsg = 'Something went wrong';
                        if (errors) {
                            errorMsg = Object.values(errors).flat().join('<br>');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMsg
                        });
                    },
                    complete: function() {
                        // ✅ I-move dito ang isSubmitting = false
                        isSubmitting = false;
                        $('#submitBtn').prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Save Equipment');
                    }
                });

                return false; // ✅ I-add ito
            });

            $('#equipmentModal').on('hidden.bs.modal', function() {
                isSubmitting = false;
                $('#equipmentForm')[0].reset();
            });
        });
    </script>
@endpush
