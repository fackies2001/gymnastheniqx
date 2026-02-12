@extends('layouts.adminlte')

@section('subtitle', 'Detailed List')
@section('content_header_title', 'Serialized Products')
@section('content_header_subtitle', $product_name)

@section('content_body')

    <div class="mb-3">
        {{-- ✅ TAMA: Babalik sa Layer 1 (Summary) --}}
        <a href="{{ route('serialized_products.index') }}" class="btn btn-default shadow-sm">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-navy">
                    <h3 class="card-title text-uppercase" style="letter-spacing: 1px;">
                        <i class="fas fa-barcode mr-2"></i> {{ $product_name }}
                    </h3>
                </div>
                <table class="table table-bordered table-hover w-100" id="specific_product_table">
                    <thead class="bg-light">
                        <tr>
                            <th>Serial Number</th>
                            <th>Status</th>
                            <th class="text-center">Scanned By</th>
                            <th>Order Date</th>
                            <th>Delivery Date</th>
                            <th>Scanned Date</th>
                            <th style="display: none;">Action Hidden</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            let token = "{{ session('sanctum_token') }}";
            let productId = "{{ $supplier_product_id }}";

            // 1. CLEAN UP: Iwas sa DataTables warning at re-init error
            if ($.fn.DataTable.isDataTable('#specific_product_table')) {
                $('#specific_product_table').DataTable().destroy();
            }

            // 2. DATA ROUTE: Safe replacement ng ID
            let url = "{{ route('serialized_products.showTable', ['id' => ':id']) }}";
            url = url.replace(':id', productId);

            // 3. INITIALIZATION: With Sorting Arrows and Responsive fix
            $('#specific_product_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                destroy: true, // Importante para sa Layer navigation
                ordering: true, // Para sa Sorting Arrows
                ajax: {
                    url: url,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    error: function(xhr) {
                        // Check if 'status' column error exists in Backend Controller
                        console.error("Fetch error:", xhr.responseText);
                    }
                },
                columns: [{
                        data: 'serial_number',
                        name: 'serial_number'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'scanned_by',
                        name: 'scanned_by',
                        className: 'text-center'
                    },
                    {
                        data: 'order_date',
                        name: 'order_date'
                    },
                    {
                        data: 'delivery_date',
                        name: 'delivery_date'
                    },
                    {
                        data: 'scanned_date',
                        name: 'scanned_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        visible: false, // Itago natin dahil plain text lang ito
                        searchable: false
                    }
                ],
                rowCallback: function(row, data) {
                    $(row).css('cursor', 'pointer').off('click').on('click', function() {
                        // 'data.action' ngayon ay yung $row->serial_number na plain text
                        if (data.action) {
                            window.location.href =
                                `/serialized_products/overview/${encodeURIComponent(data.action)}`;
                        }
                    });
                }
            });
        });
    </script>
@stop
