<?php

namespace App\Exports;

use App\Models\Tyre;
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

class TyresExport implements  FromQuery,
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
        return Tyre::query();
    }
    public function map($tyre): array{
        $tyre_details = $tyre->tyre_details;
    if ($tyre_details->count()) {
        foreach ($tyre_details as $tyre_detail) {
            $tyre_tyre_details[] = $tyre_detail->width . ' / ' .$tyre_detail->aspect_ratio . ' R ' . $tyre_detail->diameter;
        }
        $tyre_details_string = implode(", ",$tyre_tyre_details);
    }

    $make = $tyre->vehicle ? $tyre->vehicle->make : "";
    $model = $tyre->vehicle ? $tyre->vehicle->model : "";
    $regnumber = $tyre->vehicle ? $tyre->vehicle->registration_number : "";
    $vehicle = $make ." ".$model." ".$regnumber;

    if (isset($tyre_details_string)) {
        return   [
            $tyre->date,
            $tyre->order_number,
            $vehicle,
            $tyre->vendor->name,
            $tyre->odometer,
            $tyre->position,
            $tyre->tyre_condition,
            $tyre->quantity,
            $tyre->rate,
            $tyre->amount,
            $tyre->comments,
            $tyre_details_string
             ];
    }else {
        return   [
            $tyre->date,
            $tyre->order_number,
            $vehicle,
            $tyre->vendor->name,
            $tyre->odometer,
            $tyre->position,
            $tyre->tyre_condition,
            $tyre->quantity,
            $tyre->rate,
            $tyre->amount,
            $tyre->comments,
             ];
    }




    }
    public function headings(): array{
            return[
                'Date',
                'Order #',
                'Vehicle',
                'vendor',
                'Odometer',
                'Position',
                'Condition',
                'Quantity',
                'Rate',
                'Amount',
                'Comments',
                'Tyre Details',
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:K7')->applyFromArray([
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
