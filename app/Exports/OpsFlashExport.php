<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Models\Budget;
use App\Models\Fuel;
use App\Models\OffloadingPoint;
use App\Models\Shift;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Monthly "Ops Flash" (client's Ops Flash LEAD SHEET): one row per day of the
 * month, one block per offloading point side by side (template: SMC | NGEZI):
 *
 *   DAILY TARGET | DAILY TONNAGE | VARIANCE | BUDGET LOADS | DAILY LOADS | KM | DAILY FUEL | FUEL CONS-
 *
 * Source: trips on shifts, counted on the shift's production day (02:00 to 01:00
 * next day, as the Production Report). Tonnage = trip weight, loads = trips.
 * KM = trip ending - starting mileage (else trip distance, else the shift's
 * mileage shared by loads). Fuel = the shift's fuel order litres shared across
 * the shift's trips by mileage (by loads when trips carry no mileage), so a shift
 * that served two offloading points is split between them. Cons = km / litre.
 *
 * Targets come from Budgets (module Shifts, active) named after the offloading
 * point: "<point> Tonnage" and "<point> Loads", period Daily (used per day) or
 * Monthly (spread evenly over the month's days).
 *
 * The template's hidden lost-load columns (Lost DHI / Zimp / Other, Due to SH,
 * Nett Impact) have no source in the system and are left out.
 */
class OpsFlashExport implements WithEvents, WithTitle
{
    use DailyShiftReportHeader;
    use \App\Exports\Concerns\SharesShiftTrips;

    protected const BLOCK = ['DAILY TARGET', 'DAILY TONNAGE', 'VARIANCE', 'BUDGET LOADS', 'DAILY LOADS', 'KM', 'DAILY FUEL', 'FUEL CONS-'];
    protected const GROUPS = [['TONNAGE DETAILS', 0, 2], ['LOADS SUMMARY', 3, 4], ['FUEL SUMMARY', 5, 7]];

    protected const MONTH_ROW = 1;
    protected const POINT_ROW = 2;
    protected const GROUP_ROW = 3;
    protected const HEADER_ROW = 4; // rows 4-5 merged
    protected const DATA_START = 6;

    protected const BLUE = 'BDD7EE';
    protected const GOLD = 'FFE699';

    protected Carbon $month;
    protected array $budgetMap = [];

    public function __construct(?Carbon $month = null)
    {
        $this->month = ($month ?: now())->copy()->startOfMonth();
    }

    public function title(): string
    {
        return 'LEAD SHEET';
    }

    /** First column index (1-based) of block $i; blocks are 8 columns + 1 spacer, after the date column A. */
    protected function colIndex(int $block, int $offset = 0): int
    {
        return 2 + $block * 9 + $offset;
    }

    protected function col(int $block, int $offset = 0): string
    {
        return Coordinate::stringFromColumnIndex($this->colIndex($block, $offset));
    }

