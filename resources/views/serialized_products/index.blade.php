@extends('layouts.adminlte')

@section('subtitle', 'Serialized Products')
@section('content_header_title', 'Products')
@section('content_header_subtitle', 'Serialized Products')

@section('content_body')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <div class="card-title mb-0 text-uppercase" style="letter-spacing: 1ch;">
                                Serialized Products
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover w-100" id="serial_numbers_table">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Product Name</th>
                                <th>System Sku</th>
                                @can('can-see-images')
                                    <th>Images</th>
                                @endcan
                                <th>Supplier Name</th>
                                <th>Quantity</th>
                                {{-- <th>Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate rows here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            let token = "{{ session('sanctum_token') }}";

            // 1. FIX: Iwas sa "Cannot reinitialise DataTable" error
            if ($.fn.DataTable.isDataTable('#serial_numbers_table')) {
                $('#serial_numbers_table').DataTable().destroy();
            }

            // 2. Init DataTable with Sorting Arrows Fix
            let table = $('#serial_numbers_table').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                searching: true,
                ordering: true, // SIGURADONG LALABAS ANG ARROWS
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('serialized_products.indexTable') }}",
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    error: function(xhr) {
                        // Dito lilitaw kung may SQL 'status' error pa sa backend
                        console.error("Backend Error: ", xhr.responseText);
                    }
                },
                columns: [{
                        data: 'product_name',
                        name: 'product_name',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'system_sku',
                        name: 'system_sku',
                        className: 'text-center align-middle'
                    },
                    @can('can-see-images')
                        {
                            data: 'images',
                            name: 'images',
                            orderable: false,
                            searchable: false
                        },
                    @endcan {
                        data: 'supplier_name',
                        name: 'suppliers.name',
                        className: 'text-center align-middle'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        className: 'text-center align-middle',
                        render: function(data) {
                            // Mas malinis na badge logic
                            let badgeClass = data > 100 ? 'success' : 'danger';
                            let stockLabel = data > 100 ? 'High Stock' : 'Low Stock';
                            return `
                                <h5><span class="badge badge-${badgeClass}">${data}</span></h5>
                                <small class="text-uppercase font-weight-bold ${data > 100 ? 'text-success' : 'text-danger'}">
                                    ${stockLabel}
                                </small>`;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ], // I-sort base sa Product Name
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>'
                },
                rowCallback: function(row, data) {
                    // 3. FIX: Iwas sa double-click register at redirect logic
                    $(row).css('cursor', 'pointer').off('click').on('click', function() {
                        if (data.action && data.action.sp_id) {
                            const name = encodeURIComponent(data.action.sn_product_name);
                            window.location.href =
                                `/serialized_products/show/${data.action.sp_id}/${name}`;
                        }
                    });
                }
            });
        });
    </script>
@endpush

{{-- Sa pinakababa ng index.blade.php --}}

@push('js')
    <script>
        window.endpoints = {
            storePurchaseRequestsTable: "{{ route('purchase_requests.store') }}",
            purchaseRequestsTable: "{{ route('purchase_requests.indexTable') }}",
            supplierProductsDatatable: "{{ url('api/suppliers/:id/products-table') }}"
        };
    </script>
    {{-- Dito mo na lang tatawagin yung specific JS ng page na ito --}}
    @vite('resources/js/purchase_requests/purchase_requests.js')
@endpush
