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
use Illuminate\Support\Facades\DB;
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

class TripsReportExport implements FromQuery,
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
    public $from;
    public $to;
    public $trip_filter;
    public $search;
    protected int $totalTrips = 0;
    protected array $statusBreakdown = [];     // ['Loaded'=>10, 'Offloaded'=>15, ...]
    protected array $assetCounts = ['horse'=>0,'vehicle'=>0,'trailer'=>0];
    protected array $tripTypeBreakdown = [];   // [['name'=>'Local', 'total'=>12], ...]
    protected ?int $avgTripMinutes = null;     // Start -> Offloaded (minutes)
    protected float $sumTurnover = 0.0;
    protected float $sumCogs = 0.0;
    protected float $sumNet = 0.0;
    public $department_names;
    public $role_names;
    public $user;
    public $employee;
    public $company;
   

    public function __construct($from, $to, $trip_filter, $search)
    {
    
            $this->from = $from;
            $this->to = $to;
            $this->trip_filter = $trip_filter;
            $this->search = $search;
            $this->user = Auth::user();
            $this->employee = $this->user->employee;
            $this->company = $this->employee->company;
            $departments = $this->employee->departments;
            foreach($departments as $department){
                $this->department_names[] = $department->name;
            }
            $roles = $this->user->roles;
            foreach($roles as $role){
                $this->role_names[] = $role->name;
            }

            $base = $this->query();

            // Totals
            $this->totalTrips = (clone $base)->count();

            // By Status
            $this->statusBreakdown = (clone $base)
                ->select('trips.trip_status', DB::raw('COUNT(*) as total'))
                ->groupBy('trips.trip_status')
                ->pluck('total', 'trips.trip_status')
                ->toArray();

            // By Asset Type
            $this->assetCounts['horse']   = (clone $base)->whereNotNull('trips.horse_id')->count();
            $this->assetCounts['vehicle'] = (clone $base)->whereNotNull('trips.vehicle_id')->count();
        

            // By Trip Type (top 6)
            $this->tripTypeBreakdown = (clone $base)
                ->join('trip_types', 'trip_types.id', '=', 'trips.trip_type_id')
                ->groupBy('trip_types.name')
                ->orderByDesc(DB::raw('COUNT(*)'))
                ->limit(6)
                ->get(['trip_types.name as name', DB::raw('COUNT(*) as total')])
                ->toArray();

            // Average Trip Duration (Start -> Offloaded)
            $this->avgTripMinutes = (int) ((clone $base)
                ->whereNotNull('trips.start_date')
                ->whereHas('delivery_note', function ($dq) {
                    $dq->whereNotNull('offloaded_date');
                })
                ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                ->whereRaw('TIMESTAMP(delivery_notes.offloaded_date) >= TIMESTAMP(trips.start_date)')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, TIMESTAMP(trips.start_date), TIMESTAMP(delivery_notes.offloaded_date))) AS avg_minutes')
                ->value('avg_minutes')
            );

            // Financial totals
            $tot = (clone $base)->selectRaw(
               'SUM(COALESCE(trips.turnover,0)) as sum_turnover,
                SUM(COALESCE(trips.cost_of_sales,0)) as sum_cogs,
                SUM(COALESCE(trips.turnover,0) - COALESCE(trips.cost_of_sales,0)) as sum_net'
            )->first();

            $this->sumTurnover = (float) ($tot->sum_turnover ?? 0);
            $this->sumCogs     = (float) ($tot->sum_cogs ?? 0);
            $this->sumNet      = (float) ($tot->sum_net ?? 0);
           
    }

    public function query()
    { 

        $withRelations = [
        'breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note',
        'fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name','clearing_agent:id,name',
        'trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
        'vehicle','vehicle.vehicle_make','vehicle.vehicle_model','trailers:id,make,model,registration_number',
        'driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name','route:id,name,rank',
        'truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name',
        'commission:id,commission,amount'
        ];
        // Base query
        $trips = Trip::query()->with($withRelations);

        // Common search logic
        $applySearch = function ($query) {
        $search = $this->search;

        $query->where(function ($q) use ($search) {
            $q->where('trip_number', 'like', "%$search%")
            ->orWhere('trip_status', 'like', "%$search%")
            ->orWhere('trip_ref', 'like', "%$search%")
            ->orWhere('authorization', 'like', "%$search%")
            ->orWhereHas('horse', function ($q2) use ($search) {
                $q2->where('registration_number', 'like', "%$search%")
                    ->orWhere('fleet_number', 'like', "%$search%");
            })
            ->orWhereHas('trip_type', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereHas('cargo', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereRaw("DATE_FORMAT(start_date, '%Y-%m-%d') LIKE ?", ["%$search%"])
            ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m-%d') LIKE ?", ["%$search%"])
            ->orWhereHas('delivery_note', fn($q2) => 
                $q2->whereRaw("DATE_FORMAT(offloaded_date, '%Y-%m-%d') LIKE ?", ["%$search%"])
            )
            ->orWhereHas('user.employee', fn($q2) => 
                $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%$search%")
            )
            ->orWhereHas('driver.employee', fn($q2) => 
                $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%$search%")
            )
            ->orWhereHas('transporter', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereHas('vehicle', function ($q2) use ($search) {
                $q2->where('registration_number', 'like', "%$search%")
                    ->orWhere('fleet_number', 'like', "%$search%");
            })
            ->orWhereHas('trailers', function ($q2) use ($search) {
                $q2->where('registration_number', 'like', "%$search%")
                    ->orWhere('fleet_number', 'like', "%$search%");
            })
            ->orWhereHas('loading_point', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereHas('trip_documents', fn($q2) => $q2->where('document_number', 'like', "%$search%"))
            ->orWhereHas('offloading_point', fn($q2) => $q2->where('name', 'like', "%$search%"))
            ->orWhereHas('loading_point', fn($q2) => $q2->where('name', 'like', "%$search%"));
            });
        };

        // Handle offloaded_date case
        if ($this->trip_filter === "offloaded_date") {
            
            $trips->whereHas('delivery_note', function ($q) {
                if (filled($this->from) && filled($this->to)) {
                    $q->whereBetween('offloaded_date', [$this->from, $this->to]);
                } else {
                    $q->whereMonth('offloaded_date', date('m'))
                    ->whereYear('offloaded_date', date('Y'));
                }
            });

            if (filled($this->search)) {
                $trips->where(function ($q) use ($applySearch) {
                    $applySearch($q);
                });
            }

            // Join only for sorting
            $trips->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                ->orderBy('delivery_notes.offloaded_date', 'desc');

        } else {
            // Other trip_filter cases
            if (filled($this->from) && filled($this->to)) {
                $trips->whereBetween("trips.{$this->trip_filter}", [$this->from, $this->to]);
            } else {
                $trips->whereMonth("trips.{$this->trip_filter}", date('m'))
                    ->whereYear("trips.{$this->trip_filter}", date('Y'));
            }

            if (filled($this->search)) {
                $trips->where(function ($q) use ($applySearch) {
                    $applySearch($q);
                });
            }

            $trips->orderBy("trips.{$this->trip_filter}", 'desc');
        }


        return $trips;


        }

       
       
    


    public function map($trip): array{

            if ($trip->horse) {
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

           
                if (isset($trip->cost_of_sales) && $trip->cost_of_sales != "" && $trip->cost_of_sales > 0 && is_numeric($trip->cost_of_sales)) {
                    if (isset($trip->turnover) && $trip->turnover != "" && $trip->turnover > 0 && is_numeric($trip->turnover)) {
                        $net_profit = $trip->turnover - $trip->cost_of_sales;
                        if((isset($net_profit) && $net_profit > 0 && is_numeric($net_profit)) && (isset($trip->turnover) && $trip->turnover > 0)){
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
           
            if ((isset($trip->starting_mileage) && is_numeric($trip->starting_mileage)) && (isset($trip->ending_mileage) && is_numeric($trip->ending_mileage))) {
                $actual_distance = $trip->ending_mileage - $trip->starting_mileage;
            }else {
                $actual_distance = "";
            }
          

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
            }else{
                $start_date  = "";
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
                    $trailer_list,
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
                    $actual_distance,
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
                'Distance',
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
    public function registerEvents(): array
    {
        // Build inline text for status & trip types
        $statusLine = 'None';
        if (!empty($this->statusBreakdown)) {
            $pairs = [];
            foreach ($this->statusBreakdown as $status => $count) {
                $pairs[] = ($status ?? 'Unknown') . ' ' . (int)$count;
            }
            $statusLine = implode(', ', $pairs);
        }

        $tripTypeLine = 'None';
        if (!empty($this->tripTypeBreakdown)) {
            $pairs = array_map(fn($t) => ($t['name'] ?? 'Unknown') . ' ' . ($t['total'] ?? 0), $this->tripTypeBreakdown);
            $tripTypeLine = implode(', ', $pairs);
        }

        $avgTrip = $this->avgTripMinutes ? $this->formatMinutes($this->avgTripMinutes) : 'N/A';

        return [
            AfterSheet::class => function (AfterSheet $event) use ($statusLine, $tripTypeLine, $avgTrip) {
                $sheet = $event->sheet->getDelegate();

                // 1) Headings at row 15 (A..BX based on your headers)
                $sheet->getStyle('A18:BX18')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                // 2) Summary under the logo starting at row 7
                $row = 7;
                $sheet->setCellValue("A{$row}", 'Trips Summary');
                $sheet->mergeCells("A{$row}:BX{$row}");
                $sheet->getStyle("A{$row}")->applyFromArray(['font' => ['bold' => true, 'size' => 14]]);
                $row++;

                // C:D block (labels/values)
                $sheet->setCellValue("C{$row}", 'Total Trips');        $sheet->setCellValue("D{$row}", $this->totalTrips); $row++;
                $sheet->setCellValue("C{$row}", 'By Status');          $sheet->setCellValue("D{$row}", $statusLine);       $row++;
                $sheet->setCellValue("C{$row}", 'Avg Trip Duration');  $sheet->setCellValue("D{$row}", $avgTrip);          $row++;
                $sheet->setCellValue("C{$row}", 'By Asset Type');      
                $sheet->setCellValue(
                    "D{$row}",
                    sprintf('Horse %d, Vehicle %d, Trailer %d',
                        $this->assetCounts['horse'] ?? 0,
                        $this->assetCounts['vehicle'] ?? 0,
                        $this->assetCounts['trailer'] ?? 0
                    )
                );                                                                                            $row++;
                $sheet->setCellValue("C{$row}", 'By Trip Type');       $sheet->setCellValue("D{$row}", $tripTypeLine);     $row++;
                
                if(Auth::user()->employee->company->rates_managed_by_finance == True){
                     if (in_array('Finance', $this->department_names) ){
                        $sheet->setCellValue("C{$row}", 'Total Turnover');     $sheet->setCellValue("D{$row}", number_format($this->sumTurnover, 2)); $row++;
                        $sheet->setCellValue("C{$row}", 'Cost of Sales');      $sheet->setCellValue("D{$row}", number_format($this->sumCogs, 2));     $row++;
                        $sheet->setCellValue("C{$row}", 'Net Profit');         $sheet->setCellValue("D{$row}", number_format($this->sumNet, 2));      $row++;
                     }
                }
               
                // Style the C:D summary block + left align D
                $sheet->getStyle("C8:D{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);
                $sheet->getStyle("D8:D{$row}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            },
        ];
    }


    // helper from Bookings export — reuse here too
    protected function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) return '0m';
        $days = intdiv($minutes, 1440);
        $minutes -= $days * 1440;
        $hours = intdiv($minutes, 60);
        $minutes -= $hours * 60;

        $parts = [];
        if ($days)  $parts[] = "{$days}d";
        if ($hours) $parts[] = "{$hours}h";
        if ($minutes) $parts[] = "{$minutes}m";
        return implode(' ', $parts);
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
        return 'A18';
    }
}
