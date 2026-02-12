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
                        <label class="form-label font-weight-bold">Supplier SKU</label>
                        <input type="text" name="sku" id="sku" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Cost Price</label>
                        <input type="number" name="cost_price" id="cost_price" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Barcode</label>
                        <input type="text" name="barcode" id="barcode_new" class="form-control" required>
                    </div>
                    <div class="mb-3 col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_consumable" name="is_consumable"
                                value="1">
                            <label class="custom-control-label" for="is_consumable">Mark as Consumable (No Serial
                                Required)</label>
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

            // 2. Modal focus logic
            $('#createProductModal').on('shown.bs.modal', function() {
                $(this).find('input[name="name"]').focus();
            });

            // 3. Submit Button Logic (Cleaned up)
            $(document).off('click', '#submitProductBtn').on('click', '#submitProductBtn', function(e) {
                e.preventDefault();

                if (isSubmitting) return false;

                var form = $('#createProductForm');
                var submitBtn = $(this);

                // Simple validation check
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

                        // Isara ang modal
                        $('#createProductModal').modal('hide');

                        // Reset form
                        form[0].reset();
                        $('.select2').val(null).trigger('change');

                        // RELOAD TABLE (Direct call to variable)
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error('Error Details:', xhr);
                        alert('Error: ' + (xhr.responseJSON?.message ||
                            'Failed to save product'));
                    },
                    complete: function() {
                        // Anti-double click delay
                        setTimeout(function() {
                            isSubmitting = false;
                            submitBtn.prop('disabled', false).html('Save Product');
                        }, 500);
                    }
                });
            });
        });
    </script>
@endpush
