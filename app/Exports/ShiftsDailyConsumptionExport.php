<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Models\Shift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Daily fuel consumption per truck, one section per shift type as captured on
 * the shift form: Morning (A:F) | Backshift (H:M) | Night (O:T), with a blue
 * separator column between sections.
 *
 * Reporting day matches the Production Report (ShiftsDailyExport): shifts that
 * started between 02:00 the day before the As At date and 01:00 on the As At date.
 * Legacy type "Day" counts as Morning; a blank type falls back to the start hour
 * (06:00-13:59 Morning, 14:00-21:59 Backshift, else Night).
 *
 * Each section header shows that shift's actual span: earliest Shift Start to
 * latest Shift End of the shifts in it.
 *
 * KM = close - open mileage (else actual mileage); Fuel = the shift's fuel order
 * litres (else total fuel); Cons = KM / Fuel (km per litre, as on the shift form).
 * Several shifts for one truck in the same section are summed.
 *
 * Rows 1-7 hold the logo and report info; the table starts at row 9.
 */
class ShiftsDailyConsumptionExport implements FromArray, WithEvents, WithColumnWidths, WithTitle, WithDrawings, WithCustomStartCell
{
    use DailyShiftReportHeader;

    protected const TITLE_ROW = 9;
    protected const BAND_ROW = 10;
    protected const HEADER_ROW = 11;
    protected const DATA_START = 12;

    protected const BLUE = 'BDE3F5';

    /** Section key => heading, in shift-form order. */
    protected const SECTIONS = [
        'Morning' => 'MORNING',
        'Backshift' => 'BACKSHIFT',
        'Night' => 'NIGHT',
    ];

    protected const HEADER = ['TRUCK', 'DRIVER', 'KM', 'FUEL', 'CONS', 'Comments'];

    protected Carbon $asAt;
    protected int $rowCount = 0;
    protected array $reportPeriods = [];

    public function __construct(?Carbon $asAt = null)
    {
        $this->asAt = $asAt ?: now();
    }

    public function title(): string
    {
        return 'Daily Consumption';
    }

    public function startCell(): string
    {
        return 'A' . self::TITLE_ROW;
    }

    /** First column letter of section $i (0-based); each block is 6 columns + 1 separator. */
    protected function col(int $section, int $offset = 0): string
    {
        return Coordinate::stringFromColumnIndex($section * 7 + $offset + 1);
    }

    protected function lastCol(): string
    {
        return $this->col(count(self::SECTIONS) - 1, 5);
    }

    public function array(): array
    {
        $from = $this->asAt->copy()->subDay()->startOfDay()->addHours(2);
        $to = $this->asAt->copy()->startOfDay()->addHour();

        $shifts = Shift::query()
            ->where(fn ($q) => $this->whereShiftStartBetween($q, $from, $to))
            ->with(['horse', 'vehicle', 'driver.employee', 'fuel'])
            ->orderBy('date')
            ->orderBy('shift_start_time')
            ->get();

        $sections = array_fill_keys(array_keys(self::SECTIONS), []);
        $spans = array_fill_keys(array_keys(self::SECTIONS), [null, null]);

        foreach ($shifts as $shift) {
            $section = $this->section($shift);
            [$start, $end] = $this->shiftTimes($shift);

            if ($start && (!$spans[$section][0] || $start->lt($spans[$section][0]))) {
                $spans[$section][0] = $start;
            }
            if ($end && (!$spans[$section][1] || $end->gt($spans[$section][1]))) {
                $spans[$section][1] = $end;
            }

            [$key, $label] = $this->truck($shift);

            $sections[$section][$key] ??= [
                'label' => $label,
                'drivers' => [],
                'km' => 0.0,
                'fuel' => 0.0,
                'comments' => [],
            ];

            $row = &$sections[$section][$key];

            if ($name = $this->driverName($shift)) {
                $row['drivers'][$name] = $name;
            }
            $row['km'] += $this->kilometres($shift);
            $row['fuel'] += $this->litres($shift);
            if (filled($shift->comments)) {
                $row['comments'][] = trim($shift->comments);
            }

            unset($row);
        }

        $this->reportPeriods = [
            'As At' => $this->asAt->format('d M Y'),
            'Period' => $this->formatWindow($from, $to),
        ];
        foreach (array_keys(self::SECTIONS) as $s) {
            $this->reportPeriods[$s] = $this->formatSpan($spans[$s]);
        }

        // Same truck list in every section, natural fleet-number order
        $labels = [];
        foreach ($sections as $rowsBySection) {
            foreach ($rowsBySection as $key => $r) {
                $labels[$key] = $r['label'];
            }
        }
        uasort($labels, 'strnatcasecmp');

        $this->rowCount = count($labels);
        $reportDate = $this->asAt->copy()->subDay()->format('M - d, Y');

        $band = [];
        $header = [];
        foreach (self::SECTIONS as $s => $heading) {
            $band = array_merge($band, [$heading . $this->bandSuffix($spans[$s])], array_fill(0, 5, ''), ['']);
            $header = array_merge($header, self::HEADER, ['']);
        }

        $rows = [
            ["Daily Fuel Consumption - {$reportDate}"],
            array_slice($band, 0, -1),
            array_slice($header, 0, -1),
        ];

        foreach ($labels as $key => $label) {
            $line = [];
            foreach (array_keys(self::SECTIONS) as $s) {
                $line = array_merge($line, $this->sectionRow($label, $sections[$s][$key] ?? null), ['']);
            }
            $rows[] = array_slice($line, 0, -1);
        }

        $first = self::DATA_START;
        $last = max($first, self::DATA_START + $this->rowCount - 1);
        $total = $this->totalRow();

        $line = [];
        foreach (array_keys(array_keys(self::SECTIONS)) as $i) {
            $km = $this->col($i, 2);
            $fuel = $this->col($i, 3);
            $line = array_merge($line, [
                'TOTAL',
                '',
                $this->rowCount ? "=SUM({$km}{$first}:{$km}{$last})" : 0,
                $this->rowCount ? "=SUM({$fuel}{$first}:{$fuel}{$last})" : 0,
                "=IF({$fuel}{$total}>0,{$km}{$total}/{$fuel}{$total},\"\")",
                '',
                '',
            ]);
        }
        $rows[] = array_slice($line, 0, -1);

        return $rows;
    }

