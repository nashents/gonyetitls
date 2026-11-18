<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * LONG HAUL FUEL / SHORT HAUL FUEL sheet of the Daily Fuel Summary, laid out as
 * the client's template: B1 month, A2 "DATE:-", row 3 DATE + one gold column per
 * truck (+ DIESEL TOTAL), one row per day from row 4, a blank row, then SUM totals.
 */
class DailyFuelSummaryHaulSheet implements WithEvents, WithTitle
{
    use \App\Exports\Concerns\DailyShiftReportHeader;

    protected const HEADER_ROW = 3;
    protected const DATA_START = 4;
    protected const GOLD = 'FFCC00';

    /**
     * @param array<string,string> $units key => truck label (already sorted)
     * @param array<string, array<int,float>> $litres key => [day => litres]
     */
    public function __construct(
        protected Carbon $month,
        protected string $title,
        protected array $units,
        protected array $litres,
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $days = $this->month->daysInMonth;
                $header = self::HEADER_ROW;
                $first = self::DATA_START;
                $last = $first + $days - 1;
                $totalRow = $last + 2;
                $unitCount = count($this->units);
                $totalColIndex = 3 + $unitCount;
                $totalCol = Coordinate::stringFromColumnIndex($totalColIndex);
                $lastUnitCol = Coordinate::stringFromColumnIndex(max(3, $totalColIndex - 1));

                $sheet->setCellValue('B1', ExcelDate::PHPToExcel($this->month->copy()->startOfDay()));
                $sheet->getStyle('B1')->getNumberFormat()->setFormatCode('mmm-yy');
                $sheet->getStyle('B1')->getFont()->setBold(true);
                $sheet->setCellValue('A2', 'DATE:-');
                $sheet->setCellValue("B{$header}", 'DATE');

                $col = 3;
                foreach ($this->units as $key => $label) {
                    $c = Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValueExplicit("{$c}{$header}", $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                    foreach ($this->litres[$key] ?? [] as $day => $qty) {
                        if ($qty != 0) {
                            $sheet->setCellValue($c . ($first + $day - 1), round($qty, 2));
                        }
                    }

                    $sheet->setCellValue("{$c}{$totalRow}", "=SUM({$c}{$first}:{$c}{$last})");
                    $sheet->getColumnDimension($c)->setWidth(12.67);
                    $col++;
                }

                $sheet->setCellValue("{$totalCol}{$header}", 'DIESEL TOTAL');
                $sheet->getColumnDimension($totalCol)->setWidth(14);

                for ($d = 1; $d <= $days; $d++) {
                    $row = $first + $d - 1;
                    $sheet->setCellValue("B{$row}", ExcelDate::PHPToExcel($this->month->copy()->day($d)->startOfDay()));
                    $sheet->setCellValue("{$totalCol}{$row}", $unitCount
                        ? "=SUM(C{$row}:{$lastUnitCol}{$row})"
                        : 0);
                }

                $sheet->setCellValue("B{$totalRow}", 'TOTAL');
                $sheet->setCellValue("{$totalCol}{$totalRow}", "=SUM({$totalCol}{$first}:{$totalCol}{$last})");

                $sheet->getColumnDimension('A')->setWidth(8.33);
                $sheet->getColumnDimension('B')->setWidth(16.89);

                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];

                $sheet->getStyle("B{$header}:{$totalCol}{$header}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GOLD]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension($header)->setRowHeight(30);

                $sheet->getStyle("B{$header}:{$totalCol}{$last}")->getBorders()->getAllBorders()->applyFromArray($thin);
                $sheet->getStyle("B{$totalRow}:{$totalCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => $thin],
                ]);

                $sheet->getStyle("B{$first}:B{$last}")->getNumberFormat()->setFormatCode('dd-mmm-yy');
                $sheet->getStyle("B{$first}:B{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$first}:{$totalCol}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0');
                $sheet->getStyle("{$totalCol}{$first}:{$totalCol}{$totalRow}")->getFont()->setBold(true);

                $sheet->freezePane('C' . self::DATA_START);

                $this->prependReportHeader($sheet, [
                    'Report' => 'Daily Fuel Summary - ' . ucwords(strtolower($this->title)),
                    'Month' => $this->month->format('F Y'),
                    'Basis' => 'Fuel orders (not rejected); shift fuel on the shift production day 02:00 - 01:00',
                ]);
            },
        ];
    }
}
