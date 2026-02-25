@extends('layouts.adminlte')

@section('subtitle', 'Supplier Products')
@section('content_header_title', 'Supplier Products')
@section('content_header_subtitle', 'All Supplier Products')

@section('content_body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Scan product to check details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 d-flex align-items-center">
                                <span class="mr-2">Track by SRN:</span>
                                <input type="text" placeholder="Enter SRN" id="barcodeInput"
                                    style="border: none; border-bottom: 1px solid salmon; outline: none; box-shadow: none;">
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div id="lottie-animation" style="width: 400px; height: 400px;"></div>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-group">
                                    <li class="list-group-item disabled">How to check serialized product details</li>
                                    <li class="list-group-item">You can use input field/scanner</li>
                                    <li class="list-group-item">Once done it will redirect you to product overview</li>
                                    <li class="list-group-item">You can also change product status</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer"></div>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- ✅ AYOS: @push('js') ay LABAS ng @section('content_body') --}}
@push('js')
    <script>
        window.onload = function() {
            var container = document.getElementById('lottie-animation');
            if (typeof lottie !== "undefined" && container && container.children.length === 0) {
                lottie.loadAnimation({
                    container: container,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: "{{ asset('images/Scanning.json') }}"
                });
            }

            $('#barcodeInput').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    const code = $(this).val().trim();
                    $(this).val('');
                    processBarcode(code);
                }
            });

            function processBarcode(code) {
                if (code.trim() === "") return;
                const cleanCode = code.replace(/<\/?[^>]+(>|$)/g, "").trim();
                const encoded_serial_number = encodeURIComponent(cleanCode);
                const url = "{{ url('serialized_products/overview') }}/" + encoded_serial_number;
                window.location.href = url;
            }
        };
    </script>
@endpush
