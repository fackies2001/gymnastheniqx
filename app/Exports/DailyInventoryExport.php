<?php

namespace App\Exports;

use App\Models\SerializedProduct;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Collection;

class DailyInventoryExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithTitle
{
    protected $date;
    protected $allData;
    protected $rowIndex = 8; // Start after headers

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function title(): string
    {
        return 'GYMNASTHENIQX Daily Report';
    }

    public function collection()
    {
        $data = new Collection();

        // Get Purchase Requests
        $prs = PurchaseRequest::with([
            'items.supplierProducts.suppliers',
            'items.supplierProducts.category',
            'requestedBy'
        ])->whereDate('created_at', $this->date)->get();

        foreach ($prs as $pr) {
            if ($pr->items) {
                foreach ($pr->items as $item) {
                    $data->push([
                        'type' => 'Purchase Request',
                        'reference' => $pr->request_number,
                        'time' => $pr->created_at->format('h:i A'),
                        'category' => $item->supplierProducts->category->name ?? 'N/A',
                        'product_name' => $item->supplierProducts->name ?? 'N/A',
                        'serial_number' => '-',
                        'supplier' => 'Pending',
                        'requester' => $pr->requestedBy->full_name ?? 'N/A',
                        'quantity' => $item->quantity ?? 1,
                        'status' => $pr->status ?? 'Pending'
                    ]);
                }
            }
        }

        // Get Purchase Orders
        $pos = PurchaseOrder::with([
            'items.supplierProducts.suppliers',
            'items.supplierProducts.category',
            'suppliers',
            'approvedBy'
        ])->whereDate('created_at', $this->date)->get();

        foreach ($pos as $po) {
            if ($po->items) {
                foreach ($po->items as $item) {
                    $data->push([
                        'type' => 'Purchase Order',
                        'reference' => $po->po_number,
                        'time' => $po->created_at->format('h:i A'),
                        'category' => $item->supplierProducts->category->name ?? 'N/A',
                        'product_name' => $item->supplierProducts->name ?? 'N/A',
                        'serial_number' => '-',
                        'supplier' => $po->suppliers->name ?? 'N/A',
                        'requester' => $po->approvedBy->full_name ?? 'N/A',
                        'quantity' => $item->quantity ?? 1,
                        'status' => $po->status ?? 'Pending'
                    ]);
                }
            }
        }

        // Get Serialized Products
        $serialized = SerializedProduct::with([
            'supplierProducts.suppliers',
            'supplierProducts.category',
            'purchaseOrder.purchaseRequest.requestedBy',
            'scannedBy',
            'productStatus'
        ])->whereDate('created_at', $this->date)->get();

        foreach ($serialized as $item) {
            $categoryName = $item->productStatus->name ?? 'General';
            $categoryNote = '';

            if (stripos($categoryName, 'consumable') !== false) {
                $categoryNote = ' (Spray, Wipes, etc.)';
            } elseif (stripos($categoryName, 'equipment') !== false) {
                $categoryNote = ' (Dumbbell, Barbell, etc.)';
            }

            $data->push([
                'type' => 'Serialized Item',
                'reference' => $item->purchaseOrder->po_number ?? 'Manual',
                'time' => $item->created_at->format('h:i A'),
                'category' => $categoryName . $categoryNote,
                'product_name' => $item->supplierProducts->name ?? 'N/A',
                'serial_number' => $item->serial_number,
                'supplier' => $item->supplierProducts->suppliers->name ?? 'N/A',
                'requester' => $item->scannedBy->full_name ?? 'System',
                'quantity' => 1,
                'status' => strtoupper($item->status ?? 'ACTIVE')
            ]);
        }

        $this->allData = $data;
        return $data;
    }

    public function headings(): array
    {
        return [
            ['GYMNASTHENIQX INVENTORY SYSTEM'],
            ['DAILY OPERATIONAL & TRACEABILITY REPORT'],
            ['Warehouse:', auth()->user()->adminlte_warehouse() ?? 'Main Warehouse'],
            ['Generated Date:', now()->format('F d, Y h:i A')],
            ['Filter Date:', \Carbon\Carbon::parse($this->date)->format('F d, Y')],
            [''],
            [
                'TYPE',
                'REFERENCE',
                'TIME',
                'CATEGORY',
                'PRODUCT NAME / DESCRIPTION',
                'SERIAL NUMBER',
                'SUPPLIER',
                'REQUESTER/RECEIVER',
                'QTY',
                'STATUS'
            ]
        ];
    }

    public function map($row): array
    {
        return [
            $row['type'],
            $row['reference'],
            $row['time'],
            $row['category'],
            $row['product_name'],
            $row['serial_number'],
            $row['supplier'],
            $row['requester'],
            $row['quantity'],
            $row['status']
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 1. BRAND NAME HEADER
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 20, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']]
                ]);

                // 2. REPORT TITLE
                $sheet->mergeCells('A2:J2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '000000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]
                ]);

                // 3. METADATA
                $sheet->getStyle('A3:A5')->getFont()->setBold(true);

                // 4. TABLE HEADERS
                $sheet->getStyle('A7:J7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // 5. ZEBRA STRIPING
                for ($i = 8; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("A$i:J$i")->getFill()  
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }
                }

                // 6. BORDERS
                $sheet->getStyle('A7:J' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                // 7. HIGHLIGHT SERIAL NUMBERS
                $sheet->getStyle('F8:F' . $highestRow)
                    ->getFont()
                    ->setBold(true)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E40AF'));

                // 8. COLUMN WIDTHS
                $sheet->getColumnDimension('A')->setWidth(18);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(35);
                $sheet->getColumnDimension('F')->setWidth(20);
            },
        ];
    }
}
