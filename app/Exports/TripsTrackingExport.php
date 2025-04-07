<?php

namespace App\Exports;

use App\Models\Trip;
use App\Models\Destination;
use App\Models\TripDocument;
use App\Models\TripLocation;
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

class TripsTrackingExport implements FromQuery,
ShouldAutoSize,
WithMapping,
WithHeadings,
WithEvents,
WithDrawings,
WithCustomStartCell
{
    use Exportable;
    public $commission;
    /**
    * @return \Illuminate\Support\Collection
    */
    public $trip_group_id;
 
   

    public function __construct($trip_group_id)
    {
    
            $this->trip_group_id = $trip_group_id;
           
           
    }
    public function query()
    { 
        return Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
        'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])
        ->where('trip_group_id',$this->trip_group_id)
        ->orderBy('trip_number','desc');
       
    }


    public function map($trip): array{

        if ( $trip->horse) {
            $horse_make =  $trip->horse->horse_make ? $trip->horse->horse_make->name : "";
            $horse_model = $trip->horse->horse_model ? $trip->horse->horse_model->name : "";
            $horse_registration_number = $trip->horse->registration_number;
            $horse_full_details = $horse_registration_number.' '.$horse_make .' '.$horse_model;
            }else {
                $horse_make = "";
                $horse_model = "";
                $horse_registration_number = "";
                $horse_full_details = "";
            }
        if ( $trip->vehicle) {
                $vehicle_make =  $trip->vehicle->vehicle_make ? $trip->vehicle->vehicle_make->name : "";
                $vehicle_model = $trip->vehicle->vehicle_model ? $trip->vehicle->vehicle_model->name : "";
                $vehicle_registration_number = $trip->vehicle->registration_number;
                $vehicle_full_details = $vehicle_registration_number.' '.$vehicle_make .' '.$vehicle_model;
            }else {
                $vehicle_make = "";
                $vehicle_model = "";
                $vehicle_registration_number = "";
                $vehicle_full_details = "";
            }

            foreach ($trip->trailers as $trailer) {
                $trailers[] = $trailer->registration_number; 
            }
            if (isset($trailers)) {
                $trailer_list = implode(', ',$trailers);
            }else {
                $trailer_list = "";
            }
               

            $from_country = Destination::find($trip->from) ? Destination::find($trip->from)->country->name : "";
            $from_city =    Destination::find($trip->from) ? Destination::find($trip->from)->city : "";
            $to_country =    Destination::find($trip->to) ? Destination::find($trip->to)->country->name : "";
            $to_city =  Destination::find($trip->to) ? Destination::find($trip->to)->city : "";
            $driver_name =  $trip->driver->employee ? $trip->driver->employee->name : ""; 
            $driver_surname =  $trip->driver->employee ? $trip->driver->employee->surname : ""; 
           
        
            $symbol = $trip->currency ? $trip->currency->symbol : "";

            $delivery_note = $trip->delivery_note;

            if (isset($delivery_note)) {
                $offloaded_date = $delivery_note->offloaded_date;
                $offloaded_weight = $delivery_note->offloaded_weight;
                $offloaded_quantity = $delivery_note->offloaded_quantity;
                $offloaded_litreage = $delivery_note->offloaded_litreage;
                $offloaded_litreage_at_20 = $delivery_note->offloaded_litreage_at_20;
            }else {
                $offloaded_date = "";
                $offloaded_weight = "";
                $offloaded_quantity = "";
                $offloaded_litreage = "";
                $offloaded_litreage_at_20 = "";
            }
            if (isset($delivery_note)) {
                $loaded_date = $delivery_note->loaded_date;
                $loaded_weight = $delivery_note->loaded_weight;
                $loaded_quantity = $delivery_note->loaded_quantity;
                $loaded_litreage = $delivery_note->loaded_litreage;
                $loaded_litreage_at_20 = $delivery_note->loaded_litreage_at_20;
            }else {
                $loaded_date = "";
                $loaded_weight = "";
                $loaded_quantity = "";
                $loaded_litreage = "";
                $loaded_litreage_at_20 = "";
            }

                foreach ($trip->trailers as $trailer) {
                    $trailers[] = $trailer->registration_number; 
                }
                if (isset($trailers)) {
                    $trailer_list = implode(',',$trailers);
                }else {
                    $trailer_list = "";
                }
                $current_location = TripLocation::where('trip_id',$trip->id)->latest()->first();
                if (isset($current_location)) {
                  $current_position =   $current_location->country ? $current_location->country->name : "" .' '. $current_location->city;
                }else {
                    $current_position = "";
                }
    
              
                return   [
                    $trip->trip_number ,
                    $trip->transporter ? $trip->transporter->name : "",
                    $horse_full_details ?  $horse_full_details :  $vehicle_full_details,
                    $trailer_list,
                    $trip->cargo ? $trip->cargo->name : "",
                    $driver_name .' '. $driver_surname,
                    $from_country .' '. $from_city,
                    $to_country .' '. $to_city,
                    $trip->trip_status ,
                    $trip->trip_status_date ,
                    $trip->trip_status_description ,
                    $current_position,
                    $trip->loading_point ? $trip->loading_point->name : "",
                    $loaded_date,
                    $loaded_weight .' Tons',
                    $loaded_quantity .' '.  $trip->measurement,
                    $loaded_litreage .' '.  $trip->measurement,
                    $loaded_litreage_at_20 .' '.  $trip->measurement,
                    $trip->offloading_point ? $trip->offloading_point->name : "",
                    $offloaded_date,
                    $offloaded_weight .' Tons',
                    $offloaded_quantity ? $offloaded_quantity .' '.  $trip->measurement : "" ,
                    $offloaded_litreage ? $offloaded_litreage .' '.  $trip->measurement : "" ,
                    $offloaded_litreage_at_20 ? $offloaded_litreage_at_20 .' '.  $trip->measurement : "" ,
                     ];

    }

    public function headings(): array{
            return[
                'Trip#',
                'Transporter',
                'Horse',
                'Trailer',
                'Cargo',
                'Driver',
                'From',
                'To',
                'Trip Status',
                'Trip Status Date',
                'Comments',
                'Current Location',
                'Loading Point',
                'Loading Date',
                'Loaded Weight',
                'Loaded Quantity',
                'Loaded Litreage @ Ambient',
                'Loaded Litreage @ 20',
                'Offloading Point',
                'Offloading Date',
                'Offloading Weight',
                'Offloading Quantity',
                'Offloading Litreage @ Ambient',
                'Offloading Litreage @ 20',
                
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:X7')->applyFromArray([
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
