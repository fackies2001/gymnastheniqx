@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Supplier Products')
@section('content_header_subtitle', 'All Supplier Products')

@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="row">
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <div class="card-title mb-0 text-uppercase" style="letter-spacing: 1ch;">
                                Supplier Products <span class="px-4" style="background-color: whitesmoke; color: red;">
                                    <b>{{ $supplier->name }}</b>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-bordered table-striped w-100">
                            <thead style="background-color: #343a40; color: white;"> {{-- ✅ CUSTOM DARK --}}
                                <tr>
                                    <th>Supplier</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let token = "{{ session('sanctum_token') ?? '' }}";
            let id = "{{ $supplier->id ?? 0 }}";
            let url = "{{ route('suppliers_products.show_table', ['id' => ':id']) }}".replace(':id', id);

            console.log('🔵 Initializing DataTable for Supplier ID:', id);

            // ✅ EXACTLY 6 COLUMNS to match 6 <th> headers
            var columns = [{
                    data: 'supplier_name',
                    name: 'supplier.name', // ✅ Point to actual relationship
                    defaultContent: 'N/A'
                },
                {
                    data: 'category_name',
                    name: 'category.name', // ✅ Point to actual relationship
                    defaultContent: 'N/A'
                },
                {
                    data: 'product_name',
                    name: 'name', // ✅ Point to actual column
                    defaultContent: 'N/A'
                },
                {
                    data: 'system_sku',
                    name: 'system_sku',
                    defaultContent: 'N/A'
                },
                {
                    data: 'cost_price',
                    name: 'cost_price',
                    defaultContent: '0.00',
                    orderable: true // ✅ Enable sorting
                },
                {
                    data: 'date_created',
                    name: 'created_at', // ✅ Point to actual column
                    defaultContent: '-',
                    orderable: true
                }
            ];
            $('#productsTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: url,
                    type: "GET",
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    },
                    dataSrc: function(json) {
                        console.log('✅ DataTable received data:', json.data.length, 'rows');
                        return json.data;
                    },
                    error: function(xhr) {
                        console.error("❌ DataTables Error:", xhr.responseText);
                    }
                },
                columns: columns,
                responsive: true
            });
        });
    </script>
@endpush
