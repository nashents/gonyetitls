<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Models\Fuel;
use App\Models\Shift;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
 * Ops Concentrate Report: concentrate haulage per truck, one table per route
 * ("CONCENTRATES TRANSPORTATION - <loading point> TO <offloading point>"),
 * first for Yesterday then for Month To Date.
 *
 * Windows match the Production Report (ShiftsDailyExport): trips on shifts that
 * started 02:00 the day before the As At date to 01:00 on the As At date (MTD:
 * from 02:00 on the 1st). Concentrate = trip cargo name containing "Concentrate".
 *
 * Loads = trips; Tonnage = trip weight; Total Mileage = trip ending - starting
 * mileage (else trip distance; else the shift's mileage shared by loads).
 * Note: trips.shift is a column, so $trip->shift is NOT the relation.
 * Fuel = the shift's fuel order litres, shared across the shift's trips by
 * mileage (by loads when trips carry no mileage), so a shift that also hauled
 * other cargo only charges its concentrate share here.
 * Ratio columns are Excel formulas off the row's own figures.
 */
class OpsConcentrateExport implements FromArray, WithEvents, WithColumnWidths, WithTitle, WithDrawings, WithCustomStartCell
{
    use DailyShiftReportHeader;

    protected const START_ROW = 8;
    protected const NAVY = '002060';
    protected const HEADER = ['Fleet', 'loads', 'tonnage', 'fuel', 'total mileage', 'km/litre', 'Litre/ton', 'tons/load', 'km/load', 'tons/litre'];

    protected Carbon $asAt;
    protected array $reportPeriods = [];

    /** Row numbers (sheet) for styling, filled in array(). */
    protected array $sectionRows = [];
    protected array $titleRows = [];
    protected array $headerRows = [];
    protected array $tables = []; // [headerRow, totalRow]
    protected array $emptyRows = [];

    public function __construct(?Carbon $asAt = null)
    {
        $this->asAt = $asAt ?: now();
    }

    public function title(): string
    {
        return 'Concentrates';
    }

    public function startCell(): string
    {
        return 'A' . self::START_ROW;
    }

    public function array(): array
    {
        $to = $this->asAt->copy()->startOfDay()->addHour();
        $yFrom = $this->asAt->copy()->subDay()->startOfDay()->addHours(2);
        $mFrom = $this->asAt->copy()->subDay()->startOfMonth()->startOfDay()->addHours(2);
        $reportDay = $this->asAt->copy()->subDay();

        $this->reportPeriods = [
            'As At' => $this->asAt->format('d M Y'),
            'Period (Yesterday)' => $this->formatWindow($yFrom, $to),
            'Period (Month To Date)' => $this->formatWindow($mFrom, $to),
        ];

        $rows = [];
        $periods = [
            ['YESTERDAY - ' . $reportDay->format('d M Y'), $reportDay->format('d-M-y'), $yFrom],
            ['MONTH TO DATE - ' . $reportDay->format('M Y'), $reportDay->format('M-y'), $mFrom],
        ];

        foreach ($periods as [$sectionTitle, $dateLabel, $from]) {
            $this->sectionRows[] = $this->sheetRow($rows);
            $rows[] = [$sectionTitle];
            $rows[] = [''];

            $routes = $this->routes($from, $to);

            if (!$routes) {
                $this->emptyRows[] = $this->sheetRow($rows);
                $rows[] = ['No concentrate trips in this period.'];
                $rows[] = [''];
                continue;
            }

            foreach ($routes as $route) {
                $this->titleRows[] = $this->sheetRow($rows);
                $rows[] = [$dateLabel, $route['title']];
                $rows[] = [''];

                $header = $this->sheetRow($rows);
                $this->headerRows[] = $header;
                $rows[] = self::HEADER;

                foreach ($route['fleet'] as $f) {
                    $r = $this->sheetRow($rows);
                    $rows[] = [
                        $f['label'],
                        $f['loads'],
                        round($f['tons'], 2),
                        round($f['fuel'], 2),
                        round($f['km'], 2),
                        "=IF(D{$r}>0,E{$r}/D{$r},\"\")",
                        "=IF(C{$r}>0,D{$r}/C{$r},\"\")",
                        "=IF(B{$r}>0,C{$r}/B{$r},\"\")",
                        "=IF(B{$r}>0,E{$r}/B{$r},\"\")",
                        "=IF(D{$r}>0,C{$r}/D{$r},\"\")",
                    ];
                }

                $first = $header + 1;
                $last = $this->sheetRow($rows) - 1;
                $total = $this->sheetRow($rows);
                $rows[] = [
                    'Grand Totals',
                    "=SUM(B{$first}:B{$last})",
                    "=SUM(C{$first}:C{$last})",
                    "=SUM(D{$first}:D{$last})",
                    "=SUM(E{$first}:E{$last})",
                ];
                $this->tables[] = [$header, $total];

                $rows[] = [''];
                $rows[] = [''];
            }
        }

        return $rows;
    }

    /** Sheet row number the next appended row will land on. */
    protected function sheetRow(array $rows): int
    {
        return self::START_ROW + count($rows);
    }

    /**
     * Concentrate trips on shifts in the window, per route then per truck.
     *
     * @return array<int, array{title: string, fleet: array}>
     */
    protected function routes(Carbon $from, Carbon $to): array
    {
        $shiftIds = Shift::query()
            ->select('id')
            ->where(fn ($q) => $this->whereShiftStartBetween($q, $from, $to));

        // Every trip on those shifts (all cargo), so fuel can be shared fairly
        $trips = Trip::query()
            ->whereIn('shift_id', $shiftIds)
            ->with(['cargo:id,name', 'horse', 'loading_point:id,name', 'offloading_point:id,name'])
            ->get();

        if ($trips->isEmpty()) {
            return [];
        }

        // trips.shift is a column that shadows the shift() relation, so load shifts by id
        $shifts = Shift::query()->with('horse')->whereIn('id', $trips->pluck('shift_id')->unique())->get()->keyBy('id');

        $fuelByShift = Fuel::query()
            ->whereIn('shift_id', $trips->pluck('shift_id')->unique())
            ->selectRaw('shift_id, SUM(quantity) as litres')
            ->groupBy('shift_id')
            ->pluck('litres', 'shift_id');

        $routes = [];

        foreach ($trips->groupBy('shift_id') as $shiftId => $shiftTrips) {
            $shares = $this->shares($shiftTrips);
            $shift = $shifts[$shiftId] ?? null;
            $shiftKm = $this->shiftKm($shift);
            $litres = (float) ($fuelByShift[$shiftId] ?? 0);

            foreach ($shiftTrips as $trip) {
                if (!str_contains(strtolower((string) $trip->cargo?->name), 'concentrate')) {
                    continue;
                }

                $lp = $trip->loading_point?->name ?: 'Unknown Loading Point';
                $op = $trip->offloading_point?->name ?: 'Unknown Offloading Point';
                $routeKey = $trip->loading_point_id . '-' . $trip->offloading_point_id;

                $routes[$routeKey] ??= [
                    'title' => strtoupper("CONCENTRATES TRANSPORTATION - {$lp} TO {$op}"),
                    'fleet' => [],
                ];

                [$fleetKey, $label] = $this->fleet($trip, $shift);
                $routes[$routeKey]['fleet'][$fleetKey] ??= ['label' => $label, 'loads' => 0, 'tons' => 0.0, 'fuel' => 0.0, 'km' => 0.0];

                $f = &$routes[$routeKey]['fleet'][$fleetKey];
                $f['loads']++;
                $f['tons'] += is_numeric($trip->weight) ? (float) $trip->weight : 0.0;
                $f['fuel'] += $litres * $shares['by'][$trip->id];
                $f['km'] += $shares['km'][$trip->id] ?? ($shiftKm * $shares['loads']);
                unset($f);
            }
        }

        foreach ($routes as &$route) {
            uasort($route['fleet'], fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));
        }
        unset($route);

        uasort($routes, fn ($a, $b) => strcmp($a['title'], $b['title']));

        return array_values($routes);
    }

    /**
     * Each trip's share of its shift: by trip mileage when every trip has one,
     * else equally per load. 'km' holds the trip mileages found.
     */
    protected function shares(Collection $shiftTrips): array
    {
        $km = [];
        foreach ($shiftTrips as $trip) {
            if (($run = $this->tripKm($trip)) !== null) {
                $km[$trip->id] = $run;
            }
        }

        $count = $shiftTrips->count();
        $totalKm = array_sum($km);
        $byKm = count($km) === $count && $totalKm > 0;

        $by = [];
        foreach ($shiftTrips as $trip) {
            $by[$trip->id] = $byKm ? $km[$trip->id] / $totalKm : 1 / $count;
        }

        return ['by' => $by, 'km' => $km, 'loads' => 1 / $count];
    }

    protected function tripKm(Trip $trip): ?float
    {
        if (is_numeric($trip->starting_mileage) && is_numeric($trip->ending_mileage)) {
            $run = (float) $trip->ending_mileage - (float) $trip->starting_mileage;
            if ($run > 0) {
                return $run;
            }
        }

        return is_numeric($trip->distance) && $trip->distance > 0 ? (float) $trip->distance : null;
    }

    protected function shiftKm(?Shift $shift): float
    {
        if (!$shift) {
            return 0.0;
        }

        if (is_numeric($shift->open_mileage) && is_numeric($shift->close_mileage)) {
            $run = (float) $shift->close_mileage - (float) $shift->open_mileage;
            if ($run > 0) {
                return $run;
            }
        }

        return is_numeric($shift->actual_mileage) && $shift->actual_mileage > 0 ? (float) $shift->actual_mileage : 0.0;
    }

    /** [key, Fleet label]: trip horse, else the shift's horse; fleet number, else registration. */
    protected function fleet(Trip $trip, ?Shift $shift): array
    {
        $horse = $trip->horse ?: $shift?->horse;

        if (!$horse) {
            return ['none', 'No Truck'];
        }

        $fleet = trim((string) $horse->fleet_number);
        $label = $fleet !== '' && !preg_match('#^n[/?]?a$#i', $fleet)
            ? $fleet
            : (string) ($horse->registration_number ?: ('#' . $horse->id));

        return ['h' . $horse->id, $label];
    }

    public function columnWidths(): array
    {
        return ['A' => 14, 'B' => 11, 'C' => 11, 'D' => 11, 'E' => 13, 'F' => 13, 'G' => 13, 'H' => 11, 'I' => 11, 'J' => 11];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
                $medium = ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']];

                $this->writeReportInfo($sheet, 'E', $this->reportPeriods);

                foreach ($this->sectionRows as $r) {
                    $sheet->mergeCells("A{$r}:J{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(13)->setUnderline(true);
                }

                foreach ($this->emptyRows as $r) {
                    $sheet->getStyle("A{$r}")->getFont()->setItalic(true);
                }

                // Date label + navy route title bar
                foreach ($this->titleRows as $r) {
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                    $sheet->mergeCells("B{$r}:G{$r}");
                    $sheet->getStyle("B{$r}:G{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::NAVY]],
                        'borders' => ['bottom' => $medium],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }

                foreach ($this->tables as [$header, $total]) {
                    $lastData = $total - 1;

                    $sheet->getStyle("A{$header}:J{$lastData}")->applyFromArray(['borders' => ['allBorders' => $thin]]);
                    $sheet->getStyle("B{$header}:J{$header}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    if ($lastData > $header) {
                        $sheet->getStyle("B" . ($header + 1) . ":J{$total}")->getNumberFormat()
                            ->setFormatCode('#,##0.00;-#,##0.00;"-"');
                        $sheet->getStyle("B" . ($header + 1) . ":B{$total}")->getNumberFormat()
                            ->setFormatCode('#,##0;-#,##0;"-"');
                    }

                    // Grand Totals: label right-aligned, boxed totals under loads..mileage
                    $sheet->getStyle("A{$total}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("B{$total}:E{$total}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => ['allBorders' => $medium],
                    ]);
                }
            },
        ];
    }
}
