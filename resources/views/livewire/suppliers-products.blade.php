<div>
    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <label class="small font-weight-bold">Select Supplier</label>
                <select wire:model.live="selectedSupplier" class="form-control form-control-sm">
                    <option value="">-- Select Supplier --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-7">
            <span class="small font-weight-bold text-info text-uppercase">
                <i class="fas fa-search mr-1"></i> Product Catalog
            </span>
            <div class="table-responsive mt-1" style="max-height: 300px; border: 1px solid #dee2e6;">
                <table class="table table-sm table-hover mb-0">
                    <thead style="background-color: #17a2b8; color: white;">
                        <tr>
                            <th class="small">Product</th>
                            <th class="small" width="100">Unit Cost</th>
                            <th class="small text-center" width="60">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="small">{{ $product->product_name }}</td>
                                <td class="small">₱{{ number_format($product->cost_price, 2) }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-info select-product"
                                        data-id="{{ $product->id }}" data-name="{{ $product->product_name }}"
                                        data-cost="{{ $product->cost_price }}">
                                        Add
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center small text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
