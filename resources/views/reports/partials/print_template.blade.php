<div id="printArea" class="d-none d-print-block" style="font-family: 'Courier New', Courier, monospace;">
    {{-- Header ng Report --}}
    <div class="text-center mb-4">
        <h2 class="font-weight-bold mb-0">GYMNASTHENIQX INVENTORY SYSTEM</h2>
        <p class="mb-0" id="printWarehouseName">WAREHOUSE: {{ auth()->user()->warehouse_info->name ?? 'ALL BRANCHES' }}
        </p>
        <h4 class="mt-2 text-uppercase" id="printTitle" style="border-bottom: 2px solid #000; display: inline-block;"></h4>
        <p class="small mt-1">Date Generated: {{ date('F d, Y h:i A') }}</p>
    </div>

    {{-- Table Area --}}
    <table class="table table-bordered w-100" id="printTable" style="border: 1px solid black !important;">
        {{-- Dito ilalagay ng JavaScript ang data mula sa DataTable --}}
    </table>

    {{-- Traceability & Signature Section (Eto yung hiningi mo pre) --}}
    <div class="row mt-5">
        <div class="col-4 text-center">
            <p class="mb-0"><strong>Prepared/Filed by:</strong></p>
            <div style="border-bottom: 1px solid black; width: 80%; margin: 40px auto 5px auto;"></div>
            <p class="small text-uppercase">{{ auth()->user()->name }}</p>
            <p class="x-small text-muted" style="font-size: 10px;">(Employee Name & Signature)</p>
        </div>
        <div class="col-4 text-center">
            <p class="mb-0"><strong>Verified/Received by:</strong></p>
            <div style="border-bottom: 1px solid black; width: 80%; margin: 40px auto 5px auto;"></div>
            <p class="small text-uppercase">____________________</p>
            <p class="x-small text-muted" style="font-size: 10px;">(Warehouse Staff On-Duty)</p>
        </div>
        <div class="col-4 text-center">
            <p class="mb-0"><strong>Acknowledged by:</strong></p>
            <div style="border-bottom: 1px solid black; width: 80%; margin: 40px auto 5px auto;"></div>
            <p class="small text-uppercase">____________________</p>
            <p class="x-small text-muted" style="font-size: 10px;">(Warehouse Manager)</p>
        </div>
    </div>

    <div class="mt-4">
        <p class="x-small italic" style="font-size: 10px;">*This is a system-generated report. Any discrepancies between
            system count and physical count must be reported immediately.</p>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid black !important;
        }

        body {
            background: white !important;
            color: black !important;
        }
    }
</style>
