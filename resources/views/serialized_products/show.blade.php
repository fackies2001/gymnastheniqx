@extends('layouts.adminlte')

@section('subtitle', 'Detailed List')
@section('content_header_title', 'Serialized Products')
@section('content_header_subtitle', $product_name)

@section('content_body')

    <div class="mb-3">
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
@stop

@section('js')
    <script>
        $(document).ready(function() {
            let token = "{{ session('sanctum_token') }}";
            let productId = "{{ $supplier_product_id }}";

            // Clean up existing DataTable
            if ($.fn.DataTable.isDataTable('#specific_product_table')) {
                $('#specific_product_table').DataTable().destroy();
            }

            // Safe URL replacement
            let url = "{{ route('serialized_products.showTable', ['id' => ':id']) }}";
            url = url.replace(':id', productId);

            // Initialize DataTable
            $('#specific_product_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                destroy: true,
                ordering: true,
                ajax: {
                    url: url,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    error: function(xhr) {
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
                        className: 'text-center',
                        // ✅ FIXED: Show employee name instead of "System"
                        render: function(data) {
                            return data || 'N/A';
                        }
                    },
                    {
                        data: 'order_date',
                        name: 'order_date',
                        // ✅ FIXED: Remove time, show date only
                        render: function(data) {
                            if (!data || data === '-') return '-';
                            return moment(data).format('MMM D, YYYY');
                        }
                    },
                    {
                        data: 'delivery_date',
                        name: 'delivery_date',
                        // ✅ FIXED: Remove time, show date only
                        render: function(data) {
                            if (!data || data === '-') return '-';
                            return moment(data).format('MMM D, YYYY');
                        }
                    },
                    {
                        data: 'scanned_at',
                        name: 'scanned_at',
                        // ✅ FIXED: Show actual scanned date/time
                        render: function(data) {
                            if (!data || data === '-') return '-';
                            return moment(data).format('MMM D, YYYY h:mm A');
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        visible: false,
                        searchable: false
                    }
                ],
                rowCallback: function(row, data) {
                    $(row).css('cursor', 'pointer').off('click').on('click', function() {
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

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
@endpush
