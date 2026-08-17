<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Trailer;
use App\Models\Commission;
use App\Models\Destination;
use App\Models\InvoiceItem;
use App\Models\TripExpense;
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

class TrailerTripExport implements FromQuery,
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

    public $commission;
    public $trips;
    public $trailer_id;

    public function __construct($trailer_id = null)
    {
            $this->trailer_id = $trailer_id;
            $this->trailer = Trailer::find($trailer_id);
          
           
    }
    public function query()
    {
        return Trailer::query()
        ->select('trips.*')
        ->join('trailer_trip', 'trailers.id', '=', 'trailer_trip.trailer_id')
        ->join('trips', 'trailer_trip.trip_id', '=', 'trips.id')
        ->where('trailers.id', $this->trailer_id);
    }

    public function map($trip): array{

       
       
        if ( $trip->horse) {
            $fleet_number =  $trip->horse->fleet_number ? '('.$trip->horse->fleet_number.')' : "";
            $horse_make =  $trip->horse->horse_make ? $trip->horse->horse_make->name : "";
            $horse_model = $trip->horse->horse_model ? $trip->horse->horse_model->name : "";
            $horse_registration_number = $trip->horse->registration_number;
            $horse_full_details = $horse_registration_number.' '.$fleet_number.' '.$horse_make.' '.$horse_model;
            }else {
                $horse_full_details = "";
            }

            $trailer = Trailer::find($this->trailer_id); 
            // foreach ($trip->trailers as $trailer) {
            //     $fleet_number = $trailer->fleet_number ? '('.$trailer->fleet_number.')' : "";
            //     $trailers[] = $trailer->registration_number.' '.$fleet_number; 
            // }
            // if (isset($trailers)) {
            //     $trailer_list = implode(', ',$trailers);
            // }else {
            //     $trailer_list = "";
            // }
           

            if ($trip->truck_stops->count()>0) {
                foreach ($trip->truck_stops as $truck_stop) {
                    $truck_stops[] = $truck_stop->name; 
                }
                $truck_stop_list = implode(',',$truck_stops);
            }else{
                $truck_stop_list = "";
            }
          
            $from_destination = Destination::find($trip->from);
            $to_destination =  Destination::find($trip->to);
            if (isset($from_destination)) {
                $from_country = $from_destination->country ? $from_destination->country->name : "";
                $from_city =    $from_destination->city;
            }
            else {
                $from_country = "";
                $from_city = "";
            }
            if (isset($to_destination)) { 
                $to_country =   $to_destination->country ? $to_destination->country->name : "";
                $to_city =  $to_destination->city;
            }else {
                $to_country = "";
                $to_city = "";
            }
          
           
           
           
            if ($trip->driver) {
                $driver_name =  $trip->driver->employee ? $trip->driver->employee->name : ""; 
                $driver_surname =  $trip->driver->employee ? $trip->driver->employee->surname : ""; 
            }else {
               $driver_name = "";
               $driver_surname = "";
            }
           
            if ($trip->borders->count()>0) {
                foreach ($trip->borders as $border) {
                    $borders[] = $border->name; 
                }
                $border_list = implode(',',$borders);
            }else{
                $border_list = "";
            }
           
            if ($trip->clearing_agents->count()>0) {
                foreach ($trip->clearing_agents as $clearing_agent) {
                    $clearing_agents[] = $clearing_agent->name; 
                }
                $clearing_agent_list = implode(',',$clearing_agents);
            }else{
                $clearing_agent_list = "";
            }
           

           
            $agent = $trip->agent;
            $agent_name =   $trip->agent ? $trip->agent->name : "";
            $agent_surname =  $trip->agent ? $trip->agent->surname : ""; 
            if ($agent) {
                $this->commission = Commission::where('trip_id', $trip->id)
                ->where('agent_id',$agent->id)->first();
               
            }
           if ( $this->commission) {
            $commission_percentage =  $this->commission->commission ? $this->commission->commission : "";
            $commission_amount =  $this->commission->amount ? $this->commission->amount : "";
           }else{
            $commission_percentage =  "";
            $commission_amount =  "";
           }
            $symbol = $trip->currency ? $trip->currency->symbol : "";

           

            $customer_expenses_total = TripExpense::where('currency_id',$trip->currency_id)
            ->where('trip_id',$trip->id)
            ->where('category', 'customer')->sum('amount');
            $transporter_expenses_total = TripExpense::where('currency_id',$trip->currency_id)
            ->where('trip_id',$trip->id)
            ->where('category', 'transporter')->sum('amount');
            $total_expenses = TripExpense::where('currency_id',$trip->currency_id)
            ->where('trip_id',$trip->id)
            ->where('category', 'self')->sum('amount');
           

            $latest_location = TripLocation::where('trip_id',$trip->id)->orderBy('created_at','desc')->get()->first();
            if (isset($latest_location)) {
                $location = $latest_location->country ? $latest_location->country->name : "" .' | '. $latest_location->description;
            }else{
                $location = "";
            }
          

            $total_transporter_expenses = 0;
            $total_customer_expenses = 0;
            $total_expenses = 0;
                foreach ($trip->trip_expenses as $expense) {
                    if ($expense->currency_id == Auth::user()->employee->company->currency_id) {
                        if ($expense->category == "Transporter") {
                            $total_transporter_expenses = $total_transporter_expenses + $expense->amount;
                        }
                        elseif ($expense->category == "Customer") {
                            $total_customer_expenses = $total_customer_expenses + $expense->amount;
                        }
                        elseif ($expense->category == "Self") {
                            $total_expenses = $total_expenses + $expense->amount;
                        }
                    }else{
                        if ($expense->category == "Transporter") {
                            $total_transporter_expenses = $total_transporter_expenses + $expense->exchange_amount;
                        }
                        elseif ($expense->category == "Customer") {
                            $total_customer_expenses = $total_customer_expenses + $expense->exchange_amount;
                        }
                        elseif ($expense->category == "Self") {
                            $total_expenses = $total_expenses + $expense->exchange_amount;
                        }
                    }
                }

           
         
           
            if (isset($trip->starting_mileage) && isset($trip->ending_mileage)) {
                $actual_distance = $trip->ending_mileage - $trip->starting_mileage;
            }else {
                $actual_distance = "";
            }
            $emptyrun_distance = (float) $trip->emptyruns()->sum('distance');
            $total_trip_distance = (is_numeric($actual_distance) ? (float) $actual_distance : 0) + $emptyrun_distance;
            $distance_cell = "Lp-Op Distance: " . ($actual_distance !== "" ? $actual_distance : 0) . "\nTotal Distance: " . $total_trip_distance;
          

            $pod = TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
            $invoice_item = InvoiceItem::where('trip_id',$trip->id)->first();
            if (isset($invoice_item)) {
               $invoice_number = $invoice_item->invoice ? $invoice_item->invoice->invoice_number : "";
               $invoice_date = $invoice_item->invoice ? $invoice_item->invoice->date : "";
            }else {
                $invoice_number = "";
                $invoice_date = "";
            }
        

            if ($trip->start_date){
                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                if ((preg_match($pattern, $trip->start_date)) ){
                    $start_date = Carbon::parse($trip->start_date)->format('d M Y g:i A');
                }else{
                    $start_date = $trip->start_date;
                }  
            }

            if ($trip->delivery_note){
                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                if ((preg_match($pattern, $trip->delivery_note->loaded_date)) ){
                    $loading_date = Carbon::parse($trip->delivery_note->loaded_date)->format('d M Y g:i A');
                }else{
                    $loading_date = $trip->delivery_note->loaded_date;
                }  
            }else {
                $loading_date = "";
            }     
              
            if ($trip->delivery_note){
                $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
                if ((preg_match($pattern, $trip->delivery_note->offloaded_date)) ){
                    $offloading_date = Carbon::parse($trip->delivery_note->offloaded_date)->format('d M Y g:i A');
                }else{
                    $offloading_date = $trip->delivery_note->offloaded_date;
                }  
            }else {
                $offloading_date = "";
            }     
            
                $fuel_purchased = $trip->fuels->where('amount','!=',Null)->where('amount','!=','')->sum('amount');
                $fuel_sold = $trip->fuels->where('transporter_total','!=',Null)->where('transporter_total','!=','')->sum('transporter_total');
                $fuel_profit = $trip->fuels->where('profit','!=',Null)->where('profit','!=','')->sum('profit');
            
                $weight = $trip->weight ? $trip->weight : "";
                $quantity = $trip->quantity ? $trip->quantity : "";
                $litreage = $trip->litreage ? $trip->litreage : "";
                $litreage_at_20 = $trip->litreage_at_20 ? $trip->litreage_at_20 : "";
               
            if ($trip->delivery_note) {
                $loaded_weight = $trip->delivery_note ? $trip->delivery_note->loaded_weight : "";
                $loaded_quantity = $trip->delivery_note ? $trip->delivery_note->loaded_quantity : "";
                $loaded_litreage = $trip->delivery_note ? $trip->delivery_note->loaded_litreage : "";
                $loaded_litreage_at_20 = $trip->delivery_note ? $trip->delivery_note->loaded_litreage_at_20 : "";

                $offloaded_weight = $trip->delivery_note ? $trip->delivery_note->offloaded_weight : "";
                $offloaded_quantity = $trip->delivery_note ? $trip->delivery_note->offloaded_quantity : "";
                $offloaded_litreage = $trip->delivery_note ? $trip->delivery_note->offloaded_litreage : "";
                $offloaded_litreage_at_20 = $trip->delivery_note ? $trip->delivery_note->offloaded_litreage_at_20 : "";
            }else {
                $loaded_weight = "";
                $loaded_quantity = "";
                $loaded_litreage =  "";
                $loaded_litreage_at_20 = "";
                $offloaded_weight = "";
                $offloaded_quantity = "";
                $offloaded_litreage =  "";
                $offloaded_litreage_at_20 = "";
            }
            
            if ((isset($trip->delivery_note->loaded_weight) && $trip->delivery_note->loaded_weight > 0 ) && ( isset($trip->delivery_note->offloaded_weight) && $trip->delivery_note->offloaded_weight > 0 )) {
                $weight_loss = $trip->delivery_note->loaded_weight - $trip->delivery_note->offloaded_weight;
            }else {
                $weight_loss = "";
            }
            if ((isset($trip->delivery_note->loaded_quantity) && $trip->delivery_note->loaded_quantity > 0 ) && (isset($trip->delivery_note->offloaded_quantity) && $trip->delivery_note->offloaded_quantity > 0) ) {
                $quantity_loss = $trip->delivery_note->loaded_quantity - $trip->delivery_note->offloaded_quantity;
            }else {
                $quantity_loss = "";
            }
            if ((isset($trip->delivery_note->loaded_litreage_at_20) && $trip->delivery_note->loaded_litreage_at_20 > 0 ) && (isset($trip->delivery_note->offloaded_litreage_at_20) && $trip->delivery_note->offloaded_litreage_at_20 > 0)) {
                $litreage_at_20_loss = $trip->delivery_note->loaded_litreage_at_20 - $trip->delivery_note->offloaded_litreage_at_20;
            }else {
                $litreage_at_20_loss = "";
            }
            if ((isset($litreage_at_20_loss) && $litreage_at_20_loss > 0) && (isset($trip->allowable_loss_litreage) && $trip->allowable_loss_litreage > 0)) {
                $chargeable_litreage_loss =   $litreage_at_20_loss - $trip->allowable_loss_litreage;

                if (isset($trip->rate) && isset($chargeable_litreage_loss)) {
                    $deductable_litreage_loss =   $trip->rate * $chargeable_litreage_loss;
                }else {
                    $deductable_litreage_loss = "";
                }
                if (isset($trip->transporter_rate) && isset($chargeable_litreage_loss)) {
                    $transporter_deductable_litreage_loss =   $trip->transporter_rate * $chargeable_litreage_loss;
                }else {
                    $transporter_deductable_litreage_loss = "";
                }
               }else {
                $chargeable_litreage_loss = "";
                $transporter_deductable_litreage_loss = "";
                $deductable_litreage_loss = "";
               }
               if ((isset($quantity_loss) && $quantity_loss > 0) && (isset($trip->allowable_loss_quantity) && $trip->allowable_loss_quantity > 0)) {
                $chargeable_quantity_loss =   $quantity_loss - $trip->allowable_loss_quantity;
               }else{
                $chargeable_quantity_loss = "";
               }
               if ((isset($weight_loss) && $weight_loss > 0) && (isset($trip->allowable_loss_weight) && $trip->allowable_loss_weight > 0)) {
                $chargeable_weight_loss =   $weight_loss - $trip->allowable_loss_weight;
                if (isset($trip->rate) && isset($chargeable_weight_loss)) {
                    $deductable_weight_loss =   $trip->rate * $chargeable_weight_loss;
                }else {
                    $deductable_weight_loss = "";
                }
                if (isset($trip->transporter_rate) && isset($chargeable_weight_loss)) {
                    $transporter_deductable_weight_loss =   $trip->transporter_rate * $chargeable_weight_loss;
                }else {
                    $transporter_deductable_weight_loss = "";
                }
               
                
                
               }else {
                $chargeable_weight_loss = "";
                $deductable_weight_loss = "";
                $transporter_deductable_weight_loss = "";
               }

               if ($trip->trip_expenses) {
                foreach ($trip->trip_expenses as $expense) {
                 
                  if (isset($expense->category)  && isset($expense->amount) && isset($expense->currency_id)) {
      
                      if ($expense->currency_id == Auth::user()->employee->company->currency_id) {
                          if ($expense->category == "Transporter") {
                              $total_transporter_expenses = $total_transporter_expenses + $expense->amount;
                          }
                      }else{
                          if ($expense->category == "Transporter") {
                              $total_transporter_expenses = $total_transporter_expenses + $expense->exchange_amount;
                           }
                      }
                  }      
               
                }
              
              }

              $cost_of_sales_less_transporter_expenses = 0 ;

            if($trip->currency_id == Auth::user()->employee->company->currency_id){
                if ($total_transporter_expenses > 0) {
                    $cost_of_sales_less_transporter_expenses = $trip->transporter_freight - $total_transporter_expenses;
                }
            }else{
                if ($total_transporter_expenses > 0) {
                    $cost_of_sales_less_transporter_expenses = $trip->exchange_transporter_freight - $total_transporter_expenses;
                }
            }

                if (isset($trip->cost_of_sales) && $trip->cost_of_sales != "" && $trip->cost_of_sales > 0) {
                    if (isset($trip->turnover) && $trip->turnover != "" && $trip->turnover > 0) {
                        $net_profit = $trip->turnover - $trip->cost_of_sales;
                        if((isset($net_profit) && $net_profit > 0) && (isset($trip->turnover) && $trip->turnover > 0)){
                            $markup_percentage = (($net_profit/$trip->cost_of_sales) * 100);
                            $net_profit_percentage = (($net_profit/$trip->turnover) * 100);
                        }else{
                            $net_profit_percentage = 100 ;
                            $markup_percentage = 100 ;
                        }
                    } else {
                        $net_profit = $trip->turnover ;
                        $net_profit_percentage = 100 ;
                        $markup_percentage = 100 ;
                    }
                }else {
                    $net_profit = $trip->turnover ;
                    $net_profit_percentage = 100 ;
                    $markup_percentage = 100 ;
                }

                return   [
                    $trip->trip_number ,
                    $start_date,
                    $loading_date,
                    $offloading_date,
                    $trip->trip_type ? $trip->trip_type->name : "",
                    $border_list,
                    $clearing_agent_list,
                    $trip->transporter ? $trip->transporter->name : "",
                    $horse_full_details,
                    $trailer->registration_number,
                    $driver_name .' '. $driver_surname,
                    $trip->customer ? $trip->customer->name : "",
                    $trip->consignee ? $trip->consignee->name : "",
                    $agent_name .' '. $agent_surname,
                    $commission_percentage,
                    $symbol . number_format($commission_amount ? $commission_amount : 0,2),
                    $trip->broker ? $trip->broker->name : "",
                    $from_country .' '. $from_city,
                    $to_country .' '. $to_city,
                    $trip->loading_point ? $trip->loading_point->name : "",
                    $trip->offloading_point ? $trip->offloading_point->name : "",
                    $trip->route ? $trip->route->name : "",
                    $truck_stop_list,
                    $trip->trip_fuel ? $trip->trip_fuel.' L' : "",
                    $trip->starting_mileage ? $trip->starting_mileage : "" ,
                    $trip->ending_mileage ? $trip->ending_mileage : "",
                    $distance_cell,
                    $trip->cargo ? $trip->cargo->name : "",
                    $weight,
                    $loaded_weight,
                    $offloaded_weight,
                    $quantity ? $quantity.' '.$trip->measurement : ""  ,
                    $loaded_quantity ? $loaded_quantity.' '.$trip->measurement : ""  ,
                    $offloaded_quantity ? $offloaded_quantity.' '.$trip->measurement :""   ,
                    $litreage_at_20 ? $litreage_at_20 : "",
                    $loaded_litreage_at_20 ? $loaded_litreage_at_20 : "",
                    $offloaded_litreage_at_20 ? $offloaded_litreage_at_20 : "",
                    $weight_loss ? $weight_loss : "",
                    $quantity_loss ? $quantity_loss : "",
                    $litreage_at_20_loss ? $litreage_at_20_loss : "",
                    $trip->allowable_loss_weight ? $trip->allowable_loss_weight : "",
                    $trip->allowable_loss_quantity ? $trip->allowable_loss_quantity : "",
                    $trip->allowable_loss_litreage ? $trip->allowable_loss_litreage : "",
                    $chargeable_weight_loss ? $chargeable_weight_loss : "",
                    $chargeable_quantity_loss ? $chargeable_quantity_loss : "",
                    $chargeable_litreage_loss ? $chargeable_litreage_loss : "",
                    $trip->currency ? $trip->currency->name : "",
                    $trip->rate ? number_format($trip->rate,2) : 0,
                    $trip->freight ? number_format($trip->freight,2) : 0,
                    $total_customer_expenses ? number_format($total_customer_expenses,2) : 0,
                    $trip->transporter_rate ? number_format($trip->transporter_rate,2) : 0,
                    $trip->transporter_freight ? number_format($trip->transporter_freight,2) : 0,
                    $total_transporter_expenses ? number_format($total_transporter_expenses,2) : 0,
                    $trip->turnover ? number_format($trip->turnover,2) : 0,
                    $trip->gross_profit ? number_format($trip->gross_profit,2) : 0,
                    $trip->transporter_freight ? number_format($trip->transporter_freight,2) : 0,
                    $total_expenses ? number_format($total_expenses,2) : 0,
                    $deductable_weight_loss ? number_format($deductable_weight_loss,2) : 0,
                    $transporter_deductable_weight_loss ? number_format($transporter_deductable_weight_loss,2) : 0,
                    $deductable_litreage_loss ? number_format($deductable_litreage_loss,2) : 0,
                    $transporter_deductable_litreage_loss ? number_format($transporter_deductable_litreage_loss,2) : 0,
                    $trip->cost_of_sales ? number_format($trip->cost_of_sales,2) : 0,
                    $net_profit ? number_format($net_profit,2) : 0,
                    $cost_of_sales_less_transporter_expenses ? number_format($cost_of_sales_less_transporter_expenses,2) : 0,
                    isset($net_profit_percentage) ? number_format($net_profit_percentage,2).'%' : 0,
                    isset($markup_percentage) ? number_format($markup_percentage,2).'%' : 0,
                    $fuel_purchased ? $fuel_purchased : "",
                    $fuel_sold ? $fuel_sold : "",
                    $fuel_profit ? $fuel_profit : "",
                    $trip->trip_status,
                    $trip->trip_status_date,
                    $location,
                    isset($pod) ? "Submitted" : "Pending",
                    $invoice_date,
                    $invoice_number,
                    $trip->comments,

                     ];

    }

    public function headings(): array{
            return[
                'Trip#',
                'Date Booked',
                'Date Loaded',
                'Date Offloaded',
                'Trip Type',
                'Border',
                'Clearing Agent',
                'Transporter',
                'Horse',
                'Trailer(s)',
                'Driver',
                'Customer',
                'Consignee',
                'Agent',
                'Commision %',
                'Commission Amount',
                'Broker',
                'From',
                'To',
                'Loading Point',
                'Offloading Point',
                'Route',
                'Truck Stop(s)',
                'Approximate Fuel',
                'Starting Mileage',
                'Ending Mileage',
                'Distances',
                'Cargo',
                'Scheduled Weight (Tons)',
                'Loaded Weight (Tons)',
                'Offloaded Weight (Tons)',
                'Scheduled Quantity',
                'Loaded Quantity',
                'Offloaded Quantity',
                'Scheduled Vol (Litre)',
                'Loaded Vol (Litre)',
                'Offloaded Vol (Litre)',
                'Transit Loss (Tons)',
                'Transit Qty Loss',
                'Transit Loss (Litres)',
                'Allowable Loss (Tons)',
                'Allowable Qty Loss',
                'Allowable Loss (Litres)',
                'Chargeable Loss (Tons)',
                'Chargeable Qty Loss',
                'Chargeable Loss (Litres)',
                'Currency',
                'Rate',
                'Freight',
                'Client Expenses',
                'Trans Rate',
                'Trans Freight',
                'Trans Expenses',
                'TurnOver',
                'Gross Profit',
                'Trans Gross Profit',
                'Expenses',
                'Deductable Weight Loss Val',
                'Trans Deductable Weight Loss Val',
                'Deductable Vol Loss Val',
                'Trans Deductable Vol Loss Val',
                'Cost Of Sales',
                'Net Profit',
                'Trans Net Profit',
                'Net Profit Percentage',
                'Markup Percentage',
                'Fuel Purchased',
                'Fuel Sold',
                'Fuel Profit',
                'Trip Status',
                'Updated On',
                'Location',
                'POD Status',
                'Date Invoiced',
                'Invoice#',
                'Comments',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:BX7')->applyFromArray([
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
