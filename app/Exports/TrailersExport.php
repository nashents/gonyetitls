<?php

namespace App\Exports;

use App\Models\Trailer;
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

class TrailersExport implements  FromQuery,
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
        return Trailer::query();
    }
    public function map($trailer): array{
        $status = $trailer->status == 1 ? "available" : "unavailable";
        $trailer_capacities = $trailer->capacities;
        if (isset($trailer_capacities)) {
            foreach($trailer_capacities as $trailer_capacity){
                $cargo_name = $trailer_capacity->cargo ? $trailer_capacity->cargo->name : "";
                $measurement = $trailer_capacity->measurement ? $trailer_capacity->measurement->name : "";
                $capacities [] =   $cargo_name ." ".$trailer_capacity->capacity." ".$measurement;
            }
        }

        if (isset($capacities)) {
            $capacities_list = implode(', ',$capacities);
        }else {
            $capacities_list = "";
        }

        if (isset($trailer->year)) {
            $current_year = date('Y');
            $age = $current_year-$trailer->year;
        }else {
            $age = "";
        }
       
            return   [
                $trailer->trailer_number,
                $trailer->fleet_number,
                $trailer->transporter ? $trailer->transporter->name : "",
                $trailer->trailer_type ? $trailer->trailer_type->name : "",
                $trailer->trailer_group ? $trailer->trailer_group->name : "",
                $trailer->make,
                $trailer->model ,
                $trailer->registration_number,
                $trailer->year,
                $age." Year(s)",
                $trailer->color,
                $trailer->gvm,
                $trailer->nvm,
                $capacities_list,
                $trailer->compartments,
                $trailer->cargo_type,
                $trailer->condition,
                $trailer->manufacturer,
                $trailer->country_of_origin,
                $trailer->chasis_number,
                $trailer->mileage,
                $trailer->no_of_wheels,
                $status
                 ];


    }
    public function headings(): array{
            return[
                'Trailer#',
                'Fleet#',
                'Transporter',
                'Trailer Type',
                'Trailer Group',
                'Make ',
                'Model',
                'TRN',
                'Year',
                'Age',
                'Color',
                'GVM',
                'NVM',
                'Capacities',
                'Compartment Details',
                'Cargo Type',
                'Condition',
                'Manufacturer',
                'Origin',
                'Chasis#',
                'Mileage',
                'No of Wheels',
                'Availability',



            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:W7')->applyFromArray([
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
