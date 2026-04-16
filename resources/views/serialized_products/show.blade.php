@extends('layouts.adminlte')

@section('subtitle', 'Stock Movement History')
@section('content_header_title', 'Products')
@section('content_header_subtitle', $product_name)

@section('content_body')

    <div class="mb-3">
        <a href="{{ route('serialized_products.index') }}" class="btn btn-default shadow-sm">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="row">

        {{-- ✅ LEFT CARD: Product Info + Current Stock --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-navy text-white">
                    <h5 class="mb-0 text-uppercase" style="letter-spacing:1px; font-size:13px;">
                        <i class="fas fa-box mr-1"></i> {{ $product_name }}
                    </h5>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted" style="font-size:12px; width:45%">System SKU</th>
                            <td>
                                <span class="badge badge-info">{{ $product->system_sku ?? '—' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted" style="font-size:12px;">Supplier</th>
                            <td style="font-size:13px;">{{ $product->supplier->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted" style="font-size:12px;">Current Stock</th>
                            <td>
                                @if ($current_stock < ($stock->min_stock_level ?? 20))
                                    <span class="badge badge-danger" style="font-size:13px;">
                                        {{ $current_stock }} pcs
                                    </span>
                                @elseif ($current_stock > 0)
                                    <span class="badge badge-success" style="font-size:13px;">
                                        {{ $current_stock }} pcs
                                    </span>
                                @else
                                    <span class="badge badge-secondary" style="font-size:13px;">0 pcs</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted" style="font-size:12px;">Min Level</th>
                            <td style="font-size:13px;">{{ $stock->min_stock_level ?? 20 }} pcs</td>
                        </tr>
                    </table>

                    @if ($stock && $stock->isLowStock())
                        <div class="alert alert-danger py-1 px-2 mb-0 mt-2" style="font-size:12px;">
                            <i class="fas fa-exclamation-triangle"></i> LOW STOCK ALERT
                        </div>
                    @endif
                </div>
            </div>

            {{-- ✅ Stock Formula Card --}}
            <div class="card shadow-sm border-0 mt-2">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0" style="font-size:12px;">
                        <i class="fas fa-calculator mr-1"></i> Stock Formula
                    </h6>
                </div>
                <div class="card-body py-2 px-3">
                    <code style="font-size:11px; color:#333;">
                        Stock = IN − OUT − DAMAGE − LOSS ± ADJUSTMENT
                    </code>
                </div>
            </div>

            {{-- ✅ Quick Actions --}}
            <div class="card shadow-sm border-0 mt-2">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0" style="font-size:12px;">
                        <i class="fas fa-bolt mr-1"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body p-2">
                    <button class="btn btn-warning btn-block btn-sm mb-1" data-toggle="modal"
                        data-target="#reportIncidentModal">
                        <i class="fas fa-exclamation-triangle"></i> Report Damage / Loss
                    </button>
                    <button class="btn btn-secondary btn-block btn-sm" data-toggle="modal" data-target="#adjustModal">
                        <i class="fas fa-sliders-h"></i> Stock Adjustment
                    </button>
                </div>
            </div>
        </div>

        {{-- ✅ RIGHT: Movement History Table --}}
        <div class="col-md-9">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-navy">
                    <h3 class="card-title text-uppercase" style="letter-spacing:1px;">
                        <i class="fas fa-history mr-2"></i> Stock Movement History
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover table-sm w-100" id="movement_table">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>Reference</th>
                                <th>Recorded By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Report Incident Modal --}}
    <div class="modal fade" id="reportIncidentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Report Damage / Loss
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="reportIncidentForm">
                        @csrf
                        {{-- Hidden — auto-filled sa product na pinili --}}
                        <input type="hidden" name="product_id" value="{{ $supplier_product_id }}">

                        <div class="form-group">
                            <label>Incident Type</label>
                            <select name="type" class="form-control" required>
                                <option value="damage">❌ Damage</option>
                                <option value="loss">⚠️ Loss</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity (pcs)</label>
                            <input type="number" name="quantity" class="form-control" min="1" required
                                placeholder="How many pieces were damaged/lost?">
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <select name="reason_type" class="form-control" required>
                                <option value="defective_on_arrival">Defective on arrival (DOA)</option>
                                <option value="damaged_in_storage">Damaged in storage</option>
                                <option value="leaked">Leaked / Spilled</option>
                                <option value="expired">Expired</option>
                                <option value="lost_in_transit">Lost in transit</option>
                                <option value="missing_in_count">Missing in inventory count</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Remarks <small class="text-muted">(optional)</small></label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="submitIncident">
                        <i class="fas fa-save"></i> Submit Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ Adjustment Modal --}}
    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-sliders-h"></i> Stock Adjustment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size:13px;">
                        <i class="fas fa-info-circle"></i>
                        Use this if the actual count does not match the system count.
                    </div>
                    <form id="adjustForm">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $supplier_product_id }}">

                        <div class="form-group">
                            <label>System Count (current)</label>
                            <input type="text" class="form-control" readonly
                                value="{{ ($stock->current_qty ?? 0) . ' pcs' }}">
                        </div>
                        <div class="form-group">
                            <label>Actual Count <span class="text-danger">*</span></label>
                            <input type="number" name="actual_qty" class="form-control" min="0" required
                                placeholder="Actual physical count...">
                        </div>
                        <div class="form-group">
                            <label>Reason for Adjustment <span class="text-danger">*</span></label>
                            <input type="text" name="remarks" class="form-control" required
                                placeholder="e.g. Weekly physical count — Mar 27, 2026">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-dark" id="submitAdjust">
                        <i class="fas fa-save"></i> Save Adjustment
                    </button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        $(document).ready(function() {

            // ✅ Movement History DataTable
            $('#movement_table').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: "{{ route('serialized_products.showTable', ['id' => $supplier_product_id]) }}",
                    type: 'GET',
                    dataSrc: 'data',
                    error: function(xhr) {
                        console.error('Movement history fetch error:', xhr.responseText);
                    }
                },
                columns: [{
                        data: 'date',
                        name: 'date',
                        render: function(data) {
                            return data ? moment(data).format('MMM D, YYYY h:mm A') : '—';
                        }
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'reason',
                        name: 'reason'
                    },
                    {
                        data: 'reference',
                        name: 'reference'
                    },
                    {
                        data: 'recorded_by',
                        name: 'recorded_by'
                    },
                    {
                        data: 'remarks',
                        name: 'remarks'
                    },
                ],
                language: {
                    emptyTable: 'Walang stock movement record pa para sa product na ito.',
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading movements...'
                }
            });

            // ✅ Submit Damage / Loss Report
            $('#submitIncident').on('click', function() {
                if (!$('#reportIncidentForm')[0].checkValidity()) {
                    $('#reportIncidentForm')[0].reportValidity();
                    return;
                }
                let btn = $(this).prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: "{{ route('consumables.report-incident') }}",
                    method: 'POST',
                    data: $('#reportIncidentForm').serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success')
                                .then(() => location
                                    .reload()); // ← reload para makita ang updated qty
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ?? 'Something went wrong.', 'error');
                    }, // ← COMMA!
                    complete: function() {
                        $('#submitIncident').prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Submit Report');
                    }
                });
            });

            // ✅ Submit Stock Adjustment
            $('#submitAdjust').on('click', function() {
                if (!$('#adjustForm')[0].checkValidity()) {
                    $('#adjustForm')[0].reportValidity();
                    return;
                }
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: "{{ route('consumables.adjust') }}",
                    method: 'POST',
                    data: $('#adjustForm').serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Success!', res.message, 'success')
                                .then(() => location.reload());
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message ??
                            'May error na nangyari.', 'error');
                    },
                    complete: function() {
                        $('#submitAdjust').prop('disabled', false)
                            .html('<i class="fas fa-save"></i> Save Adjustment');
                    }
                });
            });
        });
    </script>
@stop

@prepend('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
@endprepend
