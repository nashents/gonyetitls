<?php

namespace App\Exports;

use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Daily rehandling production per equipment unit (Rehandlings shifts):
 * Yesterday block (A:G) | grey separator (H) | Month To Date block (I:N).
 *
 * Rows = every horse/vehicle with a Rehandlings shift this month, so both
 * blocks list the same units. Reporting day matches ShiftsDailyExport:
 * 02:00 the previous day to 01:00 on the As At date.
 */
class ShiftsRehandlingDailyExport implements FromArray, WithEvents, WithColumnWidths, WithTitle
{
    protected const TITLE_ROW = 1;
    protected const BAND_ROW = 2;
    protected const HEADER_ROW = 3;
    protected const DATA_START = 4;

    protected const RED = 'FF0000';
    protected const GREY = '808080';

    protected Carbon $asAt;
    protected int $rowCount = 0;

    public function __construct(?Carbon $asAt = null)
    {
        $this->asAt = $asAt ?: now();
    }

    public function title(): string
    {
        return 'Rehandling';
    }

    public function array(): array
    {
        $reportTo = $this->asAt->copy()->startOfDay()->addHour();
        $yFrom = $this->asAt->copy()->subDay()->startOfDay()->addHours(2);
        $mFrom = $this->asAt->copy()->subDay()->startOfMonth()->startOfDay()->addHours(2);

        $yesterday = $this->totalsByEquipment($yFrom, $reportTo);
        $mtd = $this->totalsByEquipment($mFrom, $reportTo);

        $labels = array_unique(array_merge(
            array_column($mtd, 'label', 'key'),
            array_column($yesterday, 'label', 'key')
        ));
        uasort($labels, 'strnatcasecmp');

        $reportDate = $this->asAt->copy()->subDay()->format('M - d, Y');

        $rows = [
            ["Rehandling Production - {$reportDate}"],
            ['', 'YESTERDAY PRODUCTION', '', '', '', '', '', '', '', 'MONTH TO DATE PRODUCTION'],
            ['EQUIPMENT #', 'MAN HRS', 'MACHINE HRS', 'LOADS', 'DIESEL', 'LTR/HR', 'COMMENTS', '',
             'EQUIPMENT #', 'MAN HRS', 'MACHINE HRS', 'LOADS', 'DIESEL', 'LTR/HR'],
        ];

        foreach ($labels as $key => $label) {
            $y = $yesterday[$key] ?? null;
            $m = $mtd[$key] ?? null;

            $rows[] = [
                $label,
                $y ? $y['man_hours'] : null,
                $y ? $y['machine_hours'] : null,
                $y ? $y['loads'] : null,
                $y ? $y['diesel'] : null,
                $y ? $this->litresPerHour($y['diesel'], $y['machine_hours']) : null,
                $y ? implode('; ', $y['comments']) : null,
                '',
                $label,
                $m ? $m['man_hours'] : null,
                $m ? $m['machine_hours'] : null,
                $m ? $m['loads'] : null,
                $m ? $m['diesel'] : null,
                $m ? $this->litresPerHour($m['diesel'], $m['machine_hours']) : null,
            ];
        }

        $this->rowCount = count($labels);

        // Sub total: plain SUMs, Ltr/Hr re-derived from the totals (not a sum of ratios)
        $first = self::DATA_START;
        $last = max($first, self::DATA_START + $this->rowCount - 1);
        $total = self::DATA_START + $this->rowCount;
        $sum = fn ($col) => $this->rowCount ? "=SUM({$col}{$first}:{$col}{$last})" : 0;

        $rows[] = [
            'SUB  TOTAL',
            $sum('B'), $sum('C'), $sum('D'), $sum('E'),
            "=IF(C{$total}>0,E{$total}/C{$total},0)",
            '', '', '',
            $sum('J'), $sum('K'), $sum('L'), $sum('M'),
            "=IF(K{$total}>0,M{$total}/K{$total},0)",
        ];

        return $rows;
    }

    /**
     * Rehandlings shifts in the window, summed per equipment unit.
     */
    protected function totalsByEquipment(Carbon $from, Carbon $to): array
    {
        $shifts = Shift::query()
            ->where('for', 'Rehandlings')
            ->whereBetween('shift_start_time', [$from, $to])
            ->with(['rehandlings', 'fuel', 'horse', 'vehicle'])
            ->orderBy('shift_start_time')
            ->get();

        $out = [];

        foreach ($shifts as $shift) {
            if ($shift->horse_id && $shift->horse) {
                $key = 'h' . $shift->horse_id;
                $label = $this->equipmentLabel($shift->horse);
            } elseif ($shift->vehicle_id && $shift->vehicle) {
                $key = 'v' . $shift->vehicle_id;
                $label = $this->equipmentLabel($shift->vehicle);
            } else {
                $key = 'none';
                $label = 'No Equipment';
            }

            $out[$key] ??= [
                'key' => $key,
                'label' => $label,
                'man_hours' => 0.0,
                'machine_hours' => 0.0,
                'loads' => 0.0,
                'diesel' => 0.0,
                'comments' => [],
            ];

            $out[$key]['man_hours'] += $this->manHours($shift);
            $out[$key]['machine_hours'] += $this->machineHours($shift);
            $out[$key]['loads'] += (float) $shift->rehandlings->sum(fn ($r) => is_numeric($r->loads) ? (float) $r->loads : 0);
            $out[$key]['diesel'] += $this->diesel($shift);

            if (filled($shift->comments)) {
                $out[$key]['comments'][] = (filled($shift->type) ? "{$shift->type}: " : '') . trim($shift->comments);
            }
        }

        return $out;
    }

