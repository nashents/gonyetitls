<?php

namespace App\Exports;

use App\Models\FuelRequest;
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

class FuelRequestsExport implements FromQuery,
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
        return FuelRequest::query();
    }
    public function map($fuel_request): array{

        $name = $fuel_request->employee ? $fuel_request->employee->name : "";
        $surname = $fuel_request->employee ? $fuel_request->employee->surname : "";
        $full_name =    $name. " ". $surname;

            return   [
                $fuel_request->request_number,
                $fuel_request->request_type,
                $fuel_request->employee ? $fuel_request->employee->employee_number : "",
                $full_name  ,
                $fuel_request->fuel_type,
                $fuel_request->quantity,
                $fuel_request->date,
                $fuel_request->status,
                $fuel_request->comments,
                 ];


    }
    public function headings(): array{
            return[
                '#',
                'Request Type',
                'Emp #',
                'Employee',
                'Fuel Type',
                'Quantity',
                'Date',
                'Status',
                'Comments',

            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A5:J5')->applyFromArray([
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
        return 'A6';
    }
}
