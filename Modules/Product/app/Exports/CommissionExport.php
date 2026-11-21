<?php

namespace Modules\Product\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Modules\Product\Services\CommissionReport;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommissionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting, WithEvents
{
    private const MONEY = '#,##0.00;[Red]-#,##0.00';

    protected Request $request;
    protected $data;
    protected int $counter = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $this->data = CommissionReport::query($this->request)
            ->orderByDesc('orders.created_at')
            ->orderByDesc('orders.id')
            ->get();

        return $this->data;
    }

    public function headings(): array
    {
        return ['SL', 'Date', 'Invoice', 'Vendor', 'Placed By', 'Status', 'Order Total', 'Price Diff (+/-)', 'Discount', 'Commission'];
    }

    public function map($order): array
    {
        $this->counter++;

        return [
            $this->counter,
            optional($order->created_at)->format('Y-m-d H:i'),
            $order->invoice_id,
            $order->vendor->shop_name ?? 'N/A',
            $order->placeBy->name ?? 'N/A',
            ucfirst($order->orderStatus->name ?? 'N/A'),
            (float) $order->total_amount,
            (float) $order->price_diff,
            (float) $order->discount,
            round((float) $order->commission, 2),
        ];
    }

    public function columnFormats(): array
    {
        return ['G' => self::MONEY, 'H' => self::MONEY, 'I' => self::MONEY, 'J' => self::MONEY];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 7, 'B' => 17, 'C' => 18, 'D' => 32, 'E' => 22, 'F' => 13, 'G' => 15, 'H' => 16, 'I' => 14, 'J' => 15];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $count = $this->data ? $this->data->count() : 0;
                $last  = $count + 1;

                // Green / red commission per row
                foreach (($this->data ?? collect())->values() as $i => $order) {
                    $c = (float) $order->commission;
                    if ($c != 0.0) {
                        $sheet->getStyle('J' . ($i + 2))->getFont()->setBold(true)
                            ->getColor()->setRGB($c > 0 ? '15803D' : 'DC2626');
                    }
                }

                if ($count > 0) {
                    $sheet->getStyle('A1:J' . $last)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                }

                // Summary
                $plus  = $this->data ? $this->data->filter(fn ($o) => $o->commission > 0)->sum(fn ($o) => (float) $o->commission) : 0;
                $minus = $this->data ? $this->data->filter(fn ($o) => $o->commission < 0)->sum(fn ($o) => (float) $o->commission) : 0;
                $r = $last + 2;
                $rows = [
                    ['TOTAL', $this->data ? $this->data->sum(fn ($o) => (float) $o->total_amount) : 0, $this->data ? $this->data->sum(fn ($o) => (float) $o->price_diff) : 0, $this->data ? $this->data->sum(fn ($o) => (float) $o->discount) : 0, $plus + $minus],
                ];
                $sheet->setCellValue("F{$r}", 'TOTAL');
                $sheet->setCellValue("G{$r}", round($rows[0][1], 2));
                $sheet->setCellValue("H{$r}", round($rows[0][2], 2));
                $sheet->setCellValue("I{$r}", round($rows[0][3], 2));
                $sheet->setCellValue("J{$r}", round($rows[0][4], 2));
                $sheet->getStyle("F{$r}:J{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                ]);

                $summary = [
                    ['Total plus (to pay SR)', $plus, '15803D'],
                    ['Total minus (SR pays)', $minus, 'DC2626'],
                    ['NET COMMISSION', $plus + $minus, ($plus + $minus) >= 0 ? '15803D' : 'DC2626'],
                ];
                foreach ($summary as $k => [$label, $value, $color]) {
                    $row = $r + 1 + $k;
                    $sheet->mergeCells("F{$row}:I{$row}");
                    $sheet->setCellValue("F{$row}", $label);
                    $sheet->setCellValue("J{$row}", round($value, 2));
                    $sheet->getStyle("F{$row}:J{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("J{$row}")->getFont()->getColor()->setRGB($color);
                    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle("G{$r}:J" . ($r + 3))->getNumberFormat()->setFormatCode(self::MONEY);
                $sheet->getStyle("F{$r}:J" . ($r + 3))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet->freezePane('A2');
            },
        ];
    }
}
