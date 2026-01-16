<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ShiftsExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    public $commission;
    /**
    * @return \Illuminate\Support\Collection
    */
    public $from;
    public $to;
    public $shift_filter;
    public $search;
    public $totals;
    public $filters;
    public $totalNightSeconds = 0;
    public $totalDaySeconds   = 0;
   

    public function __construct($from, $to, $shift_filter, $search, $filters)
    {
            $this->from = $from;
            $this->to = $to;
            $this->shift_filter = $shift_filter;
            $this->search = $search; 
            $this->filters = $filters;
            
            $base = $this->query();

            // Pull only what we need for summary + the relationship aggregates
            $summaryRows = (clone $base)
                ->withCount('trips')              // gives trips_count
                ->withSum('trips', 'weight')      // gives trips_sum_weight (Laravel default alias)
                ->get([
                    'id',
                    'shift_start_time',
                    'shift_end_time',
                    'actual_mileage',
                    'total_fuel',
                    'fuel_consumption_mileage',
                ]);

            $shiftCount = $summaryRows->count();

            $this->totals = (object) [
                'shift_count' => $shiftCount,
                'total_loads' => (float) $summaryRows->sum('trips_count'),
                'total_weight' => (float) $summaryRows->sum('trips_sum_weight'),
                'total_distance' => (float) $summaryRows->sum(function ($s) {
                    return (float) ($s->actual_mileage ?? 0);
                }),
                'total_fuel' => (float) $summaryRows->sum(function ($s) {
                    return (float) ($s->total_fuel ?? 0);
                }),
                'avg_fuel_consumption_mileage' => $shiftCount
                    ? (float) round($summaryRows->avg(function ($s) {
                        return is_numeric($s->fuel_consumption_mileage) ? (float) $s->fuel_consumption_mileage : null;
                    }) ?? 0, 2)
                    : 0,
            ];

            // Night hours: 21:00 -> 04:00
            // Day hours:   05:00 -> 20:00
            foreach ($summaryRows as $s) {
                if (empty($s->shift_start_time) || empty($s->shift_end_time)) {
                    continue;
                }

                $start = Carbon::parse($s->shift_start_time);
                $end   = Carbon::parse($s->shift_end_time);

                // If end is before start, assume it crosses midnight
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $this->totalNightSeconds += $this->overlapSecondsWithNightWindow($start, $end);
                $this->totalDaySeconds   += $this->overlapSecondsWithDayWindow($start, $end);
            }

    }


    private function overlapSecondsWithDayWindow(Carbon $start, Carbon $end): int
    {
        // Day window: 05:00 - 20:00 (same day)
        return $this->overlapSecondsWithWindow($start, $end, '05:00:00', '20:00:00');
    }

    private function overlapSecondsWithNightWindow(Carbon $start, Carbon $end): int
    {
        // Night window crosses midnight: 21:00 - 04:00
        // We handle it as two windows:
        //   21:00 - 23:59:59 (day D)
        //   00:00 - 04:00 (day D+1)
        $sec1 = $this->overlapSecondsWithWindow($start, $end, '21:00:00', '23:59:59');
        $sec2 = $this->overlapSecondsWithWindow($start, $end, '00:00:00', '04:00:00');
        return $sec1 + $sec2;
    }

    private function overlapSecondsWithWindow(Carbon $start, Carbon $end, string $windowStart, string $windowEnd): int
    {
        // Iterate day-by-day across the interval so it works for multi-day shifts too
        $total = 0;

        $cursor = $start->copy()->startOfDay();
        $lastDay = $end->copy()->startOfDay();

        while ($cursor->lte($lastDay)) {
            $wStart = $cursor->copy()->setTimeFromTimeString($windowStart);
            $wEnd   = $cursor->copy()->setTimeFromTimeString($windowEnd);

            // clamp overlap between [start,end] and [wStart,wEnd]
            $oStart = $start->greaterThan($wStart) ? $start : $wStart;
            $oEnd   = $end->lessThan($wEnd) ? $end : $wEnd;

            if ($oEnd->greaterThan($oStart)) {
                $total += $oEnd->diffInSeconds($oStart);
            }

            $cursor->addDay();
        }

        return $total;
    }

    private function formatSecondsToHoursMinutes(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%02dh %02dm', $hours, $minutes);
    }

    public function query()
    { 
          $baseQuery = Shift::query()
        ->with(['trips:id,shift_id,loading_point_id,offloading_point_id','customer:id,name','driver','horse','vehicle','cargo','transporter','fuel']);

        /**
         * 1) Date filtering: range if from/to, else default current month/year
         */
        $baseQuery->when(
            filled($this->from) && filled($this->to),
            fn (Builder $q) => $q->whereBetween($this->shift_filter, [
                Carbon::parse($this->from)->startOfDay(),
                Carbon::parse($this->to)->endOfDay(),
            ]),
            fn (Builder $q) => $q->whereMonth($this->shift_filter, now()->month)
                ->whereYear($this->shift_filter, now()->year)
        );

        /**
         * 2) Dropdown / selected filters
         *    (Assuming these values are IDs)
         */
        $baseQuery
        ->when(filled($this->filters['filter_transporter_id']), fn (Builder $q) => $q->where('transporter_id', $this->filters['filter_transporter_id']))
        ->when(filled($this->filters['filter_team_id']), fn (Builder $q) => $q->where('team_id', $this->filters['filter_team_id']))
        ->when(filled($this->filters['filter_customer_id']), fn (Builder $q) => $q->where('customer_id', $this->filters['filter_customer_id']))
        ->when(filled($this->filters['filter_driver_id']), fn (Builder $q) => $q->where('driver_id', $this->filters['filter_driver_id']))
        ->when(filled($this->filters['filter_horse_id']), fn (Builder $q) => $q->where('horse_id', $this->filters['filter_horse_id']))
        ->when(filled($this->filters['filter_vehicle_id']), fn (Builder $q) => $q->where('vehicle_id', $this->filters['filter_vehicle_id']))
        ->when(filled($this->filters['filter_cargo_id']), fn (Builder $q) => $q->where('cargo_id', $this->filters['filter_cargo_id']))
        ->when(filled($this->filters['filter_shift_type']), fn (Builder $q) => $q->where('type', $this->filters['filter_shift_type']))
        ->when(filled($this->filters['filter_user_id']), fn (Builder $q) => $q->where('user_id', $this->filters['filter_user_id']))

        // trips-based loading/offloading filters
        ->when(filled($this->filters['filter_from_destination']), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('from', $this->filters['filter_from_destination']))
        )
        ->when(filled($this->filters['filter_to_destination']), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('to', $this->filters['filter_to_destination']))
        )
        ->when(filled($this->filters['filter_haulage_type']), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('haulage_type', $this->filters['filter_haulage_type']))
        )
        ->when(filled($this->filters['filter_loading_point_id']), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('loading_point_id', $this->filters['filter_loading_point_id']))
        )
        ->when(filled($this->filters['filter_offloading_point_id']), fn (Builder $q) =>
            $q->whereHas('trips', fn (Builder $t) => $t->where('offloading_point_id', $this->filters['filter_offloading_point_id']))
        );

        /**
         * 3) Search filtering (free text)
         */
        $baseQuery->when(filled($this->search), function (Builder $q) {
            $term = trim($this->search);
            $like = "%{$term}%";

            $q->where(function (Builder $query) use ($term, $like) {
                $query->where('shift_number', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhere('date', 'like', $like)
                    ->orWhere('for', 'like', $like)
                    ->orWhereHas('customer', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('team', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('horse', function (Builder $qq) use ($like) {
                        $qq->where('registration_number', 'like', $like)
                        ->orWhere('fleet_number', 'like', $like);
                    })
                    ->orWhereHas('vehicle', function (Builder $qq) use ($like) {
                        $qq->where('registration_number', 'like', $like)
                        ->orWhere('fleet_number', 'like', $like);
                    })
                    ->orWhereHas('cargo', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('transporter', fn (Builder $qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('driver.employee', function (Builder $qq) use ($like) {
                        $qq->where(DB::raw("CONCAT(name,' ',surname)"), 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                    });
            });
        });

        return $baseQuery->orderByDesc($this->shift_filter);
       
       
    }


    public function map($shift): array{

                $equipment = "";
                if ($shift->equipment == "Horse") {
                    $reg_number = $shift->horse->registration_number ?? Null;
                    $fleet_number = optional($shift->horse)->fleet_number ? "(" . optional($shift->horse)->fleet_number . ")" : null;
                    $equipment = $reg_number." ".$fleet_number;
                }elseif ($shift->equipment == "Vehicle") {
                    $reg_number = $shift->vehicle->registration_number ?? Null;
                    $fleet_number = optional($shift->vehicle)->fleet_number ? "(" . optional($shift->vehicle)->fleet_number . ")" : null;
                    $equipment = $reg_number." ".$fleet_number;
                }

              
                $employee = optional(optional($shift->driver)->employee);
                $driver = $employee->name && $employee->surname
                    ? $employee->name . ' ' . $employee->surname
                    : '';

                 $start = Carbon::parse($shift->shift_start_time);
                $end = Carbon::parse($shift->shift_end_time);

                // If you have dates for the shift times, parse them directly
                // Otherwise, handle cases where only the time is given

                // If only time is stored and end is "before" start, assume it's the next day
                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                // Get total seconds difference (works for > 24 hours too)
                $diffInSeconds = $end->diffInSeconds($start);

                // Convert to hours, minutes, and seconds
                $hours = floor($diffInSeconds / 3600);
                $minutes = floor(($diffInSeconds % 3600) / 60);
                $seconds = $diffInSeconds % 60;

                // Format as HH:MM:SS, even if hours > 24
                 $durationFormatted = sprintf('%02dH: %02dM: %02dS', $hours, $minutes, $seconds);
      
                return   [
                    $shift->shift_number ,
                    $shift->type ,
                    $shift->for ,
                    $shift->date ,
                    $shift->shift_start_time ,
                    $shift->shift_end_time ,
                    $durationFormatted,
                    $shift->customer ? $shift->customer->name : "",
                    $shift->cargo ? $shift->cargo->name : "",
                    $equipment,
                    $driver,
                    $shift->total_loads,
                    $shift->total_weight,
                    $shift->calculated_mileage,
                    $shift->actual_mileage,
                    $shift->total_fuel,
                    $shift->fuel_consumption_mileage,
                     ];

    }

    public function headings(): array{
            return[
                'Shift#',
                'Type',
                'For',
                'Date',
                'Start Time',
                'Close Time',
                'Duration',
                'Customer',
                'Cargo',
                'Equipment',
                'Driver',
                'Total Loads',
                'Total Weight',
                'Calculated Mileage',
                'Actual Mileage',
                'Fuel',
                'F/C (Mileage) (l/Km)',
            ];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Styling for headings (now on A17:Q17)
                $event->sheet->getStyle('A17:Q17')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // Inject totals below the logo (starting row 6 or 7)
                $row = 7;

                $event->sheet->setCellValue("A{$row}", 'Totals Summary');
                $event->sheet->mergeCells("A{$row}:Q{$row}");
                $event->sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Shifts');
                $event->sheet->setCellValue("L{$row}", $this->totals->shift_count ?? 0);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Loads');
                $event->sheet->setCellValue("L{$row}", $this->totals->total_loads ?? 0);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Night Hours (21:00 - 04:00)');
                $event->sheet->setCellValue("L{$row}", $this->formatSecondsToHoursMinutes($this->totalNightSeconds));

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Day Hours (05:00 - 20:00)');
                $event->sheet->setCellValue("L{$row}", $this->formatSecondsToHoursMinutes($this->totalDaySeconds));

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Weight');
                $event->sheet->setCellValue("L{$row}", $this->totals->total_weight ?? 0);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Total Fuel');
                $event->sheet->setCellValue("L{$row}", $this->totals->total_fuel ?? 0);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Distance Travelled (Km)');
                $event->sheet->setCellValue("L{$row}", $this->totals->total_distance ?? 0);

                $row++;

                $event->sheet->setCellValue("K{$row}", 'Average Fuel Consumption (l/Km)');
                $event->sheet->setCellValue("L{$row}", round($this->totals->avg_fuel_consumption_mileage, 2) ?? 0);

                // Optional: style the values
                $event->sheet->getStyle("K8:L15")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        if (isset(Auth::user()->employee->company)) {
        $drawing->setName(Auth::user()->employee->company->name);
        $drawing->setDescription(Auth::user()->employee->company->name . 'Logo');
        if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
          if (file_exists(public_path('/images/uploads/'.Auth::user()->employee->company->logo))){
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
        } 
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');
        return $drawing;
    }

    public function startCell(): string{
        return 'A17';
    }
}
