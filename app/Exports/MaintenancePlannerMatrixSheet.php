<?php

namespace App\Exports;

use App\Models\MaintenanceItem;
use App\Services\Maintenance\MaintenancePlannerData;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Planner grid: unit rows x tracked-item columns. Each cell holds what is left
 * (number, unit shown by the format) coloured by status; "N/T" = the unit does
 * not track that item.
 */
class MaintenancePlannerMatrixSheet implements WithEvents, WithTitle
{
    use Concerns\DailyShiftReportHeader;

    public const FILLS = [
        'overdue' => ['FFC7CE', '9C0006'],
        'due_soon' => ['FFEB9C', '9C5700'],
        'ok' => ['C6EFCE', '006100'],
        'no_reading' => ['FFFFFF', '7F7F7F'],
        'inactive' => ['D9D9D9', '595959'],
        'not_tracked' => ['F2F2F2', 'A6A6A6'],
    ];

    protected const HEADER_ROW = 1;
    protected const FIXED_COLS = 6; // #, Equipment, Unit, Make/Model, Current Hours, Current Km

    public function __construct(protected MaintenancePlannerData $data)
    {
    }

    public function title(): string
    {
        return 'Planner';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->write($sheet);

                $this->prependReportHeader($sheet, [
                    'Report' => 'Workshop Maintenance Planner',
                    'As At' => $this->data->asAt->format('d M Y'),
                ] + $this->data->filterLines() + [
                    'Basis' => 'Left = next due - current hour meter / mileage, or days to next due date; items tracked by more than one unit show the one running out first',
                ]);
            },
        ];
    }

    protected function write(Worksheet $sheet): void
    {
        $h = self::HEADER_ROW;
        $headings = ['#', 'Equipment', 'Unit', 'Make / Model', 'Current Hours', 'Current Km'];
        foreach ($this->data->items as $item) {
            $headings[] = $item->label;
        }
        $headings[] = 'Overdue';
        $headings[] = 'Due Soon';
        $headings[] = 'Not Tracked';

        foreach ($headings as $i => $text) {
            $sheet->setCellValueExplicit(Coordinate::stringFromColumnIndex($i + 1) . $h, $text, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        $lastColIndex = count($headings);
        $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
        $firstItemCol = self::FIXED_COLS + 1;
        $lastItemCol = self::FIXED_COLS + $this->data->items->count();

        $row = $h + 1;
        foreach ($this->data->units as $n => $unit) {
            $sheet->setCellValue("A{$row}", $n + 1);
            $sheet->setCellValue("B{$row}", $unit['type']);
            $sheet->setCellValueExplicit("C{$row}", $unit['label'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $unit['make']);
            if ($unit['hours'] !== null) $sheet->setCellValue("E{$row}", $unit['hours']);
            if ($unit['km'] !== null) $sheet->setCellValue("F{$row}", $unit['km']);

            $counts = ['overdue' => 0, 'due_soon' => 0, 'not_tracked' => 0];
            $col = $firstItemCol;
            foreach ($this->data->items as $item) {
                $cell = Coordinate::stringFromColumnIndex($col) . $row;
                $schedule = $unit['schedules'][$item->id] ?? null;

                if (!$schedule) {
                    $state = 'not_tracked';
                    $sheet->setCellValue($cell, 'N/T');
                } else {
                    $state = $schedule->planner_status;
                    $remaining = $schedule->remaining($this->data->asAt);
                    // Multi-unit items (e.g. hours or km): show the unit closest to running out.
                    [$unitKey, $first] = $schedule->leadingDimension($this->data->asAt);

                    if ($state === 'inactive') {
                        $sheet->setCellValue($cell, 'Inactive');
                    } elseif (!$first || $first['remaining'] === null) {
                        $sheet->setCellValue($cell, 'No Reading');
                    } else {
                        $sheet->setCellValue($cell, $first['remaining']);
                        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(
                            '#,##0 "' . MaintenanceItem::UNIT_SUFFIX[$unitKey] . '";-#,##0 "' . MaintenanceItem::UNIT_SUFFIX[$unitKey] . '"'
                        );
                    }

                    $sheet->getComment($cell)->getText()->createText($this->comment($schedule, $remaining));
                    $sheet->getComment($cell)->setWidth('220pt')->setHeight('90pt');
                }

                $counts[$state] = ($counts[$state] ?? 0) + 1;
                [$bg, $fg] = self::FILLS[$state] ?? self::FILLS['no_reading'];
                $sheet->getStyle($cell)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bg]],
                    'font' => ['color' => ['rgb' => $fg], 'bold' => in_array($state, ['overdue', 'due_soon'])],
                ]);
                $col++;
            }

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($lastColIndex - 2) . $row, $counts['overdue']);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($lastColIndex - 1) . $row, $counts['due_soon']);
            $sheet->setCellValue($lastCol . $row, $counts['not_tracked']);
            $row++;
        }

        $lastRow = max($h + 1, $row - 1);
        if (!$this->data->units) {
            $sheet->setCellValue('A' . ($h + 1), 'No maintenance items are tracked for the selected filters. Add them under Maintenance Schedules > Track Item on Fleet.');
        }

        // Legend under the grid
        $legendRow = $lastRow + 2;
        $sheet->setCellValue("B{$legendRow}", 'Legend:');
        $sheet->getStyle("B{$legendRow}")->getFont()->setBold(true);
        $legend = ['overdue' => 'Overdue (0 or less left)', 'due_soon' => 'Due Soon (within warning)', 'ok' => 'OK', 'no_reading' => 'No Reading (no current hrs/km or last-done reading)', 'not_tracked' => 'N/T = unit does not track this item', 'inactive' => 'Inactive schedule'];
        $r = $legendRow;
        foreach ($legend as $state => $text) {
            [$bg, $fg] = self::FILLS[$state];
            $sheet->setCellValue("C{$r}", $text);
            $sheet->getStyle("C{$r}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bg]],
                'font' => ['color' => ['rgb' => $fg]],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]],
            ]);
            $r++;
        }

        // Styling
        $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
        $sheet->getStyle("A{$h}:{$lastCol}{$h}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFCC00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        if ($this->data->items->isNotEmpty()) {
            $sheet->getStyle(Coordinate::stringFromColumnIndex($firstItemCol) . $h . ':' . Coordinate::stringFromColumnIndex($lastItemCol) . $h)
                ->getAlignment()->setTextRotation(90);
            $sheet->getRowDimension($h)->setRowHeight(190);
        } else {
            $sheet->getRowDimension($h)->setRowHeight(30);
        }
        $sheet->getStyle("A{$h}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
        $sheet->getStyle("A" . ($h + 1) . ":A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E" . ($h + 1) . ":F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle(Coordinate::stringFromColumnIndex($firstItemCol) . ($h + 1) . ":{$lastCol}{$lastRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(Coordinate::stringFromColumnIndex($lastColIndex - 2) . ($h + 1) . ":{$lastCol}{$lastRow}")->getFont()->setBold(true);

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(11);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(11);
        $sheet->getColumnDimension('F')->setWidth(11);
        for ($c = $firstItemCol; $c <= $lastColIndex; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(11);
        }

        $sheet->freezePane('D' . ($h + 1));
        $sheet->setAutoFilter("A{$h}:{$lastCol}{$lastRow}");
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
    }

    protected function comment($schedule, array $remaining): string
    {
        $lines = [$schedule->inspection_schedule_number];
        if ($schedule->last_inspection_date) {
            $lines[] = 'Last done: ' . $schedule->last_inspection_date->format('d/m/Y');
        }
        foreach ($remaining as $unit => $row) {
            $suffix = MaintenanceItem::UNIT_SUFFIX[$unit];
            $next = $unit === 'days'
                ? optional($schedule->next_due_date)->format('d/m/Y')
                : ($row['next_due'] !== null ? number_format($row['next_due']) . ' ' . $suffix : '-');
            $lines[] = 'Interval: ' . ($row['interval'] !== null ? number_format($row['interval']) . ' ' . $suffix : '-') . '  Next due: ' . $next;
        }

        return implode("\n", $lines);
    }
}
