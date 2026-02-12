@extends('layouts.adminlte')

@section('subtitle', 'IO Reocords')
@section('content_header_title', 'IO Reocords')
{{-- @section('content_header_subtitle', 'IO Reocords') --}}
@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                        Employees</div>
                    <a href="#" class="btn btn-sm btn-primary ml-auto">Create User</a>
                </div>
                <div class="card-body">
                    <table id="sampleId" class="table-bordered w-100">
                        <thead class="bg-dark text-white py-5">
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Contact Number</th>
                                <th>Status</th>
                                <th>Date Hired</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            {{-- {{ dd($employees) }} --}}
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>{{ $employee->full_name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->username }}</td>
                                    <td>{{ $employee->contact_number }}</td>
                                    <td class="text-center {{ $employee->status === 'active' ? 'bg-olive' : 'bg-navy' }}">
                                        {{ ucfirst($employee->status) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($employee->date_hired)->toFormattedDateString() }}</td>
                                    <td>{{ $employee->role->role_name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="#"
                                                class="btn btn-sm btn-success mr-1 d-flex items-center justify-center">
                                                <i class="fas fa-edit"></i>&nbsp;
                                                <span>Edit</span>
                                            </a>

                                            @can('manage-users-deletion')
                                                <a href="#"
                                                    class="btn btn-sm btn-danger mr-1 d-flex items-center justify-center">
                                                    <i class="fas fa-trash-alt"></i>&nbsp;
                                                    <span>Delete</span>
                                                </a>


                                                @if ($employee->status === 'active')
                                                    @if (isset($employee->account->employee->id))
                                                        <a href="#"
                                                            class="btn btn-sm bg-maroon d-flex items-center justify-center"><i
                                                                class="fas fa-edit"></i>&nbsp;<span>Activated</span></a>
                                                    @else
                                                        <a href="#"
                                                            class="btn btn-sm bg-pink account-creation d-flex items-center justify-center"
                                                            data-id="{{ $employee->id }}"
                                                            data-full_name="{{ $employee->full_name }}"
                                                            data-email="{{ $employee->email }}"
                                                            data-role_name="{{ $employee->role->role_name ?? 'N/A' }}"
                                                            data-date_hired="{{ \Carbon\Carbon::parse($employee->date_hired)->toFormattedDateString() }}">
                                                            <i class="fas fa-plus"></i>&nbsp;
                                                            <span>Activate</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document"> <!-- Centered and Large -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="account_name"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="modal-body text-sm">
                        <div class="container-fluid">
                            <div><span class="text-muted">Employee since:</span> <span id="joined_date"></span></div>
                            <div><span class="text-muted">Working as:</span> <span id="role_name"></span></div>

                            <!-- Hidden Full Name -->
                            <input type="text" name="name" id="name" hidden>

                            <!-- Disabled Email -->
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>

                            <!-- Checkbox -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="toggleValue" checked>
                                <label class="form-check-label" for="toggleValue">
                                    Default Password ?
                                </label>
                            </div>

                            <!-- Conditional Input Field with Toggle Icon -->
                            <div class="form-group position-relative">
                                <label for="code">Code</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control"
                                        value="g123">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                            <i class="fas fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">Value will be set to "g123" if checkbox is checked</small>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(function() {
                $('#sampleId').DataTable({
                    responsive: true,
                    columnDefs: [{
                            targets: -1, // the last column (Actions)
                            responsivePriority: 1 // highest priority to stay visible
                        },
                        {
                            targets: [0, 1, 5, 6],
                            responsivePriority: 2
                        },
                        {
                            targets: [3, 4],
                            responsivePriority: 3
                        },
                        {
                            targets: [2],
                            responsivePriority: 4
                        }
                    ]
                });
                // Now targets all .account-creation buttons
                $(document).on('click', '.account-creation', function(e) {
                    e.preventDefault();
                    const employee_id = $(this).data('id');
                    const full_name = $(this).data('full_name');
                    const email = $(this).data('email');
                    const role_name = $(this).data('role_name');
                    const date_hired = $(this).data('date_hired');

                    const modal_element = $('#modelId');
                    modal_element.find('#account_name').text(full_name);
                    modal_element.find('#name').val(full_name);
                    modal_element.find('#email').val(email);
                    modal_element.find('#role_name').text(role_name);
                    modal_element.find('#joined_date').text(date_hired);
                    // Example redirect
                    // window.location.href = `/users/create?employee_id=${employeeId}`;
                    modal_element.modal('show');
                });

                $('#toggleValue').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#password').val('g123');
                    } else {
                        $('#password').val('');
                    }
                });

                $('#togglePassword').on('click', function() {
                    const input = $('#password');
                    const icon = $(this).find('i');

                    if (input.attr('type') === 'password') {
                        input.attr('type', 'text');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    } else {
                        input.attr('type', 'password');
                        icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    }
                });
            });
        </script>
    @endpush
@stop
