<?php

namespace Modules\Product\Exports;

use Illuminate\Http\Request;
use Modules\Product\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $request;
    protected $data;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::with(['vendor', 'orderStatus', 'placeBy', 'orderItems', 'vendorAccounts'])
            ->whereIn('order_status_id', [4, 5]); // Shipped or delivered only

        // Apply filters - same logic as InvoiceController@index
        if ($this->request->filled('invoice_search')) {
            $query->where('invoice_id', 'like', '%' . $this->request->invoice_search . '%');
        }

        if ($this->request->filled('vendor_filter')) {
            $query->where('vendor_id', $this->request->vendor_filter);
        }

        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        if ($this->request->filled('place_by_filter')) {
            $query->where('place_by', $this->request->place_by_filter);
        }

        if ($this->request->filled('payment_status_filter')) {
            $query->where('payment_status', $this->request->payment_status_filter);
        }

        $this->data = $query->orderBy('id', 'desc')->get();
        return $this->data;
    }

    /**
     * Define the headings for the Excel file
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            'Invoice ID',
            'Created Date',
            'Place By',
            'Vendor Name',
            'Vendor Mobile',
            'Order Status',
            'Payment Status',
            'Items Count',
            'Total Quantity',
            'Total Amount',
            'Discount Amount',
            'Paid Amount',
            'Due Amount'
        ];
    }

    /**
     * Map the data for each row
     * 
     * @param mixed $order
     * @return array
     */
    public function map($order): array
    {
        // Payment status mapping
        $paymentStatusMap = [
            0 => 'Unpaid',
            1 => 'Partial Paid',
            2 => 'Paid'
        ];

        // Calculate due amount
        $dueAmount = $order->total_amount - $order->paid_amount;

        return [
            $order->invoice_id,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->placeBy ? $order->placeBy->name : 'N/A',
            $order->vendor ? $order->vendor->shop_name : 'N/A',
            $order->vendor ? $order->vendor->mobile : 'N/A',
            $order->orderStatus ? $order->orderStatus->name : 'N/A',
            $paymentStatusMap[$order->payment_status] ?? 'Unknown',
            $order->orderItems->count(),
            $order->total_quantity,
            number_format($order->total_amount, 2),
            number_format($order->total_discount_amount, 2),
            number_format($order->paid_amount, 2),
            number_format($dueAmount, 2)
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
            'A' => 25, // Invoice ID
            'B' => 20, // Created Date
            'C' => 20, // Place By
            'D' => 30, // Vendor Name
            'E' => 15, // Vendor Mobile
            'F' => 15, // Order Status
            'G' => 15, // Payment Status
            'H' => 12, // Items Count
            'I' => 15, // Total Quantity
            'J' => 15, // Total Amount
            'K' => 15, // Discount Amount
            'L' => 15, // Paid Amount
            'M' => 15, // Due Amount
        ];
    }

    /**
     * Register events to add summary rows
     * 
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if ($this->data && $this->data->count() > 0) {
                    $sheet = $event->sheet->getDelegate();
                    $lastRow = $this->data->count() + 2; // +1 for header, +1 for next row
                    
                    // Calculate totals
                    $totalQuantity = $this->data->sum('total_quantity');
                    $totalAmount = $this->data->sum('total_amount');
                    $totalDiscount = $this->data->sum('total_discount_amount');
                    $totalPaid = $this->data->sum('paid_amount');
                    $totalDue = $totalAmount - $totalPaid;
                    $totalInvoices = $this->data->count();
                    
                    // Add empty row
                    $summaryRow = $lastRow;
                    
                    // Add summary title and values
                    $sheet->setCellValue('H' . $summaryRow, 'SUMMARY:');
                    $sheet->setCellValue('I' . $summaryRow, number_format($totalQuantity, 0));
                    $sheet->setCellValue('J' . $summaryRow, number_format($totalAmount, 2));
                    $sheet->setCellValue('K' . $summaryRow, number_format($totalDiscount, 2));
                    $sheet->setCellValue('L' . $summaryRow, number_format($totalPaid, 2));
                    $sheet->setCellValue('M' . $summaryRow, number_format($totalDue, 2));
                    
                    // Style summary row - Base style (H to K)
                    $sheet->getStyle('H' . $summaryRow . ':K' . $summaryRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4472C4']
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                        ]
                    ]);
                    
                    // Style Paid column (L) - Green
                    $sheet->getStyle('L' . $summaryRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '28A745'] // Green
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                        ]
                    ]);
                    
                    // Style Due column (M) - Red
                    $sheet->getStyle('M' . $summaryRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'DC3545'] // Red
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
                        ]
                    ]);
                    
                    // Add total invoices count
                    $countRow = $summaryRow + 1;
                    $sheet->setCellValue('H' . $countRow, 'Total Invoices:');
                    $sheet->setCellValue('I' . $countRow, $totalInvoices);
                    
                    // Style count row
                    $sheet->getStyle('H' . $countRow . ':I' . $countRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E7E6E6']
                        ]
                    ]);
                }
            },
        ];
    }
}
