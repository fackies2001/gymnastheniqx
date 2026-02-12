@extends('layouts.adminlte')

@section('title', 'Product Overview')

@section('content_body')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center d-print-none">
                <h3 class="card-title text-muted">
                    <i class="fas fa-tag mr-2"></i> Product ID: #{{ $product->id }}
                </h3>
                <a href="{{ route('reports.daily') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 text-center border-right mb-3">
                        <div class="product-placeholder d-flex align-items-center justify-content-center bg-light rounded"
                            style="height: 350px; border: 2px dashed #ddd;">
                            <i class="fas fa-dumbbell fa-10x text-secondary opacity-25"></i>
                        </div>
                    </div>

                    <div class="col-md-7 pl-md-4">
                        <h1 class="font-weight-bold text-dark mb-0">
                            {{ strtoupper(str_replace(['10KG', '5KG', '20KG', 'kg', 'KG'], '', $product->product_name)) }}
                        </h1>

                        <div class="mb-3 mt-2">
                            <span class="badge {{ $product->quantity > 0 ? 'badge-success' : 'badge-danger' }} px-3 py-2">
                                {{ $product->quantity > 0 ? 'IN STOCK' : 'OUT OF STOCK' }}
                            </span>
                        </div>

                        <div class="row mb-4 mt-4">
                            <div class="col-6 border-right">
                                <label class="text-muted small mb-1 text-uppercase" style="letter-spacing: 1px;">Base Serial
                                    Number</label>
                                <h5 class="font-weight-bold text-primary">
                                    SRN-{{ date('Y') }}-{{ str_pad($product->id, 6, '0', STR_PAD_LEFT) }}
                                </h5>
                            </div>
                            <div class="col-6 text-center">
                                <label class="text-muted small mb-1 text-uppercase" style="letter-spacing: 1px;">Product
                                    Barcode (SKU)</label>
                                <div class="p-2 border rounded bg-white shadow-sm">
                                    {!! DNS1D::getBarcodeHTML($product->sku ?? 'PROD-' . $product->id, 'C128', 1.5, 40) !!}
                                    <div class="mt-1 small font-weight-bold">{{ $product->sku ?? 'PROD-' . $product->id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row detail-box bg-light p-3 rounded">
                            <div class="col-sm-6">
                                <label class="text-muted mb-0 small">Category</label>
                                <p class="font-weight-bold mb-3">{{ $product->category ?? 'General Equipment' }}</p>

                                <label class="text-muted mb-0 small">Current Inventory</label>
                                <p class="font-weight-bold text-info mb-0" style="font-size: 1.4rem;">
                                    {{ number_format($product->quantity) }} units
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted mb-0 small">Unit Price</label>
                                <p class="font-weight-bold mb-3 text-dark">₱ {{ number_format($product->price, 2) }}</p>

                                <label class="text-muted mb-0 small">Last Encoder</label>
                                <p class="font-weight-bold mb-0 text-muted">{{ Auth::user()->name }}</p>
                            </div>
                        </div>

                        <div class="mt-4 d-print-none">
                            <button onclick="window.print()" class="btn btn-dark px-4 shadow-sm">
                                <i class="fas fa-print mr-2"></i> Print Tag
                            </button>

                            <button type="button" class="btn btn-outline-primary px-4 shadow-sm ml-2" data-toggle="modal"
                                data-target="#updateStockModal">
                                <i class="fas fa-plus-circle mr-2"></i> Update Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-right d-print-none">
                <button type="button" class="btn btn-danger px-4 shadow-sm" data-toggle="modal"
                    data-target="#reportDamageModal">
                    <i class="fas fa-tools mr-2"></i> Report Damage
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="updateStockForm">
                    @csrf
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-boxes mr-2"></i> Restock Inventory</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group text-center">
                            <label class="text-muted">Item to Restock:</label>
                            <h4>{{ $product->product_name }}</h4>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Quantity to Add</label>
                            <input type="number" name="new_quantity" class="form-control form-control-lg text-center"
                                placeholder="0" min="1" required>
                            <small class="text-info mt-2 d-block">
                                <i class="fas fa-info-circle"></i> Each unit will be assigned a unique Serial Number.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-between">
                        <button type="button" class="btn btn-link text-muted px-0" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Confirm Restock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reportDamageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="reportDamageForm">
                    @csrf
                    <div class="modal-header bg-danger text-white border-0">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Damage
                            Report</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Damaged Quantity</label>
                            <input type="number" name="damaged_qty" class="form-control"
                                max="{{ $product->quantity }}" min="1" required>
                            <small class="text-muted">Max adjustable: {{ $product->quantity }} units</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for damage..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-between">
                        <button type="button" class="btn btn-link text-muted px-0" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // --- AJAX for Update Stock ---
            $('#updateStockForm').on('submit', function(e) {
                e.preventDefault();
                let $btn = $(this).find('button[type="submit"]');
                $btn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

                let formData = {
                    _token: "{{ csrf_token() }}",
                    product_id: "{{ $product->id }}",
                    new_quantity: $('input[name="new_quantity"]').val()
                };

                $.post("{{ route('reports.update.stock') }}", formData)
                    .done(function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Stock Updated!',
                            text: response.message,
                            confirmButtonColor: '#007bff'
                        }).then(() => location.reload());
                    })
                    .fail(function(xhr) {
                        let errorMsg = xhr.responseJSON ? (xhr.responseJSON.error || xhr.responseJSON
                            .message) : 'Connection Error';
                        Swal.fire('Error!', errorMsg, 'error');
                        $btn.prop('disabled', false).text('Confirm Restock');
                    });
            });

            // --- AJAX for Report Damage ---
            $('#reportDamageForm').on('submit', function(e) {
                e.preventDefault();
                let $btn = $(this).find('button[type="submit"]');

                Swal.fire({
                    title: 'Deduct from Inventory?',
                    text: "This will reduce your available stock count.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, deduct it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $btn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin mr-1"></i> Reporting...');

                        let formData = {
                            _token: "{{ csrf_token() }}",
                            product_id: "{{ $product->id }}",
                            damaged_qty: $('input[name="damaged_qty"]').val(),
                            remarks: $('textarea[name="remarks"]').val()
                        };

                        $.post("{{ route('reports.report.damage') }}", formData)
                            .done(function(response) {
                                Swal.fire('Success!', response.message, 'success')
                                    .then(() => location.reload());
                            })
                            .fail(function(xhr) {
                                let errorMsg = xhr.responseJSON ? (xhr.responseJSON.error || xhr
                                    .responseJSON.message) : 'Action failed';
                                Swal.fire('Error!', errorMsg, 'error');
                                $btn.prop('disabled', false).text('Submit Report');
                            });
                    }
                });
            });
        });
    </script>
@endpush
