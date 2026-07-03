<?php

namespace App\Exports;

use App\Models\Budget;
use App\Models\Company;
use App\Models\Fuel;
use App\Models\LoadingPoint;
use App\Models\Shift;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ShiftsDailyExport implements FromArray, WithEvents, WithColumnWidths, WithTitle, WithDrawings, WithCustomStartCell
{
    protected string $shiftDateColumn = 'shift_start_time';
    protected string $shiftTypeColumn = 'type';
    protected string $tripWeightColumn = 'weight';
    protected string $shiftOpenMileageColumn = 'open_mileage';
    protected string $shiftCloseMileageColumn = 'close_mileage';
    protected string $shiftFuelConsumptionColumn = 'fuel_consumption_mileage';

    protected array $lpNames = [];
    protected array $lpMap = [];

    protected string $yStartCol = 'B';
    protected string $sepCol = 'J';
    protected string $mStartCol = 'K';
    protected string $lastCol = 'R';

    protected array $budgetMap = [];

    public function __construct(
        protected string $periodTitle = 'Key Operating Metrics - ',
        protected ?Carbon $asAt = null,
        protected array $loadingPointFilterNames = []
    ) {
        $reportDate = ($this->asAt ?: now())->copy()->subDay();

        $this->periodTitle = $this->periodTitle .
            $reportDate->format('M - d, Y');
        $this->asAt = $this->asAt ?: now();

        $this->bootLoadingPoints();
        $this->bootColumns();
        $this->bootBudgets();
    }

    public function title(): string
    {
        return 'Key Metrics';
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $company  = Company::where('is_active',True)->orderBy('created_at','asc')->first();

        if ($company) {
            $drawing->setName($company->name);
            $drawing->setDescription($company->name . ' Logo');

            if (file_exists(public_path('/images/uploads/' . $company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . $company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        } else {
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }

        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    protected function bootBudgets(): void
    {
        $companyId = Auth::user()->employee->company->id ?? Auth::user()->company_id ?? null;

        $budgets = Budget::query()
            ->where('module', 'Shifts')
            ->where('status', 1)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->get();

        foreach ($budgets as $budget) {
            $key = $this->budgetKey($budget->name, $budget->period);
            $this->budgetMap[$key] = (float) $budget->value;
        }
    }

    protected function budgetKey(?string $name, ?string $period): string
    {
        return strtolower(trim((string) $name)) . '|' . strtolower(trim((string) $period));
    }

    protected function getBudget(string $metricName, string $period): ?float
    {
        $key = $this->budgetKey($metricName, $period);

        return $this->budgetMap[$key] ?? null;
    }

    protected function getMtdBudget(string $metricName, int $mtdDays): ?float
    {
        /*
         * MTD budget rule:
         * - For normal metrics: Daily Budget × MTD Days
         * - For Fuel Consumption metrics: use the Daily Budget as-is
         *   because consumption is a ratio/target, not an accumulated value.
         */

        $dailyBudget = $this->getBudget($metricName, 'Daily');

        if ($dailyBudget === null) {
            return null;
        }

        if ($this->isFuelConsumptionMetric($metricName)) {
            return $dailyBudget;
        }

        return $dailyBudget * $mtdDays;
    }

    protected function isFuelConsumptionMetric(string $metricName): bool
    {
        return str_contains(strtolower($metricName), 'fuel consumption');
    }

   

    protected function mtdBudgetDays(Carbon $asAt): int
    {
        return max(
            1,
            $asAt->copy()
                ->subDay()
                ->day
        );
    }

    protected function bootLoadingPoints(): void
    {
        $q = LoadingPoint::query();

        $q->where('status', true);

        if (!empty($this->loadingPointFilterNames)) {
            $q->whereIn('name', $this->loadingPointFilterNames);
        }

        $this->lpMap = $q
            ->orderBy('name')
            ->pluck('id', 'name')
            ->toArray();

        $this->lpNames = array_keys($this->lpMap);

        if (count($this->lpNames) === 0) {
            $this->lpNames = ['(No Loading Points)'];
            $this->lpMap = [];
        }
    }

    protected function bootColumns(): void
    {
        $lpCount = count($this->lpNames);
        $blockCols = $lpCount + 4;

        $this->yStartCol = 'B';
        $yEndCol = $this->colAdd($this->yStartCol, $blockCols - 1);

        $this->sepCol = $this->colAdd($yEndCol, 1);

        $this->mStartCol = $this->colAdd($this->sepCol, 1);
        $mEndCol = $this->colAdd($this->mStartCol, $blockCols - 1);

        $this->lastCol = $mEndCol;
    }

    public function array(): array
    {
        $asAt = $this->asAt->copy();

        $reportTo = $asAt->copy()->startOfDay()->addHour();

        $yFrom = $asAt->copy()->subDay()->startOfDay()->addHours(2);
        $yTo = $reportTo;

        
        $mFrom = $asAt->copy()->subDay()->startOfMonth()->startOfDay()->addHours(2);
        $mTo = $reportTo;

        $mtdBudgetDays = $this->mtdBudgetDays($asAt);

        $y = $this->computePeriod($yFrom, $yTo, $this->lpMap);
        $m = $this->computePeriod($mFrom, $mTo, $this->lpMap);

        $yShiftStats = $this->shiftBreakdown($yFrom, $yTo);
        $mShiftStats = $this->shiftBreakdown($mFrom, $mTo);

        $header2 = array_merge(
            [''],
            $this->lpNames,
            ['Loads', 'Actual', 'Budget', 'Var'],
            [''],
            $this->lpNames,
            ['Loads', 'Actual', 'Budget', 'Var']
        );

        $lpVals = function (array $lpCounts): array {
            $out = [];

            foreach ($this->lpNames as $name) {
                $out[] = (int) ($lpCounts[$name] ?? 0);
            }

            return $out;
        };

        $rowOreLong = $this->buildLpMetricRow(
            'Total Ore Hauled - Long-haul',
            $lpVals($y['ore_long']['lp'] ?? []),
            $y['ore_long']['loads'] ?? 0,
            $y['ore_long']['actual'] ?? 0,
            'Daily',
            $lpVals($m['ore_long']['lp'] ?? []),
            $m['ore_long']['loads'] ?? 0,
            $m['ore_long']['actual'] ?? 0,
            'Monthly',
            $mtdBudgetDays
        );

        $rowFuelLong = $this->buildPlainMetricRow(
            'Total Fuel Used – Long-haul',
            $y['fuel_long'] ?? 0,
            'Daily',
            $m['fuel_long'] ?? 0,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowKmLong = $this->buildPlainMetricRow(
            'Total Kilometres - Long-haul',
            $y['km_long'] ?? 0,
            'Daily',
            $m['km_long'] ?? 0,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowConsLong = $this->buildPlainMetricRow(
            'Fuel Consumption - Long Haul',
            $y['cons_long'] ?? null,
            'Daily',
            $m['cons_long'] ?? null,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowOreShort = $this->buildLpMetricRow(
            'Total Ore Hauled – Short-haul',
            $lpVals($y['ore_short']['lp'] ?? []),
            $y['ore_short']['loads'] ?? 0,
            $y['ore_short']['actual'] ?? 0,
            'Daily',
            $lpVals($m['ore_short']['lp'] ?? []),
            $m['ore_short']['loads'] ?? 0,
            $m['ore_short']['actual'] ?? 0,
            'Monthly',
            $mtdBudgetDays
        );

        $rowFuelShort = $this->buildPlainMetricRow(
            'Total Fuel Used – Short-haul',
            $y['fuel_short'] ?? 0,
            'Daily',
            $m['fuel_short'] ?? 0,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowKmShort = $this->buildPlainMetricRow(
            'Total Kilometres - Short-haul',
            $y['km_short'] ?? 0,
            'Daily',
            $m['km_short'] ?? 0,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowConsShort = $this->buildPlainMetricRow(
            'Fuel Consumption - Short Haul',
            $y['cons_short'] ?? null,
            'Daily',
            $m['cons_short'] ?? null,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowConc = $this->buildPlainMetricRow(
            'Total Concentrates Hauled',
            $y['conc']['actual'] ?? 0,
            'Daily',
            $m['conc']['actual'] ?? 0,
            'Monthly',
            $y['conc']['loads'] ?? 0,
            $m['conc']['loads'] ?? 0,
            $mtdBudgetDays
        );

        $rowFuelConc = $this->buildPlainMetricRow(
            'Total Fuel Used – Concentrates',
            $y['fuel_conc'] ?? 0,
            'Daily',
            $m['fuel_conc'] ?? 0,
            'Monthly',
            '',
            '',
            $mtdBudgetDays
        );

        $rowSection = array_merge(
            ['SHIFT CAPTURE CONTROL'],
            array_fill(0, count($this->lpNames), ''),
            ['', '', '', ''],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', '', '', '']
        );

        $rowMorningShifts = $this->buildCaptureRow(
            'Driver Attendance At Morning Shift',
            $yShiftStats['morning_shifts'],
            $mShiftStats['morning_shifts']
        );

        $rowNightShifts = $this->buildCaptureRow(
            'Driver Attendance At Night Shift',
            $yShiftStats['night_shifts'],
            $mShiftStats['night_shifts']
        );

        $rowBackShifts = $this->buildCaptureRow(
            'Driver Attendance At Backshift Shift',
            $yShiftStats['backshift_shifts'],
            $mShiftStats['backshift_shifts']
        );

        $rowMorningLoads = $this->buildCaptureRow(
            'Total Loads Captured At Morning Shift',
            $yShiftStats['morning_loads'],
            $mShiftStats['morning_loads']
        );

        $rowNightLoads = $this->buildCaptureRow(
            'Total Loads Captured At Night Shift',
            $yShiftStats['night_loads'],
            $mShiftStats['night_loads']
        );

        $rowBackLoads = $this->buildCaptureRow(
            'Total Loads Captured At Backshift Shift',
            $yShiftStats['backshift_loads'],
            $mShiftStats['backshift_loads']
        );

        $colCount = $this->colIndex($this->lastCol);

        return [
            array_pad([$this->periodTitle], $colCount, ''),
            $header2,
            array_fill(0, $colCount, ''),

            $rowOreLong,
            $rowFuelLong,
            $rowKmLong,
            $rowConsLong,

            $rowOreShort,
            $rowFuelShort,
            $rowKmShort,
            $rowConsShort,

            $rowConc,
            $rowFuelConc,

            $rowSection,
            $rowMorningShifts,
            $rowNightShifts,
            $rowBackShifts,
            $rowMorningLoads,
            $rowNightLoads,
            $rowBackLoads,
        ];
    }

    protected function buildLpMetricRow(
        string $metricName,
        array $yLpValues,
        $yLoads,
        $yActual,
        string $yBudgetPeriod,
        array $mLpValues,
        $mLoads,
        $mActual,
        string $mBudgetPeriod,
        int $mtdBudgetDays = 1
    ): array {
        $yBudget = $this->getBudget($metricName, $yBudgetPeriod);

        $mBudget = $mBudgetPeriod === 'Monthly'
            ? $this->getMtdBudget($metricName, $mtdBudgetDays)
            : $this->getBudget($metricName, $mBudgetPeriod);

        return array_merge(
            [$metricName],
            $yLpValues,
            [
                $yLoads,
                $yActual,
                $yBudget,
                $this->variance($yActual, $yBudget),
            ],
            [''],
            $mLpValues,
            [
                $mLoads,
                $mActual,
                $mBudget,
                $this->variance($mActual, $mBudget),
            ]
        );
    }

    protected function buildPlainMetricRow(
        string $metricName,
        $yActual,
        string $yBudgetPeriod,
        $mActual,
        string $mBudgetPeriod,
        $yLoads = '',
        $mLoads = '',
        int $mtdBudgetDays = 1
    ): array {
        $yBudget = $this->getBudget($metricName, $yBudgetPeriod);

        $mBudget = $mBudgetPeriod === 'Monthly'
            ? $this->getMtdBudget($metricName, $mtdBudgetDays)
            : $this->getBudget($metricName, $mBudgetPeriod);

        return array_merge(
            [$metricName],
            array_fill(0, count($this->lpNames), ''),
            [
                $yLoads,
                $yActual,
                $yBudget,
                $this->variance($yActual, $yBudget),
            ],
            [''],
            array_fill(0, count($this->lpNames), ''),
            [
                $mLoads,
                $mActual,
                $mBudget,
                $this->variance($mActual, $mBudget),
            ]
        );
    }

    protected function buildCaptureRow(string $label, $yActual, $mActual): array
    {
        return array_merge(
            [$label],
            array_fill(0, count($this->lpNames), ''),
            [
                '',
                $yActual,
                '',
                '',
            ],
            [''],
            array_fill(0, count($this->lpNames), ''),
            [
                '',
                $mActual,
                '',
                '',
            ]
        );
    }

    protected function computePeriod(Carbon $from, Carbon $to, array $lpMap): array
    {
        $shiftBase = Shift::query()
            ->whereBetween($this->shiftDateColumn, [$from, $to]);

        $tripBase = Trip::query()
            ->whereHas('shift', fn (Builder $q) => $q->whereBetween($this->shiftDateColumn, [$from, $to]));

        $oreLongTrips = (clone $tripBase)
            ->where('haulage_type', 'long_haul')
            ->whereHas('cargo', fn (Builder $q) => $q->where('name', 'Platinum Ore'));

        $oreLongLoads = (clone $oreLongTrips)->count();
        $oreLongActual = (clone $oreLongTrips)->sum($this->tripWeightColumn);
        $oreLongLp = $this->tripCountsByLoadingPoint(clone $oreLongTrips, $lpMap);

        $fuelLong = Fuel::query()
            ->whereHas('shift', fn (Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                    ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'long_haul'))
            )
            ->sum('quantity');

        $kmLong = (clone $shiftBase)
            ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'long_haul'))
            ->selectRaw("COALESCE(SUM(COALESCE({$this->shiftCloseMileageColumn},0) - COALESCE({$this->shiftOpenMileageColumn},0)),0) as km")
            ->value('km');

        $consLong = (clone $shiftBase)
            ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'long_haul'))
            ->avg($this->shiftFuelConsumptionColumn);

        $oreShortTrips = (clone $tripBase)
            ->where('haulage_type', 'short_haul')
            ->whereHas('cargo', fn (Builder $q) => $q->where('name', 'Platinum Ore'));

        $oreShortLoads = (clone $oreShortTrips)->count();
        $oreShortActual = (clone $oreShortTrips)->sum($this->tripWeightColumn);
        $oreShortLp = $this->tripCountsByLoadingPoint(clone $oreShortTrips, $lpMap);

        $fuelShort = Fuel::query()
            ->whereHas('shift', fn (Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                    ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'short_haul'))
            )
            ->sum('quantity');

        $kmShort = (clone $shiftBase)
            ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'short_haul'))
            ->selectRaw("COALESCE(SUM(COALESCE({$this->shiftCloseMileageColumn},0) - COALESCE({$this->shiftOpenMileageColumn},0)),0) as km")
            ->value('km');

        $consShort = (clone $shiftBase)
            ->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', 'short_haul'))
            ->avg($this->shiftFuelConsumptionColumn);

        $concTrips = (clone $tripBase)
            ->whereHas('cargo', fn (Builder $q) => $q->where('name', 'Platinum Concentrate'));

        $concLoads = (clone $concTrips)->count();
        $concActual = (clone $concTrips)->sum($this->tripWeightColumn);

        $fuelConc = Fuel::query()
            ->whereHas('shift', fn (Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                    ->whereHas('trips', fn (Builder $t) =>
                        $t->whereHas('cargo', fn (Builder $c) => $c->where('name', 'Platinum Concentrate'))
                    )
            )
            ->sum('quantity');

        return [
            'ore_long' => [
                'loads' => (int) $oreLongLoads,
                'actual' => (float) $oreLongActual,
                'lp' => $oreLongLp,
            ],
            'fuel_long' => (float) $fuelLong,
            'km_long' => (float) $kmLong,
            'cons_long' => $consLong !== null ? round((float) $consLong, 2) : null,

            'ore_short' => [
                'loads' => (int) $oreShortLoads,
                'actual' => (float) $oreShortActual,
                'lp' => $oreShortLp,
            ],
            'fuel_short' => (float) $fuelShort,
            'km_short' => (float) $kmShort,
            'cons_short' => $consShort !== null ? round((float) $consShort, 2) : null,

            'conc' => [
                'loads' => (int) $concLoads,
                'actual' => (float) $concActual,
            ],
            'fuel_conc' => (float) $fuelConc,
        ];
    }

    protected function shiftBreakdown(Carbon $from, Carbon $to): array
    {
        $shifts = Shift::query()
            ->whereBetween($this->shiftDateColumn, [$from, $to]);

        return [
            'morning_shifts' => (clone $shifts)
                ->where($this->shiftTypeColumn, 'Morning')
                ->count(),

            'night_shifts' => (clone $shifts)
                ->where($this->shiftTypeColumn, 'Night')
                ->count(),

            'backshift_shifts' => (clone $shifts)
                ->where($this->shiftTypeColumn, 'Backshift')
                ->count(),

            'morning_loads' => Trip::query()
                ->whereHas('shift', function ($q) use ($from, $to) {
                    $q->whereBetween($this->shiftDateColumn, [$from, $to])
                        ->where($this->shiftTypeColumn, 'Morning');
                })
                ->count(),

            'night_loads' => Trip::query()
                ->whereHas('shift', function ($q) use ($from, $to) {
                    $q->whereBetween($this->shiftDateColumn, [$from, $to])
                        ->where($this->shiftTypeColumn, 'Night');
                })
                ->count(),

            'backshift_loads' => Trip::query()
                ->whereHas('shift', function ($q) use ($from, $to) {
                    $q->whereBetween($this->shiftDateColumn, [$from, $to])
                        ->where($this->shiftTypeColumn, 'Backshift');
                })
                ->count(),
        ];
    }

    protected function tripCountsByLoadingPoint(Builder $tripQuery, array $lpMap): array
    {
        $ids = array_values($lpMap);

        $counts = (clone $tripQuery)
            ->when(!empty($ids), fn ($q) => $q->whereIn('loading_point_id', $ids))
            ->selectRaw('loading_point_id, COUNT(*) as c')
            ->groupBy('loading_point_id')
            ->pluck('c', 'loading_point_id')
            ->toArray();

        $out = [];

        foreach ($lpMap as $name => $id) {
            $out[$name] = (int) ($counts[$id] ?? 0);
        }

        return $out;
    }

    protected function variance($actual, $budget): ?float
    {
        if ($actual === null || $budget === null || $budget === '') {
            return null;
        }

        return (float) $actual - (float) $budget;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 34];

        $lpCount = count($this->lpNames);
        $blockCols = $lpCount + 4;

        $col = 'B';

        for ($i = 0; $i < $blockCols; $i++) {
            $isNumericTail = $i >= $lpCount;
            $widths[$col] = $isNumericTail ? 12 : 6;
            $col = $this->colAdd($col, 1);
        }

        $widths[$this->sepCol] = 2;

        $col = $this->mStartCol;

        for ($i = 0; $i < $blockCols; $i++) {
            $isNumericTail = $i >= $lpCount;
            $widths[$col] = $isNumericTail ? 12 : 6;
            $col = $this->colAdd($col, 1);
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $startRow = 7;
                $headerRow1 = $startRow;
                $headerRow2 = $startRow + 1;
                $spacerRow = $startRow + 2;
                $dataStart = $startRow + 3;

                $lastRow = $sheet->getHighestRow();
                $rangeAll = "A{$headerRow1}:{$this->lastCol}{$lastRow}";

                $headerFill = 'D9D9D9';
                $greyFill = 'BFBFBF';
                $sepFill = '7B61A6';
                $sectionFill = '305496';

                $lpCount = count($this->lpNames);
                $blockCols = $lpCount + 4;

                $yEndCol = $this->colAdd($this->yStartCol, $blockCols - 1);

                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->mergeCells("{$this->yStartCol}{$headerRow1}:{$yEndCol}{$headerRow1}");
                $sheet->mergeCells("{$this->mStartCol}{$headerRow1}:{$this->lastCol}{$headerRow1}");
                $sheet->mergeCells("{$this->sepCol}{$headerRow1}:{$this->sepCol}{$lastRow}");

                $sheet->setCellValue("{$this->yStartCol}{$headerRow1}", 'YESTERDAY PRODUCTION');
                $sheet->setCellValue("{$this->mStartCol}{$headerRow1}", 'MTD PRODUCTION');

                $sheet->getStyle("A{$headerRow1}:{$this->lastCol}{$headerRow2}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => $headerFill],
                    ],
                ]);

                $sheet->getStyle("A{$headerRow1}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("{$this->sepCol}{$headerRow1}:{$this->sepCol}{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => $sepFill],
                    ],
                ]);

                $sheet->getRowDimension($headerRow1)->setRowHeight(22);
                $sheet->getRowDimension($headerRow2)->setRowHeight(18);
                $sheet->getRowDimension($spacerRow)->setRowHeight(6);

                $sheet->freezePane("{$this->yStartCol}{$dataStart}");

                $sheet->getStyle($rangeAll)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $yLoadsCol = $this->colAdd($this->yStartCol, $lpCount);
                $yActualCol = $this->colAdd($yLoadsCol, 1);
                $yBudgetCol = $this->colAdd($yLoadsCol, 2);
                $yVarCol = $this->colAdd($yLoadsCol, 3);

                $mLoadsCol = $this->colAdd($this->mStartCol, $lpCount);
                $mActualCol = $this->colAdd($mLoadsCol, 1);
                $mBudgetCol = $this->colAdd($mLoadsCol, 2);
                $mVarCol = $this->colAdd($mLoadsCol, 3);

                $sheet->getStyle("{$this->yStartCol}{$dataStart}:{$yLoadsCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle("{$this->mStartCol}{$dataStart}:{$mLoadsCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle("{$yActualCol}{$dataStart}:{$yVarCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("{$mActualCol}{$dataStart}:{$mVarCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("A{$dataStart}:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("{$this->yStartCol}{$dataStart}:{$yVarCol}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("{$this->mStartCol}{$dataStart}:{$mVarCol}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("{$yBudgetCol}{$dataStart}:{$yBudgetCol}{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("{$mBudgetCol}{$dataStart}:{$mBudgetCol}{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $metricName = trim((string) $sheet->getCell("A{$r}")->getValue());

                    if ($metricName === 'SHIFT CAPTURE CONTROL') {
                        $sheet->getStyle("A{$r}:{$this->lastCol}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => $sectionFill],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);
                    }

                    $yFirst = trim((string) $sheet->getCell("{$this->yStartCol}{$r}")->getValue());
                    $mFirst = trim((string) $sheet->getCell("{$this->mStartCol}{$r}")->getValue());

                    if ($yFirst === '') {
                        $sheet->getStyle("{$this->yStartCol}{$r}:{$yLoadsCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => $greyFill],
                            ],
                        ]);
                    }

                    if ($mFirst === '') {
                        $sheet->getStyle("{$this->mStartCol}{$r}:{$mLoadsCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => $greyFill],
                            ],
                        ]);
                    }
                }

                $sheet->getStyle("{$this->yStartCol}{$headerRow2}:{$yVarCol}{$headerRow2}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("{$this->mStartCol}{$headerRow2}:{$mVarCol}{$headerRow2}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    protected function colIndex(string $col): int
    {
        $col = strtoupper($col);
        $n = 0;

        for ($i = 0; $i < strlen($col); $i++) {
            $n = $n * 26 + (ord($col[$i]) - 64);
        }

        return $n;
    }

    protected function colFromIndex(int $index): string
    {
        $s = '';

        while ($index > 0) {
            $m = ($index - 1) % 26;
            $s = chr(65 + $m) . $s;
            $index = intdiv($index - 1, 26);
        }

        return $s;
    }

    protected function colAdd(string $col, int $offset): string
    {
        return $this->colFromIndex($this->colIndex($col) + $offset);
    }
}