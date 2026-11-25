<?php

namespace App\Exports;

use App\Models\InspectionSchedule;
use App\Models\MaintenanceItem;
use App\Services\Maintenance\MaintenancePlannerData;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** One row per maintenance schedule (and per hours/km/days dimension), worst first. */
class MaintenancePlannerDetailSheet implements WithEvents, WithTitle
{
    use Concerns\DailyShiftReportHeader;

    protected const HEADINGS = [
        'Schedule#', 'Equipment', 'Unit', 'Tracks', 'Category', 'Tracked By', 'Interval',
        'Last Done Date', 'Last Done Reading', 'Next Due', 'Current Reading', 'Left', 'Status', 'Notes',
    ];

    protected const RANK = ['overdue' => 0, 'due_soon' => 1, 'no_reading' => 2, 'ok' => 3, 'inactive' => 4];

    public function __construct(protected MaintenancePlannerData $data)
    {
    }

    public function title(): string
    {
        return 'Schedule Detail';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count(self::HEADINGS));

                foreach (self::HEADINGS as $i => $text) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '1', $text);
                }

                $schedules = $this->data->schedules->sortBy(fn ($s) => sprintf(
                    '%d|%s|%s|%s',
                    self::RANK[$s->planner_status] ?? 9,
                    $s->equipmentType(),
                    str_pad($s->equipmentLabel(), 30),
                    $s->scheduleFor()
                ), SORT_NATURAL);

                $row = 2;
                foreach ($schedules as $schedule) {
                    $inactive = $schedule->planner_status === 'inactive';

                    foreach ($schedule->remaining($this->data->asAt) as $unit => $dim) {
                        $suffix = MaintenanceItem::UNIT_SUFFIX[$unit];
                        $state = $inactive ? 'inactive' : $dim['status'];

                        $sheet->setCellValueExplicit("A{$row}", (string) $schedule->inspection_schedule_number, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValue("B{$row}", $schedule->equipmentType());
                        $sheet->setCellValueExplicit("C{$row}", $schedule->equipmentLabel(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->setCellValue("D{$row}", $schedule->scheduleFor());
                        $sheet->setCellValue("E{$row}", $schedule->maintenance_item->category ?? ($schedule->service_type->name ?? ''));
                        $sheet->setCellValue("F{$row}", MaintenanceItem::UNITS[$unit]);
                        if ($dim['interval'] !== null) $sheet->setCellValue("G{$row}", $dim['interval']);
                        if ($schedule->last_inspection_date) {
                            $sheet->setCellValue("H{$row}", ExcelDate::PHPToExcel($schedule->last_inspection_date->copy()->startOfDay()));
                        }

                        $last = $unit === 'hours' ? $schedule->last_inspection_hours : ($unit === 'km' ? $schedule->last_inspection_km : null);
                        if (is_numeric($last)) $sheet->setCellValue("I{$row}", (float) $last);

                        if ($unit === 'days') {
                            if ($schedule->next_due_date) {
                                $sheet->setCellValue("J{$row}", ExcelDate::PHPToExcel($schedule->next_due_date->copy()->startOfDay()));
                                $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                            }
                            $sheet->setCellValue("K{$row}", ExcelDate::PHPToExcel($this->data->asAt));
                            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                        } else {
                            if ($dim['next_due'] !== null) $sheet->setCellValue("J{$row}", $dim['next_due']);
                            if ($dim['current'] !== null) $sheet->setCellValue("K{$row}", $dim['current']);
                            $sheet->getStyle("I{$row}:K{$row}")->getNumberFormat()->setFormatCode('#,##0 "' . $suffix . '"');
                        }
                        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0 "' . $suffix . '"');

                        if ($dim['remaining'] !== null) {
                            $sheet->setCellValue("L{$row}", $dim['remaining']);
                            $sheet->getStyle("L{$row}")->getNumberFormat()->setFormatCode('#,##0 "' . $suffix . '";-#,##0 "' . $suffix . '"');
                        }

                        $sheet->setCellValue("M{$row}", $inactive ? 'Inactive' : (InspectionSchedule::STATUS_LABELS[$state] ?? $state));
                        $sheet->setCellValue("N{$row}", (string) $schedule->notes);

                        [$bg, $fg] = MaintenancePlannerMatrixSheet::FILLS[$state] ?? MaintenancePlannerMatrixSheet::FILLS['no_reading'];
                        $sheet->getStyle("L{$row}:M{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bg]],
                            'font' => ['color' => ['rgb' => $fg], 'bold' => in_array($state, ['overdue', 'due_soon'])],
                        ]);
                        $row++;
                    }
                }

                $lastRow = max(2, $row - 1);
                if ($row === 2) {
                    $sheet->setCellValue('A2', 'No maintenance schedules for the selected filters.');
                }

                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFCC00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
                $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle("N2:N{$lastRow}")->getAlignment()->setWrapText(true);

                foreach (['A' => 14, 'B' => 10, 'C' => 16, 'D' => 34, 'E' => 16, 'F' => 10, 'G' => 13, 'H' => 13, 'I' => 15, 'J' => 15, 'K' => 15, 'L' => 13, 'M' => 12, 'N' => 40] as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                $sheet->freezePane('D2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

                $this->prependReportHeader($sheet, [
                    'Report' => 'Workshop Maintenance Planner - Schedule Detail',
                    'As At' => $this->data->asAt->format('d M Y'),
                ] + $this->data->filterLines());
            },
        ];
    }
}
