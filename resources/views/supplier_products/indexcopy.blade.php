

@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Supplier Products')
@section('content_header_subtitle', 'All Supplier Products')

@section('plugins.Datatables', true)

{{-- @section('content_body')
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
                                    <th>ID</th>
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
    </div> --}}

{{--    
                    {{-- Product Name --}}
                    {{-- <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Product Name</label>
                        <x-bootstrap.input id="name" name="name" required />
                    </div> --}}

                    {{-- Supplier SKU --}}
                    {{-- <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Supplier SKU</label>
                        <x-bootstrap.input id="sku" name="sku" required />
                    </div> --}}

                    {{-- Cost Price --}}
                    {{-- <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Cost Price</label>
                        <x-bootstrap.input id="cost_price" name="cost_price" type="number" step="0.01" required />
                    </div>  --}}
                    {{-- Barcode --}}
                    {{-- <div class="mb-3 col-md-6">
                        <label class="form-label font-weight-bold">Barcode</label>
                        <x-bootstrap.input id="barcode_new" name="barcode" required />
                    </div> --}}

                    {{-- Consumable Checkbox --}}
                    {{-- <div class="mb-3 col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_consumable" name="is_consumable"
                                value="1">
                            <label class="custom-control-label" for="is_consumable">Mark as Consumable (No Serial
                                Required)</label>
                        </div>
                    </div>
                </div> --}}

                {{-- <x-slot:footer>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    {{-- Submit button na walang extra attributes para iwas conflict --}}
                    {{-- <button type="submit" class="btn btn-primary btn-sm">Save Product</button>
                </x-slot:footer> --}}

            </x-bootstrap.modal>
        </form>
    @endnot_api
@endsection


@push('js')

    {{-- <script>
        (function($) {
            'use strict';
            $(document).ready(function() {
                var tableId = '#productsTable';

                // --- 1. DATATABLE INITIALIZATION (Fixed Version) ---
                var table = $(tableId).DataTable({
                    destroy: true,
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: {
                        url: "{{ route('supplier_products.data') }}",
                        type: "GET",
                        error: function(xhr, error, code) {
                            console.error("DataTable Error: ", xhr.responseText);
                            console.error("Error Code: ", code);
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            title: 'ID',
                            orderable: false,
                            searchable: false
                        },
                        {
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
                            title: 'Price',
                            orderable: true
                        },
                        {
                            data: 'barcode',
                            name: 'barcode',
                            title: 'Barcode'
                        }
                    ],
                    order: [
                        [1, 'asc']
                    ], // Sort by Supplier name (column index 1)
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100],
                        [10, 25, 50, 100]
                    ],
                    language: {
                        processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                        emptyTable: "No supplier products found. Click 'Create Supplier Product' to add one.",
                        zeroRecords: "No matching records found"
                    }
                });

                // --- 2. 🔫 SCANNER GUN AUTO-FOCUS ---
                $('#createProductModal').on('shown.bs.modal', function() {
                    $(this).find('input[name="barcode"]').focus();
                });

                // --- 3. FORM SUBMIT HANDLER ---
                $('#createProductForm').on('submit', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var form = $(this);
                    var formData = form.serialize();
                    var submitBtn = form.find('button[type="submit"]');

                    // Disable submit button
                    submitBtn.prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Saving...');

                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            console.log('Success:', response);

                            // Close modal
                            $('#createProductModal').modal('hide');

                            // Reset form
                            form[0].reset();

                            // Reload datatable
                            table.ajax.reload(null, false);

                            // Show success message
                            alert('Supplier product created successfully!');

                            // Re-enable button
                            submitBtn.prop('disabled', false).html('Save Product');
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            console.error('Response:', xhr.responseJSON);

                            var errorMsg = 'Error creating product.';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                errorMsg += '\n\nValidation Errors:\n';
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    errorMsg += '- ' + value[0] + '\n';
                                });
                            }

                            alert(errorMsg);

                            // Re-enable button
                            submitBtn.prop('disabled', false).html('Save Product');
                        }
                    });

                    return false;
                });
            });
        })(jQuery);
    </script> --}}
@endpush
