<div class="modal fade" id="scanInModal" tabindex="-1" role="dialog" aria-labelledby="scanInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center" id="scanInModalLabel">
                    <i class="fas fa-qrcode mr-2"></i> Scan-in Product
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- LEFT: Scanned Items Cart -->
                    <div class="col-md-6" style="min-height: 20rem;">

                        <button type="button" class="btn btn-sm btn-warning mb-2" id="scan_state">
                            <i class="fas fa-barcode mr-1"></i> Begin Scan
                        </button>

                        {{-- ✅ Barcode Input Field --}}
                        <input type="text" id="barcodeInput" class="form-control mb-3"
                            placeholder="📱 Scan barcode here..." autofocus
                            style="border: 2px solid #007bff; font-size: 1.1rem;">

                        <div class="mb-2 font-weight-bold">
                            <i class="fas fa-shopping-cart mr-2 text-success"></i> Scanned Items
                        </div>

                        {{-- ✅ UPDATED: Cart Table with Image Column --}}
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover" id="cartTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="60">Image</th>
                                        <th>Product Name</th>
                                        <th class="text-right" width="80">Price</th>
                                        <th class="text-center" width="150">Qty</th>
                                        <th class="text-right" width="100">Total</th>
                                        <th class="text-center" width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Cart items will be populated here by JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        {{-- Grand Total Card --}}
                        <div class="card bg-gradient-success text-white mt-3">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h5 class="mb-0">Grand Total:</h5>
                                    </div>
                                    <div class="col-auto">
                                        <h3 class="mb-0">₱<span id="grandTotal">0.00</span></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        @php
                            $assignedWarehouse = auth()->user()?->employee?->assignedAt;
                        @endphp

                        <input type="hidden" name="warehouse_id" id="warehouse_id"
                            value="{{ $assignedWarehouse?->id }}">

                    </div>

                    <!-- RIGHT: Purchase Order Items -->
                    <div class="col-md-6">
                        <div class="alert alert-primary alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong><i class="fas fa-clipboard-list mr-2"></i> Purchase Order Products</strong>
                            <br><small>Scan items to mark as received.</small>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm w-100" id="purchase_orders_table">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>SKU</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Unit Cost</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-center">Progress</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($purchase_order))
                                        @foreach ($purchase_order->purchaseRequest->pr_products() as $item)
                                            <tr data-product-id="{{ $item['product']?->id }}"
                                                data-sku="{{ $item['product']?->supplier_sku }}"
                                                data-required="{{ $item['quantity'] }}">
                                                <td class="text-center">
                                                    <small>{{ $item['product']?->supplier_sku }}</small>
                                                </td>
                                                <td class="text-start pl-3">
                                                    <strong>{{ $item['product']?->name }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $item['quantity'] }}</span>
                                                </td>
                                                <td class="text-right">₱{{ number_format($item['cost_price'], 2) }}</td>
                                                <td class="text-right">{{ $item['discount'] }}</td>
                                                <td class="text-right">₱{{ number_format($item['subtotal'], 2) }}</td>
                                                <td class="text-center text-danger progress-cell">
                                                    <i class="fas fa-times"></i>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light">
                <button class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button class="btn btn-success btn-lg" type="submit">
                    <i class="fas fa-save mr-2"></i> Serialize Products
                </button>
            </div>

        </div>
    </div>
</div>

@push('js')
    <script>
        window.appData = {
            token: "{{ session('sanctum_token') }}",
            po: @json($purchase_order ?? null),
            serializedProductsRoute: "{{ route('serialized_products.store') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>

    {{-- ✅ THIS IS WHAT'S MISSING - Load the barcode scanner JavaScript --}}
    @vite(['resources/js/barcode-scanner/barcode-scanner.js'])
@endpush
