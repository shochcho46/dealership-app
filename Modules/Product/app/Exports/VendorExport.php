<?php

namespace Modules\Product\Exports;

use Modules\Product\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Fetch vendors with total due calculation, ordered by due balance (biggest first)
        return Vendor::with('country')
            ->withSum(
                ['vendorAccounts as total_debit' => function ($q) {
                    $q->where('type', 1);
                }],
                'amount'
            )
            ->withSum(
                ['vendorAccounts as total_credit' => function ($q) {
                    $q->where('type', 2);
                }],
                'amount'
            )
            ->get()
            ->map(function ($vendor) {
                $vendor->due_balance = ($vendor->total_debit ?? 0) - ($vendor->total_credit ?? 0);
                return $vendor;
            })
            ->sortByDesc('due_balance')
            ->values();
    }

    /**
     * Define the headings for the Excel file
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            'Vendor Name',
            'Address',
            'Mobile',
            'Total Due'
        ];
    }

    /**
     * Map the data for each row
     * 
     * @param mixed $vendor
     * @return array
     */
    public function map($vendor): array
    {
        return [
            $vendor->shop_name,
            $vendor->full_address ?? 'N/A',
            $vendor->mobile,
            number_format($vendor->due_balance, 2)
        ];
    }

    /**
     * Apply styles to the worksheet
     * 
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }

    /**
     * Set column widths
     * 
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,  // Vendor Name
            'B' => 50,  // Address
            'C' => 20,  // Mobile
            'D' => 15,  // Total Due
        ];
    }
}
