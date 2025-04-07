<?php

namespace App\Exports;

use App\Models\Horse;
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

class HorsesExport implements  FromQuery,
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
        return Horse::query();
    }
    public function map($horse): array{
        $status = $horse->status == 1 ? "available" : "unavailable";

        if (isset($horse->year)) {
            $current_year = date('Y');
            $age = $current_year-$horse->year;
        }else {
            $age = "";
        }

            return   [
                $horse->horse_number,
                $horse->fleet_number,
                $horse->transporter ? $horse->transporter->name : "",
                $horse->horse_type ? $horse->horse_type->name : "",
                $horse->horse_group ? $horse->horse_group->name : "",
                $horse->horse_make ? $horse->horse_make->name : "",
                $horse->horse_model ? $horse->horse_model->name : "" ,
                $horse->registration_number,
                $horse->year,
                $age." Year(s)",
                $horse->color,
                $horse->gvm,
                $horse->nvm,
                $horse->condition,
                $horse->manufacturer,
                $horse->country_of_origin,
                $horse->chasis_number,
                $horse->engine_number,
                $horse->mileage,
                $horse->fuel_type,
                $horse->fuel_measurement,
                $horse->fuel_consumption,
                $horse->track_usage,
                $horse->no_of_wheels,
                $status
                 ];


    }
    public function headings(): array{
            return[
                'Horse#',
                'Fleet#',
                'Transporter',
                'Horse Type',
                'Horse Group',
                'Make ',
                'Model',
                'HRN',
                'Year',
                'Age',
                'Color',
                'GVM',
                'NVM',
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
                'No of Wheels',
                'Availability',



            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:Y7')->applyFromArray([
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
