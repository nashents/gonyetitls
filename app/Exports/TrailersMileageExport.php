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

class TrailersMileageExport implements  FromQuery,
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
        
        $fleet_number = $trailer->fleet_number ? "(".$trailer->fleet_number.")" : "";

        if ((isset($trailer->mileage) && $trailer->mileage > 0) && (isset($trailer->next_service) && $trailer->next_service > 0)) {
            if ($trailer->mileage >= $trailer->next_service) {
                $status = "Due for service";
            }elseif ($trailer->mileage < $trailer->next_service) {
                $status = "Fit for use";
            }
            $difference = $trailer->next_service - $trailer->mileage;
        }else {
            $difference = "";
            $status = "";
        }
      
       

        return   [
            $trailer->transporter ? $trailer->transporter->name : "",
            $trailer->make ." ". $trailer->model ." ". $trailer->registration_number ." ".  $fleet_number,
            $trailer->prev_service ? $trailer->prev_service."Kms" : "" ,
            $trailer->prev_service_date ,
            $trailer->mileage ? $trailer->mileage."Kms" : "" ,
            $trailer->next_service ? $trailer->next_service."Kms" : "" ,
            $difference ? $difference."Kms" : "",
            $status,
          
             ];


}
public function headings(): array{
        return[
            'Transporter',
            'Trailer',
            'Prev Service Mileage',
            'Prev Service Date',
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
