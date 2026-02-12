<?php

namespace App\Exports;

use App\Models\SerializedProduct;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyInventoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $date;
    public function __construct($date)
    {
        $this->date = $date;
    }

    public function collection()
    {
        return SerializedProduct::with(['supplierProducts', 'purchaseOrder.purchaseRequest'])
            ->whereDate('created_at', $this->date)->get();
    }

    public function headings(): array
    {
        return ['Date', 'PR #', 'PO #', 'SKU', 'Serial Number', 'Product Name', 'Supplier', 'Staff'];
    }

    public function map($item): array
    {
        return [
            $item->created_at->format('Y-m-d'),
            $item->purchaseOrder?->purchaseRequest?->request_number ?? 'N/A',
            $item->purchaseOrder?->po_number ?? 'Manual',
            $item->supplierProducts?->system_sku,
            $item->serial_number,
            $item->supplierProducts?->name,
            $item->supplierProducts?->suppliers?->name,
            $item->scannedBy?->full_name,
        ];
    }
}
