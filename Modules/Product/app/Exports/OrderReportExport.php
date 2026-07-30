<?php

namespace Modules\Product\Exports;

use Illuminate\Http\Request;
use Modules\Product\Models\OrderItem;
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

class OrderReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
        $query = OrderItem::with(['order.vendor', 'order.placeBy', 'product', 'product.company', 'orderItemStocks.stock']);

        // Apply filters - same logic as ReportController@orderReport
        
        // Filter by date range
        if ($this->request->filled('date_from')) {
            $query->whereHas('order', function ($q) {
                $q->whereDate('created_at', '>=', $this->request->date_from);
            });
        }

        if ($this->request->filled('date_to')) {
            $query->whereHas('order', function ($q) {
                $q->whereDate('created_at', '<=', $this->request->date_to);
            });
        }

        // Filter by vendor (multi)
        if ($this->request->filled('vendor_id')) {
            $vendorIds = is_array($this->request->vendor_id) ? $this->request->vendor_id : [$this->request->vendor_id];
            $query->whereHas('order', function ($q) use ($vendorIds) {
                $q->whereIn('vendor_id', $vendorIds);
            });
        }

        // Filter by product (multi)
        if ($this->request->filled('product_id')) {
            $productIds = is_array($this->request->product_id) ? $this->request->product_id : [$this->request->product_id];
            $query->whereIn('product_id', $productIds);
        }

        // Filter by company (multi)
        if ($this->request->filled('company_id')) {
            $companyIds = is_array($this->request->company_id) ? $this->request->company_id : [$this->request->company_id];
            $query->whereHas('product', function ($q) use ($companyIds) {
                $q->whereIn('company_id', $companyIds);
            });
        }

        // Filter by place_by (multi)
        if ($this->request->filled('place_by')) {
            $placeByIds = is_array($this->request->place_by) ? $this->request->place_by : [$this->request->place_by];
            $query->whereHas('order', function ($q) use ($placeByIds) {
                $q->whereIn('place_by', $placeByIds);
            });
        }

        // Filter by status (multi)
        if ($this->request->filled('status_filter')) {
            $statusIds = is_array($this->request->status_filter) ? $this->request->status_filter : [$this->request->status_filter];
            $query->whereIn('order_status_id', $statusIds);
        }

        // Filter by payment status (multi)
        if ($this->request->filled('payment_status_filter')) {
            $paymentStatusIds = is_array($this->request->payment_status_filter) ? $this->request->payment_status_filter : [$this->request->payment_status_filter];
            $query->whereHas('order', function ($q) use ($paymentStatusIds) {
                $q->whereIn('payment_status', $paymentStatusIds);
            });
        }

        // Exclude cancelled orders
        $query = $query->whereHas('order', function ($q) {
            $q->where('order_status_id', '!=', 6);
        });

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
            'SL',
            'Invoice ID',
            'Date',
            'Vendor',
            'Product',
            'Company',
            'Order By',
            'Total Qty',
            'Damage Qty',
            'Return Qty',
            'Lost Qty',
            'Actual Sold Qty',
            'Purchase Price',
            'Total Purchase',
            'Sell Price',
            'Total Sell',
            'Discount',
            'Profit'
        ];
    }

    /**
     * Map the data for each row
     * 
     * @param mixed $item
     * @return array
     */
    public function map($item): array
    {
        static $counter = 0;
        $counter++;

        $actualSoldQty = $item->quantity - $item->return_quantity;

        return [
            $counter,
            $item->order->invoice_id ?? 'N/A',
            $item->order ? $item->order->created_at->format('Y-m-d H:i:s') : 'N/A',
            $item->order && $item->order->vendor ? $item->order->vendor->shop_name : 'N/A',
            $item->product ? $item->product->name : 'N/A',
            $item->product && $item->product->company ? $item->product->company->name : 'N/A',
            $item->order && $item->order->placeBy ? $item->order->placeBy->name : 'N/A',
            $item->quantity,
            $item->damage_quantity,
            $item->return_quantity,
            $item->lost_quantity,
            $actualSoldQty,
            number_format($item->purchase_price, 2),
            number_format($item->total_purchase, 2),
            number_format($item->sell_price, 2),
            number_format($item->total_sell, 2),
            number_format($item->discount_price, 2),
            number_format($item->item_total_profit, 2)
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
                    'fillType' => Fill::FILL_SOLID,
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
            'A' => 8,   // SL
            'B' => 20,  // Invoice ID
            'C' => 18,  // Date
            'D' => 25,  // Vendor
            'E' => 30,  // Product
            'F' => 25,  // Company
            'G' => 20,  // Order By
            'H' => 12,  // Total Qty
            'I' => 12,  // Damage Qty
            'J' => 12,  // Return Qty
            'K' => 12,  // Lost Qty
            'L' => 15,  // Actual Sold Qty
            'M' => 15,  // Purchase Price
            'N' => 15,  // Total Purchase
            'O' => 15,  // Sell Price
            'P' => 15,  // Total Sell
            'Q' => 15,  // Discount
            'R' => 15,  // Profit
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
                    $totalQuantity = $this->data->sum('quantity');
                    $totalDamage = $this->data->sum('damage_quantity');
                    $totalReturn = $this->data->sum('return_quantity');
                    $totalLost = $this->data->sum('lost_quantity');
                    $totalActualSold = $totalQuantity - $totalReturn;
                    $totalPurchase = $this->data->sum('total_purchase');
                    $totalSell = $this->data->sum('total_sell');
                    $totalDiscount = $this->data->sum('discount_price');
                    $totalProfit = $this->data->sum('item_total_profit');
                    
                    // Add empty row
                    $summaryRow = $lastRow;
                    
                    // Add summary title and values
                    $sheet->setCellValue('G' . $summaryRow, 'SUMMARY:');
                    $sheet->setCellValue('H' . $summaryRow, number_format($totalQuantity, 0));
                    $sheet->setCellValue('I' . $summaryRow, number_format($totalDamage, 0));
                    $sheet->setCellValue('J' . $summaryRow, number_format($totalReturn, 0));
                    $sheet->setCellValue('K' . $summaryRow, number_format($totalLost, 0));
                    $sheet->setCellValue('L' . $summaryRow, number_format($totalActualSold, 0));
                    $sheet->setCellValue('M' . $summaryRow, '');
                    $sheet->setCellValue('N' . $summaryRow, number_format($totalPurchase, 2));
                    $sheet->setCellValue('O' . $summaryRow, '');
                    $sheet->setCellValue('P' . $summaryRow, number_format($totalSell, 2));
                    $sheet->setCellValue('Q' . $summaryRow, number_format($totalDiscount, 2));
                    $sheet->setCellValue('R' . $summaryRow, number_format($totalProfit, 2));
                    
                    // Style summary row
                    $sheet->getStyle('G' . $summaryRow . ':R' . $summaryRow)->applyFromArray([
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
                    
                    // Add borders to all data rows
                    $sheet->getStyle('A1:R' . ($lastRow - 1))->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);
                }
            },
        ];
    }
}
