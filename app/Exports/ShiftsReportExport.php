<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Events\AfterSheet;
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

class ShiftsReportExport implements  FromQuery,
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
  
    public $filters;
    public $totals;
   

    public function __construct($filters)
    {
       
            $this->filters = $filters;
           

            $query = $this->query();

            // Clone the query for clean execution
            $this->totals = (clone $query)->selectRaw('
                SUM(total_loads) as total_loads,
                SUM(total_fuel) as total_fuel,
                AVG(fuel_consumption_mileage) as avg_fuel_consumption_mileage,
                AVG(fuel_consumption_hours) as avg_fuel_consumption_hours,
                SUM(total_weight) as total_weight,
                SUM(actual_mileage) as total_distance,
                COUNT(*) as shift_count
            ')->first();
    }
    public function query()
    { 
        
        $query = Shift::query();


        if ($this->filters['selectedTransporter']) {
            $query->where('transporter_id', $this->filters['selectedTransporter']);
        }

        if ($this->filters['selectedCustomer']) {
            $query->where('customer_id', $this->filters['selectedCustomer']);
        }

        if ($this->filters['selectedDriver']) {
            $query->where('driver_id', $this->filters['selectedDriver']);
        }

        if ($this->filters['selectedEmployee']) {
            $query->where('employee_id', $this->filters['selectedEmployee']);
        }

        if ($this->filters['selectedHorse']) {
            $query->where('horse_id', $this->filters['selectedHorse']);
        }

        if ($this->filters['from'] && $this->filters['to']) {
            $query->whereBetween($this->filters['shift_filter'], [$this->filters['from'], $this->filters['to']]);
        }else{
             $query->whereMonth($this->filters['shift_filter'], date('m'))
           ->whereYear($this->filters['shift_filter'], date('Y'));;
        }

        if ($this->filters['selectedCargo']) {
            $query->where('cargo_id', $this->filters['selectedCargo']);
        }

        if ($this->filters['selectedVehicle']) {
            $query->where('vehicle_id', $this->filters['selectedVehicle']);
        }

        if ($this->filters['type']) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['selectedLoadingPoint'])) {
        $query->whereHas('loading_points', function ($q) {
            $q->where('loading_points.id', $this->filters['selectedLoadingPoint']);
        });
        }

        if (!empty($this->filters['selectedOffloadingPoint'])) {
            $query->whereHas('offloading_points', function ($q) {
                $q->where('offloading_points.id', $this->filters['selectedOffloadingPoint']);
            });
        }

        return $query;
       
       
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
                    $durationFormatted ,
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
                // Styling for headings (now on A15)
                $event->sheet->getStyle('A15:P15')->applyFromArray([
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
                $event->sheet->getStyle("K8:L13")->applyFromArray([
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
        return 'A15';
    }
}
