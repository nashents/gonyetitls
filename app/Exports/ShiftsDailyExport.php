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

    protected array $lpCodes = ['Dome', 'P3', 'P4 Flask', 'P6 Dome'];

    public function __construct(
        protected string $periodTitle = 'Key Operating Metrics - ',
        protected ?Carbon $asAt = null,
        protected array $budgets = []
    ) {
        $this->periodTitle = $this->periodTitle.date('M - d');
        $this->asAt = $this->asAt ?: now();
    }

    public function title(): string
    {
        return 'Key Metrics';
    }

    /**
     * ✅ Start the whole table at A7 (logo stays above)
     */
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

    public function array(): array
    {
        $asAt = $this->asAt->copy();

        // reporting cutoff: today @ 01:00
        $reportTo = $asAt->copy()->startOfDay()->addHour(); // 01:00 today

        // Yesterday Production window: day before @ 02:00  -> today @ 01:00
        $yFrom = $asAt->copy()->subDay()->startOfDay()->addHours(2); // 02:00 yesterday
        $yTo   = $reportTo;                                          // 01:00 today

        // MTD window: start of month @ 02:00 -> today @ 01:00
        $mFrom = $asAt->copy()->startOfMonth()->startOfDay()->addHours(2); // 02:00 month start
        $mTo   = $reportTo;  

        $lpMap = LoadingPoint::query()
            ->whereIn('name', $this->lpCodes)
            ->pluck('id', 'name')
            ->toArray();

        $y = $this->computePeriod($yFrom, $yTo, $lpMap);
        $m = $this->computePeriod($mFrom, $mTo, $lpMap);

        $b = fn(string $bucket, string $key) => $this->budgets[$bucket][$key] ?? null;

        return [
            // Header row 1 (merged later)
            [$this->periodTitle, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            // Header row 2
            ['', 'Dome', 'P3', 'P4 Flask', 'P6 Dome', 'Loads', 'Actual', 'Budget', 'Var', '', 'Dome', 'P3', 'P4 Flask', 'P6 Dome', 'Loads', 'Actual', 'Budget', 'Var'],
            // Spacer
            array_fill(0, 18, ''),

            // Long-haul ore (with D/P3/P4/P6 counts)
            [
                'Total Ore Hauled - Long-haul',
                $y['ore_long']['lp']['Dome'] ?? 0,
                $y['ore_long']['lp']['P3'] ?? 0,
                $y['ore_long']['lp']['P4 Flask'] ?? 0,
                $y['ore_long']['lp']['P6 Dome'] ?? 0,
                $y['ore_long']['loads'] ?? 0,
                $y['ore_long']['actual'] ?? 0,
                $b('yesterday', 'ore_long'),
                $this->variance($y['ore_long']['actual'] ?? null, $b('yesterday','ore_long')),
                '',
                $m['ore_long']['lp']['Dome'] ?? 0,
                $m['ore_long']['lp']['P3'] ?? 0,
                $m['ore_long']['lp']['P4 Flask'] ?? 0,
                $m['ore_long']['lp']['P6 Dome'] ?? 0,
                $m['ore_long']['loads'] ?? 0,
                $m['ore_long']['actual'] ?? 0,
                $b('mtd', 'ore_long'),
                $this->variance($m['ore_long']['actual'] ?? null, $b('mtd','ore_long')),
            ],

            [
                'Total Fuel Used – Long-haul',
                '', '', '', '', '',
                $y['fuel_long'] ?? 0,
                $b('yesterday', 'fuel_long'),
                $this->variance($y['fuel_long'] ?? null, $b('yesterday','fuel_long')),
                '',
                '', '', '', '', '',
                $m['fuel_long'] ?? 0,
                $b('mtd', 'fuel_long'),
                $this->variance($m['fuel_long'] ?? null, $b('mtd','fuel_long')),
            ],

            [
                'Total Kilometres - Long-haul',
                '', '', '', '', '',
                $y['km_long'] ?? 0,
                $b('yesterday', 'km_long'),
                $this->variance($y['km_long'] ?? null, $b('yesterday','km_long')),
                '',
                '', '', '', '', '',
                $m['km_long'] ?? 0,
                $b('mtd', 'km_long'),
                $this->variance($m['km_long'] ?? null, $b('mtd','km_long')),
            ],

            [
                'Fuel Consumption - Long Haul',
                '', '', '', '', '',
                $y['cons_long'] ?? null,
                $b('yesterday', 'cons_long'),
                $this->variance($y['cons_long'] ?? null, $b('yesterday','cons_long')),
                '',
                '', '', '', '', '',
                $m['cons_long'] ?? null,
                $b('mtd', 'cons_long'),
                $this->variance($m['cons_long'] ?? null, $b('mtd','cons_long')),
            ],

            // Short-haul ore
            [
                'Total Ore Hauled – Short-haul',
                $y['ore_short']['lp']['Dome'] ?? 0,
                $y['ore_short']['lp']['P3'] ?? 0,
                $y['ore_short']['lp']['P4 Flask'] ?? 0,
                $y['ore_short']['lp']['P6 Dome'] ?? 0,
                $y['ore_short']['loads'] ?? 0,
                $y['ore_short']['actual'] ?? 0,
                $b('yesterday', 'ore_short'),
                $this->variance($y['ore_short']['actual'] ?? null, $b('yesterday','ore_short')),
                '',
                $m['ore_short']['lp']['Dome'] ?? 0,
                $m['ore_short']['lp']['P3'] ?? 0,
                $m['ore_short']['lp']['P4 Flask'] ?? 0,
                $m['ore_short']['lp']['P6 Dome'] ?? 0,
                $m['ore_short']['loads'] ?? 0,
                $m['ore_short']['actual'] ?? 0,
                $b('mtd', 'ore_short'),
                $this->variance($m['ore_short']['actual'] ?? null, $b('mtd','ore_short')),
            ],

            [
                'Total Fuel Used – Short-haul',
                '', '', '', '', '',
                $y['fuel_short'] ?? 0,
                $b('yesterday', 'fuel_short'),
                $this->variance($y['fuel_short'] ?? null, $b('yesterday','fuel_short')),
                '',
                '', '', '', '', '',
                $m['fuel_short'] ?? 0,
                $b('mtd', 'fuel_short'),
                $this->variance($m['fuel_short'] ?? null, $b('mtd','fuel_short')),
            ],

            [
                'Total Kilometres - Short-haul',
                '', '', '', '', '',
                $y['km_short'] ?? 0,
                $b('yesterday', 'km_short'),
                $this->variance($y['km_short'] ?? null, $b('yesterday','km_short')),
                '',
                '', '', '', '', '',
                $m['km_short'] ?? 0,
                $b('mtd', 'km_short'),
                $this->variance($m['km_short'] ?? null, $b('mtd','km_short')),
            ],

            [
                'Fuel Consumption - Short Haul',
                '', '', '', '', '',
                $y['cons_short'] ?? null,
                $b('yesterday', 'cons_short'),
                $this->variance($y['cons_short'] ?? null, $b('yesterday','cons_short')),
                '',
                '', '', '', '', '',
                $m['cons_short'] ?? null,
                $b('mtd', 'cons_short'),
                $this->variance($m['cons_short'] ?? null, $b('mtd','cons_short')),
            ],

            // Concentrates
            [
                'Total Concentrates Hauled',
                '', '', '', '', $y['conc']['loads'] ?? 0,
                $y['conc']['actual'] ?? 0,
                $b('yesterday', 'conc'),
                $this->variance($y['conc']['actual'] ?? null, $b('yesterday','conc')),
                '',
                '', '', '', '', $m['conc']['loads'] ?? 0,
                $m['conc']['actual'] ?? 0,
                $b('mtd', 'conc'),
                $this->variance($m['conc']['actual'] ?? null, $b('mtd','conc')),
            ],

            [
                'Total Fuel Used – Concentrates',
                '', '', '', '', '',
                $y['fuel_conc'] ?? 0,
                $b('yesterday', 'fuel_conc'),
                $this->variance($y['fuel_conc'] ?? null, $b('yesterday','fuel_conc')),
                '',
                '', '', '', '', '',
                $m['fuel_conc'] ?? 0,
                $b('mtd', 'fuel_conc'),
                $this->variance($m['fuel_conc'] ?? null, $b('mtd','fuel_conc')),
            ],
        ];
    }

    protected function computePeriod(Carbon $from, Carbon $to, array $lpMap): array
    {
        $shiftBase = Shift::query()->whereBetween($this->shiftDateColumn, [$from, $to]);

        // Trips constrained by shifts in date range
        $tripBase = Trip::query()->whereHas('shift', fn(Builder $q) => $q->whereBetween($this->shiftDateColumn, [$from, $to]));

        // Long-haul ore
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

        // Short-haul ore
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

        // Concentrates
        $concTrips = (clone $tripBase)->whereHas('cargo', fn(Builder $q) => $q->where('name', '	Platinum Concentrate'));
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
        $counts = (clone $tripQuery)
            ->whereIn('loading_point_id', array_values(array_filter($lpMap)))
            ->selectRaw('loading_point_id, COUNT(*) as c')
            ->groupBy('loading_point_id')
            ->pluck('c', 'loading_point_id')
            ->toArray();

        $out = [];
        foreach ($this->lpCodes as $code) {
            $id = $lpMap[$code] ?? null;
            $out[$code] = $id ? (int)($counts[$id] ?? 0) : 0;
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
        return [
            'A' => 34,
            'B' => 5,  'C' => 5,  'D' => 5,  'E' => 5,  'F' => 7,
            'G' => 12, 'H' => 12, 'I' => 10,
            'J' => 2,
            'K' => 5,  'L' => 5,  'M' => 5,  'N' => 5,  'O' => 7,
            'P' => 12, 'Q' => 12, 'R' => 10,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // ✅ Because startCell is A7, everything shifts down
                $startRow   = 7;
                $headerRow1 = $startRow;       // 7
                $headerRow2 = $startRow + 1;   // 8
                $spacerRow  = $startRow + 2;   // 9
                $dataStart  = $startRow + 3;   // 10

                $lastRow    = $sheet->getHighestRow();
                $rangeAll   = "A{$headerRow1}:R{$lastRow}";

                $headerFill = 'D9D9D9';
                $greyFill   = 'BFBFBF';
                $sepFill    = '7B61A6';

                // Merge headers
                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->mergeCells("B{$headerRow1}:I{$headerRow1}");
                $sheet->mergeCells("K{$headerRow1}:R{$headerRow1}");

                // Separator column
                $sheet->mergeCells("J{$headerRow1}:J{$lastRow}");

                $sheet->setCellValue("B{$headerRow1}", 'YESTERDAY PRODUCTION');
                $sheet->setCellValue("K{$headerRow1}", 'MTD PRODUCTION');

                // Header styling
                $sheet->getStyle("A{$headerRow1}:R{$headerRow2}")->applyFromArray([
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
                $sheet->getStyle("J{$headerRow1}:J{$lastRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $sepFill]],
                ]);

                // Row heights + freeze
                $sheet->getRowDimension($headerRow1)->setRowHeight(22);
                $sheet->getRowDimension($headerRow2)->setRowHeight(18);
                $sheet->getRowDimension($spacerRow)->setRowHeight(6);

                $sheet->freezePane("B{$dataStart}");

                // Borders
                $sheet->getStyle($rangeAll)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Number formats (data area only)
                $sheet->getStyle("B{$dataStart}:F{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle("K{$dataStart}:O{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

                $sheet->getStyle("G{$dataStart}:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("P{$dataStart}:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Alignment
                $sheet->getStyle("A{$dataStart}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("B{$dataStart}:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$dataStart}:R{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Bold budgets
                $sheet->getStyle("H{$dataStart}:H{$lastRow}")->getFont()->setBold(true);
                $sheet->getStyle("Q{$dataStart}:Q{$lastRow}")->getFont()->setBold(true);

                // Grey-out blocks (based on whether D col is blank)
                for ($r = $dataStart; $r <= $lastRow; $r++) {
                    $yesterdayD = trim((string)$sheet->getCell("B{$r}")->getValue());
                    $mtdD       = trim((string)$sheet->getCell("K{$r}")->getValue());

                    if ($yesterdayD === '') {
                        $sheet->getStyle("B{$r}:F{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $greyFill]],
                        ]);
                    }
                    if ($mtdD === '') {
                        $sheet->getStyle("K{$r}:O{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $greyFill]],
                        ]);
                    }
                }

                // Center the subheaders row
                $sheet->getStyle("B{$headerRow2}:I{$headerRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("K{$headerRow2}:R{$headerRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}