    protected function totalRow(): int
    {
        return self::DATA_START + $this->rowCount;
    }

    protected function sectionRow(string $label, ?array $r): array
    {
        if (!$r) {
            return [$label, null, null, null, null, null];
        }

        return [
            $label,
            implode(', ', $r['drivers']),
            $r['km'] > 0 ? round($r['km'], 2) : null,
            $r['fuel'] > 0 ? round($r['fuel'], 2) : null,
            $r['km'] > 0 && $r['fuel'] > 0 ? round($r['km'] / $r['fuel'], 2) : null,
            implode('; ', $r['comments']),
        ];
    }

    /** Morning / Backshift / Night from the shift type, else from the start hour. */
    protected function section(Shift $shift): string
    {
        $type = strtolower(preg_replace('/[\s_-]+/', '', (string) $shift->type));

        if (str_starts_with($type, 'night')) {
            return 'Night';
        }
        if (str_starts_with($type, 'back')) {
            return 'Backshift';
        }
        if (str_starts_with($type, 'morning') || str_starts_with($type, 'day')) {
            return 'Morning';
        }

        [$start] = $this->shiftTimes($shift);

        if (!$start) {
            return 'Morning';
        }

        return match (true) {
            $start->hour >= 6 && $start->hour < 14 => 'Morning',
            $start->hour >= 14 && $start->hour < 22 => 'Backshift',
            default => 'Night',
        };
    }

