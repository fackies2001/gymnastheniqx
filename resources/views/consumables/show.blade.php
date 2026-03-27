@extends('adminlte::page')

@section('title', $product->name . ' — Movement History')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-history"></i> {{ $product->name }} <small class="text-muted">Movement History</small></h1>
        <a href="{{ route('consumables.index') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{-- ✅ Product Info Card --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">{{ strtoupper($product->name) }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>System SKU</th>
                                <td>
                                    <span class="badge badge-info">{{ $product->system_sku ?? '—' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th>Supplier</th>
                                <td>{{ $product->supplier->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Current Stock</th>
                                <td>
                                    @if ($stock)
                                        <span
                                            class="badge badge-{{ $stock->isLowStock() ? 'danger' : 'success' }} badge-lg">
                                            {{ $stock->current_qty }} pcs
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">0 pcs</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Min Stock Level</th>
                                <td>{{ $stock->min_stock_level ?? 20 }} pcs</td>
                            </tr>
                            @if ($stock && $stock->isLowStock())
                                <tr>
                                    <td colspan="2">
                                        <div class="alert alert-danger mb-0 py-1">
                                            <i class="fas fa-exclamation-triangle"></i> LOW STOCK ALERT
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- ✅ Formula Card --}}
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calculator"></i> Stock Formula</h6>
                    </div>
                    <div class="card-body py-2">
                        <code class="text-dark" style="font-size:13px;">
                            Stock = IN − OUT − DAMAGE − LOSS ± ADJUSTMENT
                        </code>
                    </div>
                </div>
            </div>

            {{-- ✅ Movement History Table --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> All Stock Movements</h5>
                    </div>
                    <div class="card-body">
                        <table id="movementsTable" class="table table-bordered table-striped table-hover table-sm">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Reason</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
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

@section('js')
    <script>
        $(document).ready(function() {
            $('#movementsTable').DataTable({
                ajax: {
                    url: "{{ route('consumables.movements', $product->id) }}",
                    dataSrc: 'data'
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'date'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'reason'
                    },
                    {
                        data: 'reference'
                    },
                    {
                        data: 'recorded_by'
                    },
                ]
            });
        });
    </script>
@endsection
