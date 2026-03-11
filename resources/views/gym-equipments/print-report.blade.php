<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Equipment Report - {{ $filterLabel }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 20px;
        }

        /* ===== HEADER ===== */
        .report-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .report-header h1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-header h2 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 4px 0;
        }

        .report-header .meta {
            font-size: 10px;
            margin-top: 5px;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        table th {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
            background: #000;
            color: #fff;
            text-align: left;
        }

        table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
        }

        /* ===== SIGNATORY ===== */
        .signatory {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signatory-col {
            width: 30%;
        }

        .signatory-col .line {
            border-bottom: 1px solid #000;
            margin: 40px auto 5px auto;
            width: 85%;
        }

        .signatory-col p {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .signatory-col small {
            font-size: 9px;
        }

        /* ===== PRINT MEDIA ===== */
        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }
        }

        @media screen {
            body {
                background: #f5f5f5;
                padding: 30px;
                max-width: 210mm;
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="report-header">
        <h1>GYMNASTHENIQX WAREHOUSE</h1>
        <p style="font-size: 11px; text-transform: uppercase;">WAREHOUSE: GYMNASTHENIQX</p>
        <h2>GYM EQUIPMENT INVENTORY REPORT</h2>
        <div class="meta">
            Report Date: {{ now()->format('F d, Y') }} |
            Generated: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    {{-- TABLE --}}
    @if ($equipments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 15%;">ITEM CODE</th>
                    <th style="width: 35%;">EQUIPMENT NAME</th>
                    <th style="width: 10%;">QTY</th>
                    <th style="width: 18%;">STATUS</th>
                    <th style="width: 14%;">DATE ADDED</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipments as $index => $equipment)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $equipment->item_code }}</td>
                        <td>{{ $equipment->name }}</td>
                        <td style="text-align: center;">{{ $equipment->quantity }}</td>
                        <td style="text-align: center;">{{ $equipment->status }}</td>
                        <td style="text-align: center;">{{ $equipment->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; margin-top: 30px;">No equipment records found for: {{ $filterLabel }}</p>
    @endif

    {{-- SIGNATORY --}}
    <div class="signatory">
        <div class="signatory-col">
            <p><strong>Prepared/Filed by:</strong></p>
            <div class="line"></div>
            <p>{{ auth()->user()->full_name ?? auth()->user()->name }}</p>
            <small>(Employee Name & Signature)</small>
        </div>
        <div class="signatory-col">
            <p><strong>Verified/Received by:</strong></p>
            <div class="line"></div>
            <p>____________________</p>
            <small>(Warehouse Staff On-Duty)</small>
        </div>
        <div class="signatory-col">
            <p><strong>Acknowledged by:</strong></p>
            <div class="line"></div>
            <p>____________________</p>
            <small>(Warehouse Manager)</small>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>
