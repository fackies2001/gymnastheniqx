@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Supplier Products')
@section('content_header_subtitle', 'All Supplier Products')

@section('content_body')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="row w-100">
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
                            <thead style="background-color: #343a40; color: white;">
                                <tr>
                                    <th>Supplier</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
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

            var table = $('#productsTable').DataTable({
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
                        return json.data;
                    },
                    error: function(xhr) {
                        console.error("❌ DataTables Error:", xhr.responseText);
                    }
                },
                columns: [{
                        data: 'supplier_name',
                        name: 'supplier.name',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'category_name',
                        name: 'category.name',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'product_name',
                        name: 'supplier_product.name',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'system_sku',
                        name: 'supplier_product.system_sku',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'cost_price',
                        name: 'supplier_product.cost_price',
                        defaultContent: '0.00'
                    },
                    {
                        data: 'date_created',
                        name: 'supplier_product.created_at',
                        defaultContent: '-'
                    },
                    {
                        // ✅ ACTION COLUMN — Delete button
                        data: 'id',
                        name: 'id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `<button class="btn btn-danger btn-sm delete-product-btn"
                                        data-id="${data}"
                                        data-name="${row.product_name ?? 'this product'}"
                                        title="Delete Product">
                                        <i class="fas fa-trash"></i>
                                    </button>`;
                        }
                    }
                ],
                responsive: true
            });

            // ✅ DELETE BUTTON CLICK
            $(document).on('click', '.delete-product-btn', function() {
                let productId = $(this).data('id');
                let productName = $(this).data('name');

                Swal.fire({
                    title: 'Delete Product?',
                    html: `Are you sure you want to delete <b>${productName}</b>?<br><br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> This cannot be undone. The product will be permanently removed from the database.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/supplier_products/' + productId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        table.ajax
                                            .reload(); // ✅ Reload table — no page refresh needed
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Delete Failed',
                                        text: response.message,
                                        confirmButtonColor: '#d33'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        'Something went wrong.',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
