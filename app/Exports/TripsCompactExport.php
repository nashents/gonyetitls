<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Trip;
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

class TripsCompactExport implements FromQuery,
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
    public $search_id;
    public $category;
    public $to;
    public $from;
    public $trip_filter;

    public function __construct($search_id, $category, $from, $to, $trip_filter)
    {
        
            $this->search_id = $search_id;
            $this->category = $category;
            $this->from = $from;
            $this->to = $to;
            $this->trip_filter = $trip_filter;
           
    }
    public function query()
    {
        if (isset($this->from) && isset($this->to)) {   

            if ($this->category == "Customer") {
                return Trip::query()->where('customer_id', $this->search_id)
                                    ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }elseif ($this->category == "Transporter") {
                return Trip::query()->where('transporter_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Agent") {
                return Trip::query()->where('agent_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Currency") {
                return Trip::query()->where('currency_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Trip Type") {
                return Trip::query()->where('trip_type_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "User") {
                return Trip::query()->where('user_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Driver") {
                return Trip::query()->where('driver_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            } elseif ($this->category == "Route") {
                return Trip::query()->where('route_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Horse") {
                return Trip::query()->where('horse_id', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
            elseif ($this->category == "Status") {
                return Trip::query()->where('trip_status', $this->search_id)
                                     ->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            }
          
            return Trip::query()->whereBetween( $this->trip_filter,[$this->from, $this->to] );
            // return Trip::query();
      
        }else {
            if ($this->category == "Customer") {
                return Trip::query()->where('customer_id', $this->search_id);
            }elseif ($this->category == "Transporter") {
                return Trip::query()->where('transporter_id', $this->search_id);
            }
            elseif ($this->category == "Agent") {
                return Trip::query()->where('agent_id', $this->search_id);
            }
            elseif ($this->category == "Currency") {
                return Trip::query()->where('currency_id', $this->search_id);
            }
            elseif ($this->category == "User") {
                return Trip::query()->where('user_id', $this->search_id);
            }
            elseif ($this->category == "Trip Type") {
                return Trip::query()->where('trip_type_id', $this->search_id);
            }
            elseif ($this->category == "Route") {
                return Trip::query()->where('route_id', $this->search_id);
            }elseif ($this->category == "Horse") {
                return Trip::query()->where('horse_id', $this->search_id);
            }elseif ($this->category == "Driver") {
                return Trip::query()->where('driver_id', $this->search_id);
            }elseif ($this->category == "TripGroup") {
                return Trip::query()->where('trip_group_id', $this->search_id);
            }elseif ($this->category == "Route") {
                return Trip::query()->where('route_id', $this->search_id);
            }elseif ($this->category == "Status") {
                return Trip::query()->where('trip_status', $this->search_id);
            }elseif($this->category == "") {

                return Trip::query()->whereMonth('created_at', date('m'))
                ->whereYear($this->trip_filter, date('Y'))->orderBy($this->trip_filter,'desc');
            }
        }
      
      
    }

    public function map($trip): array{

        if ( $trip->horse) {
            $fleet_number =  $trip->horse->fleet_number ? '('.$trip->horse->fleet_number.')' : "";
            $horse_registration_number = $trip->horse->registration_number;
           $horse_full_details = $horse_registration_number.' '."(".$fleet_number.")";
            }else {
                $horse_full_details = "";
            }

            foreach ($trip->trailers as $trailer) {
                $fleet_number = $trailer->fleet_number ? '('.$trailer->fleet_number.')' : "";
                $trailers[] = $trailer->registration_number.' '.$fleet_number; 
            }
            if (isset($trailers)) {
                $trailer_list = implode(', ',$trailers);
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
                            if (is_numeric($expense->amount)) {
                                $total_transporter_expenses = $total_transporter_expenses + $expense->amount;
                            }
                          
                        }
                        elseif ($expense->category == "Customer") {
                            if (is_numeric($expense->amount)) {
                                $total_customer_expenses = $total_customer_expenses + $expense->amount;
                            }
                            
                        }
                        elseif ($expense->category == "Self") {
                            if (is_numeric($expense->amount)) {
                                $total_expenses = $total_expenses + $expense->amount;
                            }
                           
                        }
                    }else{
                        if ($expense->category == "Transporter") {
                            if (is_numeric($expense->exchange_amount)) {
                                $total_transporter_expenses = $total_transporter_expenses + $expense->exchange_amount;
                            }
                          
                        }
                        elseif ($expense->category == "Customer") {
                            if (is_numeric($expense->exchange_amount)) {
                                $total_customer_expenses = $total_customer_expenses + $expense->exchange_amount;
                            }
                           
                        }
                        elseif ($expense->category == "Self") {
                            if (is_numeric($expense->exchange_amount)) {
                                $total_expenses = $total_expenses + $expense->exchange_amount;
                            }
                           
                        }
                    }
                }

           
                if (is_numeric($total_expenses) && $total_expenses > 0 ) {
                 
                    if (is_numeric($trip->freight) && $trip->freight > 0 ) {

                         if ($trip->currency_id == Auth::user()->employee->company->currency_id) {
                            $net_profit = $trip->freight - $total_expenses;
                         }else{
                            $net_profit = $trip->exchange_customer_freight - $total_expenses;
                         }
                    } else {
                        $net_profit = $trip->freight ;
                      
                    }
                }else {
                    $net_profit = $trip->freight ;
                   
                }
           
            if ((isset($trip->starting_mileage) && is_numeric($trip->starting_mileage)) && (isset($trip->ending_mileage) && is_numeric($trip->ending_mileage))) {
                $actual_distance = $trip->ending_mileage - $trip->starting_mileage;
            }else {
                $actual_distance = "";
            }
          

          
            
            $invoice_item = InvoiceItem::where('trip_id',$trip->id)->first();
           
            if (isset($invoice_item)) {
               $invoice = $invoice_item->invoice;
               $payment = $invoice->payments->first();
               $payment_date = $payment ? $payment->date : "";
               $invoice_date = $invoice_item->invoice ? $invoice_item->invoice->date : "";
            }else {
                $payment_date = "";
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
            
            if ((isset($trip->delivery_note->loaded_weight) && $trip->delivery_note->loaded_weight > 0 && is_numeric($trip->delivery_note->loaded_weight)) && ( isset($trip->delivery_note->offloaded_weight) && $trip->delivery_note->offloaded_weight > 0 && is_numeric($trip->delivery_note->offloaded_weight) )) {
                $weight_loss = $trip->delivery_note->loaded_weight - $trip->delivery_note->offloaded_weight;
            }else {
                $weight_loss = "";
            }
            if ((isset($trip->delivery_note->loaded_quantity) && $trip->delivery_note->loaded_quantity > 0 && is_numeric($trip->delivery_note->loaded_quantity)) && (isset($trip->delivery_note->offloaded_quantity) && $trip->delivery_note->offloaded_quantity > 0 && is_numeric($trip->delivery_note->offloaded_quantity)) ) {
                $quantity_loss = $trip->delivery_note->loaded_quantity - $trip->delivery_note->offloaded_quantity;
            }else {
                $quantity_loss = "";
            }
            if ((isset($trip->delivery_note->loaded_litreage_at_20) && $trip->delivery_note->loaded_litreage_at_20 > 0 && is_numeric($trip->delivery_note->loaded_litreage_at_20)) && (isset($trip->delivery_note->offloaded_litreage_at_20) && $trip->delivery_note->offloaded_litreage_at_20 > 0 && is_numeric($trip->delivery_note->offloaded_litreage_at_20))) {
                $litreage_at_20_loss = $trip->delivery_note->loaded_litreage_at_20 - $trip->delivery_note->offloaded_litreage_at_20;
            }else {
                $litreage_at_20_loss = "";
            }
            if ((isset($litreage_at_20_loss) && $litreage_at_20_loss > 0 && is_numeric($litreage_at_20_loss)) && (isset($trip->allowable_loss_litreage) && $trip->allowable_loss_litreage > 0 && is_numeric($trip->allowable_loss_litreage))) {
                $chargeable_litreage_loss =   $litreage_at_20_loss - $trip->allowable_loss_litreage;

                if ((isset($trip->rate) && is_numeric($trip->rate)) && (isset($chargeable_litreage_loss) && is_numeric($chargeable_litreage_loss))) {
                    $deductable_litreage_loss =   $trip->rate * $chargeable_litreage_loss;
                }else {
                    $deductable_litreage_loss = "";
                }
                if ((isset($trip->transporter_rate) && is_numeric($trip->transporter_rate)) && (isset($chargeable_litreage_loss) && is_numeric($chargeable_litreage_loss) )) {
                    $transporter_deductable_litreage_loss =   $trip->transporter_rate * $chargeable_litreage_loss;
                }else {
                    $transporter_deductable_litreage_loss = "";
                }
               }else {
                $chargeable_litreage_loss = "";
                $transporter_deductable_litreage_loss = "";
                $deductable_litreage_loss = "";
               }
               if ((isset($quantity_loss) && $quantity_loss > 0 && is_numeric($quantity_loss)) && (isset($trip->allowable_loss_quantity) && $trip->allowable_loss_quantity > 0 && is_numeric($trip->allowable_loss_quantity))) {
                $chargeable_quantity_loss =   $quantity_loss - $trip->allowable_loss_quantity;
               }else{
                $chargeable_quantity_loss = "";
               }
               if ((isset($weight_loss) && $weight_loss > 0 && is_numeric($weight_loss)) && (isset($trip->allowable_loss_weight) && $trip->allowable_loss_weight > 0 && is_numeric($trip->allowable_loss_weight))) {
                $chargeable_weight_loss =   $weight_loss - $trip->allowable_loss_weight;
                if ((isset($trip->rate) && is_numeric($trip->rate)) && (isset($chargeable_weight_loss) && is_numeric($chargeable_weight_loss))) {
                    $deductable_weight_loss =   $trip->rate * $chargeable_weight_loss;
                }else {
                    $deductable_weight_loss = "";
                }
                if ((isset($trip->transporter_rate) && is_numeric($trip->transporter_rate)) && (isset($chargeable_weight_loss) && is_numeric($chargeable_weight_loss) )) {
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
                            if (is_numeric($expense->amount)) {
                                $total_transporter_expenses = $total_transporter_expenses + $expense->amount;
                            }
                             
                          }
                      }else{
                          if ($expense->category == "Transporter") {
                            if (is_numeric($expense->exchange_amount)) {
                                $total_transporter_expenses = $total_transporter_expenses + $expense->exchange_amount;
                            }
                             
                           }
                      }
                  }      
               
                }
              
              }

            $cost_of_sales_less_transporter_expenses = 0 ;

            if($trip->currency_id == Auth::user()->employee->company->currency_id){
                if ($total_transporter_expenses > 0) {
                    if (is_numeric($trip->transporter_freight) && is_numeric($total_transporter_expenses)) {
                        $cost_of_sales_less_transporter_expenses = $trip->transporter_freight - $total_transporter_expenses;
                    }
                   
                }
            }else{
                if ($total_transporter_expenses > 0) {
                    if (is_numeric($trip->exchange_transporter_freight) && is_numeric($total_transporter_expenses)) {
                        $cost_of_sales_less_transporter_expenses = $trip->exchange_transporter_freight - $total_transporter_expenses;
                    }
                   
                }
            }
            $starting_mileage =  $trip->starting_mileage ? $trip->starting_mileage : "";
            $ending_mileage =   $trip->ending_mileage ? $trip->ending_mileage : "";
            if (is_numeric($starting_mileage) && is_numeric($ending_mileage)) {
                $distance =  $ending_mileage -  $starting_mileage;
            }else{
                $distance = "";
            }

            $trip_freight = $trip->freight ? number_format($trip->freight,2) : 0 ;
            $currency_name = $trip->currency ? $trip->currency->name : "";
            $currency_symbol = $trip->currency ? $trip->currency->symbol : "";
            $trip_currency =  $currency_name . " " .  $currency_symbol ;

            $fuel = $trip->fuels->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
            $fuel_amount = $trip->fuels->where('amount','!=','')->where('amount','!=',Null)->sum('amount');

            $fromRoutes = collect();
            if ($trip->trip_origins && $trip->trip_origins->count()) {
                $fromRoutes = $trip->trip_origins
                    ->map(function ($trip_origin) {
                        $from = $trip_origin->destination
                            ? trim(($trip_origin->destination->country?->name ?? '') . ' ' . ($trip_origin->destination->city ?? ''))
                            : null;

                        $loadingPoint = $trip_origin->loading_point?->name;

                        return [
                            'label' => trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -'),
                            'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                        ];
                    })
                    ->filter(fn ($item) => !empty($item['label']))
                    ->unique('key')
                    ->values();
            } else {
                $from = $trip->fromDestination
                    ? trim(($trip->fromDestination->country?->name ?? '') . ' ' . ($trip->fromDestination->city ?? ''))
                    : null;

                $loadingPoint = $trip->loading_point?->name;

                $label = trim(($from ?? '') . ' - ' . ($loadingPoint ?? ''), ' -');

                if (!empty($label)) {
                    $fromRoutes = collect([
                        [
                            'label' => $label,
                            'key'   => md5(($from ?? '') . '|' . ($loadingPoint ?? '')),
                        ]
                    ]);
                }
            }

            $toRoutes = collect();
            if ($trip->trip_destinations && $trip->trip_destinations->count()) {
                $toRoutes = $trip->trip_destinations
                    ->map(function ($trip_destination) {
                        $to = $trip_destination->destination
                            ? trim(($trip_destination->destination->country?->name ?? '') . ' ' . ($trip_destination->destination->city ?? ''))
                            : null;

                        $offloadingPoint = $trip_destination->offloading_point?->name;

                        return [
                            'label' => trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -'),
                            'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                        ];
                    })
                    ->filter(fn ($item) => !empty($item['label']))
                    ->unique('key')
                    ->values();
            } else {
                $to = $trip->toDestination
                    ? trim(($trip->toDestination->country?->name ?? '') . ' ' . ($trip->toDestination->city ?? ''))
                    : null;

                $offloadingPoint = $trip->offloading_point?->name;

                $label = trim(($to ?? '') . ' - ' . ($offloadingPoint ?? ''), ' -');

                if (!empty($label)) {
                    $toRoutes = collect([
                        [
                            'label' => $label,
                            'key'   => md5(($to ?? '') . '|' . ($offloadingPoint ?? '')),
                        ]
                    ]);
                }
            }

            $fromRouteLabels = $fromRoutes->pluck('label')->implode("\n");
            $toRouteLabels   = $toRoutes->pluck('label')->implode("\n");

                return   [
                    $trip->trip_number . ($trip->trip_ref ? " / " . $trip->trip_ref : ""),
                    $trip->transporter ? $trip->transporter->name : "",
                    $horse_full_details,
                    $trailer_list,
                    $driver_name .' '. $driver_surname,
                    $loading_date,
                    $offloading_date,
                    $trip->customer ? $trip->customer->name : "",
                    $fromRouteLabels ?: "",
                    $toRouteLabels ?: "",
                    $starting_mileage." - ".$ending_mileage ,
                    $distance,
                    $fuel,
                    $fuel_amount,
                    $trip->cargo ? $trip->cargo->name : "",
                    $loaded_weight,
                    $offloaded_weight,
                    $weight_loss ? $weight_loss : "",
                    $trip->allowable_loss_weight ? $trip->allowable_loss_weight : "",
                    $trip_currency,
                    $trip_freight,   
                    $total_expenses,
                    $net_profit,
                    $invoice_date,
                    $payment_date,
                     ];

    }

    public function headings(): array{
            return[
                'Trip#',
                'Transporter',
                'Horse',
                'Trailer(s)',
                'Driver',
                'Date Loaded',
                'Date Offloaded',
                'Customer',
                'From',
                'To',
                'Mileage',
                'Distance',
                'Fuel',
                'Fuel Amount',
                'Cargo',
                'Loaded Weight',
                'Offloaded Weight',
                'Loss (Weight)',
                'Allowable Loss (Weight)',
                'Currency',
                'Freight',
                'Expenses',
                'Net Amount',
                'Invoice Date',
                'Payment Date',
            ];
    }
    public function registerEvents(): array{
        return[
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getStyle('A7:AC7')->applyFromArray([
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
