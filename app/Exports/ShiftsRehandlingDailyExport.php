<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Models\Fuel;
use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Daily rehandling production per equipment unit (Rehandling shifts):
 * Yesterday block (A:I) | grey separator (J) | Month To Date block (K:R).
 *
 * Rows = every horse/vehicle with a Rehandling shift this month, so both
 * blocks list the same units. Reporting day matches ShiftsDailyExport:
 * 02:00 the previous day to 01:00 on the As At date.
 *
 * Man Hrs = sum of each work's Close Time - Start Time; DHI Runs = the same
 * for works described "DHI Runs"; Charged Hrs = Man Hrs - DHI Runs.
 * Ltr/Hr = litres on the shift's fuel order / hour-meter change since the
 * equipment's previous fuel order.
 *
 * Rows 1-6 hold the logo and report info; the table starts at row 7.
 */
class ShiftsRehandlingDailyExport implements FromArray, WithEvents, WithColumnWidths, WithTitle, WithDrawings, WithCustomStartCell
{
    use DailyShiftReportHeader;

    protected const TITLE_ROW = 7;
    protected const BAND_ROW = 8;
    protected const HEADER_ROW = 9;
    protected const DATA_START = 10;

    protected const RED = 'FF0000';
    protected const GREY = '808080';

    protected Carbon $asAt;
    protected int $rowCount = 0;
    protected array $reportPeriods = [];

    public function __construct(?Carbon $asAt = null)
    {
        $this->asAt = $asAt ?: now();
    }

    public function title(): string
    {
        return 'Rehandling';
    }

    public function startCell(): string
    {
        return 'A' . self::TITLE_ROW;
    }

    public function array(): array
    {
        $reportTo = $this->asAt->copy()->startOfDay()->addHour();
        $yFrom = $this->asAt->copy()->subDay()->startOfDay()->addHours(2);
        $mFrom = $this->asAt->copy()->subDay()->startOfMonth()->startOfDay()->addHours(2);

        $this->reportPeriods = [
            'Period (Yesterday)' => $this->formatWindow($yFrom, $reportTo),
            'Period (Month To Date)' => $this->formatWindow($mFrom, $reportTo),
        ];

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
            ['', 'YESTERDAY PRODUCTION', '', '', '', '', '', '', '', '', '', 'MONTH TO DATE PRODUCTION'],
            ['EQUIPMENT #', 'MAN HRS', 'DHI RUNS', 'CHARGED HRS', 'MACHINE HRS', 'LOADS', 'DIESEL', 'LTR/HR', 'COMMENTS', '',
             'EQUIPMENT #', 'MAN HRS', 'DHI RUNS', 'CHARGED HRS', 'MACHINE HRS', 'LOADS', 'DIESEL', 'LTR/HR'],
        ];

        foreach ($labels as $key => $label) {
            $y = $yesterday[$key] ?? null;
            $m = $mtd[$key] ?? null;

            $rows[] = array_merge(
                [$label],
                $this->figures($y),
                [$y ? implode('; ', $y['comments']) : null, '', $label],
                $this->figures($m)
            );
        }

        $this->rowCount = count($labels);

        // Sub total: plain SUMs; Ltr/Hr re-derived from the fuel-order totals (not a sum of ratios)
        $first = self::DATA_START;
        $last = max($first, self::DATA_START + $this->rowCount - 1);
        $sum = fn ($col) => $this->rowCount ? "=SUM({$col}{$first}:{$col}{$last})" : 0;

        $rows[] = [
            'SUB  TOTAL',
            $sum('B'), $sum('C'), $sum('D'), $sum('E'), $sum('F'), $sum('G'),
            $this->totalLitresPerHour($yesterday),
            '', '', '',
            $sum('L'), $sum('M'), $sum('N'), $sum('O'), $sum('P'), $sum('Q'),
            $this->totalLitresPerHour($mtd),
        ];

