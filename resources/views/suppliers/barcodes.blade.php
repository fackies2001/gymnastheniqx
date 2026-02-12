@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Products')
@section('content_header_subtitle', 'Supplier Barcodes')

@section('content_body')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" style="letter-spacing: 1px;">📦 Product Barcodes</h5>
                    <small class="text-light">Total: {{ count($barcodes) }}</small>
                </div>

                <div class="card-body">
                    @if ($barcodes->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-barcode fa-3x mb-3"></i>
                            <p>No barcodes available</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($barcodes as $barcode)
                                <div class="col-md-3 col-sm-6">
                                    <div class="card h-100 text-center border-0 shadow-sm hover-shadow">
                                        <div class="card-body">
                                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($barcode, 'C128') }}"
                                                alt="barcode" class="img-fluid mb-2">
                                            <p class="mb-0 fw-semibold text-dark" style="font-size: 0.9rem;">
                                                {{ $barcode }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: 0.2s ease-in-out;
        }
    </style>
@stop
