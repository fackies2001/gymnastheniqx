@extends('layouts.adminlte')

@section('subtitle', 'Supplier')
@section('content_header_title', 'Supplier')
{{-- @section('content_header_subtitle', 'Dashboard') --}}
@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header d-flex align-items-center">
                    <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                        Details
                    </div>
                    <!-- Button to trigger the Create Supplier modal -->
                    {{-- <a href="#" class="btn btn-sm btn-primary ml-auto" data-toggle="modal"
                        data-target="#createSupplierModal">Create Supplier</a> --}}
                </div>
                <div class="card-body">
                    <table id="sampleId" class="table-bordered w-100">
                        <thead class="bg-dark text-white py-5">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->name }}</td>
                                    <td>{{ $supplier->email }}</td>
                                    <td>{{ $supplier->phone }}</td>
                                    <td>{{ $supplier->address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Creating Supplier -->
    <div class="modal fade" id="createSupplierModal" tabindex="-1" role="dialog"
        aria-labelledby="createSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSupplierModalLabel">Create Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createSupplierForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <x-bootstrap.label for="createName" value="Supplier Name" />
                                    <x-bootstrap.input id="createName" name="name" required
                                        placeholder="Enter supplier name" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="createEmail" value="Supplier Email" />
                                    <x-bootstrap.input id="createEmail" name="email" type="email" required
                                        placeholder="Enter supplier email" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="createPhone" value="Supplier Phone" />
                                    <x-bootstrap.input id="createPhone" name="phone" required
                                        placeholder="Enter supplier phone" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="createAddress" value="Supplier Address" />
                                    <x-bootstrap.input id="createAddress" name="address" required
                                        placeholder="Enter supplier address" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="createSupplierSubmit">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade font-poppins" id="supplierModal" tabindex="-1" role="dialog"
        aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">Supplier Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="modalName">Name</label>
                            <input type="text" class="form-control" id="modalName" readonly>
                        </div>
                        <div class="form-group">
                            <label for="modalEmail">Email</label>
                            <input type="email" class="form-control" id="modalEmail" readonly>
                        </div>
                        <div class="form-group">
                            <label for="modalPhone">Phone</label>
                            <input type="text" class="form-control" id="modalPhone" readonly>
                        </div>
                        <div class="form-group">
                            <label for="modalAddress">Address</label>
                            <input type="text" class="form-control" id="modalAddress" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(function() {
                $('#sampleId').DataTable({
                    responsive: true,
                });

                $('#supplierModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    $('#modalName').val(button.data('name'));
                    $('#modalEmail').val(button.data('email'));
                    $('#modalPhone').val(button.data('phone'));
                    $('#modalAddress').val(button.data('address'));
                });

                $('#createSupplierSubmit').on('click', function() {
                    var data = {
                        name: $('#createName').val(),
                        email: $('#createEmail').val(),
                        phone: $('#createPhone').val(),
                        address: $('#createAddress').val()
                    };

                    console.log('Creating supplier:', data);
                    // Dito mo ilalagay yung AJAX mo brother
                });
            });
        </script>
    @endpush
@stop
