@extends('layouts.adminlte')

@section('subtitle', 'Serialized Products')
@section('content_header_title', 'Products')
@section('content_header_subtitle', 'Serialized Products')

@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title text-uppercase font-weight-bold" style="letter-spacing: 1px;">
                        <i class="fas fa-boxes mr-2 text-primary"></i> Stock Level Overview
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover w-100" id="summary_table">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>Product Name</th>
                                <th>System SKU</th>
                                <th>Supplier Name</th>
                                <th class="text-center">Quantity</th>
                            </tr>
                        </thead>
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

            // --- DATATABLE LOGIC ---
            if ($.fn.DataTable.isDataTable('#summary_table')) {
                $('#summary_table').DataTable().destroy();
            }

            let table = $('#summary_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ordering: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('serialized_products.indexTable') }}",
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    }
                }, // <--- DITO DAPAT NAGSASARA ANG AJAX AT MAY COMMA
                columns: [{
                        data: 'product_name',
                        name: 'name',
                        render: function(data) {
                            return `<span class="font-weight-bold"><i class="fas fa-box text-secondary mr-2"></i>${data}</span>`;
                        }
                    },
                    {
                        data: 'system_sku',
                        name: 'system_sku'
                    },
                    {
                        data: 'supplier_name',
                        name: 'supplier.name'
                    },
                    {
                        // _index.blade.php

                        data: 'quantity',
                        name: 'quantity',
                        className: 'text-center align-middle',
                        render: function(data) {
                            // Ngayon, ang 'data' ay numero na (halimbawa: 10)
                            let color = data <= 5 ? 'danger' : 'success';
                            return `<span class="badge badge-${color} shadow-sm px-3 py-2" style="font-size: 0.9rem;">${data} Units</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        visible: false
                    }
                ],
                order: [
                    [3, 'asc']
                ],
                rowCallback: function(row, data) {
                    $(row).css('cursor', 'pointer').off('click').on('click', function() {
                        if (data.action && data.action.id) {
                            let safeName = encodeURIComponent(data.action.name).replace(
                                /%2F/g,
                                "");
                            window.location.href =
                                `/serialized_products/show/${data.action.id}/${safeName}`;
                        }
                    });
                }
            });
        });
    </script>
@endpush
