<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Exports\Concerns\SharesShiftTrips;
use App\Models\Fuel;
use App\Models\Shift;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Monthly "Concentrates Haulage" (client's "<Month> Concentrates haulage report"):
 * daily tonnage, fuel and mileage reconciliation per truck. One row per day,
 * seven side-by-side sections with one column per truck plus a total/average:
 *
 *   Tonnes | Litres | Litres/Tonne | Loads | Km | Km/Load | Km/Litre
 *
 * Source: concentrate trips (cargo name contains "Concentrate") on shifts, on the
 * shift's production day (02:00 to 01:00 next day). Tonnage = trip weight; loads
 * = trips; Km = trip ending - starting mileage (else trip distance, else the
 * shift's mileage shared by loads); fuel = the shift's fuel order litres shared
 * across all the shift's trips by mileage (else per load), so a shift that also
 * hauled other cargo only charges its concentrate share. Ratio sections are
 * Excel formulas off the base sections; their TOTALS come from the month totals.
 */
class ConcentratesHaulageExport implements WithEvents, WithTitle
{
    use DailyShiftReportHeader;
    use SharesShiftTrips;

    /** [title, unit, value key or [numerator, denominator], number format, total label] */
    protected const SECTIONS = [
        ['TONNAGE MOVED SUMMARY', 'Tonnes', 'tons', '#,##0.00', 'TOTAL'],
        ['FUEL CONSUMPTION SUMMARY', 'Litres', 'fuel', '#,##0', 'TOTAL'],
        ['FUEL CONSUMPTION', 'Litres/Tonne', [1, 0], '0.00', 'Ave.'],
        ['LOADS', 'Loads', 'loads', '0', 'TOTAL'],
        ['DAILY MILEAGE', 'Km', 'km', '#,##0', 'TOTAL'],
        ['AVERAGE PER LOAD', 'Km/Load', [4, 3], '#,##0.0', 'Ave.'],
        ['FUEL CONSUMPTION', 'Km/Litre', [4, 1], '0.00', 'Ave.'],
    ];

    protected const SECTION_ROW = 5;
    protected const REG_ROW = 6;
    protected const FLEET_ROW = 7;
    protected const UNIT_ROW = 8;
    protected const DATA_START = 9;

    protected const BLUE = 'DDEBF7';
    protected const GOLD = 'FFE699';

    protected Carbon $month;
    protected int $trucks = 0;

    public function __construct(?Carbon $month = null)
    {
        $this->month = ($month ?: now())->copy()->startOfMonth();
    }

    public function title(): string
    {
        return 'Concentrates Haulage';
    }

    /** Column of truck $j (0-based; $j = truck count is the total column) in section $s. */
    protected function col(int $s, int $j): string
    {
        return Coordinate::stringFromColumnIndex(2 + $s * ($this->trucks + 1) + $j);
    }

    /**
     * @return array<string, array{fleet: string, reg: string, days: array<int, array{tons: float, loads: int, km: float, fuel: float}>}>
     */
    protected function collect(): array
    {
        $start = $this->month->copy();
        $end = $this->month->copy()->endOfMonth();

        $shifts = Shift::query()
            ->with('horse')
            ->where(fn (Builder $q) => $this->whereShiftStartBetween(
                $q,
                $start->copy()->addHours(2),
                $end->copy()->addDay()->startOfDay()->addHour()->addMinutes(59)
            ))
            ->get()
            ->keyBy('id');

        if ($shifts->isEmpty()) {
            return [];
        }

        // Every trip on those shifts (all cargo), so fuel is shared fairly
        $trips = Trip::query()
            ->whereIn('shift_id', $shifts->keys())
            ->with(['cargo:id,name', 'horse'])
            ->get();

        $fuelByShift = Fuel::query()
            ->whereIn('shift_id', $shifts->keys())
            ->where(fn (Builder $q) => $q->whereNull('authorization')->orWhere('authorization', '!=', 'rejected'))
            ->selectRaw('shift_id, SUM(quantity) as litres')
            ->groupBy('shift_id')
            ->pluck('litres', 'shift_id');

        $trucks = [];

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
                if (!str_contains(strtolower((string) $trip->cargo?->name), 'concentrate')) {
                    continue;
                }

                $horse = $trip->horse ?: $shift->horse;
                $key = $horse ? 'h' . $horse->id : 'none';
                $trucks[$key] ??= [
                    'fleet' => $horse ? $this->fleetLabel($horse) : 'No Truck',
                    'reg' => $horse ? trim((string) $horse->registration_number) : '',
                    'days' => [],
                ];

                $d = &$trucks[$key]['days'][$day->day];
                $d ??= ['tons' => 0.0, 'loads' => 0, 'km' => 0.0, 'fuel' => 0.0];
                $d['tons'] += is_numeric($trip->weight) ? (float) $trip->weight : 0.0;
                $d['loads']++;
                $d['km'] += $shares['km'][$trip->id] ?? ($shiftKm * $shares['loads']);
                $d['fuel'] += $litres * $shares['by'][$trip->id];
                unset($d);
            }
        }

        uasort($trucks, fn ($a, $b) => strnatcasecmp($a['fleet'], $b['fleet']));

        return $trucks;
    }

    protected function fleetLabel($horse): string
    {
        $fleet = trim((string) $horse->fleet_number);

        return $fleet !== '' && !preg_match('#^n[/?]?a$#i', $fleet)
            ? $fleet
            : (string) ($horse->registration_number ?: ('#' . $horse->id));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $trucks = $this->collect();
                $this->render($sheet, $trucks);

                $this->prependReportHeader($sheet, [
                    'Report' => 'Concentrates Haulage - Daily Tonnage, Fuel and Mileage Reconciliation',
                    'Month' => $this->month->format('F Y'),
                    'Trucks' => (string) count($trucks),
                    'Basis' => 'Concentrate trips on shifts, production day 02:00 - 01:00; shift fuel and km shared across the shift\'s trips',
                ]);
            },
        ];
    }

    protected function render(Worksheet $sheet, array $trucks): void
    {
        $this->trucks = count($trucks);
        $n = $this->trucks;
        $keys = array_keys($trucks);
        $days = $this->month->daysInMonth;
        $first = self::DATA_START;
        $last = $first + $days - 1;
        $totalRow = $last + 2;
        $lastCol = $this->col(count(self::SECTIONS) - 1, $n);

        $thin = ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']];
        $medium = ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']];
        $center = ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true];

        // Titles
        $sheet->setCellValue('A1', 'CONCENTRATES TRANSPORTATION');
        $sheet->setCellValue('A2', 'DAILY TONNAGE, FUEL AND MILEAGE RECONCILIATION - ' . strtoupper($this->month->format('F Y')));
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);

        // Date column
        $sheet->mergeCells('A' . self::SECTION_ROW . ':A' . self::UNIT_ROW);
        $sheet->setCellValue('A' . self::SECTION_ROW, 'Date');
        for ($d = 1; $d <= $days; $d++) {
            $sheet->setCellValue('A' . ($first + $d - 1), ExcelDate::PHPToExcel($this->month->copy()->day($d)->startOfDay()));
        }
        $sheet->getStyle("A{$first}:A{$last}")->getNumberFormat()->setFormatCode('dd-mmm-yy');
        $sheet->getStyle("A{$first}:A{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("A{$totalRow}", 'TOTALS');
        $sheet->getColumnDimension('A')->setWidth(14);

        foreach (self::SECTIONS as $s => [$title, $unit, $value, $format, $totalLabel]) {
            $from = $this->col($s, 0);
            $tot = $this->col($s, $n);

            $sheet->setCellValue($from . self::SECTION_ROW, $title);
            if ($n > 0) {
                $sheet->mergeCells($from . self::SECTION_ROW . ':' . $tot . self::SECTION_ROW);
            }

            // Truck headers
            foreach ($keys as $j => $key) {
                $c = $this->col($s, $j);
                $sheet->setCellValueExplicit($c . self::REG_ROW, $trucks[$key]['reg'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit($c . self::FLEET_ROW, $trucks[$key]['fleet'], DataType::TYPE_STRING);
                $sheet->setCellValue($c . self::UNIT_ROW, $unit);
                $sheet->getColumnDimension($c)->setWidth(10.5);
            }
            $sheet->setCellValue($tot . self::FLEET_ROW, $totalLabel);
            $sheet->setCellValue($tot . self::UNIT_ROW, $unit);
            $sheet->getColumnDimension($tot)->setWidth(12);

            // Values / formulas, truck columns and the total column ($j = $n)
            for ($j = 0; $j <= $n; $j++) {
                $c = $this->col($s, $j);
                $isTotal = $j === $n;

                for ($d = 1; $d <= $days; $d++) {
                    $r = $first + $d - 1;

                    if (is_array($value)) {
                        [$num, $den] = $value;
                        $a = $this->col($num, $j) . $r;
                        $b = $this->col($den, $j) . $r;
                        $sheet->setCellValue("{$c}{$r}", "=IF(N({$b})>0,N({$a})/{$b},\"\")");
                    } elseif ($isTotal) {
                        $sheet->setCellValue("{$c}{$r}", $n ? '=SUM(' . $this->col($s, 0) . $r . ':' . $this->col($s, $n - 1) . $r . ')' : 0);
                    } else {
                        $v = $trucks[$keys[$j]]['days'][$d][$value] ?? 0;
                        if ($v > 0) {
                            $sheet->setCellValue("{$c}{$r}", $value === 'loads' ? (int) $v : round($v, 2));
                        }
                    }
                }

                if (is_array($value)) {
                    [$num, $den] = $value;
                    $a = $this->col($num, $j) . $totalRow;
                    $b = $this->col($den, $j) . $totalRow;
                    $sheet->setCellValue("{$c}{$totalRow}", "=IF(N({$b})>0,N({$a})/{$b},\"\")");
                } else {
                    $sheet->setCellValue("{$c}{$totalRow}", "=SUM({$c}{$first}:{$c}{$last})");
                }
            }

            // Styling per section
            $sheet->getStyle($from . self::SECTION_ROW . ':' . $tot . self::UNIT_ROW)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => $center,
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::BLUE]],
            ]);
            $sheet->getStyle("{$from}{$first}:{$tot}{$totalRow}")->getNumberFormat()->setFormatCode($format);
            $sheet->getStyle("{$tot}" . self::FLEET_ROW . ":{$tot}{$totalRow}")->getFont()->setBold(true);
            $sheet->getStyle($from . self::SECTION_ROW . ":{$tot}{$totalRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
            $sheet->getStyle($from . self::SECTION_ROW . ":{$tot}{$totalRow}")->getBorders()->getOutline()->applyFromArray($medium);
        }

        $sheet->getStyle('A' . self::SECTION_ROW . ':A' . self::UNIT_ROW)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => $center,
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::BLUE]],
        ]);
        $sheet->getStyle('A' . self::SECTION_ROW . ":A{$totalRow}")->getBorders()->getAllBorders()->applyFromArray($thin);
        $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => self::GOLD]],
            'borders' => ['top' => $medium, 'bottom' => $medium],
        ]);
        $sheet->getRowDimension(self::SECTION_ROW)->setRowHeight(30);

        if ($n === 0) {
            $sheet->setCellValue('A3', 'No concentrate trips on shifts for this month.');
            $sheet->getStyle('A3')->getFont()->setItalic(true);
        }

        $sheet->freezePane('B' . self::DATA_START);
    }
}
