<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * LIGHT VEHICLES FUEL sheet of the Daily Fuel Summary, laid out as the client's
 * template: B1 month, row 1 vehicle headers + DIESEL TOTAL, one row per day from
 * row 2, then TOTAL FUEL, TOTAL KM and CONSUMPTION (km per litre).
 */
class DailyFuelSummaryLightVehiclesSheet implements WithEvents, WithTitle
{
    use \App\Exports\Concerns\DailyShiftReportHeader;

    protected const HEADER_ROW = 1;
    protected const DATA_START = 2;
    protected const GREY = 'D9D9D9';

    /**
     * @param array<string,string> $units key => vehicle label (already sorted)
     * @param array<string, array<int,float>> $litres key => [day => litres]
     * @param array<string,float> $km key => kilometres run in the month
     */
    public function __construct(
        protected Carbon $month,
        protected array $units,
        protected array $litres,
        protected array $km,
    ) {
    }

    public function title(): string
    {
        return 'LIGHT VEHICLES FUEL';
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
                $fuelRow = $last + 1;
                $kmRow = $last + 2;
                $consRow = $last + 3;
                $unitCount = count($this->units);
                $totalColIndex = 3 + $unitCount;
                $totalCol = Coordinate::stringFromColumnIndex($totalColIndex);
                $lastUnitCol = Coordinate::stringFromColumnIndex(max(3, $totalColIndex - 1));

                $sheet->setCellValue('B1', ExcelDate::PHPToExcel($this->month->copy()->startOfDay()));
                $sheet->getStyle('B1')->getNumberFormat()->setFormatCode('mmm-yy');

                $col = 3;
                foreach ($this->units as $key => $label) {
                    $c = Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValueExplicit("{$c}{$header}", $label, DataType::TYPE_STRING);

                    foreach ($this->litres[$key] ?? [] as $day => $qty) {
                        if ($qty != 0) {
                            $sheet->setCellValue($c . ($first + $day - 1), round($qty, 2));
                        }
                    }

                    $sheet->setCellValue("{$c}{$fuelRow}", "=SUM({$c}{$first}:{$c}{$last})");
                    $sheet->setCellValue("{$c}{$kmRow}", round($this->km[$key] ?? 0, 2));
                    $sheet->setCellValue("{$c}{$consRow}", "=IF({$c}{$fuelRow}>0,{$c}{$kmRow}/{$c}{$fuelRow},0)");
                    $sheet->getColumnDimension($c)->setWidth(13);
                    $col++;
                }

                $sheet->setCellValue("{$totalCol}{$header}", 'DIESEL TOTAL');
                $sheet->getColumnDimension($totalCol)->setWidth(13);

                for ($d = 1; $d <= $days; $d++) {
                    $row = $first + $d - 1;
                    $sheet->setCellValue("B{$row}", ExcelDate::PHPToExcel($this->month->copy()->day($d)->startOfDay()));
                    $sheet->setCellValue("{$totalCol}{$row}", $unitCount ? "=SUM(C{$row}:{$lastUnitCol}{$row})" : 0);
                }

                $sheet->setCellValue("B{$fuelRow}", 'TOTAL FUEL');
                $sheet->setCellValue("B{$kmRow}", 'TOTAL KM');
                $sheet->setCellValue("B{$consRow}", 'CONSUMPTION');
                $sheet->setCellValue("{$totalCol}{$fuelRow}", "=SUM({$totalCol}{$first}:{$totalCol}{$last})");
                $sheet->setCellValue("{$totalCol}{$kmRow}", $unitCount ? "=SUM(C{$kmRow}:{$lastUnitCol}{$kmRow})" : 0);
                $sheet->setCellValue("{$totalCol}{$consRow}", "=IF({$totalCol}{$fuelRow}>0,{$totalCol}{$kmRow}/{$totalCol}{$fuelRow},0)");

                $sheet->getColumnDimension('A')->setWidth(3.78);
                $sheet->getColumnDimension('B')->setWidth(22.78);

                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];

                $sheet->getStyle("B{$header}:{$totalCol}{$header}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GREY]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension($header)->setRowHeight(45);

                $sheet->getStyle("B{$header}:{$totalCol}{$consRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
                $sheet->getStyle("B{$fuelRow}:{$totalCol}{$consRow}")->getFont()->setBold(true);
                $sheet->getStyle("{$totalCol}{$first}:{$totalCol}{$consRow}")->getFont()->setBold(true);

                $sheet->getStyle("B{$first}:B{$last}")->getNumberFormat()->setFormatCode('dd mmmm yyyy');
                $sheet->getStyle("B{$first}:B{$consRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$first}:{$totalCol}{$fuelRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("C{$kmRow}:{$totalCol}{$consRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->freezePane('C' . self::DATA_START);

                $this->prependReportHeader($sheet, [
                    'Report' => 'Daily Fuel Summary - Light Vehicles Fuel',
                    'Month' => $this->month->format('F Y'),
                    'Basis' => 'Vehicle fuel orders with no haul; KM from odometer readings; consumption = km per litre',
                ]);
            },
        ];
    }
}
