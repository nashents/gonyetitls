<?php

namespace App\Exports;

use App\Models\User;
use App\Models\TyreAssignment;
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

class TrailerTyreAssignmentExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    /**
    * return \Illuminate\Support\Collection
    */

    public $trailer_id;


    public function __construct($trailer_id = null) {
        $this->trailer_id = $trailer_id;
    }

    public function query()
    {
        return TyreAssignment::query()->with('trailer')->where('trailer_id',$this->trailer_id)
        ->whereYear('created_at', date('Y'))->orderBy('created_at','desc');
         
     
    }
    public function map($tyre_assignment): array{
           
            $product_name = $tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : "";
            $brand_name = $tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : "";
             $width = $tyre_assignment->tyre ? $tyre_assignment->tyre->width : "";
             $ratio = $tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : "";
             $diameter = $tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : "";                             


            return   [
               $tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : "",
               $product_name." ".$brand_name,
               $tyre_assignment->tyre ? $tyre_assignment->tyre->serial_number : "",
                $width."/".$ratio."R".$diameter,
                $tyre_assignment->position,
                $tyre_assignment->axle,
               $tyre_assignment->starting_odometer ? $tyre_assignment->starting_odometer." Kms" : "",
               $tyre_assignment->ending_odometer ? $tyre_assignment->ending_odometer."Kms" : ""
                 ];


    }
    public function headings(): array{
            return[
                'Tyre#',
                'Product ',
                'Serial#',
                'Specifications',
                'Axle',
                'Position',
                'Starting Mileage',
                'Ending Mileage',
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:J7')->applyFromArray([
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
