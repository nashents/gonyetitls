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
        $trips = Trip::query()->with(['customer:id,name','transporter:id,name', 'trip_type:id,name','currency:id,name,symbol', 'agent:id,name,surname', 'border:id,name','clearing_agent:id,name','driver.employee:id,name,surname','trailers:id,make,model,registration_number','truck_stops:id,name','transporter:id,name','horse:id,registration_number',
            'horse.horse_make:id,name','horse.horse_model:id,name','loading_point:id,name','offloading_point:id,name','invoice_items','trip_documents']);

        // offloaded_date lives on delivery_notes, not trips — filtering it as
        // trips.offloaded_date would throw an "unknown column" error. Whitelist
        // trip_filter before it reaches raw SQL, since it comes from a public
        // Livewire property that can be tampered with client-side.
        if ($this->trip_filter === 'offloaded_date') {
            $trips->whereHas('delivery_note', function ($q) {
                if (filled($this->from) && filled($this->to)) {
                    // offloaded_date is a VARCHAR storing datetime-local values
                    // (e.g. "2026-09-30T14:30"), so a plain string whereBetween
                    // against "2026-09-30" excludes any trip offloaded later that
                    // day — DATE() extracts just the date portion for comparison.
                    $q->whereRaw('DATE(offloaded_date) BETWEEN ? AND ?', [$this->from, $this->to]);
                } elseif (! filled($this->search)) {
                    // Skip the default "this month" restriction while searching so
                    // matches outside the current period aren't hidden.
                    $q->whereMonth('offloaded_date', date('m'))
                        ->whereYear('offloaded_date', date('Y'));
                }
            });
        } else {
            $dateColumn = in_array($this->trip_filter, ['created_at', 'start_date', 'end_date', 'trip_status_date'], true)
                ? $this->trip_filter
                : 'created_at';

            if (filled($this->from) && filled($this->to)) {
                $trips->whereRaw("DATE({$dateColumn}) BETWEEN ? AND ?", [$this->from, $this->to]);
            } elseif (! filled($this->search)) {
                $trips->whereMonth($dateColumn, date('m'))
                    ->whereYear($dateColumn, date('Y'));
            }
        }

        if (filled($this->search)) {
            $search = $this->search;

            // Grouped in a closure so these OR conditions stay ANDed with the
            // date filter above instead of overriding it entirely.
            $trips->where(function ($query) use ($search) {
                $query->where('trip_number', 'like', "%{$search}%")
                    ->orWhere('trip_status', 'like', "%{$search}%")
                    ->orWhere('authorization', 'like', "%{$search}%")
                    ->orWhereHas('horse', function ($q) use ($search) {
                        $q->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('fleet_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('delivery_note', function ($q) use ($search) {
                        $q->where('offloaded_date', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user.employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transporter', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('loading_point', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('offloading_point', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trip_documents', function ($q) use ($search) {
                        $q->where('document_number', 'like', "%{$search}%");
                    });
            });
        }

        return $trips->orderBy('trip_number', 'desc');
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