        return $rows;
    }

    /** Man Hrs, DHI Runs, Charged Hrs, Machine Hrs, Loads, Diesel, Ltr/Hr for one block. */
    protected function figures(?array $t): array
    {
        if (!$t) {
            return array_fill(0, 7, null);
        }

        return [
            round($t['man_hours'], 2),
            round($t['dhi_runs'], 2),
            round(max($t['man_hours'] - $t['dhi_runs'], 0), 2),
            $t['machine_hours'],
            $t['loads'],
            $t['diesel'],
            $this->litresPerHour($t['lph_litres'], $t['lph_hours']),
        ];
    }

    protected function totalLitresPerHour(array $block): ?float
    {
        return $this->litresPerHour(
            array_sum(array_column($block, 'lph_litres')),
            array_sum(array_column($block, 'lph_hours'))
        );
    }

    /**
     * Rehandling shifts in the window, summed per equipment unit.
     */
    protected function totalsByEquipment(Carbon $from, Carbon $to): array
    {
        $shifts = Shift::query()
            // The shift form stores "Rehandling"; accept the plural too
            ->whereIn('for', ['Rehandling', 'Rehandlings'])
            ->where(fn ($q) => $this->whereShiftStartBetween($q, $from, $to))
            ->with(['rehandlings.work', 'fuel', 'horse', 'vehicle'])
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
                'dhi_runs' => 0.0,
                'machine_hours' => 0.0,
                'loads' => 0.0,
                'diesel' => 0.0,
                'lph_litres' => 0.0,
                'lph_hours' => 0.0,
                'comments' => [],
            ];

            $out[$key]['man_hours'] += $this->manHours($shift);
            $out[$key]['dhi_runs'] += $this->dhiRuns($shift);
            $out[$key]['machine_hours'] += $this->machineHours($shift);
            $out[$key]['loads'] += (float) $shift->rehandlings->sum(fn ($r) => is_numeric($r->loads) ? (float) $r->loads : 0);
            $out[$key]['diesel'] += $this->diesel($shift);

            if ($run = $this->fuelOrderHourRun($shift->fuel)) {
                $out[$key]['lph_litres'] += (float) $shift->fuel->quantity;
                $out[$key]['lph_hours'] += $run;
            }

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

    /** Sum of every work's Close Time - Start Time, in hours. */
    protected function manHours(Shift $shift): float
    {
        return (float) $shift->rehandlings->sum(fn ($r) => $this->workHours($r));
    }

    /** Same as man hours, for works described "DHI Runs" only. */
    protected function dhiRuns(Shift $shift): float
    {
        return (float) $shift->rehandlings
            ->filter(fn ($r) => preg_match('/^\s*dhi\s*runs?\s*$/i', (string) $r->work?->description))
            ->sum(fn ($r) => $this->workHours($r));
    }

    /** Work Start Time -> Close Time ("H:i"), wrapping past midnight. */
    protected function workHours($rehandling): float
    {
        $start = $this->minutesOfDay($rehandling->start_time);
        $stop = $this->minutesOfDay($rehandling->stop_time);

        if ($start === null || $stop === null) {
            return 0.0;
        }

        if ($stop < $start) {
            $stop += 24 * 60;
        }

        return ($stop - $start) / 60;
    }

    protected function minutesOfDay($time): ?int
    {
        if (blank($time) || !preg_match('/(\d{1,2}):(\d{2})/', (string) $time, $m)) {
            return null;
        }

        return (int) $m[1] * 60 + (int) $m[2];
    }

    /**
     * Hour-meter change between this fuel order and the same equipment's
     * previous (non-rejected) fuel order; null when either reading is missing.
     */
    protected function fuelOrderHourRun(?Fuel $fuel): ?float
    {
        if (!$fuel || !is_numeric($fuel->hours) || !is_numeric($fuel->quantity) || (float) $fuel->quantity <= 0) {
            return null;
        }

        if (!$fuel->horse_id && !$fuel->vehicle_id) {
            return null;
        }

        $previous = Fuel::query()
            ->when($fuel->horse_id, fn ($q) => $q->where('horse_id', $fuel->horse_id))
            ->when(!$fuel->horse_id, fn ($q) => $q->where('vehicle_id', $fuel->vehicle_id))
            ->where('id', '!=', $fuel->id)
            ->where('authorization', '!=', 'rejected')
            ->when(filled($fuel->date), fn ($q) => $q->where(fn ($w) => $w
                ->where('date', '<', $fuel->date)
                ->orWhere(fn ($s) => $s->where('date', $fuel->date)->where('id', '<', $fuel->id))))
            ->when(blank($fuel->date), fn ($q) => $q->where('id', '<', $fuel->id))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(20)
            ->get(['id', 'hours'])
            ->first(fn ($f) => is_numeric($f->hours));

        if (!$previous) {
            return null;
        }

        $run = (float) $fuel->hours - (float) $previous->hours;

        return $run > 0 ? $run : null;
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

    protected function litresPerHour(float $litres, float $hours): ?float
    {
        return $hours > 0 ? round($litres / $hours, 2) : null;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 11, 'C' => 11, 'D' => 14, 'E' => 14, 'F' => 10, 'G' => 10, 'H' => 10, 'I' => 32,
            'J' => 3,
            'K' => 18, 'L' => 11, 'M' => 11, 'N' => 14, 'O' => 14, 'P' => 10, 'Q' => 10, 'R' => 10,
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

                // Report info beside the logo, above the MTD block
                $this->writeReportInfo($sheet, 'K', $this->reportPeriods);

                // Title
                $title = self::TITLE_ROW;
                $sheet->mergeCells("A{$title}:R{$title}");
                $sheet->getStyle("A{$title}")->getFont()->setBold(true)->setSize(13);
                $sheet->getRowDimension($title)->setRowHeight(22);

                // Grey band with the two block titles
                $sheet->mergeCells("B{$band}:I{$band}");
                $sheet->mergeCells("L{$band}:R{$band}");
                $sheet->getStyle("A{$band}:R{$band}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'underline' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_BOTTOM],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GREY]],
                    'borders' => ['outline' => $redBorder],
                ]);
                $sheet->getRowDimension($band)->setRowHeight(30);

                // Column headers
                $sheet->getStyle("A{$header}:R{$header}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                foreach (["A{$header}", "K{$header}"] as $cell) {
                    $sheet->getStyle($cell)->getFont()->setUnderline(true);
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Red grid on both blocks
                foreach (['A', 'K'] as $start) {
                    $end = $start === 'A' ? 'I' : 'R';
                    $sheet->getStyle("{$start}{$header}:{$end}{$totalRow}")->applyFromArray([
                        'borders' => ['allBorders' => $redBorder],
                    ]);
                }

                // Equipment labels bold, figures right-aligned with "-" for zero
                $sheet->getStyle("A{$dataStart}:A{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("K{$dataStart}:K{$totalRow}")->getFont()->setBold(true);
                $numberFormat = '#,##0.00;-#,##0.00;"-"';
                foreach (["B{$dataStart}:H{$totalRow}", "L{$dataStart}:R{$totalRow}"] as $range) {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode($numberFormat);
                    $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
                $sheet->getStyle("F{$dataStart}:F{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
                $sheet->getStyle("P{$dataStart}:P{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0;"-"');
                $sheet->getStyle("I{$dataStart}:I{$totalRow}")->getAlignment()->setWrapText(true);

                // Sub total row: red label, bold, heavier top line
                $sheet->getStyle("A{$totalRow}:R{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRow}")->getFont()->getColor()->setRGB(self::RED);
                foreach (["A{$totalRow}:I{$totalRow}", "K{$totalRow}:R{$totalRow}"] as $range) {
                    $sheet->getStyle($range)->getBorders()->getTop()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::RED);
                }

                // Grey separator column between the blocks
                $sheet->getStyle("J{$band}:J{$totalRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::GREY);

                $sheet->freezePane("B{$dataStart}");
            },
        ];
    }
}