    /**
     * Shift start/end as Carbon. Both columns are strings: "Y-m-d\TH:i" from the
     * form or "H:i[:s]" from imports (day in shifts.date). A time-only end before
     * the start rolls to the next day.
     */
    protected function shiftTimes(Shift $shift): array
    {
        $start = $this->parseShiftTime($shift->shift_start_time, $shift->date);
        $end = $this->parseShiftTime($shift->shift_end_time, $start?->toDateString() ?? $shift->date);

        if ($start && $end && $end->lt($start) && !preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $shift->shift_end_time)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    protected function parseShiftTime($value, $date): ?Carbon
    {
        $value = trim((string) $value);

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{1,2}:\d{2}/', $value)) {
                return Carbon::parse(str_replace('T', ' ', substr($value, 0, 16)));
            }
            if (preg_match('/^(\d{1,2}):(\d{2})/', $value, $m) && filled($date)) {
                return Carbon::parse($date)->startOfDay()->setTime((int) $m[1], (int) $m[2]);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    protected function formatSpan(array $span): string
    {
        [$start, $end] = $span;

        if (!$start && !$end) {
            return 'No shifts';
        }

        return ($start ? $start->format('d M Y H:i') : '?') . ' - ' . ($end ? $end->format('d M Y H:i') : 'open');
    }

    protected function bandSuffix(array $span): string
    {
        [$start, $end] = $span;

        if (!$start && !$end) {
            return '';
        }

        return '  (' . ($start ? $start->format('d M H:i') : '?') . ' - ' . ($end ? $end->format('d M H:i') : 'open') . ')';
    }

    /** [group key, TRUCK label]: fleet number, else registration. */
    protected function truck(Shift $shift): array
    {
        if ($shift->horse_id && $shift->horse) {
            return ['h' . $shift->horse_id, $this->unitLabel($shift->horse)];
        }
        if ($shift->vehicle_id && $shift->vehicle) {
            return ['v' . $shift->vehicle_id, $this->unitLabel($shift->vehicle)];
        }

        return ['none', 'No Truck'];
    }

    protected function unitLabel($unit): string
    {
        $fleet = trim((string) $unit->fleet_number);

        if ($fleet !== '' && !preg_match('#^n[/?]?a$#i', $fleet)) {
            return $fleet;
        }

        return (string) ($unit->registration_number ?: ('#' . $unit->id));
    }

    protected function driverName(Shift $shift): ?string
    {
        $employee = $shift->driver?->employee;

        if (!$employee) {
            return null;
        }

        $name = trim($employee->name . ' ' . $employee->surname);

        return $name !== '' ? $name : null;
    }

    protected function kilometres(Shift $shift): float
    {
        if (is_numeric($shift->open_mileage) && is_numeric($shift->close_mileage)) {
            $run = (float) $shift->close_mileage - (float) $shift->open_mileage;
            if ($run > 0) {
                return $run;
            }
        }

        return is_numeric($shift->actual_mileage) && $shift->actual_mileage > 0 ? (float) $shift->actual_mileage : 0.0;
    }

    protected function litres(Shift $shift): float
    {
        if ($shift->fuel && is_numeric($shift->fuel->quantity)) {
            return (float) $shift->fuel->quantity;
        }

        return is_numeric($shift->total_fuel) ? (float) $shift->total_fuel : 0.0;
    }

    public function columnWidths(): array
    {
        $widths = [];
        $sizes = [10, 22, 11, 11, 10, 36];

        foreach (array_keys(array_keys(self::SECTIONS)) as $i) {
            foreach ($sizes as $offset => $w) {
                $widths[$this->col($i, $offset)] = $w;
            }
            if ($i < count(self::SECTIONS) - 1) {
                $widths[$this->col($i, 6)] = 3;
            }
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $title = self::TITLE_ROW;
                $band = self::BAND_ROW;
                $header = self::HEADER_ROW;
                $dataStart = self::DATA_START;
                $totalRow = $this->totalRow();
                $lastCol = $this->lastCol();
                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
                $medium = ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']];
                $blue = ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::BLUE]];

                $this->writeReportInfo($sheet, 'D', $this->reportPeriods);

                $sheet->mergeCells("A{$title}:{$lastCol}{$title}");
                $sheet->getStyle("A{$title}")->getFont()->setBold(true)->setSize(13);
                $sheet->getRowDimension($title)->setRowHeight(22);

                $sheet->getStyle("A{$band}:{$lastCol}{$band}")->applyFromArray([
                    'font' => ['size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => $blue,
                ]);
                $sheet->getRowDimension($band)->setRowHeight(26);

                $sheet->getStyle("A{$header}:{$lastCol}{$header}")->applyFromArray([
                    'font' => ['italic' => true],
                    'fill' => $blue,
                    'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
                ]);
                $sheet->getRowDimension($header)->setRowHeight(24);
                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->getFont()->setBold(true);

                foreach (array_keys(array_keys(self::SECTIONS)) as $i) {
                    $start = $this->col($i);
                    $end = $this->col($i, 5);
                    $truck = $start;
                    $km = $this->col($i, 2);
                    $cons = $this->col($i, 4);

                    $sheet->mergeCells("{$start}{$band}:{$end}{$band}");

                    $sheet->getStyle("{$start}{$header}:{$end}{$totalRow}")->applyFromArray([
                        'borders' => ['allBorders' => $thin],
                    ]);
                    $sheet->getStyle("{$start}{$band}:{$end}{$totalRow}")->applyFromArray([
                        'borders' => ['outline' => $medium],
                    ]);
                    $sheet->getStyle("{$start}{$band}:{$end}{$band}")->getBorders()->getBottom()->applyFromArray($medium);
                    $sheet->getStyle("{$start}{$totalRow}:{$end}{$totalRow}")->getBorders()->getTop()->applyFromArray($medium);

                    // TRUCK header bold; truck numbers bold + centred
                    $sheet->getStyle("{$truck}{$header}")->getFont()->setBold(true);
                    $sheet->getStyle("{$truck}{$dataStart}:{$truck}{$totalRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle("{$km}{$dataStart}:{$cons}{$totalRow}")->getNumberFormat()
                        ->setFormatCode('#,##0.00;-#,##0.00;"-"');
                    $sheet->getStyle("{$km}{$dataStart}:{$cons}{$totalRow}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("{$end}{$dataStart}:{$end}{$totalRow}")->getAlignment()->setWrapText(true);

                    // Blue separator after every section but the last
                    if ($i < count(self::SECTIONS) - 1) {
                        $sep = $this->col($i, 6);
                        $sheet->getStyle("{$sep}{$band}:{$sep}{$totalRow}")->applyFromArray([
                            'fill' => $blue,
                            'borders' => ['outline' => $medium],
                        ]);
                    }
                }

                $sheet->freezePane("A{$dataStart}");
            },
        ];
    }
}
