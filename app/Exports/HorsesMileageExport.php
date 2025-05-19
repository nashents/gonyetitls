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

class HorsesMileageExport implements  FromQuery,
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
        
        $make = $horse->horse_make? $horse->horse_make->name : "";
        $model = $horse->horse_model? $horse->horse_model->name : "";
        $fleet_number = $horse->fleet_number ? "(".$horse->fleet_number.")" : "";

        if (((isset($horse->mileage) && $horse->mileage > 0) && (isset($horse->next_service) && $horse->next_service > 0)) || ((isset($horse->hours) && $horse->hours > 0) && (isset($horse->next_service_hours) && $horse->next_service_hours > 0))) {
            if (($horse->mileage >= $horse->next_service) || ($horse->hours >= $horse->next_service_hours)) {
                $status = "Due for service";
            }elseif (($horse->mileage < $horse->next_service) || ($horse->hours < $horse->next_service_hours)) {
                $status = "Fit for use";
            }
            $difference = $horse->next_service - $horse->mileage;
            $hours_difference = $horse->next_service_hours - $horse->hours;
        }else {
            $difference = "";
            $hours_difference = "";
            $status = "";
        }
      
       

        return   [
            $horse->transporter ? $horse->transporter->name : "",
            $make." ".$model." ".$horse->registration_number ." ". $fleet_number ,
            $horse->prev_service_date ,
            $horse->prev_service ? $horse->prev_service."Kms" : "" ,
            $horse->prev_service_hours ? $horse->prev_service_hours."Hours" : "" ,
            $horse->mileage ? $horse->mileage."Kms" : "" ,
            $horse->hours ? $horse->hours."Hours" : "" ,
            $horse->next_service ? $horse->next_service."Kms" : "" ,
            $horse->next_service_hours ? $horse->next_service_hours."Hours" : "" ,
            $difference ? $difference."Kms" : "",
            $hours_difference ? $hours_difference."Hours" : "",
            $status,
          
             ];


}
public function headings(): array{
        return[
            'Transporter',
            'Horse',
            'Prev Service Date',
            'Prev Service Mileage',
            'Prev Service Hours',
            'Current Mileage',
            'Next Service Mileage',
            'Current - Next Service Mileage Diff',
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
