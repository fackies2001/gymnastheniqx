<?php
/*
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DashboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $filter;

    public function __construct($data, $filter = 'today')
    {
        $this->data = $data;
        $this->filter = $filter;
    }

    /**
     * Return collection of data
     */
    /*
    public function collection()
    {
        $collection = collect();

        // Summary Statistics
        $collection->push([
            'section' => 'SUMMARY STATISTICS',
            'metric' => '',
            'value' => '',
            'date' => now()->format('Y-m-d H:i:s'),
        ]);

        $collection->push([
            'section' => 'Suppliers',
            'metric' => 'Total Count',
            'value' => $this->data['small_boxes']['supplier_counts'],
            'date' => $this->getFilterDateRange(),
        ]);

        $collection->push([
            'section' => 'Purchase Requests',
            'metric' => 'Total Count',
            'value' => $this->data['small_boxes']['purchase_request_counts'],
            'date' => $this->getFilterDateRange(),
        ]);

        $collection->push([
            'section' => 'Purchase Orders',
            'metric' => 'Total Count',
            'value' => $this->data['small_boxes']['purchase_order_counts'],
            'date' => $this->getFilterDateRange(),
        ]);

        $collection->push([
            'section' => 'Available Stock',
            'metric' => 'Total Count',
            'value' => $this->data['small_boxes']['serial_number_counts'],
            'date' => $this->getFilterDateRange(),
        ]);

        // Product Status Breakdown
        $collection->push([
            'section' => 'PRODUCT STATUS BREAKDOWN',
            'metric' => '',
            'value' => '',
            'date' => '',
        ]);

        foreach ($this->data['doughnut']['product_status_counts'] as $status => $count) {
            $collection->push([
                'section' => 'Product Status',
                'metric' => ucfirst($status),
                'value' => $count,
                'date' => '',
            ]);
        }

        // Purchase Request Status
        $collection->push([
            'section' => 'PURCHASE REQUEST STATUS',
            'metric' => '',
            'value' => '',
            'date' => '',
        ]);

        foreach ($this->data['doughnut']['purchase_request_status_counts'] as $status => $count) {
            $collection->push([
                'section' => 'PR Status',
                'metric' => ucfirst($status),
                'value' => $count,
                'date' => '',
            ]);
        }

        // Low Stock Products
        if (isset($this->data['low_stock_products']) && count($this->data['low_stock_products']) > 0) {
            $collection->push([
                'section' => 'LOW STOCK ALERT',
                'metric' => '',
                'value' => '',
                'date' => '',
            ]);

            foreach ($this->data['low_stock_products'] as $product) {
                $collection->push([
                    'section' => 'Low Stock',
                    'metric' => $product->name,
                    'value' => $product->available_count ?? 0,
                    'date' => 'SKU: ' . ($product->system_sku ?? 'N/A'),
                ]);
            }
        }

        return $collection;
    }

    /**
     * Headings for the Excel sheet
     */
    /*
    public function headings(): array
    {
        return [
            'Section',
            'Metric',
            'Value',
            'Additional Info',
        ];
    }

    /**
     * Map each row
     */
    /*
    public function map($row): array
    {
        return [
            $row['section'] ?? '',
            $row['metric'] ?? '',
            $row['value'] ?? '',
            $row['date'] ?? '',
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    /*
    public function styles(Worksheet $sheet)
    {
        return [
            // Header row - bold and background color
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],

            // Section headers - bold
            'A:A' => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Set the title of the sheet
     */
    /*
    public function title(): string
    {
        return 'Dashboard Report - ' . $this->getFilterDateRange();
    }

    /**
     * Get the date range based on filter
     */
    /*
    private function getFilterDateRange(): string
    {
        switch ($this->filter) {
            case 'today':
                return 'Today: ' . now()->format('Y-m-d');

            case 'week':
                return 'This Week: ' . now()->startOfWeek()->format('Y-m-d') . ' to ' . now()->endOfWeek()->format('Y-m-d');

            case 'month':
                return 'This Month: ' . now()->startOfMonth()->format('Y-m-d') . ' to ' . now()->endOfMonth()->format('Y-m-d');

            default:
                return now()->format('Y-m-d');
        }
    }
}
