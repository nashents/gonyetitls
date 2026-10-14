<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Services\ZimraScheduleCalculator;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * ZIMRA Sales & Purchases Schedule workbook, laid out like the schedule the
 * accountants submit: one block per currency/section, VAT and totals as
 * live formulas so the sheet can still be adjusted by hand.
 */
class ZimraScheduleExport implements FromArray, WithEvents, WithTitle
{
    public function __construct(
        private array $groups,
        private string $companyName,
        private string $periodLabel
    ) {
    }

    public function array(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'ZIMRA Schedule';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->build($event->sheet->getDelegate()),
        ];
    }

    private function build(Worksheet $ws): void
    {
        $money = '#,##0.00;(#,##0.00)';
        $r = 2;

        $ws->setCellValue("B{$r}", strtoupper($this->companyName));
        $ws->mergeCells("B{$r}:F{$r}");
        $ws->getStyle("B{$r}")->getFont()->setBold(true)->setSize(14);
        $r++;
        $ws->setCellValue("B{$r}", "ZIMRA SALES AND PURCHASES SCHEDULE - {$this->periodLabel}");
        $ws->getStyle("B{$r}")->getFont()->setBold(true);
        $r += 2;

        foreach ($this->groups as $group) {
            $cur = $group['currency']?->name ?? 'NO CURRENCY';

            foreach ($group['sections'] as $key => $section) {
                $isPurchase = in_array($key, [ZimraScheduleCalculator::PURCHASES_VAT, ZimraScheduleCalculator::PURCHASES_NO_VAT]);
                $isCredit = $key === ZimraScheduleCalculator::CREDIT_NOTES;

                $headers = $isPurchase
                    ? ['DATE', 'DETAILS', 'VAT NUMBER', 'INVOICE NUMBER', 'BILL #', 'DESCRIPTIONS', 'SUB AMOUNT', 'VAT AMOUNT', 'TOTAL AMOUNT']
                    : ['DATE', 'DETAILS', 'VAT / TIN', $isCredit ? 'CREDIT NOTE No' : 'INVOICE NUMBER', 'AMOUNT BF VAT', 'VAT', 'TOTAL AMOUNT'];
                $lastCol = $isPurchase ? 'J' : 'H';
                [$subCol, $vatCol, $totCol] = $isPurchase ? ['H', 'I', 'J'] : ['F', 'G', 'H'];

                $ws->setCellValue("B{$r}", "{$this->periodLabel} {$cur} {$section['title']}");
                $ws->getStyle("B{$r}")->getFont()->setBold(true)->setUnderline(true);
                $r += 2;

                $ws->fromArray($headers, null, "B{$r}");
                $ws->getStyle("B{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
                $ws->getStyle("B{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E4E7E9');
                $headerRow = $r;
                $r++;

                $first = $r;
                foreach ($section['rows'] as $row) {
                    $values = [
                        $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : '',
                        strtoupper($row['party']),
                        $row['vat_tin'] ?: 'N/A',
                        $row['number'] ?: 'N/A',
                    ];
                    if ($isPurchase) {
                        $values[] = $row['reference'];
                        $values[] = $row['description'];
                    }
                    $ws->fromArray($values, null, "B{$r}", true);
                    // Numbers stay text-safe (leading zeros) in the id columns.
                    foreach (['D', 'E'] as $col) {
                        $ws->getCell("{$col}{$r}")->setValueExplicit((string) $ws->getCell("{$col}{$r}")->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }

                    $ws->setCellValue("{$subCol}{$r}", $row['sub']);
                    $ws->setCellValue("{$vatCol}{$r}", $row['vat']);
                    $ws->setCellValue("{$totCol}{$r}", "={$subCol}{$r}+{$vatCol}{$r}");
                    $r++;
                }
                $last = max($first, $r - 1);
                if ($r === $first) {
                    $r++;
                }

                $ws->setCellValue("C{$r}", "TOTAL {$cur} AMOUNT");
                foreach ([$subCol, $vatCol, $totCol] as $col) {
                    $ws->setCellValue("{$col}{$r}", "=SUM({$col}{$first}:{$col}{$last})");
                }
                $ws->getStyle("B{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
                $ws->getStyle("B{$r}:{$lastCol}{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $ws->getStyle("B{$r}:{$lastCol}{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

                $ws->getStyle("{$subCol}{$headerRow}:{$totCol}{$r}")->getNumberFormat()->setFormatCode($money);
                $ws->getStyle("B{$headerRow}:{$lastCol}" . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $r += 4;
            }

            $s = $group['summary'];
            $ws->setCellValue("B{$r}", "{$cur} VAT SUMMARY");
            $ws->getStyle("B{$r}")->getFont()->setBold(true)->setUnderline(true);
            $r++;
            foreach ([
                'Output VAT (Sales)' => $s['output_vat'],
                'Less: Credit Note VAT' => $s['credit_vat'],
                'Less: Input VAT (Purchases)' => $s['input_vat'],
                $s['net_vat'] < 0 ? 'Net VAT Refundable' : 'Net VAT Payable' => $s['net_vat'],
            ] as $label => $value) {
                $ws->setCellValue("C{$r}", $label);
                $ws->setCellValue("F{$r}", $value);
                $ws->getStyle("F{$r}")->getNumberFormat()->setFormatCode($money);
                $r++;
            }
            $ws->getStyle('C' . ($r - 1) . ':F' . ($r - 1))->getFont()->setBold(true);
            $r += 3;
        }

        $widths = ['A' => 3, 'B' => 13, 'C' => 38, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 40, 'H' => 16, 'I' => 16, 'J' => 16];
        foreach ($widths as $col => $width) {
            $ws->getColumnDimension($col)->setWidth($width);
        }
        $ws->getStyle('G:G')->getAlignment()->setWrapText(true);
    }
}
