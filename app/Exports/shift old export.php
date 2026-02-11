<?php


namespace App\Exports;

use Carbon\Carbon;
use App\Models\Shift;
use App\Models\Trip;
use App\Models\Fuel;
use App\Models\LoadingPoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ShiftsDailyExport implements FromArray, WithEvents, WithColumnWidths, WithTitle, WithDrawings, WithCustomStartCell
{
    // ✅ Adjust if your schema uses different names
    protected string $shiftDateColumn            = 'shift_start_time';
    protected string $tripWeightColumn           = 'weight';
    protected string $shiftOpenMileageColumn     = 'open_mileage';
    protected string $shiftCloseMileageColumn    = 'close_mileage';
    protected string $shiftFuelConsumptionColumn = 'fuel_consumption_mileage';

    /**
     * Dynamic Loading Points (names) and map (name => id)
     */
    protected array $lpNames = [];
    protected array $lpMap   = [];

    /**
     * Column bookkeeping (so styling is dynamic)
     */
    protected string $yStartCol = 'B';
    protected string $sepCol    = 'J';
    protected string $mStartCol = 'K';
    protected string $lastCol   = 'R';

    public function __construct(
        protected string $periodTitle = 'Key Operating Metrics - ',
        protected ?Carbon $asAt = null,
        protected array $budgets = [],
        protected array $loadingPointFilterNames = [] // optional: pass specific LP names
    ) {
        $this->periodTitle = $this->periodTitle . date('M - d');
        $this->asAt = $this->asAt ?: now();

        $this->bootLoadingPoints();
        $this->bootColumns();
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

        $company = Auth::user()?->employee?->company;

        $drawing->setName($company?->name ?? 'Company');
        $drawing->setDescription(($company?->name ?? 'Company') . ' Logo');

        $logoPath = public_path('/images/uploads/logo.png');

        if ($company && !empty($company->logo)) {
            $candidate = public_path('/images/uploads/' . $company->logo);
            if (file_exists($candidate)) {
                $logoPath = $candidate;
            }
        }

        $drawing->setPath($logoPath);
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    /**
     * Load LPs dynamically.
     * Tweak the query to match your schema (company scoping, active flag, sort order etc.)
     */
    protected function bootLoadingPoints(): void
    {
        $q = LoadingPoint::query();

        // Example scoping (uncomment if you have these columns)
        // $companyId = Auth::user()?->employee?->company_id;
        // if ($companyId) $q->where('company_id', $companyId);

        // if you have "active" flag
        $q->where('status', true);

        // Optional: keep the old behavior by allowing caller to pass a list of names
        if (!empty($this->loadingPointFilterNames)) {
            $q->whereIn('name', $this->loadingPointFilterNames);
        }

        $this->lpMap = $q
            ->orderBy('name')
            ->pluck('id', 'name')
            ->toArray();

        $this->lpNames = array_keys($this->lpMap);

        // Safety: if DB returns none, avoid breaking the sheet
        if (count($this->lpNames) === 0) {
            $this->lpNames = ['(No Loading Points)'];
            $this->lpMap   = [];
        }
    }

    /**
     * Determine the dynamic columns based on number of LPs.
     *
     * Layout:
     * A = metric label
     * B.. ? = Yesterday block: (LPs...) + Loads + Actual + Budget + Var
     * sep col (purple)
     * then MTD block same shape
     */
    protected function bootColumns(): void
    {
        $lpCount = count($this->lpNames);
        $blockCols = $lpCount + 4; // + Loads, Actual, Budget, Var

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

        // reporting cutoff: today @ 01:00
        $reportTo = $asAt->copy()->startOfDay()->addHour(); // 01:00 today

        // Yesterday Production window: day before @ 02:00  -> today @ 01:00
        $yFrom = $asAt->copy()->subDay()->startOfDay()->addHours(2); // 02:00 yesterday
        $yTo   = $reportTo;                                          // 01:00 today

        // MTD window: start of month @ 02:00 -> today @ 01:00
        $mFrom = $asAt->copy()->startOfMonth()->startOfDay()->addHours(2);
        $mTo   = $reportTo;

        $y = $this->computePeriod($yFrom, $yTo, $this->lpMap);
        $m = $this->computePeriod($mFrom, $mTo, $this->lpMap);

        $b = fn(string $bucket, string $key) => $this->budgets[$bucket][$key] ?? null;

        // Build header row 2 dynamically:
        // ['', LP1, LP2, ... , Loads, Actual, Budget, Var, '', LP1.., Loads, Actual, Budget, Var]
        $header2 = array_merge(
            [''],
            $this->lpNames,
            ['Loads', 'Actual', 'Budget', 'Var'],
            [''],
            $this->lpNames,
            ['Loads', 'Actual', 'Budget', 'Var']
        );

        // Prebuild helper to output LP counts in correct order
        $lpVals = function(array $lpCounts): array {
            $out = [];
            foreach ($this->lpNames as $name) {
                $out[] = (int)($lpCounts[$name] ?? 0);
            }
            return $out;
        };

        // Build each metric row using dynamic LP columns
        $rowOreLong = array_merge(
            ['Total Ore Hauled - Long-haul'],
            $lpVals($y['ore_long']['lp'] ?? []),
            [
                $y['ore_long']['loads'] ?? 0,
                $y['ore_long']['actual'] ?? 0,
                $b('yesterday', 'ore_long'),
                $this->variance($y['ore_long']['actual'] ?? null, $b('yesterday','ore_long')),
            ],
            [''],
            $lpVals($m['ore_long']['lp'] ?? []),
            [
                $m['ore_long']['loads'] ?? 0,
                $m['ore_long']['actual'] ?? 0,
                $b('mtd', 'ore_long'),
                $this->variance($m['ore_long']['actual'] ?? null, $b('mtd','ore_long')),
            ]
        );

        $rowFuelLong = array_merge(
            ['Total Fuel Used – Long-haul'],
            array_fill(0, count($this->lpNames), ''), // no LP breakdown
            ['', $y['fuel_long'] ?? 0, $b('yesterday', 'fuel_long'), $this->variance($y['fuel_long'] ?? null, $b('yesterday','fuel_long'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['fuel_long'] ?? 0, $b('mtd', 'fuel_long'), $this->variance($m['fuel_long'] ?? null, $b('mtd','fuel_long'))]
        );

        $rowKmLong = array_merge(
            ['Total Kilometres - Long-haul'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['km_long'] ?? 0, $b('yesterday', 'km_long'), $this->variance($y['km_long'] ?? null, $b('yesterday','km_long'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['km_long'] ?? 0, $b('mtd', 'km_long'), $this->variance($m['km_long'] ?? null, $b('mtd','km_long'))]
        );

        $rowConsLong = array_merge(
            ['Fuel Consumption - Long Haul'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['cons_long'] ?? null, $b('yesterday', 'cons_long'), $this->variance($y['cons_long'] ?? null, $b('yesterday','cons_long'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['cons_long'] ?? null, $b('mtd', 'cons_long'), $this->variance($m['cons_long'] ?? null, $b('mtd','cons_long'))]
        );

        $rowOreShort = array_merge(
            ['Total Ore Hauled – Short-haul'],
            $lpVals($y['ore_short']['lp'] ?? []),
            [
                $y['ore_short']['loads'] ?? 0,
                $y['ore_short']['actual'] ?? 0,
                $b('yesterday', 'ore_short'),
                $this->variance($y['ore_short']['actual'] ?? null, $b('yesterday','ore_short')),
            ],
            [''],
            $lpVals($m['ore_short']['lp'] ?? []),
            [
                $m['ore_short']['loads'] ?? 0,
                $m['ore_short']['actual'] ?? 0,
                $b('mtd', 'ore_short'),
                $this->variance($m['ore_short']['actual'] ?? null, $b('mtd','ore_short')),
            ]
        );

        $rowFuelShort = array_merge(
            ['Total Fuel Used – Short-haul'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['fuel_short'] ?? 0, $b('yesterday', 'fuel_short'), $this->variance($y['fuel_short'] ?? null, $b('yesterday','fuel_short'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['fuel_short'] ?? 0, $b('mtd', 'fuel_short'), $this->variance($m['fuel_short'] ?? null, $b('mtd','fuel_short'))]
        );

        $rowKmShort = array_merge(
            ['Total Kilometres - Short-haul'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['km_short'] ?? 0, $b('yesterday', 'km_short'), $this->variance($y['km_short'] ?? null, $b('yesterday','km_short'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['km_short'] ?? 0, $b('mtd', 'km_short'), $this->variance($m['km_short'] ?? null, $b('mtd','km_short'))]
        );

        $rowConsShort = array_merge(
            ['Fuel Consumption - Short Haul'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['cons_short'] ?? null, $b('yesterday', 'cons_short'), $this->variance($y['cons_short'] ?? null, $b('yesterday','cons_short'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['cons_short'] ?? null, $b('mtd', 'cons_short'), $this->variance($m['cons_short'] ?? null, $b('mtd','cons_short'))]
        );

        $rowConc = array_merge(
            ['Total Concentrates Hauled'],
            array_fill(0, count($this->lpNames), ''),
            [
                $y['conc']['loads'] ?? 0,
                $y['conc']['actual'] ?? 0,
                $b('yesterday', 'conc'),
                $this->variance($y['conc']['actual'] ?? null, $b('yesterday','conc')),
            ],
            [''],
            array_fill(0, count($this->lpNames), ''),
            [
                $m['conc']['loads'] ?? 0,
                $m['conc']['actual'] ?? 0,
                $b('mtd', 'conc'),
                $this->variance($m['conc']['actual'] ?? null, $b('mtd','conc')),
            ]
        );

        $rowFuelConc = array_merge(
            ['Total Fuel Used – Concentrates'],
            array_fill(0, count($this->lpNames), ''),
            ['', $y['fuel_conc'] ?? 0, $b('yesterday', 'fuel_conc'), $this->variance($y['fuel_conc'] ?? null, $b('yesterday','fuel_conc'))],
            [''],
            array_fill(0, count($this->lpNames), ''),
            ['', $m['fuel_conc'] ?? 0, $b('mtd', 'fuel_conc'), $this->variance($m['fuel_conc'] ?? null, $b('mtd','fuel_conc'))]
        );

        $colCount = $this->colIndex($this->lastCol); // 1-based count
        return [
            // Header row 1 (merged later)
            array_pad([$this->periodTitle], $colCount, ''),
            // Header row 2 (dynamic)
            $header2,
            // Spacer
            array_fill(0, $colCount, ''),
            // Data rows
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
        ];
    }

    protected function computePeriod(Carbon $from, Carbon $to, array $lpMap): array
    {
        $shiftBase = Shift::query()->whereBetween($this->shiftDateColumn, [$from, $to]);

        $tripBase = Trip::query()
            ->whereHas('shift', fn(Builder $q) => $q->whereBetween($this->shiftDateColumn, [$from, $to]));

        $oreLongTrips = (clone $tripBase)
            ->where('haulage_type', 'long_haul')
            ->whereHas('cargo', fn(Builder $q) => $q->where('name', 'Platinum Ore'));

        $oreLongLoads  = (clone $oreLongTrips)->count();
        $oreLongActual = (clone $oreLongTrips)->sum($this->tripWeightColumn);
        $oreLongLp     = $this->tripCountsByLoadingPoint(clone $oreLongTrips, $lpMap);

        $fuelLong = Fuel::query()
            ->whereHas('shift', fn(Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                  ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'long_haul'))
            )->sum('quantity');

        $kmLong = (clone $shiftBase)
            ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'long_haul'))
            ->selectRaw("COALESCE(SUM(COALESCE({$this->shiftCloseMileageColumn},0) - COALESCE({$this->shiftOpenMileageColumn},0)),0) as km")
            ->value('km');

        $consLong = (clone $shiftBase)
            ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'long_haul'))
            ->avg($this->shiftFuelConsumptionColumn);

        $oreShortTrips = (clone $tripBase)
            ->where('haulage_type', 'short_haul')
            ->whereHas('cargo', fn(Builder $q) => $q->where('name', 'Platinum Ore'));

        $oreShortLoads  = (clone $oreShortTrips)->count();
        $oreShortActual = (clone $oreShortTrips)->sum($this->tripWeightColumn);
        $oreShortLp     = $this->tripCountsByLoadingPoint(clone $oreShortTrips, $lpMap);

        $fuelShort = Fuel::query()
            ->whereHas('shift', fn(Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                  ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'short_haul'))
            )->sum('quantity');

        $kmShort = (clone $shiftBase)
            ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'short_haul'))
            ->selectRaw("COALESCE(SUM(COALESCE({$this->shiftCloseMileageColumn},0) - COALESCE({$this->shiftOpenMileageColumn},0)),0) as km")
            ->value('km');

        $consShort = (clone $shiftBase)
            ->whereHas('trips', fn(Builder $t) => $t->where('haulage_type', 'short_haul'))
            ->avg($this->shiftFuelConsumptionColumn);

       
        $concTrips = (clone $tripBase)->whereHas('cargo', fn(Builder $q) => $q->where('name', 'Platinum Concentrate'));
        $concLoads  = (clone $concTrips)->count();
        $concActual = (clone $concTrips)->sum($this->tripWeightColumn);

        $fuelConc = Fuel::query()
            ->whereHas('shift', fn(Builder $q) =>
                $q->whereBetween($this->shiftDateColumn, [$from, $to])
                  ->whereHas('trips', fn(Builder $t) =>
                      $t->whereHas('cargo', fn(Builder $c) => $c->where('name', 'Platinum Concentrate'))
                  )
            )->sum('quantity');

        return [
            'ore_long' => ['loads' => (int)$oreLongLoads, 'actual' => (float)$oreLongActual, 'lp' => $oreLongLp],
            'fuel_long' => (float)$fuelLong,
            'km_long'   => (float)$kmLong,
            'cons_long' => $consLong !== null ? round((float)$consLong, 2) : null,

            'ore_short' => ['loads' => (int)$oreShortLoads, 'actual' => (float)$oreShortActual, 'lp' => $oreShortLp],
            'fuel_short' => (float)$fuelShort,
            'km_short'   => (float)$kmShort,
            'cons_short' => $consShort !== null ? round((float)$consShort, 2) : null,

            'conc' => ['loads' => (int)$concLoads, 'actual' => (float)$concActual],
            'fuel_conc' => (float)$fuelConc,
        ];
    }

    protected function tripCountsByLoadingPoint(Builder $tripQuery, array $lpMap): array
    {
        $ids = array_values($lpMap);

        $counts = (clone $tripQuery)
            ->when(!empty($ids), fn($q) => $q->whereIn('loading_point_id', $ids))
            ->selectRaw('loading_point_id, COUNT(*) as c')
            ->groupBy('loading_point_id')
            ->pluck('c', 'loading_point_id')
            ->toArray();

        $out = [];
        foreach ($lpMap as $name => $id) {
            $out[$name] = (int)($counts[$id] ?? 0);
        }
        return $out;
    }

    protected function variance($actual, $budget): ?float
    {
        if ($actual === null || $budget === null || $budget === '') return null;
        return (float)$actual - (float)$budget;
    }

    public function columnWidths(): array
    {
        // A: metric label
        $widths = ['A' => 34];

        $lpCount = count($this->lpNames);
        $blockCols = $lpCount + 4;

        // Yesterday block
        $col = 'B';
        for ($i = 0; $i < $blockCols; $i++) {
            // LP columns narrow; numeric columns wider
            $isNumericTail = $i >= $lpCount; // Loads/Actual/Budget/Var
            $widths[$col] = $isNumericTail ? 12 : 6;
            $col = $this->colAdd($col, 1);
        }

        // Separator
        $widths[$this->sepCol] = 2;

        // MTD block
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

                $startRow   = 7;
                $headerRow1 = $startRow;
                $headerRow2 = $startRow + 1;
                $spacerRow  = $startRow + 2;
                $dataStart  = $startRow + 3;

                $lastRow    = $sheet->getHighestRow();
                $rangeAll   = "A{$headerRow1}:{$this->lastCol}{$lastRow}";

                $headerFill = 'D9D9D9';
                $greyFill   = 'BFBFBF';
                $sepFill    = '7B61A6';

                $yEndCol = $this->colAdd($this->yStartCol, (count($this->lpNames) + 4) - 1);

                // Merge headers
                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->mergeCells("{$this->yStartCol}{$headerRow1}:{$yEndCol}{$headerRow1}");
                $sheet->mergeCells("{$this->mStartCol}{$headerRow1}:{$this->lastCol}{$headerRow1}");

                // Separator column
                $sheet->mergeCells("{$this->sepCol}{$headerRow1}:{$this->sepCol}{$lastRow}");

                $sheet->setCellValue("{$this->yStartCol}{$headerRow1}", 'YESTERDAY PRODUCTION');
                $sheet->setCellValue("{$this->mStartCol}{$headerRow1}", 'MTD PRODUCTION');

                // Header styling
                $sheet->getStyle("A{$headerRow1}:{$this->lastCol}{$headerRow2}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['rgb' => $headerFill],
                    ],
                ]);

                // Title left aligned
                $sheet->getStyle("A{$headerRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Purple separator
                $sheet->getStyle("{$this->sepCol}{$headerRow1}:{$this->sepCol}{$lastRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $sepFill]],
                ]);

                // Row heights + freeze
                $sheet->getRowDimension($headerRow1)->setRowHeight(22);
                $sheet->getRowDimension($headerRow2)->setRowHeight(18);
                $sheet->getRowDimension($spacerRow)->setRowHeight(6);

                $sheet->freezePane("{$this->yStartCol}{$dataStart}");

                // Borders
                $sheet->getStyle($rangeAll)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Number formats:
                // LP counts + Loads are integers; Actual/Budget/Var are decimals (except consumption maybe)
                $lpCount = count($this->lpNames);
                $blockCols = $lpCount + 4;

                $yLoadsCol   = $this->colAdd($this->yStartCol, $lpCount);     // after LPs
                $yActualCol  = $this->colAdd($yLoadsCol, 1);
                $yBudgetCol  = $this->colAdd($yLoadsCol, 2);
                $yVarCol     = $this->colAdd($yLoadsCol, 3);

                $mLoadsCol   = $this->colAdd($this->mStartCol, $lpCount);
                $mActualCol  = $this->colAdd($mLoadsCol, 1);
                $mBudgetCol  = $this->colAdd($mLoadsCol, 2);
                $mVarCol     = $this->colAdd($mLoadsCol, 3);

                // Yesterday integers (LPs + Loads)
                $sheet->getStyle("{$this->yStartCol}{$dataStart}:{$yLoadsCol}{$lastRow}")
                      ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

                // MTD integers (LPs + Loads)
                $sheet->getStyle("{$this->mStartCol}{$dataStart}:{$mLoadsCol}{$lastRow}")
                      ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

                // Yesterday money/decimals
                $sheet->getStyle("{$yActualCol}{$dataStart}:{$yVarCol}{$lastRow}")
                      ->getNumberFormat()->setFormatCode('#,##0.00');

                // MTD money/decimals
                $sheet->getStyle("{$mActualCol}{$dataStart}:{$mVarCol}{$lastRow}")
                      ->getNumberFormat()->setFormatCode('#,##0.00');

                // Alignment
                $sheet->getStyle("A{$dataStart}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("{$this->yStartCol}{$dataStart}:{$yVarCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("{$this->mStartCol}{$dataStart}:{$mVarCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Bold budgets
                $sheet->getStyle("{$yBudgetCol}{$dataStart}:{$yBudgetCol}{$lastRow}")->getFont()->setBold(true);
                $sheet->getStyle("{$mBudgetCol}{$dataStart}:{$mBudgetCol}{$lastRow}")->getFont()->setBold(true);

                // Grey-out blocks (based on whether the first LP col is blank)
                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $yFirst = trim((string)$sheet->getCell("{$this->yStartCol}{$r}")->getValue());
                    $mFirst = trim((string)$sheet->getCell("{$this->mStartCol}{$r}")->getValue());

                    if ($yFirst === '') {
                        $sheet->getStyle("{$this->yStartCol}{$r}:{$yLoadsCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $greyFill]],
                        ]);
                    }
                    if ($mFirst === '') {
                        $sheet->getStyle("{$this->mStartCol}{$r}:{$mLoadsCol}{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $greyFill]],
                        ]);
                    }
                }

                // Center subheaders row
                $sheet->getStyle("{$this->yStartCol}{$headerRow2}:{$yVarCol}{$headerRow2}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("{$this->mStartCol}{$headerRow2}:{$mVarCol}{$headerRow2}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    /**
     * Excel column helpers (A, B, ..., Z, AA, AB, ...)
     */
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