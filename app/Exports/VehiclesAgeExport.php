<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Vehicle;
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

class VehiclesAgeExport implements  FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Vehicle::query();
    }
    public function map($vehicle): array{
        
        $make = $vehicle->vehicle_make? $vehicle->vehicle_make->name : "";
        $model = $vehicle->vehicle_model? $vehicle->vehicle_model->name : "";
        $fleet_number = $vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : "";
        if (isset($vehicle->year) && is_numeric($vehicle->year)) {

            $current_year = (int) date('Y');

            if (is_numeric($current_year)) {
                $age = $current_year - (int) $vehicle->year;
            } else {
                $age = "";
            }

        } else {
            $age = "";
        }
    
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        $today = Carbon::today();
        if ((preg_match($pattern, $vehicle->start_date)) ){
            $start_date = Carbon::parse($vehicle->start_date);
            $yearsDifference = $start_date->diffInYears($today);
        }else {
            $yearsDifference = "";
        }
        if ((preg_match($pattern, $vehicle->end_date)) ){
            $end_date = Carbon::parse($vehicle->end_date);
            $yearsOfVehicleDifference = $start_date->diffInYears($end_date);
        }else {
            $yearsOfVehicleDifference = "";
        }
       

        return   [
            $vehicle->transporter ? $vehicle->transporter->name : "",
            $make." ".$model." ".$vehicle->registration_number ." ".  $fleet_number,
            $vehicle->year,
            $age ? $age." Year(s)" : "",
            $vehicle->start_date,
            $yearsDifference ? $yearsDifference." Year(s)" : "",
            $vehicle->end_date,
            $yearsOfVehicleDifference ? $yearsOfVehicleDifference." Year(s)" : "",
             ];


}
public function headings(): array{
        return[
            'Transporter',
            'Vehicle',
            'Year of Manufacture',
            'Age',
            'Purchased',
            'Years Owned',
            'Disposed',
            'Total Years Used',
        ];


}
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:H7')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ]
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
            $drawing->setPath(public_path('/images/uploads/'.Auth::user()->employee->company->logo));
        }else{
            $drawing->setPath(public_path('/images/uploads/logo.png'));
        }
            } 
        $drawing->setHeight(90);
        $drawing->setCoordinates('A2');

        return $drawing;
    }

    public function startCell(): string{
        return 'A7';
    }
}
