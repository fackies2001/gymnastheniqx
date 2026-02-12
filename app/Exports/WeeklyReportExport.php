<?php

namespace App\Exports;

use App\Models\RetailerOrder; // In-update ko para mag-match sa orders mo
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class WeeklyReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        // Ginamit natin ang RetailerOrder base sa table mo sa HeidiSQL kanina
        return RetailerOrder::whereIn('status', ['Approved', 'Completed'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['GYMNASTHENIQX - WEEKLY SALES PERFORMANCE'],
            ['Period: ' . Carbon::now()->subDays(7)->format('M d') . ' to ' . Carbon::now()->format('M d, Y')],
            [''],
            ['TRANSACTION DATE', 'RETAILER NAME', 'PRODUCT NAME', 'QTY', 'UNIT PRICE', 'TOTAL AMOUNT']
        ];
    }

    public function map($order): array
    {
        return [
            Carbon::parse($order->created_at)->format('M d, Y h:i A'),
            strtoupper($order->retailer_name), // Base sa screenshot mo: retailer_name
            strtoupper($order->product_name),  // Base sa screenshot mo: product_name
            $order->quantity,
            number_format($order->unit_price, 2),
            number_format($order->total_amount, 2)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1'); // Adjusted to F1 kasi 6 columns na tayo
        $sheet->mergeCells('A2:F2');

        // Auto-size columns para hindi dikit-dikit ang text
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['alignment' => ['horizontal' => 'center']],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '007BFF']]
            ],
        ];
    }
}
