<?php

namespace App\Exports;

use App\Models\Assignment;
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

class AssignmentsExport implements   FromQuery,
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
        return Assignment::query();
    }
    public function map($assignment): array{
        $status = $assignment->status == 1 ? "active" : "inactive";
        $make = $assignment->vehicle ? $assignment->vehicle->make : "";
        $model = $assignment->vehicle ? $assignment->vehicle->model : "" ;
        $regnumber = $assignment->driver ? $assignment->driver->employee->surname : "";
        $vehicle = $make ." ". $model ."" .$regnumber;
        $name = $assignment->driver ? $assignment->driver->employee->name : "";
        $surname = $assignment->driver ? $assignment->driver->employee->name : "";
        $employee = $name ." ".$surname;
            return   [
                $vehicle,
                $employee ,
                $assignment->starting_odometer,
                $assignment->ending_odometer,
                $assignment->start_date,
                $assignment->end_date,
                $assignment->comments,
                $status
                 ];


    }
    public function headings(): array{
            return[
                'Vehicle',
                'Driver',
                'Starting Odometer',
                'Ending Odometer',
                'Start Date',
                'End Date',
                'Comments',
                'Status',
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
