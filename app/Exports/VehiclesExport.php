<?php

namespace App\Exports;

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

class VehiclesExport implements  FromQuery,
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
        $status = $vehicle->status == 1 ? "available" : "unavailable";
            return   [
                $vehicle->vehicle_number,
                $vehicle->vehicle_type ? $vehicle->vehicle_type->name : "",
                $vehicle->vehicle_group ? $vehicle->vehicle_group->name : "",
                $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "",
                $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "",
                $vehicle->registration_number,
                $vehicle->year,
                $vehicle->color,
                $vehicle->condition,
                $vehicle->manufacturer,
                $vehicle->country_of_origin,
                $vehicle->chasis_number,
                $vehicle->engine_number,
                $vehicle->mileage,
                $vehicle->fuel_type,
                $vehicle->fuel_measurement,
                $vehicle->fuel_consumption,
                $vehicle->track_usage,
                $vehicle->no_of_wheels,
                $status
                 ];


    }
    public function headings(): array{
            return[
                'Vehicle#',
                'Vehicle Type',
                'Vehicle Group',
                'Make ',
                'Model',
                'VRN',
                'Year',
                'Color',
                'Condition',
                'Manufacturer',
                'Origin',
                'Chasis#',
                'Engine#',
                'Mileage',
                'Fuel Type',
                'Fuel Measurement',
                'Fuel Consumption',
                'Track Usage',
                'No Of Wheels',
                'Status',



            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:T7')->applyFromArray([
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
