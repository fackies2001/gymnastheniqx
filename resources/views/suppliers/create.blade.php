@extends('layouts.adminlte')

@section('subtitle', 'Supplier')
@section('content_header_title', 'Supplier')

@section('content_body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="card-title mb-0" style="letter-spacing: 1ch; text-transform: uppercase;" id="title_emp">
                            <h3 class="my-4">Create Supplier</h3>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('suppliers.store') }}" id="createSupplierForm">
                        <div class="card-body row">
                            @csrf
                            <div class="col-sm-12 @can('can-create-supplier-api') col-md-6 @endcan">

                                <div class="mb-3">
                                    <x-bootstrap.label for="name" value="Supplier Name" :required="true" />
                                    <x-bootstrap.input id="name" name="name" required
                                        placeholder="Enter supplier name" />
                                    <x-bootstrap.input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="email" value="Supplier Email" :required="true" />
                                    <x-bootstrap.input id="email" name="email" type="email" required
                                        placeholder="Enter supplier email" />
                                    <x-bootstrap.input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="contact_number" value="Supplier Phone" :required="true" />
                                    <x-bootstrap.input id="contact_number" name="contact_number" required
                                        placeholder="Enter supplier phone" />
                                    <x-bootstrap.input-error :messages="$errors->get('contact_number')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="address" value="Supplier Address" :required="true" />
                                    <x-bootstrap.input id="address" name="address" required
                                        placeholder="Enter supplier address" />
                                    <x-bootstrap.input-error :messages="$errors->get('address')" />
                                </div>
                                <div class="mb-3">
                                    <x-bootstrap.label for="contact_person" value="Contact Person" />
                                    <x-bootstrap.input id="contact_person" name="contact_person"
                                        placeholder="Enter contact person name" />
                                    <x-bootstrap.input-error :messages="$errors->get('contact_person')" />
                                </div>

                            </div>
                        </div>
                        <div class="card-footer d-flex">
                            <button type="button" class="btn btn-success btn-sm ml-auto" id="createSupplierSubmit">
                                Save Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {

            $('#createSupplierSubmit').on('click', function(e) {
                e.preventDefault();

                // HTML5 native validation muna
                if (!document.getElementById('createSupplierForm').checkValidity()) {
                    document.getElementById('createSupplierForm').reportValidity();
                    return false;
                }

                // ✅ AJAX duplicate check — name + email combo
                $.ajax({
                    url: '{{ route('suppliers.check_duplicate') }}',
                    type: 'GET',
                    data: {
                        name: $('#name').val().trim(),
                        email: $('#email').val().trim()
                    },
                    success: function(response) {
                        if (response.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Supplier Already Exists!',
                                text: '"' + $('#name').val().trim() +
                                    '" with this email is already registered. Sister company? Use a different email.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            $('#createSupplierForm').submit();
                        }
                    },
                    error: function() {
                        $('#createSupplierForm').submit();
                    }
                });
            });

        });
    </script>
@endpush
