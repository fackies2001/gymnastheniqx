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
            margin: 20mm 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            width: 210mm;
            margin: 0 auto;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #000;
        }

        .report-header h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .report-header h2 {
            font-size: 13px;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .report-header .meta {
            font-size: 10px;
            color: #666;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-left: 4px solid #000;
            font-size: 10px;
        }

        .meta-info strong {
            font-weight: 600;
        }

        .section-header {
            background: #000;
            color: white;
            padding: 10px 15px;
            margin: 20px 0 12px 0;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background: #2d3748;
            color: white;
        }

        table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1a202c;
        }

        table tbody td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }

        table tbody tr:nth-child(odd) {
            background: #f7fafc;
        }

        table tbody tr:hover {
            background: #edf2f7;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-available {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .status-maintenance {
            background: #fef5e7;
            color: #744210;
            border: 1px solid #f6e05e;
        }

        .status-broken {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .report-footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #718096;
            text-align: center;
        }

        .report-footer p {
            margin: 3px 0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .report-header,
            .meta-info,
            .section-header {
                page-break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }

        @media screen {
            body {
                background: #f5f5f5;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="report-header">
        <h1>GYMNASTHENIGX WAREHOUSE</h1>
        <h2>GYM EQUIPMENT INVENTORY REPORT</h2>
        <div class="meta">
            <strong>Period:</strong> {{ $filterLabel }} |
            <strong>Generated on:</strong> {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <div class="meta-info">
        <div><strong>Warehouse:</strong> Main Warehouse - Gymnasthenigx Luzon</div>
        <div><strong>Filter Applied:</strong> {{ $filterLabel }}</div>
        <div><strong>Total Records:</strong> {{ $equipments->count() }}</div>
    </div>

    <div class="section-header">📋 EQUIPMENT INVENTORY DETAILS</div>

    @if ($equipments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">RANK</th>
                    <th style="width: 15%;">ITEM CODE</th>
                    <th style="width: 32%;">EQUIPMENT NAME</th>
                    <th style="width: 12%;">QUANTITY</th>
                    <th style="width: 18%;">STATUS</th>
                    <th style="width: 15%;">DATE ADDED</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipments as $index => $equipment)
                    <tr>
                        <td style="text-align: center; font-weight: 600;">{{ $index + 1 }}</td>
                        <td style="font-weight: 600; color: #2d3748;">{{ $equipment->item_code }}</td>
                        <td>{{ $equipment->name }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ $equipment->quantity }}</td>
                        <td style="text-align: center;">
                            @php
                                $statusClass = match ($equipment->status) {
                                    'Available' => 'status-available',
                                    'Under Maintenance' => 'status-maintenance',
                                    'Out of Order' => 'status-broken',
                                    default => 'status-available',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $equipment->status }}</span>
                        </td>
                        <td style="text-align: center;">{{ $equipment->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">🔭 No equipment records found</p>
            <p>No data available for the selected filter: {{ $filterLabel }}</p>
        </div>
    @endif

    <div class="report-footer">
        <p><strong>GYMNASTHENIGX</strong> - Professional Gym Management System</p>
        <p>© {{ date('Y') }} All Rights Reserved | This is a computer-generated document</p>
        <p style="margin-top: 5px;">
            Report ID: GYM-{{ strtoupper(uniqid()) }} |
            Generated by: {{ auth()->user()->full_name ?? 'System Administrator' }}
        </p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
