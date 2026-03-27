@extends('adminlte::page')

@section('title', 'Consumables Inventory')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-boxes"></i> Consumables <small class="text-muted">Stock Level Overview</small></h1>
        <div>
            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#reportIncidentModal">
                <i class="fas fa-exclamation-triangle"></i> Report Damage/Loss
            </button>
            <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#adjustModal">
                <i class="fas fa-sliders-h"></i> Stock Adjustment
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">

        {{-- ✅ Summary Cards --}}
        <div class="row mb-3" id="summaryCards">
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Low Stock Items</span>
                        <span class="info-box-number" id="lowStockCount">—</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Daily Received</span>
                        <span class="info-box-number" id="dailyReceived">—</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Daily Outflow</span>
                        <span class="info-box-number" id="dailyOutflow">—</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Damaged / Lost Today</span>
                        <span class="info-box-number" id="dailyDamagedLost">—</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ✅ Stock Level Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> STOCK LEVEL OVERVIEW</h3>
            </div>
            <div class="card-body">
                <table id="consumablesTable" class="table table-bordered table-striped table-hover">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Product Name</th>
                            <th>System SKU</th>
                            <th>Supplier</th>
                            <th>Current Stock</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- ✅ Report Incident Modal --}}
        <div class="modal fade" id="reportIncidentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Report Damage / Loss</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="reportIncidentForm">
                            @csrf
                            <div class="form-group">
                                <label>Product</label>
                                <select name="product_id" class="form-control select2" required>
                                    <option value="">— Select Product —</option>
                                    @foreach ($stocks as $stock)
                                        <option value="{{ $stock->product_id }}">
                                            {{ $stock->product->name ?? '—' }} (Stock: {{ $stock->current_qty }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Incident Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="damage">❌ Damage (Nasira)</option>
                                    <option value="loss">⚠️ Loss (Nawala)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Quantity (pcs)</label>
                                <input type="number" name="quantity" class="form-control" min="1" required>
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
                        <h5 class="modal-title"><i class="fas fa-sliders-h"></i> Stock Adjustment</h5>
                        <button type="button" class="close text-white"
                            data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Gamitin ito kung hindi match ang actual count vs system count.
                        </div>
                        <form id="adjustForm">
                            @csrf
                            <div class="form-group">
                                <label>Product</label>
                                <select name="product_id" class="form-control select2" required id="adjustProductSelect">
                                    <option value="">— Select Product —</option>
                                    @foreach ($stocks as $stock)
                                        <option value="{{ $stock->product_id }}" data-qty="{{ $stock->current_qty }}">
                                            {{ $stock->product->name ?? '—' }} (System: {{ $stock->current_qty }} pcs)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>System Count</label>
                                <input type="text" id="systemQtyDisplay" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label>Actual Count (physical count)</label>
                                <input type="number" name="actual_qty" class="form-control" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Reason for Adjustment <span class="text-danger">*</span></label>
                                <input type="text" name="remarks" class="form-control"
                                    placeholder="e.g. Weekly physical count — Feb 28, 2026" required>
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

    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {

            // ✅ Load summary cards
            function loadSummary() {
                $.get("{{ route('consumables.daily-summary') }}", function(data) {
                    $('#lowStockCount').text(data.low_stock_count + ' items');
                    $('#dailyReceived').text(data.daily_received + ' pcs');
                    $('#dailyOutflow').text(data.daily_outflow + ' pcs');
                    $('#dailyDamagedLost').text(data.daily_damaged_lost + ' pcs');
                });
            }
            loadSummary();

            // ✅ DataTable
            $('#consumablesTable').DataTable({
                ajax: {
                    url: "{{ route('consumables.table') }}",
                    dataSrc: 'data'
                },
                columns: [{
                        data: 'product_name'
                    },
                    {
                        data: 'system_sku'
                    },
                    {
                        data: 'supplier_name'
                    },
                    {
                        data: 'current_qty',
                        render: function(data, type, row) {
                            let color = row.is_low_stock ? 'danger' : 'success';
                            return `<button class="btn btn-${color} btn-sm">${data} Units</button>`;
                        }
                    },
                    {
                        data: 'min_stock_level',
                        render: d => d + ' pcs'
                    },
                    {
                        data: 'status_badge'
                    },
                    {
                        data: 'id',
                        render: function(id, type, row) {
                            return `
                        <a href="/consumables/${id}" class="btn btn-info btn-xs">
                            <i class="fas fa-eye"></i> View History
                        </a>
                        <button class="btn btn-secondary btn-xs btn-set-min" data-id="${id}" data-name="${row.product_name}">
                            <i class="fas fa-cog"></i> Set Min
                        </button>
                    `;
                        }
                    },
                ],
                rowCallback: function(row, data) {
                    if (data.is_low_stock) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // ✅ Show system qty in adjustment modal
            $('#adjustProductSelect').on('change', function() {
                let qty = $(this).find(':selected').data('qty');
                $('#systemQtyDisplay').val(qty !== undefined ? qty + ' pcs' : '—');
            });

            // ✅ Submit incident report
            $('#submitIncident').on('click', function() {
                let formData = $('#reportIncidentForm').serialize();
                $.post("{{ route('consumables.report-incident') }}", formData, function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#reportIncidentModal').modal('hide');
                        $('#consumablesTable').DataTable().ajax.reload();
                        loadSummary();
                    }
                }).fail(function(xhr) {
                    toastr.error(xhr.responseJSON?.message ?? 'Error occurred.');
                });
            });

            // ✅ Submit adjustment
            $('#submitAdjust').on('click', function() {
                let formData = $('#adjustForm').serialize();
                $.post("{{ route('consumables.adjust') }}", formData, function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#adjustModal').modal('hide');
                        $('#consumablesTable').DataTable().ajax.reload();
                        loadSummary();
                    }
                }).fail(function(xhr) {
                    toastr.error(xhr.responseJSON?.message ?? 'Error occurred.');
                });
            });
        });
    </script>
@endsection