    /** Equipment # column: fleet number (FL3, E4, ...), else registration. */
    protected function equipmentLabel($unit): string
    {
        $fleet = trim((string) $unit->fleet_number);

        if ($fleet !== '' && !preg_match('#^n[/?]?a$#i', $fleet)) {
            return $fleet;
        }

        return filled($unit->registration_number) ? (string) $unit->registration_number : $unit->identifier_label;
    }

    /** Shift clock-in to clock-out, in hours. */
    protected function manHours(Shift $shift): float
    {
        if (blank($shift->shift_start_time) || blank($shift->shift_end_time)) {
            return 0.0;
        }

        try {
            $minutes = Carbon::parse($shift->shift_start_time)->diffInMinutes(Carbon::parse($shift->shift_end_time), false);
        } catch (\Throwable $e) {
            return 0.0;
        }

        return $minutes > 0 ? round($minutes / 60, 2) : 0.0;
    }

    /** Hour meter run: rehandling entries' close - open, else the shift's own readings. */
    protected function machineHours(Shift $shift): float
    {
        $hours = $shift->rehandlings->sum(fn ($r) => $this->meterRun($r->open_hours, $r->close_hours));

        if ($hours <= 0) {
            $hours = $this->meterRun($shift->open_hours, $shift->close_hours);
        }

        return round((float) $hours, 2);
    }

    protected function meterRun($open, $close): float
    {
        if (!is_numeric($open) || !is_numeric($close)) {
            return 0.0;
        }

        $run = (float) $close - (float) $open;

        return $run > 0 ? $run : 0.0;
    }

    protected function diesel(Shift $shift): float
    {
        if ($shift->fuel && is_numeric($shift->fuel->quantity)) {
            return (float) $shift->fuel->quantity;
        }

        return is_numeric($shift->total_fuel) ? (float) $shift->total_fuel : 0.0;
    }

    protected function litresPerHour(float $diesel, float $machineHours): ?float
    {
        return $machineHours > 0 ? round($diesel / $machineHours, 2) : null;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 11, 'C' => 14, 'D' => 10, 'E' => 10, 'F' => 10, 'G' => 32,
            'H' => 3,
            'I' => 18, 'J' => 11, 'K' => 14, 'L' => 10, 'M' => 10, 'N' => 10,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $band = self::BAND_ROW;
                $header = self::HEADER_ROW;
                $dataStart = self::DATA_START;
                $totalRow = self::DATA_START + $this->rowCount;
                $redBorder = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::RED]];

                // Title
                $sheet->mergeCells('A1:N1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Grey band with the two block titles
                $sheet->mergeCells("B{$band}:G{$band}");
                $sheet->mergeCells("J{$band}:N{$band}");
                $sheet->getStyle("A{$band}:N{$band}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'underline' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GREY]],
                    'borders' => ['outline' => $redBorder],
                ]);
                $sheet->getRowDimension($band)->setRowHeight(30);

                // Column headers
                $sheet->getStyle("A{$header}:N{$header}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                foreach (["A{$header}", "I{$header}"] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setUnderline(true);
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Red grid on both blocks
                foreach (['A', 'I'] as $start) {
                    $end = $start === 'A' ? 'G' : 'N';
                    $sheet->getStyle("{$start}{$header}:{$end}{$totalRow}")->applyFromArray([
                        'borders' => ['allBorders' => $redBorder],
                    ]);
                }

                // Equipment labels bold, figures right-aligned with "-" for zero
                $sheet->getStyle("A{$dataStart}:A{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("I{$dataStart}:I{$totalRow}")->getFont()->setBold(true);
                $numberFormat = '#,##0.00;-#,##0.00;"-"';
                foreach (["B{$dataStart}:F{$totalRow}", "J{$dataStart}:N{$totalRow}"] as $range) {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle("D{$dataStart}:D{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
                $sheet->getStyle("L{$dataStart}:L{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
                $sheet->getStyle("G{$dataStart}:G{$totalRow}")->getAlignment()->setWrapText(true);

                // Sub total row: red label, bold, heavier top line
                $sheet->getStyle("A{$totalRow}:N{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}")->getFont()->getColor()->setRGB(self::RED);
                foreach (["A{$totalRow}:G{$totalRow}", "I{$totalRow}:N{$totalRow}"] as $range) {
                    $sheet->getStyle($range)->getBorders()->getTop()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::RED);
                }

                // Grey separator column between the blocks
                $sheet->getStyle("H{$band}:H{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::GREY);

                $sheet->freezePane("B{$dataStart}");
            },
        ];
    }
}
