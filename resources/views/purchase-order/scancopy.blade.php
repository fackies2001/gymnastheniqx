@extends('layouts.adminlte')

@section('subtitle', 'Scan Purchase Order')
@section('content_header_title', 'Purchase Orders')
@section('content_header_subtitle', 'Scan Items - ' . $po->po_number)

@section('content_body')
    <div id="scanContainer" data-po-id="{{ $po->id }}">

        {{-- HEADER SECTION --}}
        {{-- <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-gradient-primary">
                        <h3 class="card-title text-white">
                            <i class="fas fa-barcode mr-2"></i>
                            <strong>{{ $po->po_number }}</strong> - {{ $po->supplier->name ?? 'N/A' }}
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-light" id="cancelScanBtn">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                        </div>
                    </div> --}}
                    {{-- <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Order Date:</strong></p>
                                <p>{{ $po->order_date ? $po->order_date->format('M d, Y') : 'N/A' }}</p>
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <p class="mb-1"><strong>Delivery Date:</strong></p>
                                <p>{{ $po->delivery_date ? $po->delivery_date->format('M d, Y') : 'N/A' }}</p>
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <p class="mb-1"><strong>Total Items:</strong></p>
                                <p><span class="badge badge-info"
                                        id="totalOrdered">{{ $po->items->sum('quantity_ordered') }}</span></p>
                            </div> --}}
                            {{-- <div class="col-md-3">
                                <p class="mb-1"><strong>Grand Total:</strong></p>
                                <p class="text-success font-weight-bold">₱{{ number_format($po->grand_total ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- SCANNER STATUS ALERT --}}
        {{-- <div class="alert alert-warning shadow-sm" id="scanStatusAlert" style="display: none;">
            <div class="d-flex align-items-center">
                <div class="mr-3">
                    <div class="status-dot" id="statusDot"></div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong id="scanStatus">Ready to Scan</strong>
                    </h5>
                    <p class="mb-0 small">Point your scanner at the barcode or type manually</p>
                </div>
            </div>
        </div> --}}

        {{-- MAIN SCANNING INTERFACE --}}
        <div class="row">

            {{-- LEFT: SCANNER INPUT & SCANNED ITEMS --}}
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-qrcode mr-2"></i> Scanner Input
                        </h5>
                    </div>
                    <div class="card-body">

                        {{-- SCANNER CONTROLS --}}
                        <div class="mb-3">
                            <button type="button" class="btn btn-success btn-lg btn-block" id="beginScanBtn">
                                <i class="fas fa-play mr-2"></i> BEGIN SCAN
                            </button>
                            <button type="button" class="btn btn-danger btn-lg btn-block" id="endScanBtn"
                                style="display: none;">
                                <i class="fas fa-stop mr-2"></i> END SCAN
                            </button>
                        </div>

                        {{-- BARCODE INPUT FIELD --}}
                        <div class="form-group">
                            <label for="barcodeInput"><i class="fas fa-barcode mr-2"></i>Barcode</label>
                            <input type="text" id="barcodeInput" class="form-control form-control-lg"
                                placeholder="📱 Scan or type barcode here..." disabled autocomplete="off"
                                style="border: 3px solid #007bff; font-size: 1.2rem; font-family: 'Courier New', monospace;">
                            <small class="form-text text-muted">
                                Press ENTER after scanning or typing
                            </small>
                        </div>

                        {{-- SCAN STATISTICS --}}
                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text">Scanned</span>
                                        <span class="info-box-number text-primary" id="totalScanned">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text">Ordered</span>
                                        <span class="info-box-number text-info"
                                            id="totalOrderedDisplay">{{ $po->items->sum('quantity_ordered') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text">Progress</span>
                                        <span class="info-box-number text-success" id="scanProgress">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- SCANNED ITEMS LIST --}}
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Scanned Items
                            <span class="badge badge-success float-right" id="scannedCount">0 items</span>
                        </h6>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th width="120">Barcode</th>
                                        <th>Product Name</th>
                                        <th class="text-center" width="80">Qty</th>
                                        <th class="text-right" width="100">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="scannedItemsBody">
                                    <tr class="empty-state">
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">No items scanned yet</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- GRAND TOTAL --}}
                        <div class="card bg-gradient-success text-white mt-3">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h5 class="mb-0">Scanned Total:</h5>
                                    </div>
                                    <div class="col-auto">
                                        <h3 class="mb-0" id="scanGrandTotal">₱0.00</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: PURCHASE ORDER ITEMS --}}
            {{-- <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-clipboard-list mr-2"></i> Purchase Order Items
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-dark sticky-top">
                                    <tr>
                                        <th width="40"></th>
                                        <th>Product Name</th>
                                        <th class="text-center" width="80">Qty</th>
                                        <th class="text-right" width="100">Price</th>
                                        <th width="200">Progress</th>
                                    </tr>
                                </thead>
                                <tbody id="poItemsBody">
                                    @foreach ($po->items as $item)
                                        <tr data-product-id="{{ $item->product_id }}"
                                            data-quantity-ordered="{{ $item->quantity_ordered }}"
                                            data-quantity-scanned="{{ $item->quantity_scanned ?? 0 }}">
                                            <td class="text-center">
                                                <i class="far fa-circle text-muted"></i>
                                            </td>
                                            <td>
                                                <strong>{{ $item->supplierProduct->name ?? 'Unknown Product' }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    SKU: {{ $item->supplierProduct->supplier_sku ?? 'N/A' }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary">{{ $item->quantity_ordered }}</span>
                                            </td>
                                            <td class="text-right">
                                                ₱{{ number_format($item->unit_cost, 2) }}
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                                        role="progressbar" style="width: 0%">
                                                        <span class="progress-text">0/{{ $item->quantity_ordered }}</span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">0% complete</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- FOOTER ACTIONS --}}
        {{-- <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <button type="button" class="btn btn-success btn-lg px-5" id="completeScanBtn">
                            <i class="fas fa-check-double mr-2"></i> COMPLETE SCANNING
                        </button>
                    </div>
                </div>
            </div>
        </div> --}}

    </div>
@endsection

{{-- @push('css')
    <style>
        /* Scanner Status Dot Animation */
        .status-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #6c757d;
            transition: all 0.3s ease;
        }

        .status-dot.active {
            background: #28a745;
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        /* Barcode Input Highlight */
        #barcodeInput:focus {
            border-color: #28a745;
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }

        #barcodeInput:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
        }

        /* Progress Bar Text */
        .progress-text {
            font-weight: bold;
            font-size: 12px;
        }

        /* Completed Row Style */
        tr.completed {
            background-color: #d4edda !important;
        }

        tr.completed td {
            color: #155724;
        }

        /* Sticky Header */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Info Box Styling */
        .info-box {
            border-radius: 8px;
            padding: 10px;
        }

        .info-box-number {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
@endpush --}}

@push('js')
    {{-- Load Barcode Scanner JavaScript --}}
    {{-- @vite(['resources/js/barcode-scanner/barcode-scanner.js']) --}}
@endpush

feb 12