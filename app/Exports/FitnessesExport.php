<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Fitness;
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

class FitnessesExport implements  FromQuery,
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

    public $category;
    public $filter_id;
   

    public function __construct($filter_id, $category)
    {
    
            $this->category = $category;
            $this->filter_id = $filter_id;
          
           
    }

    public function query()
    {
        if ($this->category == "Horse") {
            return Fitness::query()->where('horse_id', $this->filter_id)->where('closed',false)->where('status',true);
        }elseif ($this->category == "Trailer") {
            return Fitness::query()->where('trailer_id', $this->filter_id)->where('closed',false)->where('status',true);
        }elseif ($this->category == "Vehicle") {
            return Fitness::query()->where('vehicle_id', $this->filter_id)->where('closed',false)->where('status',true);
        }elseif ($this->category == "Employee") {
            return Fitness::query()->where('employee_id', $this->filter_id)->where('closed',false)->where('status',true);
        }
     
    }
    public function map($fitness): array{
        

        if ($fitness->horse) {
            $horse_reg_number = $fitness->horse->registration_number;
            $horse_fleet_number = $fitness->horse->fleet_number ? "(".$fitness->horse->fleet_number.")" : "";
            $horse_make = $fitness->horse->horse_make ? $fitness->horse->horse_make->name : "";
            $horse_model = $fitness->horse->horse_model ? $fitness->horse->horse_model->name : "";
            $reminder_for =  "Horse | ".$horse_reg_number." ". $horse_fleet_number." ".$horse_make." ".  $horse_model;
        }elseif ($fitness->vehicle) {
            $vehicle_reg_number = $fitness->vehicle->registration_number;
            $vehicle_fleet_number = $fitness->vehicle->fleet_number ? "(".$fitness->vehicle->fleet_number.")" : "";
            $vehicle_make = $fitness->vehicle->vehicle_make ? $fitness->vehicle->vehicle_make->name : "";
            $vehicle_model = $fitness->vehicle->vehicle_model ? $fitness->vehicle->vehicle_model->name : "";
            $reminder_for =  "Vehicle | ".$vehicle_reg_number." ". $vehicle_fleet_number." ".$vehicle_make." ".  $vehicle_model;
          
        }elseif ($fitness->employee) {
            $reminder_for = "Employee | ".$fitness->employee->name ." ". $fitness->employee->surname;
        }elseif ($fitness->trailer) {
            $trailer_reg_number = $fitness->trailer->registration_number;
            $trailer_fleet_number = $fitness->trailer->fleet_number ? "(".$fitness->trailer->fleet_number.")" : "";
            $reminder_for =  "Trailer | ".$trailer_reg_number." ". $trailer_fleet_number;
        }else {
            $reminder_for = "";
        }

            return   [
                $fitness->reminder_item ? $fitness->reminder_item->name : "",
                $reminder_for,
                Carbon::parse($fitness->issued_at)->format('d M Y h:i a'),
                Carbon::parse($fitness->expires_at)->format('d M Y h:i a'),
                Carbon::parse($fitness->first_reminder_at)->format('d M Y h:i a'),
                Carbon::parse($fitness->second_reminder_at)->format('d M Y h:i a'),
                Carbon::parse($fitness->third_reminder_at)->format('d M Y h:i a'),
                 ];


    }
    public function headings(): array{
            return[
                'Reminder',
                'Reminder For',
                'Issued @ ',
                'Expires @ ',
                '1st Reminder @',
                '2nd Reminder @',
                '3rd Reminder @',
            ];


    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:G7')->applyFromArray([
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
