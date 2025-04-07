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

class PodTracker implements FromQuery,
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
    public $from;
    public $to;
    public $trip_filter;
    public $search;
   

    public function __construct($from, $to, $trip_filter, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->trip_filter = $trip_filter;
            $this->search = $search;
           
    }
    public function query()
    { 
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
            return Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
            'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->where('trip_number','like', '%'.$this->search.'%')
            ->orWhere('trip_status','like', '%'.$this->search.'%')
            ->orWhere('authorization','like', '%'.$this->search.'%')
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('customer', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('delivery_note', function ($query) {
                return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('user.employee', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('transporter', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('loading_point', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('offloading_point', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('trip_documents', function ($query) {
                return $query->where('document_number', 'like', '%'.$this->search.'%');
            })
            ->orderBy('trip_number','desc');
            }else{
                return Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
                'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy('trip_number','desc');
            }
           
        }elseif ($this->search) {
            return Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
            'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->where('trip_number','like', '%'.$this->search.'%')
            ->orWhere('trip_status','like', '%'.$this->search.'%')
            ->orWhere('authorization','like', '%'.$this->search.'%')
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('customer', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('delivery_note', function ($query) {
                return $query->where('offloaded_date', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('fleet_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('user.employee', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('transporter', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('loading_point', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('offloading_point', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('trip_documents', function ($query) {
                return $query->where('document_number', 'like', '%'.$this->search.'%');
            })
            ->orderBy('trip_number','desc');
        }
        else {
            return Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
            'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents'])->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))->orderBy('trip_number','desc');
        }
       
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
                $trailer_list = implode(',',$trailers);
            }else {
                $trailer_list = "";
            }
           

            if ($trip->truck_stops->count()>0) {
                foreach ($trip->truck_stops as $truck_stop) {
                    $truck_stops[] = $truck_stop->name; 
                }
                $truck_stop_list = implode(',',$truck_stops);
            }else{
                $truck_stop_list = "";
            }
          

            $from_country = Destination::find($trip->from) ? Destination::find($trip->from)->country->name : "";
            $from_city =    Destination::find($trip->from) ? Destination::find($trip->from)->city : "";
            $to_country =    Destination::find($trip->to) ? Destination::find($trip->to)->country->name : "";
            $to_city =  Destination::find($trip->to) ? Destination::find($trip->to)->city : "";
            if ($trip->driver) {
                $driver_name =  $trip->driver->employee ? $trip->driver->employee->name : ""; 
                $driver_surname =  $trip->driver->employee ? $trip->driver->employee->surname : ""; 
            }else{
                $driver_name = "";
                $driver_surname = "";
            }
          
           
        
            $symbol = $trip->currency ? $trip->currency->symbol : "";

            if ($trip->delivery_note) {
                $offloading_date = $trip->delivery_note->offloaded_date ;
            }else {
                $offloading_date = "";
            }

            $deliver_note = $trip->delivery_note;
            if (isset($delivery_note)) {
                $offloaded_weight = $deliver_note->offloaded_weight;
                $offloaded_quantity = $deliver_note->offloaded_quantity;
                $offloaded_litreage = $deliver_note->offloaded_litreage;
                $offloaded_litreage_at_20 = $deliver_note->offloaded_litreage_at_20;
            }else {
                $offloaded_weight = "";
                $offloaded_quantity = "";
                $offloaded_litreage = "";
                $offloaded_litreage_at_20 = "";
            }

                $weight = $trip->weight;
                $quantity = $trip->quantity;
                $litreage = $trip->litreage;
                $litreage_at_20 = $trip->litreage_at_20;

                if ((isset($offloaded_weight) && $offloaded_weight != "" && $offloaded_weight != Null) && isset($weight) && $weight != "" && $weight != Null) {
                   $weight_loss_var = $weight - $offloaded_weight;
                   $weight_loss = $weight_loss_var;
                   $weight_loss_percentage = ($weight_loss_var / $weight) * 100;
                }else {
                    $weight_loss = "";
                    $weight_loss_percentage = "";
                }
                if ((isset($offloaded_quantity) && $offloaded_quantity != "" && $offloaded_quantity != Null) && isset($quantity) && $quantity != "" && $quantity != Null) {
                   $quantity_loss_var = $quantity - $offloaded_quantity;
                   $quantity_loss = $loss;
                   $quantity_loss_percentage = ($quantity_loss_var / $quantity) * 100;
                }else {
                    $quantity_loss = "";
                    $quantity_loss_percentage = "";
                }
                if ((isset($offloaded_litreage) && $offloaded_litreage != "" && $offloaded_litreage != Null) && isset($litreage) && $litreage != "" && $litreage != Null) {
                   $litreage_loss_var = $litreage - $offloaded_litreage;
                   $litreage_loss = $loss;
                   $litreage_loss_percentage = ($litreage_loss_var / $litreage) * 100;
                }else {
                    $litreage_loss = "";
                    $litreage_loss_percentage = "";
                }
                if ((isset($offloaded_litreage_at_20) && $offloaded_litreage_at_20 != "" && $offloaded_litreage_at_20 != Null) && isset($litreage_at_20) && $litreage_at_20 != "" && $litreage_at_20 != Null) {
                   $litreage_at_20_loss_var = $litreage_at_20 - $offloaded_litreage_at_20;
                   $litreage_at_20_loss = $loss;
                   $litreage_at_20_loss_percentage = ($litreage_at_20_loss_var / $litreage_at_20) * 100;
                }else {
                    $litreage_at_20_loss = "";
                    $litreage_at_20_loss_percentage = "";
                }
                

         

            $pod = TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
            $cargo = $trip->cargo;
            if (isset($cargo)) {
               $cargo_type = $cargo->type;
            }
              
                return   [
                    $trip->trip_number ,
                    $offloading_date,
                    $horse_full_details ?  $horse_full_details :  $vehicle_full_details,
                    $driver_name .' '. $driver_surname,
                    $trip->transporter ? $trip->transporter->name : "",
                    $pod ? $pod->document_number : "",
                    $from_country .' '. $from_city,
                    $trip->loading_point ? $trip->loading_point->name : "",
                    $to_country .' '. $to_city,
                    $trip->offloading_point ? $trip->offloading_point->name : "",
                    $trip->customer ? $trip->customer->name : "",
                    $trip->cargo ? $trip->cargo->name : "",
                    $trip->weight ? $trip->weight . ' Tons' : "" ,
                    $offloaded_weight ? $offloaded_weight .' Tons' : "" ,
                    $weight_loss ? $weight_loss . ' Tons' : "" ,
                    $weight_loss_percentage ? $weight_loss_percentage .'%' : "",
                    $trip->quantity ? $trip->quantity .' '.  $trip->measurement : "" ,
                    $offloaded_quantity ? $offloaded_quantity .' '.  $trip->measurement : "" ,
                    $quantity_loss ? $quantity_loss .' '.  $trip->measurement : "" ,
                    $quantity_loss_percentage ? $quantity_loss_percentage .'%' : "",
                    $trip->litreage ? $trip->litreage .' '.  $trip->measurement : "" ,
                    $offloaded_litreage ? $offloaded_litreage .' '.  $trip->measurement : "" ,
                    $litreage_loss ? $litreage_loss .' '.  $trip->measurement : "" ,
                    $litreage_loss_percentage ? $litreage_loss_percentage .'%' : "",
                    $trip->litreage_at_20 ? $trip->litreage_at_20 .' '.  $trip->measurement : "" ,
                    $offloaded_litreage_at_20 ? $offloaded_litreage_at_20 .' '.  $trip->measurement : "" ,
                    $litreage_at_20_loss ? $litreage_at_20_loss .' '.  $trip->measurement : "" ,
                    $litreage_at_20_loss_percentage ? $litreage_at_20_loss_percentage .'%' : "",
                    isset($pod) ? "Uploaded" : "Pending",
                    Auth::user()->employee->name .' '. Auth::user()->employee->surname,
                    "",
                   
                     ];

    }

    public function headings(): array{
            return[
                'Trip#',
                'Offloading Date',
                'Reg#',
                'Driver',
                'Transporter',
                'WayBill/POD#',
                'From',
                'Loading Point',
                'To',
                'Offloading Point',
                'Customer',
                'Cargo',
                'Weight Loaded',
                'Weight Offloaded',
                'Loss',
                'Loss %',
                'Quantity Loaded',
                'Quantity Offloaded',
                'Loss',
                'Loss %',
                'Litreage Loaded @ Ambient',
                'Litreage Offloaded @ Ambient',
                'Loss',
                'Loss %',
                'Litreage Loaded @ 20',               
                'Litreage Offloaded @ 20',
                'Loss',
                'Loss %',
                'PODS',
                'Exported By',
                'Submitted To',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:AE7')->applyFromArray([
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