    /**
     * @return array<int|string, array{name: string, days: array<int, array{tons: float, loads: int, km: float, fuel: float}>}>
     */
    protected function collect(): array
    {
        $start = $this->month->copy();
        $end = $this->month->copy()->endOfMonth();

        $shifts = Shift::query()
            ->where(fn (Builder $q) => $this->whereShiftStartBetween(
                $q,
                $start->copy()->addHours(2),
                $end->copy()->addDay()->startOfDay()->addHour()->addMinutes(59)
            ))
            ->get()
            ->keyBy('id');

        $points = [];

        if ($shifts->isNotEmpty()) {
            $trips = Trip::query()
                ->whereIn('shift_id', $shifts->keys())
                ->with('offloading_point:id,name')
                ->get();

            $fuelByShift = Fuel::query()
                ->whereIn('shift_id', $shifts->keys())
                ->where(fn (Builder $q) => $q->whereNull('authorization')->orWhere('authorization', '!=', 'rejected'))
                ->selectRaw('shift_id, SUM(quantity) as litres')
                ->groupBy('shift_id')
                ->pluck('litres', 'shift_id');

            // trips.shift is a column that shadows the shift() relation, so shifts are looked up by id
            foreach ($trips->groupBy('shift_id') as $shiftId => $shiftTrips) {
                $shift = $shifts[$shiftId] ?? null;
                $day = $shift ? $this->productionDay($shift) : null;
                if (!$day || $day->lt($start) || $day->gt($end)) {
                    continue;
                }

                $shares = $this->shares($shiftTrips);
                $shiftKm = $this->shiftKm($shift);
                $litres = (float) ($fuelByShift[$shiftId] ?? 0);

                foreach ($shiftTrips as $trip) {
                    $key = $trip->offloading_point_id ?: 'none';
                    $points[$key] ??= [
                        'name' => $trip->offloading_point?->name ?: 'Unknown Offloading Point',
                        'days' => [],
                    ];

                    $d = &$points[$key]['days'][$day->day];
                    $d ??= ['tons' => 0.0, 'loads' => 0, 'km' => 0.0, 'fuel' => 0.0];
                    $d['tons'] += is_numeric($trip->weight) ? (float) $trip->weight : 0.0;
                    $d['loads']++;
                    $d['km'] += $shares['km'][$trip->id] ?? ($shiftKm * $shares['loads']);
                    $d['fuel'] += $litres * $shares['by'][$trip->id];
                    unset($d);
                }
            }
        }

        // Points with a target but no trips yet this month still get a block
        $this->bootBudgets();
        foreach (OffloadingPoint::query()->get(['id', 'name']) as $op) {
            if (!isset($points[$op->id]) && $this->hasTarget($op->name)) {
                $points[$op->id] = ['name' => $op->name, 'days' => []];
            }
        }

        uasort($points, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        return $points;
    }

    protected function bootBudgets(): void
    {
        $user = Auth::user();
        $companyId = $user?->employee?->company?->id ?? $user?->company_id ?? null;

        $this->budgetMap = [];
        Budget::query()
            ->where('module', 'Shifts')
            ->where('status', 1)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->get()
            ->each(function ($b) {
                $this->budgetMap[$this->budgetKey($b->name, $b->period)] = (float) $b->value;
            });
    }

    protected function budgetKey(?string $name, ?string $period): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim((string) $name))) . '|' . strtolower(trim((string) $period));
    }

    protected function hasTarget(string $point): bool
    {
        return $this->dailyTarget($point, 'Tonnage') !== null || $this->dailyTarget($point, 'Loads') !== null;
    }

    /** Daily budget "<point> <metric>", else the Monthly one spread over the month's days. */
    protected function dailyTarget(string $point, string $metric): ?float
    {
        $name = "{$point} {$metric}";

        $daily = $this->budgetMap[$this->budgetKey($name, 'Daily')] ?? null;
        if ($daily !== null) {
            return $daily;
        }

        $monthly = $this->budgetMap[$this->budgetKey($name, 'Monthly')] ?? null;

        return $monthly !== null ? $monthly / $this->month->daysInMonth : null;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $points = $this->collect();
                $this->render($sheet, $points);

                $this->prependReportHeader($sheet, [
                    'Report' => 'Ops Flash',
                    'Month' => $this->month->format('F Y'),
                    'Offloading Points' => $points ? implode(', ', array_map(fn ($p) => trim($p['name']), $points)) : 'None',
                    'Basis' => 'Trips on shifts, production day 02:00 - 01:00; targets from Shifts budgets "<point> Tonnage" / "<point> Loads"',
                ]);
            },
        ];
    }

    protected function render(Worksheet $sheet, array $points): void
    {
        $days = $this->month->daysInMonth;
        $first = self::DATA_START;
        $last = $first + $days - 1;
        $totalRow = $last + 1;
        $targetRow = $last + 2;
        $hr = self::HEADER_ROW;

        // Days still to come (and today, not yet closed) get no variance
        $closedDays = $this->month->isSameMonth(now())
            ? now()->day - 1
            : ($this->month->lt(now()) ? $days : 0);

        $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
        $medium = ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']];
        $center = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true];

        // Date column
        $sheet->setCellValue('A' . self::MONTH_ROW, ExcelDate::PHPToExcel($this->month->copy()->startOfDay()));
        $sheet->getStyle('A' . self::MONTH_ROW)->getNumberFormat()->setFormatCode('"MONTH: "mmmm yyyy');
        $sheet->mergeCells('A' . self::MONTH_ROW . ':A' . self::POINT_ROW);
        $sheet->setCellValue('A' . self::GROUP_ROW, 'Date');
        $sheet->mergeCells('A' . self::GROUP_ROW . ':A' . ($hr + 1));
        for ($d = 1; $d <= $days; $d++) {
            $sheet->setCellValue('A' . ($first + $d - 1), ExcelDate::PHPToExcel($this->month->copy()->day($d)->startOfDay()));
        }
        $sheet->getStyle("A{$first}:A{$last}")->getNumberFormat()->setFormatCode('dddd, mmmm dd, yyyy');
        $sheet->getStyle("A{$first}:A{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("A{$targetRow}", 'MONTHLY TARGET');
        $sheet->getStyle('A' . self::MONTH_ROW . ":A{$targetRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$first}:A{$last}")->getFont()->setBold(false);
        $sheet->getStyle('A' . self::MONTH_ROW . ':A' . ($hr + 1))->getAlignment()->applyFromArray($center);
        $sheet->getStyle('A' . self::GROUP_ROW . ":A{$targetRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
        $sheet->getColumnDimension('A')->setWidth(29.56);

        if (!$points) {
            $sheet->setCellValue('B' . self::POINT_ROW, 'No shift trips or offloading point targets for this month.');
            $sheet->getStyle('B' . self::POINT_ROW)->getFont()->setItalic(true);
            return;
        }

        $sheet->getRowDimension(self::POINT_ROW)->setRowHeight(20);
        $sheet->getRowDimension($hr)->setRowHeight(15);
        $sheet->getRowDimension($hr + 1)->setRowHeight(15);

        foreach (array_values($points) as $i => $point) {
            $c = fn (int $o) => $this->col($i, $o);
            [$tgt, $tons, $var, $bLoads, $loads, $km, $fuel, $cons] = array_map($c, range(0, 7));
            $tonTarget = $this->dailyTarget($point['name'], 'Tonnage');
            $loadTarget = $this->dailyTarget($point['name'], 'Loads');

            // Headings
            $sheet->setCellValue($tgt . self::MONTH_ROW, 'MONTHLY TARGET TONNAGE: ' . ($tonTarget !== null ? number_format($tonTarget * $days, 0) : 'not set'));
            $sheet->mergeCells($tgt . self::MONTH_ROW . ':' . $cons . self::MONTH_ROW);
            $sheet->setCellValue($tgt . self::POINT_ROW, strtoupper(trim($point['name'])));
            $sheet->mergeCells($tgt . self::POINT_ROW . ':' . $cons . self::POINT_ROW);

            foreach (self::GROUPS as [$label, $from, $to]) {
                $sheet->setCellValue($c($from) . self::GROUP_ROW, $label);
                $sheet->mergeCells($c($from) . self::GROUP_ROW . ':' . $c($to) . self::GROUP_ROW);
            }
            foreach (self::BLOCK as $o => $label) {
                $sheet->setCellValue($c($o) . $hr, $label);
                $sheet->mergeCells($c($o) . $hr . ':' . $c($o) . ($hr + 1));
            }

            // Days
            for ($d = 1; $d <= $days; $d++) {
                $r = $first + $d - 1;
                $v = $point['days'][$d] ?? null;

                if ($tonTarget !== null) {
                    $sheet->setCellValue("{$tgt}{$r}", round($tonTarget, 2));
                }
                if ($loadTarget !== null) {
                    $sheet->setCellValue("{$bLoads}{$r}", round($loadTarget, 2));
                }
                if ($v) {
                    $sheet->setCellValue("{$tons}{$r}", round($v['tons'], 2));
                    $sheet->setCellValue("{$loads}{$r}", $v['loads']);
                    if ($v['km'] > 0) {
                        $sheet->setCellValue("{$km}{$r}", round($v['km'], 2));
                    }
                    if ($v['fuel'] > 0) {
                        $sheet->setCellValue("{$fuel}{$r}", round($v['fuel'], 2));
                    }
                    $sheet->setCellValue("{$cons}{$r}", "=IF(N({$fuel}{$r})>0,N({$km}{$r})/{$fuel}{$r},\"\")");
                }
                if ($d <= $closedDays || $v) {
                    $sheet->setCellValue("{$var}{$r}", "=N({$tons}{$r})-N({$tgt}{$r})");
                }
            }

            // Totals + monthly target
            foreach ([$tgt, $tons, $var, $bLoads, $loads, $km, $fuel] as $col) {
                $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}{$first}:{$col}{$last})");
            }
            $sheet->setCellValue("{$cons}{$totalRow}", "=IF({$fuel}{$totalRow}>0,{$km}{$totalRow}/{$fuel}{$totalRow},\"\")");
            $sheet->setCellValue("{$tgt}{$targetRow}", "=SUM({$tgt}{$first}:{$tgt}{$last})");
            $sheet->setCellValue("{$bLoads}{$targetRow}", "=SUM({$bLoads}{$first}:{$bLoads}{$last})");

            // Styling
            $sheet->getStyle($tgt . self::MONTH_ROW . ':' . $cons . self::POINT_ROW)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $center,
            ]);
            $sheet->getStyle($tgt . self::POINT_ROW)->getFont()->setSize(13);
            $sheet->getStyle($tgt . self::GROUP_ROW . ':' . $cons . ($hr + 1))->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $center,
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::BLUE]],
            ]);
            $sheet->getStyle($tgt . self::MONTH_ROW . ":{$cons}{$targetRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
            $sheet->getStyle($tgt . self::MONTH_ROW . ":{$cons}{$targetRow}")->getBorders()->getOutline()->applyFromArray($medium);
            $sheet->getStyle("{$tgt}{$totalRow}:{$cons}{$targetRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GOLD]],
            ]);
            $sheet->getStyle("{$tgt}{$first}:{$cons}{$targetRow}")->getNumberFormat()->setFormatCode('#,##0;-#,##0');
            $sheet->getStyle("{$var}{$first}:{$var}{$totalRow}")->getNumberFormat()->setFormatCode('#,##0;[Red]-#,##0');
            $sheet->getStyle("{$cons}{$first}:{$cons}{$totalRow}")->getNumberFormat()->setFormatCode('0.00');

            foreach ([12.89, 11, 10, 9.5, 9.5, 10, 10.5, 9] as $o => $w) {
                $sheet->getColumnDimension($c($o))->setWidth($w);
            }
            $sheet->getColumnDimension($c(8))->setWidth(3);
        }

        $sheet->freezePane('B' . self::DATA_START);
    }
}
