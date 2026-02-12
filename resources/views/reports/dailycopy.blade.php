@extends('layouts.adminlte')

@section('subtitle', 'Daily Report')
@section('content_header_title', 'Reports')
@section('content_header_subtitle', 'Daily Report')

{{-- Importante para sa Arrows at Pagination --}}
@section('plugins.Datatables', true)

@section('content_body')
    <div class="container-fluid">
        {{-- Statistics Cards (No Print) --}}
        {{-- <div class="row no-print">
            <div class="col-lg-3 col-6" onclick="filterByStatus('low_stock')" style="cursor: pointer;">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $lowStockCount }}</h3>
                        <p>Low Stock Items</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div> --}}
{{-- 
            <div class="col-lg-3 col-6" onclick="filterByStatus('received')" style="cursor: pointer;">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $newArrivals }}</h3>
                        <p>Daily Received</p>
                    </div>
                    <div class="icon"><i class="fas fa-download"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div> --}}

            {{-- <div class="col-lg-3 col-6" onclick="filterByStatus('outflow')" style="cursor: pointer;">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dailyOutflow }}</h3>
                        <p>Daily Outflow</p>
                    </div>
                    <div class="icon"><i class="fas fa-upload"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div> --}}
{{-- 
            <div class="col-lg-3 col-6" onclick="filterByStatus('damaged')" style="cursor: pointer;">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $damagedCount ?? 0 }}</h3>
                        <p>Damaged/Return</p>
                    </div>
                    <div class="icon"><i class="fas fa-tools"></i></div>
                    <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div> --}}

        {{-- Main Table Card --}}
        {{-- <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center bg-white no-print">
                        <div class="card-title mb-0 text-uppercase font-weight-bold">
                            <i class="fas fa-clipboard-list mr-1"></i> Inventory Activity
                        </div> --}}
                        {{-- <div class="ml-auto">
                            <div class="form-inline">
                                <input type="date" id="reportDate" class="form-control form-control-sm mr-2"
                                    value="{{ $date }}">
                                <button onclick="handlePrint()" class="btn btn-dark btn-sm shadow-sm">
                                    <i class="fas fa-print"></i> PRINT REPORT
                                </button>
                            </div>
                        </div>
                    </div> --}}
                    {{-- <div class="card-body">
                        <div class="table-responsive">
                            <table id="dailyTable" class="table table-bordered table-hover w-100">
                                <thead class="bg-dark text-white text-uppercase">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Traceability</th>
                                        <th class="text-center">Qty</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- HIDDEN PRINT TEMPLATE (Courier Font Style) --}}
        <div id="printArea" class="d-none d-print-block"
            style="font-family: 'Courier New', Courier, monospace; color: black; padding: 10px;">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold mb-0">GYMNASTHENIQX INVENTORY SYSTEM</h2>
                <p class="mb-0 text-uppercase">Warehouse: {{ auth()->user()->adminlte_warehouse() ?? 'Main Warehouse' }}</p>
                <h4 class="mt-2 text-uppercase font-weight-bold"
                    style="border-bottom: 2px solid #000; display: inline-block; padding-bottom: 5px;">
                    DAILY OPERATIONAL & TRACEABILITY REPORT
                </h4>
                <p class="small mt-2">
                    Report Date: {{ \Carbon\Carbon::parse($date)->format('F d, Y') }} |
                    Generated: {{ date('F d, Y h:i A') }}
                </p>
            </div>

            <table class="table table-bordered w-100" style="border: 2px solid black !important; font-size: 12px;">
                <thead style="background-color: #eee !important;">
                    <tr>
                        <th style="border: 1px solid black !important;">PRODUCT NAME</th>
                        <th style="border: 1px solid black !important;">CATEGORY</th>
                        <th style="border: 1px solid black !important;">SERIAL/TRACE</th>
                        <th style="border: 1px solid black !important; text-align: center;">QTY</th>
                        <th style="border: 1px solid black !important;">STATUS</th>
                    </tr>
                </thead>
                <tbody id="printTableBody">
                    {{-- JS populated --}}
                </tbody>
            </table>

            {{-- SIGNATURE SECTION (Traceability) --}}
            <div class="row mt-5">
                <div class="col-4 text-center">
                    <p class="mb-0"><strong>Prepared/Filed by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">{{ auth()->user()->name }}</p>
                    <p style="font-size: 10px;">(Employee Name & Signature)</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-0"><strong>Verified/Received by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">____________________</p>
                    <p style="font-size: 10px;">(Warehouse Staff On-Duty)</p>
                </div>
                <div class="col-4 text-center">
                    <p class="mb-0"><strong>Acknowledged by:</strong></p>
                    <div style="border-bottom: 1px solid black; width: 85%; margin: 45px auto 5px auto;"></div>
                    <p class="small text-uppercase mb-0">____________________</p>
                    <p style="font-size: 10px;">(Warehouse Manager)</p>
                </div>
            </div>

            <div class="mt-5">
                <p class="small italic" style="font-size: 10px;">*This is a system-generated report. Discrepancies between
                    system and physical count must be reported immediately.</p>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var currentFilter = 'all';
        var table;

        function filterByStatus(status) {
            currentFilter = status;
            table.ajax.reload();
        }

        function handlePrint() {
            let rows = table.rows({
                search: 'applied'
            }).data();
            let html = '';

            rows.each(function(data) {
                let cleanName = $('<div>').html(data.product_name).text();
                let cleanCat = $('<div>').html(data.category_name).text();

                html += `<tr>
                    <td style="border: 1px solid black !important; padding: 5px;">${cleanName}</td>
                    <td style="border: 1px solid black !important; padding: 5px;">${cleanCat}</td>
                    <td style="border: 1px solid black !important; padding: 5px;">${data.traceability || '-'}</td>
                    <td style="border: 1px solid black !important; padding: 5px; text-align: center;">${data.quantity}</td>
                    <td style="border: 1px solid black !important; padding: 5px;">${data.status || 'ACTIVE'}</td>
                </tr>`;
            });

            $('#printTableBody').html(html ||
                '<tr><td colspan="5" class="text-center">No transactions recorded.</td></tr>');
            window.print();
        }

        $(document).ready(function() {
            table = $('#dailyTable').DataTable({
                responsive: true,
                autoWidth: false,
                destroy: true,
                order: [
                    [0, 'asc']
                ],
                language: {
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                },
                ajax: {
                    url: "{{ route('reports.daily.data') }}",
                    data: function(d) {
                        d.date = $('#reportDate').val();
                    },
                    dataSrc: function(json) {
                        var data = json.data;

                        // ✅ Filter based on status
                        if (currentFilter === 'all') return data;

                        return data.filter(function(row) {
                            if (currentFilter === 'low_stock') {
                                // ✅ Show items with "Low Stock" status
                                return row.status === 'Low Stock'; < --NEW LOGIC(CORRECT!)
                            }
                            if (currentFilter === 'received') {
                                return row.status === 'Received';
                            }
                            if (currentFilter === 'outflow') {
                                return row.status === 'Outflow';
                            }
                            if (currentFilter === 'damaged') {
                                return row.status === 'Damaged';
                            }
                            return true;
                        });
                    }
                },
                columns: [{
                        data: 'product_name',
                        render: function(data) {
                            return data.replace(/text-muted/g, 'text-dark').replace(/<small>/g,
                                '<span>').replace(/<\/small>/g, '</span>');
                        }
                    },
                    {
                        data: 'category_name',
                        render: function(data, type, row) {
                            var temp = document.createElement("div");
                            temp.innerHTML = data;
                            var txt = temp.textContent || "";
                            var img = (row.image && row.image !== 'null') ?
                                "{{ asset('products') }}/" + row.image :
                                "{{ asset('images/gym_equip.jpg') }}";
                            return `<div class="d-flex align-items-center"><img src="${img}" class="mr-2 rounded border" style="width:30px;height:30px;object-fit:cover;"><b>${txt}</b></div>`;
                        }
                    },
                    {
                        data: 'traceability'
                    },
                    {
                        data: 'quantity',
                        className: 'text-center font-weight-bold'
                    }
                ]
            });

            $('#reportDate').on('change', () => table.ajax.reload());
        });
    </script>
@endpush

@push('css')
    <style>
        /* Table Sorting UI Fix */
        table.dataTable thead .sorting:before,
        table.dataTable thead .sorting:after {
            bottom: .5em !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important;
            }

            .no-print {
                display: none !important;
            }

            .card {
                border: none !important;
            }

            table {
                border: 2px solid black !important;
            }

            th {
                background-color: #eee !important;
                color: black !important;
            }
        }
    </style>
@endpush

feb 12