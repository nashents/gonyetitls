<?php

namespace App\Http\Livewire\Trips;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\User;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\EmptyRun;
use App\Models\GatePass;
use App\Models\Container;
use App\Models\TripStatus;
use App\Mail\FuelOrderMail;
use App\Models\BillExpense;
use App\Models\Destination;
use App\Models\Measurement;
use App\Models\Transporter;
use App\Models\TripExpense;
use App\Models\DeliveryNote;
use App\Models\LoadingPoint;
use App\Mail\TripUpdatesMail;
use App\Models\TransportOrder;
use App\Mail\TransportOrderMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class Show extends Component
{

    public $trip_id;
    public $company;
    public $trip;

    public $driver_id;
    public $horse_id;
    public $pattern;
    public $initial_fuel;
 
    public $trailer_regnumbers;
    public $trailer_reg_numbers;
    public $collection_point;
    public $deliver_point;
    public $weight;
    public $cargo;
    public $measurement;
    public $litreage;
    public $quantity;
    public $authorized_by;
    public $ending_mileage;
    public $starting_mileage;
    public $checked_by;
    public $start_date;
    public $transporter_id;
    public $subtotal;

    public $total = 0;

    public $clearing_agent;
    public $boarder;
    public $route;
    public $truck_stops;

    //fuel order variables
    public $fuels;
    public $fuel_id;
    public $order_number;
    public $date;
    public $fullname;
    public $station_name;
    public $station_email;
    public $email;
    public $regnumber;
    public $fuel_type;
    public $fuel_order_quantity;
    public $driver;
    public $horse;
    public $delivery_point;
    public $fuel;
    public $mileage;
    public $emptyrun_origin;
    public $emptyrun_destination;

    public $search;
    protected $queryString = ['search'];

  
    public $customer_updates;
   
    public $customer_id;
    public $trip_expenses;
    public $net_profit;
    public $net_profit_percentage;
    public $markup_percentage;
    public $gross_profit;
   
    public $currency_id;
    public $currency;
    public $trailers;
   
    public $fuel_order_date;
    public $from_destination;
    public $to_destination;
    public $from_destination_country;
    public $to_destination_country;

    public $to;
    public $from;
    public $trip_filter;

    public $offloading_point;
    public $loading_point;
    public $loading_point_email;
    public $customer_email;
    public $fuel_station_email;
   
    public $end_date;
 
    public $rate;
    public $freight;
    public $distance;
    public $trip_status;

    public $trips;
    public $authorize;
    public $comments;
    public $default_currency;

  
    public $status;

    public $actual_offloading_date;
    public $estimated_offloading_date;
    
    public $customer_total;
    public $transporter_total;

    public $currencies;
    public $loaded_quantity;
    public $loaded_litreage;
    public $loaded_litreage_at_20;
    public $loaded_weight;
    public $loaded_rate;
    public $loaded_freight;
    public $loaded_date;
    public $offloaded_quantity;
    public $offloaded_distance;
    public $offloaded_litreage;
    public $offloaded_litreage_at_20;
    public $offloaded_weight;
    public $offloaded_rate;
    public $offloaded_freight;
    public $transporter_offloaded_rate;
    public $transporter_offloaded_freight;
    public $transporter_loaded_rate;
    public $transporter_loaded_freight;
    public $offloaded_date;
    public $payment_status;
    public $selectedStatus;
    public $trip_status_date;
    public $trip_status_description;
    public $selectedDeliveryNote;
    public $freight_calculation;

    public $cpk = 0;
    public $actual_distance = 0;
    public $total_expenses = 0;
    public $total_customer_expenses = 0;
    public $total_transporter_expenses = 0;
    public $cost_of_sales = 0;
    public $turnover = 0;
    public $grossprofit;
    public $cargo_type;
    public $active_tab;
    public $delivery_note;

    //Loss Details
   
    public $authorizer;
    public $employee;
    public $user;
    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];
   

    private function initializeUserDetails() {
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
        $this->default_currency = $this->company->currency;
    
        foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }
    }

    private function initializeTrip($id){
        $this->trip_id = $id;
        $this->trip = Trip::with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','fuels','transporter:id,name','trip_type:id,name','border:id,name',
        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->find($id);
    }

    private function calculateActualDistance(){
        
        if ((isset($this->trip->starting_mileage) && $this->trip->starting_mileage > 0) && (isset($this->trip->ending_mileage) && $this->trip->ending_mileage > 0)) {
            $this->actual_distance =   $this->trip->ending_mileage - $this->trip->starting_mileage;
            if($this->actual_distance && is_numeric($this->actual_distance) && $this->actual_distance > 0){
                if (is_numeric($this->trip->cost_of_sales) && $this->trip->cost_of_sales > 0) {
                    $this->cpk = $this->trip->cost_of_sales / $this->actual_distance;
                }
            }
        }
    }

    private function loadExpenses($id){
        $this->trip_expenses = TripExpense::select('id','currency_id','amount','exchange_amount','category')->where('trip_id',$id)->get();
        
        if(isset($this->trip_expenses)){
            foreach ($this->trip_expenses as $expense) {
                $amount = $expense->currency_id == $this->company->currency_id ? $expense->amount : $expense->exchange_amount;
            
                switch ($expense->category) {
                    case 'Transporter':
                        $this->total_transporter_expenses += $amount;
                        break;
                    case 'Customer':
                        $this->total_customer_expenses += $amount;
                        break;
                    case 'Self':
                        $this->total_expenses += $amount;
                        break;
                }
            }
        }
    }


    public function mount($id){
        $this->initializeUserDetails();
        $this->initializeTrip($id);
        $this->calculateActualDistance();
        $this->loadExpenses($id);
        
    
        $this->initial_fuel = $this->trip->fuels->where('fillup',1)->first();
        $this->emptyrun_origin  = EmptyRun::where('trip_id',$this->trip->id)->where('emptyrun_origin',True)->first();
        $this->emptyrun_destination  = EmptyRun::where('trip_id',$this->trip->id)->where('emptyrun_destination',True)->first();
        $this->from = Destination::with('country:id,name')->find($this->trip->from);
        $this->to = Destination::with('country:id,name')->find($this->trip->to);
      
        $this->delivery_note = $this->trip->delivery_note;
        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $this->authorizer = User::find($this->trip->authorized_by_id);
    }

   

    public function billNumber(){

      if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }

    public function gate_passNumber(){

     if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
        $fuel = GatePass::orderBy('id', 'desc')->first();
        if(!$fuel){
        $gate_pass_number =  $initials .'GP'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else{
        $number = $fuel->id + 1 ;
        $gate_pass_number = $initials .'GP'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
        return $gate_pass_number;
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }
    public function updated($value){
        $this->validateOnly($value);
    }


      public function paymentStatus($id){
        $trip = Trip::withTrashed()->find($id);
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->payment_status = $trip->payment_status;
        $this->dispatchBrowserEvent('show-paymentStatusModal');
      }



      public function updateStatus(){
        $trip = Trip::withTrashed()->find($this->trip_id);
        $trip->payment_status = $this->payment_status;
        $trip->update();
        $this->dispatchBrowserEvent('hide-paymentStatusModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Payment Status Updated Successfully!!"
        ]);
        return redirect(route('trips.show', $this->trip_id));
      }


      private function resetInputFields(){
        $this->authorize = "";
        $this->comments = "";
    }

      public function authorize($id){
        $trip = Trip::find($id);
        $this->trip_id = $trip->id;
        $this->trip = $trip;
        $this->mileage = $trip->starting_mileage;
        $this->email = $trip->customer ? $trip->customer->email : "";
        $this->customer_updates = $trip->customer_updates;
        if (Auth::user()->company) {
            $this->company = Auth::user()->company;
        } elseif (Auth::user()->employee->company) {
            $this->company = Auth::user()->employee->company;
        }
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function updateAuthorization(){
      // try{

      $trip = Trip::find($this->trip_id);
      $trip->authorized_by_id = Auth::user()->id;
      $trip->authorization = $this->authorize;
      $trip->reason = $this->comments;
      $trip->update();

      if ($this->authorize == 'approved') {  

          $gate_pass = new GatePass;
          $gate_pass->user_id = Auth::user()->id;
          $gate_pass->gate_pass_number = $this->gate_passNumber();
          if (Auth::user()->employee->branch) {
              $gate_pass->branch_id = Auth::user()->employee->branch ? Auth::user()->employee->branch->id : "";
          }
          $gate_pass->type = "Trip";
          $gate_pass->trip_id = $trip->id;
          $gate_pass->driver_id = $trip->driver_id ? $trip->driver_id : null;
          $gate_pass->horse_id = $trip->horse_id ? $trip->horse_id : null;
          $gate_pass->exit = $trip->start_date;
          $trailers = $trip->trailers;

          foreach ($trailers as $trailer) {
              $trailer_ids[] = $trailer->id;
          }

          $gate_pass->save();
          if (isset($trailer_ids)) {
              $gate_pass->trailers()->sync($trailer_ids);
          }
         
          if (isset($trip->vehicle_id)) {
              $vehicle = Vehicle::find($trip->vehicle_id);
              $current_mileage = $vehicle->mileage;
              if($this->mileage > $current_mileage){
                  $vehicle->mileage = $this->mileage;
              }
              $vehicle->update();

          }elseif(isset($trip->horse_id)){

              $horse = Horse::find($trip->horse_id);
              $current_mileage = $horse->mileage;
              if($this->mileage > $current_mileage){
                  $horse->mileage = $this->mileage;
              }
              $horse->update();
          }

          if(isset($trip->starting_mileage)){

              $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
              if(isset($last_mileage)){
                  if($last_mileage < $trip->starting_mileage){
                      $mileage = new Mileage;
                      $mileage->user_id = Auth::user()->id;
                      $mileage->trip_id = $trip->id;
                      $mileage->horse_id = $trip->horse_id;
                      $mileage->trailer_id = $trip->trailer_id;
                      $mileage->vehicle_id = $trip->vehicle_id;
                      $mileage->mileage = $trip->starting_mileage;
                      $mileage->date = $trip->start_date;
                      $mileage->category = "Trip";
                      $mileage->position = "starting";
                      $mileage->save();
                  }
              }           
            }
  
            if(isset($trip->ending_mileage)){
              $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
              if(isset($last_mileage)){
                  if($last_mileage < $trip->ending_mileage){
                      $mileage = new Mileage;
                      $mileage->user_id = Auth::user()->id;
                      $mileage->trip_id = $trip->id;
                      $mileage->horse_id = $trip->horse_id;
                      $mileage->trailer_id = $trip->trailer_id;
                      $mileage->vehicle_id = $trip->vehicle_id;
                      $mileage->mileage = $trip->ending_mileage;
                      $mileage->date = $trip->end_date;
                      $mileage->category = "Trip";
                      $mileage->position = "ending";
                      $mileage->save();
                  }
              }
            }


          $expenses = Trip::find($this->trip_id)->trip_expenses;
          
          if($expenses->count()>0){
              foreach ($expenses as $trip_expense) {

                  if(isset($trip_expense->fuel_id)){

                      $fuel = Fuel::find($trip_expense->fuel_id);

                      if (isset($fuel)) {

                          if (isset($fuel->container)) {

                                   $account = Account::where('name','Trip Expense')->get()->first();

                                  $bill = new Bill;
                                  $bill->user_id = Auth::user()->id;
                                  $bill->bill_number = $this->billNumber();
                                  $bill->trip_id = $trip->id;
                                  $bill->fuel_id = $trip_expense->fuel_id;
                                  $bill->trip_expense_id = $trip_expense->id;
                                  $bill->horse_id = $trip->horse_id;
                                  $bill->vehicle_id = $trip->vehicle_id;
                                  if (isset($account)) {
                                      $bill->account_id = $account->id;
                                      $bill->account_type_id = $account->account_type->id;
                                  }
                                  if($fuel->container->purchase_type == "Once Off Buy"){
                                      $bill->to_be_paid = True;
                                  }else{
                                      $bill->to_be_paid = False;
                                  }
                                  $bill->driver_id = $trip->driver_id;
                                  $bill->category = "Trip Expense - Fuel Order";
                                  $bill->bill_date = date("Y-m-d");
                                  $bill->currency_id = $trip_expense->currency_id;
                                  $bill->subtotal = $trip_expense->amount;
                                  $bill->total = $trip_expense->amount;
                                  $bill->exchange_amount = $trip_expense->exchange_amount;
                                  $bill->balance = $trip_expense->amount;
                                  $bill->authorized_by_id = Auth::user()->id;
                                  $bill->authorization = $this->authorize;
                                  $bill->comments = $this->comments;
                                  $bill->save();
              
                                  $bill_expense = new BillExpense;
                                  $bill_expense->user_id = Auth::user()->id;
                                  $bill_expense->bill_id = $bill->id;
                                  if (isset($account)) {
                                      $bill_expense->account_id = $account->id;
                                      $bill_expense->account_type_id = $account->account_type->id;
                                  }
                                  $bill_expense->currency_id = $bill->currency_id;
                                  $bill_expense->expense_id = $trip_expense->expense_id;
                                  $bill_expense->qty = 1;
                                  $bill_expense->amount = $trip_expense->amount;
                                  $bill_expense->subtotal = $trip_expense->amount;
                                  $bill_expense->subtotal_incl = $trip_expense->amount;
                                  $bill_expense->save();
                            
                          }
                   
                      }
                 
                  }elseif(isset($trip_expense->transporter_id)){
                              
                      $expense = Expense::where('name','Transporter Payment')->get()->first();
                      $account = Account::where('name','Trip Expense')->get()->first();
  
                      $bill = new Bill;
                      $bill->user_id = Auth::user()->id;
                      $bill->bill_number = $this->billNumber();
                      $bill->trip_id = $trip->id;
                      $bill->category = "Trip Expense - Transporter Payment";
                      $bill->transporter_id = $trip_expense->transporter_id;
                      $bill->trip_expense_id = $trip_expense->id;
                      $bill->bill_date = $trip->start_date;
                      if (isset($account)) {
                          $bill->account_id = $account->id;
                          $bill->account_type_id = $account->account_type->id;
                      }
                      $bill->currency_id = $trip_expense->currency_id;
                      $bill->subtotal = $trip_expense->amount;
                      $bill->total = $trip_expense->amount;
                      $bill->balance = $trip_expense->amount;

                      $bill->authorized_by_id = Auth::user()->id;
                      $bill->authorization = $this->authorize;
                      $bill->comments = $this->comments;
                      $bill->save();
  
                     

                      $bill_expense = new BillExpense;
                      $bill_expense->user_id = Auth::user()->id;
                      $bill_expense->bill_id = $bill->id;
                      $bill_expense->currency_id = $bill->currency_id;
                      if (isset($expense)) {
                          $bill_expense->expense_id = $expense->id;
                      }
                      if (isset($account)) {
                          $bill_expense->account_id = $account->id;
                          $bill_expense->account_type_id = $account->account_type->id;
                      }
                      $bill_expense->qty = 1;
                      $bill_expense->amount = $trip_expense->amount;
                      $bill_expense->subtotal = $trip_expense->amount;
                      $bill_expense->subtotal_incl = $trip_expense->amount;
                      $bill_expense->save();

               
              }else{

                      $account = Account::where('name','Trip Expense')->get()->first();

                      $bill = new Bill;
                      $bill->user_id = Auth::user()->id;
                      $bill->bill_number = $this->billNumber();
                      $bill->trip_id = $trip->id;
                      $bill->fuel_id = $trip_expense->fuel_id;
                      $bill->trip_expense_id = $trip_expense->id;
                      $bill->horse_id = $trip->horse_id;
                      $bill->vehicle_id = $trip->vehicle_id;
                      if (isset($account)) {
                          $bill->account_id = $account->id;
                          $bill->account_type_id = $account->account_type->id;
                      }
                      $bill->driver_id = $trip->driver_id;
                      $bill->category = "Trip Expense";
                      $bill->bill_date = date("Y-m-d");
                      $bill->currency_id = $trip_expense->currency_id;
                      $bill->subtotal = $trip_expense->amount;
                      $bill->total = $trip_expense->amount;
                      $bill->exchange_amount = $trip_expense->exchange_amount;
                      $bill->balance = $trip_expense->amount;
                      $bill->authorized_by_id = Auth::user()->id;
                      $bill->authorization = $this->authorize;
                      $bill->comments = $this->comments;
                      $bill->save();

                      $bill_expense = new BillExpense;
                      $bill_expense->user_id = Auth::user()->id;
                      $bill_expense->bill_id = $bill->id;
                      $bill_expense->currency_id = $bill->currency_id;
                      $bill_expense->expense_id = $trip_expense->expense_id;
                      $bill_expense->allowance_id = $trip_expense->allowance_id;
                      if (isset($account)) {
                          $bill_expense->account_id = $account->id;
                          $bill_expense->account_type_id = $account->account_type->id;
                      }
                      $bill_expense->qty = 1;
                      $bill_expense->amount = $trip_expense->amount;
                      $bill_expense->subtotal = $trip_expense->amount;
                      $bill_expense->subtotal_incl = $trip_expense->amount;
                      $bill_expense->save();
                  }

                  
              }
          }
          
 

     

              if ($trip->trailers->count()>0) {
                  foreach ($trip->trailers as $trailer) {
                      $trailer_regnumbers[] = $trailer->registration_number;
                  }
                  $regnumbers_string = implode(",",$trailer_regnumbers);
              }

              $user = User::find($trip->user_id);

              $transport_order = new TransportOrder;
              $transport_order->user_id = Auth::user()->id;
              $transport_order->trip_id = $trip->id;
              $transport_order->transporter_id = $trip->transporter_id;
              $transport_order->driver_id = $trip->driver_id;
              $transport_order->horse_id = $trip->horse_id;
              if (isset($regnumbers_string)) {
                  $transport_order->trailer_regnumber = $regnumbers_string;
              }
              $transport_order->collection_point = $trip->loading_point ? $trip->loading_point->name : "";
              $transport_order->delivery_point = $trip->offloading_point ? $trip->offloading_point->name : "";
              $transport_order->cargo = $trip->cargo ? $trip->cargo->name : "";
              $transport_order->weight = $trip->weight;
              if (isset($trip->quantity)) {
                  $transport_order->quantity = $trip->quantity;
              }else{
                  $transport_order->litreage = $trip->litreage;
              }
              $transport_order->measurement = $trip->measurement;
             
              $transport_order->date = $trip->start_date;
              $user = $trip->user;
              $name =  $user->employee ? $user->employee->name : "";
              $surname = $user->employee ? $user->employee->surname : "";
              $transport_order->checked_by = $name . ' ' . $surname;
              $transport_order->authorized_by = Auth::user()->employee->name . ' ' .Auth::user()->employee->surname;
              $transport_order->save();
           
              $this->trip_id = $trip->id;
              $this->driver_id = $trip->driver_id;
              $this->horse_id = $trip->horse_id;
              $this->transporter_id = $trip->transporter_id;
              $this->start_date = $trip->start_date;
              $user = $trip->user;
              $name =  $user->employee ? $user->employee->name : "";
              $surname = $user->employee ? $user->employee->surname : "";
              $this->checked_by = $name . ' ' . $surname;
              $auth_name = Auth::user()->employee->name;
              $auth_surname =Auth::user()->employee->surname;
              $this->authorized_by =  $auth_name. ' ' .$auth_surname;
              if (isset($trip->quantity)) {
                  $this->quantity = $trip->quantity;
              }else{
                  $this->litreage = $trip->litreage;
              }
              $this->measurement = $trip->measurement;
              $this->cargo = $trip->cargo ? $trip->cargo->name : "";
              $this->weight = $trip->weight;
              $this->delivery_point = $trip->offloading_point ? $trip->offloading_point->name : "";
              $this->collection_point = $trip->loading_point ? $trip->loading_point->name : "";
              if (isset($regnumbers_string)) {
                  $this->trailer_reg_numbers = $regnumbers_string;
              }
           
              $loading_point = LoadingPoint::find($trip->loading_point_id);
              if ( $loading_point) {
                  $this->loading_point_email =   $loading_point->email;
              }
             

              if ( isset($this->loading_point_email) && $this->loading_point_email != "") {
                  $data = array(
                      'email'=> $this->loading_point_email,
                      'date'=> $this->start_date,
                      'horse'=> Horse::find($this->horse_id),
                      'driver'=> Driver::find($this->driver_id),
                      'transporter'=> Transporter::find($this->transporter_id),
                      'trip'=> Trip::find($this->trip_id),
                      'regnumbers'=> $this->trailer_reg_numbers ? $this->trailer_reg_numbers : "",
                      'authorized_by'=> $this->authorized_by,
                      'checked_by'=> $this->checked_by,
                      'collection_point'=> $this->collection_point,
                      'delivery_point'=> $this->delivery_point,
                      'cargo'=> $this->cargo,
                      'litreage'=> $this->litreage,
                      'quantity'=> $this->quantity,
                      'measurement'=> $this->measurement,
                      'weight'=> $this->weight,
                     );
      
                     if (isset(Auth::user()->company)) {
                      $company = Auth::user()->company;
                      }elseif (isset(Auth::user()->employee->company)) {
                          $company = Auth::user()->employee->company;
                      }
      
                     Mail::to($this->loading_point_email)->send(new TransportOrderMail($data, $company));
              }
              

              if ($trip->trip_status != "Offloaded" && $trip->trip_status != "Cancelled" && $trip->trip_status != "Scheduled") {
                  
                  $horse = Horse::withTrashed()->find($trip->horse_id);
                  if(isset($horse)){
                      $horse->status = 0;
                      $horse->update();
                  }

                  $vehicle = Vehicle::withTrashed()->find($trip->vehicle_id);
                  if(isset($vehicle)){
                      $vehicle->status = 0;
                      $vehicle->update();
                  }
  
                  $driver = Driver::withTrashed()->find($trip->driver_id);
                  if(isset($driver)){
                      $driver->status = 0;
                      $driver->update();
                  }
  
                  if ($trip->trailers->count()>0) {
                      foreach ($trip->trailers as $trailer) {
                          $trailer = Trailer::withTrashed()->find($trailer->id);
                          $trailer->status = 0;
                          $trailer->update();
                      }
                  }
  
              }
        


              if ($this->customer_updates == TRUE) {

                  $this->customer_email = $this->trip->customer->email;
                 
                  if (isset($this->customer_email) && $this->customer_email != "") {
                  Mail::to($this->customer_email)->send(new TripUpdatesMail($this->trip, $this->company));
                   }
               }


              if ($this->trip->fuel_order == True) {

                  $fuel = Fuel::find($this->trip->fuel->id);
                  $fuel->authorization = $this->authorize;
                  $fuel->authorized_by_id = Auth::user()->id;
                  $fuel->comments = $this->comments;
                  $fuel->update();
          
                  if ($this->authorize == "approved") {

                      // sending fuel order email to supplier
                      $trip = $fuel->trip;

                      if ($fuel->horse) {
                          $horse = Horse::find($fuel->horse_id);
                        
                          if(is_numeric($fuel->quantity)){
                              $horse->fuel_balance = $horse->fuel_balance + $fuel->quantity;
                          }
                         
                          $current_mileage = $horse->mileage;
                          if ($fuel->odometer >  $current_mileage) {
                              $horse->mileage = $fuel->odometer;
                          }
                        
                          $horse->update();
                      }
                      if ($fuel->vehicle) {
                          $vehicle = Vehicle::find($fuel->vehicle_id);
                          if(is_numeric($fuel->quantity)){
                              $vehicle->fuel_balance = $vehicle->fuel_balance + $fuel->quantity;
                          }
                         
                          $current_mileage = $vehicle->mileage;
                          if ($fuel->odometer >  $current_mileage) {
                              $vehicle->mileage = $fuel->odometer;
                          }
                        
                          $vehicle->update();
          
                      }

                      $user = User::find($trip->user_id);
                      $this->fuel_station_email = $fuel->container ? $fuel->container->email : "";
                      $this->station_name = $fuel->container ? $fuel->container->name : "";
                      $this->fuel_order_date = $fuel->datze;
                      $this->order_number = $fuel->order_number;
                      $this->driver = $fuel->driver;
                      $this->horse = $fuel->horse;
                      $this->collection_point = $fuel->trip->loading_point ? $fuel->trip->loading_point->name : "";
                      $this->delivery_point = $fuel->trip->offloading_point ? $fuel->trip->offloading_point->name : "";
                      $this->fuel_type = $fuel->container ? $fuel->container->fuel_type : "";
                      $this->fuel_order_quantity = $fuel->quantity;
                      $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
                      $this->checked_by = $fuel->user->employee->name . ' ' . $fuel->user->employee->surname;
                      $this->regnumber = $fuel->horse ? $fuel->horse->registration_number : "";
          
                      if (isset($this->fuel_station_email) && $this->fuel_station_email != "") {
                      $data = array(
                          'station_email'=> $this->fuel_station_email,
                          'station_name'=> $this->station_name,
                          'date'=> $this->fuel_order_date,
                          'order_number'=> $this->order_number,
                          'driver'=> $this->driver,
                          'horse'=> $this->horse,
                          'regnumber'=> $this->regnumber,
                          'authorized_by'=> $this->authorized_by,
                          'checked_by'=> $this->checked_by,
                          'collection_point'=> $this->collection_point,
                          'delivery_point'=> $this->delivery_point,
                          'fuel_type'=> $this->fuel_type,
                          'quantity'=> $this->fuel_order_quantity,
                         );
          
                         if (isset(Auth::user()->company)) {
                          $company = Auth::user()->company;
                          }elseif (isset(Auth::user()->employee->company)) {
                              $company = Auth::user()->employee->company;
                          }
          
                         Mail::to($this->fuel_station_email)->send(new FuelOrderMail($data, $company));
                      }

                      $container = Container::find($fuel->container_id);

                      if (isset($container)) {
                          if($container->balance >= $fuel->quantity){
                              $container->balance = $container->balance - $fuel->quantity;
                              if(isset($container->account_balance)){
                                  $container->account_balance = $container->account_balance - $fuel->amount;
                              }
                              $container->update();
                          }
                      }
                     
                    
                  }
              }

              if ($trip->fuel) {

              $this->dispatchBrowserEvent('hide-authorizationModal');
              $this->resetInputFields();
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Trip & Fuel Order Approved Successfully!!"
              ]);
             return redirect()->route('trips.approved');
          }else {
              $this->dispatchBrowserEvent('hide-authorizationModal');
              $this->resetInputFields();
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Trip Approved Successfully!!"
              ]);
             return redirect()->route('trips.approved');
          }
          
      }else {
          if ($trip->fuel) {
              $this->dispatchBrowserEvent('hide-authorizationModal');
              $this->resetInputFields();
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Trip & Fuel Order Rejected Successfully!!"
              ]);
              return redirect()->route('trips.rejected');
          }else{

              $this->dispatchBrowserEvent('hide-authorizationModal');
              $this->resetInputFields();
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Trip Rejected Successfully!!"
              ]);
              return redirect()->route('trips.rejected');
          }
        



      }

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while updating trip!!"
    //     ]);
    // }



      }

      public function updatedTripStatusDate($date){
        if(isset($this->selectedStatus) && ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded")){
            $this->offloaded_date = $date;
        }
      }




    public function render()
    {
        return view('livewire.trips.show');
    }
}
