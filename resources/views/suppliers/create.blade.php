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
                                    <x-bootstrap.label for="supplier_code" value="Supplier ID" />
                                    <input type="text" class="form-control bg-light"
                                        value="Auto-generated (e.g. SUP-0012)" readonly disabled
                                        style="font-weight:600; color:#6c757d; letter-spacing:1px;">
                                    <small class="text-muted">Automatically assigned upon saving.</small>
                                </div>

                                <div class="mb-3">
                                    <x-bootstrap.label for="name" value="Supplier Name" :required="true" />
                                    <x-bootstrap.input id="name" name="name" required
                                        placeholder="Enter supplier name" value="{{ old('name') }}" />
                                    {{-- ✅ INALIS ang x-bootstrap.input-error para hindi mag-show ng PHP validation error --}}
                                </div>

                                <div class="mb-3">
                                    <x-bootstrap.label for="email" value="Supplier Email" :required="true" />
                                    <x-bootstrap.input id="email" name="email" type="email" required
                                        placeholder="Enter supplier email" value="{{ old('email') }}" />
                                    {{-- ✅ INALIS ang x-bootstrap.input-error --}}
                                </div>

                                <div class="mb-3">
                                    <x-bootstrap.label for="contact_number" value="Supplier Phone" :required="true" />
                                    <x-bootstrap.input id="contact_number" name="contact_number" required
                                        placeholder="Enter supplier phone" value="{{ old('contact_number') }}" />
                                    <x-bootstrap.input-error :messages="$errors->get('contact_number')" />
                                </div>

                                <div class="mb-3">
                                    <x-bootstrap.label for="address" value="Supplier Address" :required="true" />
                                    <x-bootstrap.input id="address" name="address" required
                                        placeholder="Enter supplier address" value="{{ old('address') }}" />
                                    <x-bootstrap.input-error :messages="$errors->get('address')" />
                                </div>

                                <div class="mb-3">
                                    <x-bootstrap.label for="contact_person" value="Contact Person" />
                                    <x-bootstrap.input id="contact_person" name="contact_person"
                                        placeholder="Enter contact person name" value="{{ old('contact_person') }}" />
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

            // Prevent native form submit entirely
            $('#createSupplierForm').on('submit', function(e) {
                e.preventDefault();
                return false;
            });

            let isSubmitting = false;

            $('#createSupplierSubmit').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // ✅ Guard — block if already submitting
                if (isSubmitting) return;

                // ✅ Validate FIRST — bago pa mag-lock ng button o tanggalin ang listener
                if (!document.getElementById('createSupplierForm').checkValidity()) {
                    document.getElementById('createSupplierForm').reportValidity();
                    return; // ✅ hindi pa naka-lock, pwede pa mag-retry
                }

                // ✅ DITO NA LANG mag-lock — after validation passed na
                isSubmitting = true;
                $('#createSupplierSubmit').off('click'); // ✅ dito na tanggalin
                $('#createSupplierSubmit').prop('disabled', true).text('Saving...');

                // Step 1: Check duplicate first
                $.ajax({
                    url: '{{ route('suppliers.check_duplicate') }}',
                    type: 'GET',
                    data: {
                        name: $('#name').val().trim(),
                        email: $('#email').val().trim()
                    },
                    success: function(response) {
                        if (response.exists) {
                            // ✅ Reset — para makapag-edit at makapag-submit ulit
                            isSubmitting = false;
                            $('#createSupplierSubmit').prop('disabled', false).text(
                                'Save Supplier');
                            // ✅ Re-attach listener
                            $('#createSupplierSubmit').off('click').on('click', arguments
                                .callee);

                            Swal.fire({
                                icon: 'warning',
                                title: 'Supplier Already Exists!',
                                text: '"' + $('#name').val().trim() +
                                    '" with this email is already registered.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            // Step 2: Actually store the supplier
                            $.ajax({
                                url: $('#createSupplierForm').attr('action'),
                                type: 'POST',
                                data: $('#createSupplierForm').serialize(),
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        sessionStorage.setItem('swal_success',
                                            response.message);
                                        window.location.href = response.redirect;
                                    }
                                },
                                error: function(xhr) {
                                    // ✅ Reset on error — makapag-retry ang user
                                    isSubmitting = false;
                                    $('#createSupplierSubmit').prop('disabled',
                                        false).text('Save Supplier');
                                    // ✅ Re-attach listener
                                    $('#createSupplierSubmit').off('click').on(
                                        'click', arguments.callee);

                                    if (xhr.status === 409) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Duplicate Detected!',
                                            text: 'This supplier was already recently submitted.',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'OK'
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error!',
                                            text: 'Something went wrong. Please try again.',
                                            confirmButtonColor: '#d33',
                                        });
                                    }
                                }
                            });
                        }
                    },
                    error: function() {
                        // ✅ Reset on error
                        isSubmitting = false;
                        $('#createSupplierSubmit').prop('disabled', false).text(
                        'Save Supplier');
                        // ✅ Re-attach listener
                        $('#createSupplierSubmit').off('click').on('click', arguments.callee);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Could not check duplicate. Please try again.',
                        });
                    }
                });
            });
        });
    </script>
@endpush
