@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Supplier Products')
@section('content_header_subtitle', 'All Supplier Products')

@section('plugins.Datatables', true)

@section('content_body')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center bg-white">
                        <div class="card-title mb-0 text-uppercase font-weight-bold" style="letter-spacing: 0.05em;">
                            Supplier Products List
                        </div>
                        <div class="ml-auto">
                            <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                                data-target="#createProductModal">
                                <i class="fas fa-box"></i> Create Supplier Product
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="productsTable" class="table table-bordered table-hover w-100">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Supplier</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Barcode</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @not_api
        <form id="createProductForm" action="{{ route('supplier_products.store') }}" method="POST">
            @csrf
            <x-bootstrap.modal id="createProductModal" title="Create Supplier Product" size="modal-lg" position="centered">
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Supplier</label>
                        <x-bootstrap.select id="supplier_id_new" name="supplier_id" :options="$suppliers" required />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Category</label>
                        <select name="category_id" id="category_id" class="form-control select2" required>
                            <option value="">Select Category</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Product Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">SKU</label>
                        <input type="text" name="sku" id="sku_new" class="form-control" placeholder="e.g. SUP-001"
                            required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Cost Price</label>
                        <input type="number" name="cost_price" id="cost_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Barcode</label>
                        <input type="text" name="barcode" id="barcode_new" class="form-control" required>
                    </div>

                    {{-- ✅ CONSUMABLE CHECKBOX - Always Visible --}}
                    <div class="mb-3 col-md-12" id="consumable-container" style="display: block !important;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_consumable" name="is_consumable"
                                value="1">
                            <label class="custom-control-label font-weight-bold" for="is_consumable">
                                Mark as Consumable (No Serial Required)
                            </label>
                        </div>
                    </div>
                </div>
                <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="submitProductBtn">Save Product</button>
                </x-slot:footer>
            </x-bootstrap.modal>
        </form>
    @endnot_api
@endsection

@push('css')
    <style>
        /* ✅ Force consumable checkbox to always be visible */
        #consumable-container,
        #is_consumable,
        #is_consumable+label {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            var isSubmitting = false;
            var tableId = '#productsTable';

            // 1. Initialize DataTable
            var table = $(tableId).DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('supplier_products.data') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'supplier_name',
                        name: 'supplier_name',
                        title: 'Supplier'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        title: 'Product Name'
                    },
                    {
                        data: 'system_sku',
                        name: 'system_sku',
                        title: 'SKU'
                    },
                    {
                        data: 'cost_price',
                        name: 'cost_price',
                        title: 'Price'
                    },
                    {
                        data: 'barcode',
                        name: 'barcode',
                        title: 'Barcode'
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // 2. Modal open - ensure checkbox is visible
            $('#createProductModal').on('shown.bs.modal', function() {
                $('#name').focus();

                // ✅ Force checkbox visibility
                $('#consumable-container').show().css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });

                console.log('Modal opened, checkbox visible:', $('#is_consumable').is(':visible'));
            });

            // 3. ✅ Prevent category change from hiding checkbox
            $('#category_id').on('change select2:select', function() {
                setTimeout(function() {
                    $('#consumable-container').show().css({
                        'display': 'block',
                        'visibility': 'visible',
                        'opacity': '1'
                    });
                    console.log('Category changed, checkbox still visible');
                }, 100);
            });

            // 4. Submit Button Logic
            $(document).off('click', '#submitProductBtn').on('click', '#submitProductBtn', function(e) {
                e.preventDefault();

                if (isSubmitting) return false;

                var form = $('#createProductForm');
                var submitBtn = $(this);

                // Validation
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return false;
                }

                isSubmitting = true;
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log('Success:', response);

                        // Close modal
                        $('#createProductModal').modal('hide');

                        // Reset form
                        form[0].reset();
                        $('.select2').val(null).trigger('change');

                        // Reload table
                        table.ajax.reload(null, false);

                        // Success message
                        if (response.message) {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error Details:', xhr);
                        alert('Error: ' + (xhr.responseJSON?.message ||
                            'Failed to save product'));
                    },
                    complete: function() {
                        setTimeout(function() {
                            isSubmitting = false;
                            submitBtn.prop('disabled', false).html('Save Product');
                        }, 500);
                    }
                });
            });

            // 5. ✅ Debug helper - check if checkbox is being hidden by external scripts
            setInterval(function() {
                if ($('#createProductModal').hasClass('show')) {
                    if (!$('#is_consumable').is(':visible')) {
                        console.warn('⚠️ Checkbox was hidden! Forcing visibility...');
                        $('#consumable-container').show().css('display', 'block');
                    }
                }
            }, 500);
        });
    </script>
@endpush
