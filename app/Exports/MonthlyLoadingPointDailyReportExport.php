<?php

namespace App\Exports;

use App\Models\LoadingPoint;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyLoadingPointDailyReportExport implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected int $year;
    protected int $month;
    protected Collection $loading_points;
    protected array $dailyData = [];
    protected int $totalDataCols; // loading point dynamic cols count
    protected array $colMap = [];  // maps purpose => Excel col letter

    // Configurable budget/target values (inject or set as needed)
    public float $dailyBudgetLoads = 69;
    public float $dailyBudgetTonnage = 7245;
    public float $dailyFuelBudget = 3360;
    public float $fuelTargetConsumption = 1.12; // per ton

    public function __construct(int $year, int $month)
    {
        $this->year  = $year;
        $this->month = $month;
        $this->loading_points = LoadingPoint::orderBy('name')->get();
        $this->buildDailyData();
    }

    // ─────────────────────────────────────────────────────────
    // DATA BUILDING
    // ─────────────────────────────────────────────────────────

    protected function buildDailyData(): void
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $end   = $start->copy()->endOfMonth()->endOfDay();

        // Load all shifts for the month keyed by calendar date (using shift start_time)
        $shifts = Shift::with([
            'trips.loading_point',
            'fuel',
        ])
            ->whereBetween('shift_start_time', [$start, $end])
            ->get()
            ->groupBy(fn($s) => Carbon::parse($s->shift_start_time)->format('Y-m-d'));

        $daysInMonth = $start->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date    = Carbon::create($this->year, $this->month, $day);
            $dateKey = $date->format('Y-m-d');

            $dayShifts = $shifts->get($dateKey, collect());

            // Per loading-point aggregates
            $lpLoads   = [];
            $lpTonnage = [];
            foreach ($this->loading_points as $lp) {
                $lpLoads[$lp->id]   = 0;
                $lpTonnage[$lp->id] = 0.0;
            }

            $totalLoads   = 0;
            $totalTonnage = 0.0;
            $totalFuel    = 0.0;

            foreach ($dayShifts as $shift) {
                foreach ($shift->trips as $trip) {
                    $lpId = $trip->loading_point_id;
                    if (isset($lpLoads[$lpId])) {
                        $lpLoads[$lpId]++;
                        $lpTonnage[$lpId] += (float) $trip->weight;
                    }
                    $totalLoads++;
                    $totalTonnage += (float) $trip->weight;
                }

                if ($shift->fuel) {
                    $totalFuel += (float) $shift->fuel->quantity;
                }
            }

            $fuelConsumption = $totalTonnage > 0 ? round($totalFuel / $totalTonnage, 2) : 0;

            $dailyBudgetLoads   = $this->getDailyBudgetLoads($date);
            $dailyBudgetTonnage = $this->getDailyBudgetTonnage($date);
            $dailyFuelBudget    = $this->getDailyFuelBudget($date);

            $this->dailyData[$dateKey] = [
                'date'             => $date,
                'lp_loads'         => $lpLoads,
                'lp_tonnage'       => $lpTonnage,
                'total_loads'      => $totalLoads,
                'total_tonnage'    => $totalTonnage,
                'budget_loads'     => $dailyBudgetLoads,
                'budget_tonnage'   => $dailyBudgetTonnage,
                'variance_loads'   => $totalLoads - $dailyBudgetLoads,
                'variance_tonnage' => $totalTonnage - $dailyBudgetTonnage,
                'total_fuel'       => $totalFuel,
                'fuel_consumption' => $fuelConsumption,
                'budget_fuel'      => $dailyFuelBudget,
                'fuel_variance'    => $totalFuel - $dailyFuelBudget,
            ];
        }
    }

    /**
     * Override these methods for dynamic budget logic if needed.
     */
    protected function getDailyBudgetLoads(Carbon $date): float
    {
        return $this->dailyBudgetLoads;
    }

    protected function getDailyBudgetTonnage(Carbon $date): float
    {
        return $this->dailyBudgetTonnage;
    }

    protected function getDailyFuelBudget(Carbon $date): float
    {
        return $this->dailyFuelBudget;
    }

    // ─────────────────────────────────────────────────────────
    // ARRAY BUILDING FOR SHEET
    // ─────────────────────────────────────────────────────────

    public function array(): array
    {
        $lpCount = $this->loading_points->count();
        $rows    = [];

        // ── ROW 1: Month header
        $monthLabel = Carbon::create($this->year, $this->month, 1)->format('M-y');
        $rows[]     = array_merge(['MONTH', $monthLabel], array_fill(0, $lpCount * 2 + 8, ''));

        // ── ROW 2: Loading point group headers (dynamic)
        $row2 = ['', ''];
        foreach ($this->loading_points as $lp) {
            $row2[] = strtoupper($lp->name);
            $row2[] = '';
        }
        $row2 = array_merge($row2, ['TOTALS', '', 'BUDGET', 'VARIANCE', 'FUEL SUMMARY', '']);
        $rows[] = $row2;

        // ── ROW 3: Sub-headers
        $row3 = ['Date', 'Day'];
        foreach ($this->loading_points as $lp) {
            $row3[] = 'LOADS';
            $row3[] = 'TONNAGE';
        }
        $row3 = array_merge($row3, [
            'TOTAL LOADS', 'TOTAL TONNAGE',
            'BUDGET LOADS', 'BUDGET TONNAGE',
            'VARIANCE LOADS', 'VARIANCE TONNAGE',
            'TOTAL FUEL', 'FUEL CONSUMPTION',
        ]);
        $rows[] = $row3;

        // ── DATA ROWS
        foreach ($this->dailyData as $dateKey => $d) {
            /** @var Carbon $date */
            $date = $d['date'];
            $row  = [
                $date->format('l, F d, Y'),
                $date->format('D'),
            ];

            foreach ($this->loading_points as $lp) {
                $row[] = $d['lp_loads'][$lp->id] ?? 0;
                $row[] = $d['lp_tonnage'][$lp->id] ?? 0;
            }

            $row[] = $d['total_loads'];
            $row[] = $d['total_tonnage'];
            $row[] = $d['budget_loads'];
            $row[] = $d['budget_tonnage'];
            $row[] = $d['variance_loads'];
            $row[] = $d['variance_tonnage'];
            $row[] = $d['total_fuel'];
            $row[] = $d['fuel_consumption'] > 0 ? $d['fuel_consumption'] : '';

            $rows[] = $row;
        }

        // ── TOTALS ROW
        $daysInMonth = count($this->dailyData);
        $totalsRow   = ['TOTALS', ''];
        foreach ($this->loading_points as $lp) {
            $totalsRow[] = array_sum(array_column($this->dailyData, 'lp_loads.' . $lp->id) ?: array_map(fn($d) => $d['lp_loads'][$lp->id] ?? 0, $this->dailyData));
            $totalsRow[] = array_sum(array_map(fn($d) => $d['lp_tonnage'][$lp->id] ?? 0, $this->dailyData));
        }
        $totalsRow[] = array_sum(array_column($this->dailyData, 'total_loads'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'total_tonnage'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'budget_loads'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'budget_tonnage'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'variance_loads'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'variance_tonnage'));
        $totalsRow[] = array_sum(array_column($this->dailyData, 'total_fuel'));
        $avgFuelCons = collect($this->dailyData)
            ->filter(fn($d) => $d['fuel_consumption'] > 0)
            ->avg('fuel_consumption');
        $totalsRow[] = $avgFuelCons ? round($avgFuelCons, 2) : '';

        $rows[] = $totalsRow;

        return $rows;
    }

    // ─────────────────────────────────────────────────────────
    // STYLING
    // ─────────────────────────────────────────────────────────

    public function styles(Worksheet $sheet): array
    {
        return []; // handled via AfterSheet event for full control
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28, // Date
            'B' => 6,  // Day
        ];
    }

    public function title(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('M Y') . ' Report';
    }

    // ─────────────────────────────────────────────────────────
    // AFTER SHEET EVENT — full formatting
    // ─────────────────────────────────────────────────────────

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lpCount   = $this->loading_points->count();
                $dataRows  = count($this->dailyData);
                $headerRow = 3; // 1=month, 2=lp groups, 3=sub-headers
                $firstData = 4;
                $lastData  = $firstData + $dataRows - 1;
                $totalsRow = $lastData + 1;

                // Total columns = 2 (date+day) + lpCount*2 + 8 (totals/budget/variance/fuel)
                $totalCols = 2 + ($lpCount * 2) + 8;
                $lastCol   = $this->colLetter($totalCols);

                // ── Freeze panes after col A, row 4
                $sheet->freezePane('C4');

                // ── Row 1: Month header styling
                $sheet->mergeCells('A1:B1');
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1F497D']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Row 2: Loading point group headers
                $col = 3; // start at col C (0-indexed internally, but 1-based here: C=3)
                foreach ($this->loading_points as $lp) {
                    $startLetter = $this->colLetter($col);
                    $endLetter   = $this->colLetter($col + 1);
                    $sheet->mergeCells("{$startLetter}2:{$endLetter}2");
                    $sheet->getStyle("{$startLetter}2:{$endLetter}2")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    $col += 2;
                }

                // Totals group header
                $totalsStart = $this->colLetter($col);
                $totalsEnd   = $this->colLetter($col + 1);
                $sheet->mergeCells("{$totalsStart}2:{$totalsEnd}2");
                $sheet->getStyle("{$totalsStart}2:{$totalsEnd}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF70AD47']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Budget group header
                $budgetStart = $this->colLetter($col + 2);
                $budgetEnd   = $this->colLetter($col + 3);
                $sheet->mergeCells("{$budgetStart}2:{$budgetEnd}2");
                $sheet->getStyle("{$budgetStart}2:{$budgetEnd}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFED7D31']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Variance group header
                $varStart = $this->colLetter($col + 4);
                $varEnd   = $this->colLetter($col + 5);
                $sheet->mergeCells("{$varStart}2:{$varEnd}2");
                $sheet->getStyle("{$varStart}2:{$varEnd}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF0000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Fuel group header
                $fuelStart = $this->colLetter($col + 6);
                $fuelEnd   = $this->colLetter($col + 7);
                $sheet->mergeCells("{$fuelStart}2:{$fuelEnd}2");
                $sheet->getStyle("{$fuelStart}2:{$fuelEnd}2")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7030A0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Row 3: Sub-headers
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF999999']],
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // ── Data rows: alternating shading
                for ($r = $firstData; $r <= $lastData; $r++) {
                    $fillColor = ($r % 2 === 0) ? 'FFFCFCFC' : 'FFE9EFF7';
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillColor]],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFCCCCCC']],
                        ],
                        'font'    => ['size' => 9],
                    ]);

                    // Variance columns: red for negative, green for positive
                    $varLoadsCol   = $this->colLetter(2 + $lpCount * 2 + 5);
                    $varTonnageCol = $this->colLetter(2 + $lpCount * 2 + 6);

                    foreach ([$varLoadsCol, $varTonnageCol] as $vc) {
                        $cellVal = $sheet->getCell("{$vc}{$r}")->getValue();
                        if (is_numeric($cellVal)) {
                            $color = $cellVal < 0 ? 'FFFF0000' : 'FF00B050';
                            $sheet->getStyle("{$vc}{$r}")->getFont()->getColor()->setARGB($color);
                        }
                    }
                }

                // ── Totals row
                $sheet->getStyle("A{$totalsRow}:{$lastCol}{$totalsRow}")->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 10],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F497D']],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']],
                    ],
                ]);
                $sheet->getStyle("A{$totalsRow}:{$lastCol}{$totalsRow}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');

                // ── Number formats: tonnage columns
                $col = 3;
                foreach ($this->loading_points as $lp) {
                    $tonnageCol = $this->colLetter($col + 1);
                    $sheet->getStyle("{$tonnageCol}{$firstData}:{$tonnageCol}{$totalsRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.0');
                    $col += 2;
                }

                // Total tonnage, budget tonnage, variance tonnage
                foreach ([
                    2 + $lpCount * 2 + 2,  // total tonnage
                    2 + $lpCount * 2 + 4,  // budget tonnage
                    2 + $lpCount * 2 + 6,  // variance tonnage
                ] as $cIdx) {
                    $cl = $this->colLetter($cIdx);
                    $sheet->getStyle("{$cl}{$firstData}:{$cl}{$totalsRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.0');
                }

                // Fuel consumption
                $fcCol = $this->colLetter($totalCols);
                $sheet->getStyle("{$fcCol}{$firstData}:{$fcCol}{$totalsRow}")
                    ->getNumberFormat()->setFormatCode('0.00');

                // ── Dynamic column widths for LP columns
                $col = 3;
                foreach ($this->loading_points as $lp) {
                    $loadsLetter   = $this->colLetter($col);
                    $tonnageLetter = $this->colLetter($col + 1);
                    $sheet->getColumnDimension($loadsLetter)->setWidth(8);
                    $sheet->getColumnDimension($tonnageLetter)->setWidth(10);
                    $col += 2;
                }

                // Summary/fuel column widths
                for ($i = 0; $i < 8; $i++) {
                    $sheet->getColumnDimension($this->colLetter(2 + $lpCount * 2 + 1 + $i))->setWidth(12);
                }

                // ── Outer border
                $sheet->getStyle("A1:{$lastCol}{$totalsRow}")->applyFromArray([
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1F497D']],
                    ],
                ]);

                // ── Print settings
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getHeaderFooter()
                    ->setOddHeader('&C&B Monthly Transport Report — ' . Carbon::create($this->year, $this->month)->format('F Y'));
                $sheet->getHeaderFooter()
                    ->setOddFooter('&L&D &T&R Page &P of &N');
            },
        ];
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    protected function colLetter(int $colIndex): string
    {
        // 1-based: 1=A, 2=B, 27=AA …
        $letter = '';
        while ($colIndex > 0) {
            $mod    = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colIndex = (int)(($colIndex - $mod) / 26);
        }
        return $letter;
    }
}
