<?php

namespace App\Exports;

use Carbon\Carbon;
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

class HorsesAgeExport implements  FromQuery,
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

        
        if (isset($horse->year) && is_numeric($horse->year)) {

            $current_year = (int) date('Y');

            if (is_numeric($current_year)) {
                $age = $current_year - (int) $horse->year;
            } else {
                $age = "";
            }

        } else {
            $age = "";
        }
    
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        $today = Carbon::today();
        if ((preg_match($pattern, $horse->start_date)) ){
            $start_date = Carbon::parse($horse->start_date);
            $yearsDifference = $start_date->diffInYears($today);
        }else {
            $yearsDifference = "";
        }
        if ((preg_match($pattern, $horse->end_date)) ){
            $end_date = Carbon::parse($horse->end_date);
            $yearsOfHorseDifference = $start_date->diffInYears($end_date);
        }else {
            $yearsOfHorseDifference = "";
        }
       

        return   [
            $horse->transporter ? $horse->transporter->name : "",
            $make." ".$model." ".$horse->registration_number ." ". $fleet_number ,
            $horse->year,
            $age ? $age." Year(s)" : "",
            $horse->start_date,
            $yearsDifference ? $yearsDifference." Year(s)" : "",
            $horse->end_date,
            $yearsOfHorseDifference ? $yearsOfHorseDifference." Year(s)" : "",
             ];


}
public function headings(): array{
        return[
            'Transporter',
            'Horse',
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
