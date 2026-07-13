<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Bill;
use App\Models\Fuel;
use App\Models\Hour;
use App\Models\User;
use App\Models\Horse;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Mileage;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Container;
use App\Mail\FuelOrderMail;
use App\Models\BillExpense;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\AuthorizationNotificationMail;

class Rejected extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    // private $fuels;
    public $fuel_id;
    public $trip_id;
    public $authorize;
    public $comments;

    public $order_number;
    public $date;
    public $fullname;
    public $station_name;
    public $station_email;
    public $email;
    public $regnumber;
    public $authorized_by;
    public $checked_by;
    public $fuel_type;
    public $quantity;
    public $driver;
    public $horse;
    public $vehicle;
    public $collection_point;
    public $delivery_point;
    public $trip;
    public $fuel;

    public $fuel_filter;
    public $from;
    public $to;

    public $selectedRows = [];
    public $selectPageRows = false;

    public function mount(){
        $this->fuel_filter = "created_at";
    }
    public function authorize($id){
        $fuel = Fuel::find($id);
        $this->fuel_id = $fuel->id;
        $this->fuel = $fuel;
        $this->trip_id = $fuel->trip_id;
        $this->trip = $fuel->trip;
        $this->dispatchBrowserEvent('show-fuelAuthorizationModal');
      }

      
    public function billNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
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

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->fuels->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      public function getFuelsProperty(){
 
        if (isset($this->from) && isset($this->to)) {
            return Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
            ])->where('authorization','rejected')->whereBetween($this->fuel_filter,[$this->from, $this->to] )->orderBy('created_at','desc')->paginate(10);
           
        }else { 
            return Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
            ])->where('authorization','rejected')->whereMonth('created_at', date('m'))
            ->whereYear($this->fuel_filter, date('Y'))->orderBy('created_at','desc')->paginate(10);
          
        }

    }

      public function authorizeSelectedRows(){

         $this->validate([
            'authorize' => 'required',
        ]);

         DB::transaction(function () {

        $selected_fuels = Fuel::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_fuels)) {

             foreach($selected_fuels as $fuel){
        
                
                $fuel->authorized_by_id = Auth::user()->id;
                $fuel->authorization = $this->authorize;
                $fuel->authorization_date = now();
                $fuel->reason = $this->comments;
                $fuel->update();

                $company =  Auth::user()->employee->company;
                $user = $fuel->user;
                $email = $user?->email ?? null;
                $notification = "Fuel Order Authorization";
                if($email){
                    Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $fuel));
                }

                if ($this->authorize == "approved") {
        

                    $container = Container::find($fuel->container_id);

                    if (isset($container) || $fuel->source_horse_id) {

                    if ($fuel->horse) {
                        $horse = Horse::find($fuel->horse_id);
                        if((isset($horse->fuel_balance) && is_numeric($horse->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
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
                        if((isset($vehicle->fuel_balance) && is_numeric($vehicle->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
                            $vehicle->fuel_balance = $vehicle->fuel_balance + $fuel->quantity;
                        }

                        $current_mileage = $vehicle->mileage;
                        if ($fuel->odometer >  $current_mileage) {
                            $vehicle->mileage = $fuel->odometer;
                        }

                        $vehicle->update();

                    }

                    if ($fuel->source_horse_id) {
                        $source_horse = Horse::find($fuel->source_horse_id);
                        if ($source_horse && isset($source_horse->fuel_balance) && is_numeric($source_horse->fuel_balance) && isset($fuel->quantity) && is_numeric($fuel->quantity)) {
                            $source_horse->fuel_balance = $source_horse->fuel_balance - $fuel->quantity;
                            $source_horse->update();
                        }
                    }

                    $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
                    if(isset($last_mileage)){
                        if($last_mileage < $fuel->odometer){
                            $mileage = new Mileage;
                            $mileage->user_id = Auth::user()->id;
                            $mileage->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                            $mileage->fuel_id = $fuel->id;
                            $mileage->horse_id = $fuel->horse_id;
                            $mileage->vehicle_id = $fuel->vehicle_id;
                            $mileage->mileage = $fuel->odometer;
                            $mileage->date = $fuel->date;
                            $mileage->category = "Fuel Order";
                            $mileage->save();
                        }
                    }
                
                    $last_hours = Hour::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
                    if(isset($last_hours)){
                        if($last_hours < $fuel->hours){
                            $hours = new Hour;
                            $hours->user_id = Auth::user()->id;
                            $hours->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                            $hours->fuel_id = $fuel->id;
                            $hours->horse_id = $fuel->horse_id;
                            $hours->vehicle_id = $fuel->vehicle_id;
                            $hours->hours = $fuel->hours;
                            $hours->date = $fuel->date;
                            $hours->category = "Fuel Order";
                            $hours->save();
                        }
                    }


                   if ($container) {
                   if($container->purchase_type == "Bulk Buy"){

                        if ($fuel->deduct_from == "quantity") {
                            if($container->balance && is_numeric($container->balance) && ($fuel->quantity && is_numeric($fuel->quantity)) ){
                                if($container->balance >= $fuel->quantity){
                                    $container->balance = $container->balance - $fuel->quantity;
                                }
                            }
                        }elseif($fuel->deduct_from == "account"){
                            if($container->account_balance && is_numeric($container->account_balance) && ($fuel->amount && is_numeric($fuel->amount)) ){
                                if($container->account_balance >= $fuel->amount){
                                    $container->account_balance = $container->account_balance - $fuel->amount;
                                }
                            }
                        }

                        $container->update();
                    }

                    $expense = Expense::where('name','Fuel Topup')->get()->first();


                    $bill = new Bill;
                    if($fuel->trip){
                        $bill->trip_id = $fuel->trip_id;
                        $trip_expense = $fuel->trip_expense;
                        if(isset($trip_expense)){
                            $bill->trip_expense_id = $trip_expense->id;
                        }
                        $bill->category = "Trip Expense - Fuel Order";
                        $account = Account::where('name','Trip Expense')->get()->first();
                    }else{
                        $bill->category = "Fuel";
                        $account = Account::where('name','Fuel')->get()->first();
                    }
                    $bill->user_id = Auth::user()->id;
                    $bill->bill_number = $this->billNumber();
                  
                    if (isset($account)) {
                        $bill->account_id = $account->id;
                        $bill->account_type_id = $account->account_type->id;
                    }
    
                    if($fuel->container->purchase_type == "Once Off Buy"){
                        $bill->to_be_paid = True;
                    }else{
                        $bill->to_be_paid = False;
                    }
    
                   
                   
                    
                    
    
                    $bill->fuel_id = $fuel->id;
                    $bill->bill_date = $fuel->date;
                    $bill->horse_id = $fuel->horse_id;
                    $bill->vehicle_id = $fuel->vehicle_id;
                    $bill->asset_id = $fuel->asset_id;
                    $bill->currency_id = $fuel->currency_id;
                    $bill->subtotal = $fuel->amount;
                    $bill->total = $fuel->amount;
                    $bill->balance = $fuel->amount;
                    $bill->authorized_by_id = Auth::user()->id;
                    $bill->authorization = $this->authorize;
                    $bill->comments = $this->comments;
                    $bill->save();
    
                    $bill_expense = new BillExpense;
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    if($fuel->trip){
                        if (isset($expense)) {
                            $bill_expense->expense_id = $expense->id;
                        }
                    }
                  
                    if (isset($account)) {
                        $bill_expense->account_id = $account->id;
                        $bill_expense->account_type_id = $account->account_type->id;
                    }
                    $bill_expense->qty = $fuel->quantity;
                    $bill_expense->amount = $fuel->unit_price;
                    $bill_expense->subtotal = $fuel->amount;
                    $bill_expense->subtotal_incl = $fuel->amount;
                    $bill_expense->save();


                    // sending fuel order email to station
                    $trip = $fuel->trip;
                    $this->station_email = $fuel->container ? $fuel->container->email : "";
                    $this->station_name = $fuel->container ? $fuel->container->name : "";
                    $this->date = $fuel->date;
                    $this->order_number = $fuel->order_number;
                    $this->driver = $fuel->driver;
                    $this->horse = $fuel->horse;
                    $this->vehicle = $fuel->vehicle;
                    if (isset($trip)) {
                        $this->collection_point = $trip->loading_point ? $trip->loading_point->name : "";
                        $this->delivery_point = $trip->offloading_point ? $trip->offloading_point->name : "";
                    }
                 
                    $this->fuel_type = $fuel->container ? $fuel->container->fuel_type : "";
                    $this->quantity = $fuel->quantity;
                    $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
                    $this->checked_by = $fuel->user->employee->name . ' ' . $fuel->user->employee->surname;
                    $this->regnumber = $fuel->horse ? $fuel->horse->registration_number : "";
        
                    if ($this->station_email != "") {

                        if (filter_var($this->station_email, FILTER_VALIDATE_EMAIL)) {

                        $data = array(
                            'station_email'=> $this->station_email,
                            'station_name'=> $this->station_name,
                            'date'=> $this->date,
                            'order_number'=> $this->order_number,
                            'driver'=> $this->driver,
                            'horse'=> $this->horse,
                            'vehicle'=> $this->vehicle,
                            'regnumber'=> $this->regnumber,
                            'authorized_by'=> $this->authorized_by,
                            'checked_by'=> $this->checked_by,
                            'collection_point'=> $this->collection_point,
                            'delivery_point'=> $this->delivery_point,
                            'fuel_type'=> $this->fuel_type,
                            'quantity'=> $this->quantity,
                        );
            
                        if (isset(Auth::user()->company)) {
                            $company = Auth::user()->company;
                            }elseif (isset(Auth::user()->employee->company)) {
                                $company = Auth::user()->employee->company;
                            }
            
                        Mail::to($this->station_email)->send(new FuelOrderMail($data, $company));
            
                        
            
                        }

                    }
                    }

                }else{
                    $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Please select fueling station to proceed!!"
                    ]);
                }

                }
            }

            $this->reset(['selectedRows','selectPageRows']);

            if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Fuel Order(s) Approved Successfully !!"
                ]);
                return redirect()->route('fuels.approved');
            }else {
    
                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Fuel Order(s) Rejected Successfully"
                ]);
                return redirect()->route('fuels.rejected');
            }
     

          

        }

    });

    }


      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

         DB::transaction(function () {

        $fuel = Fuel::find($this->fuel_id);

        $fuel->authorized_by_id = Auth::user()->id;
        $fuel->authorization = $this->authorize;
        $fuel->authorization_date = now();
        $fuel->reason = $this->comments;
        $fuel->update();

        $company =  Auth::user()->employee->company;
        $user = $fuel->user;
        $email = $user?->email ?? null;
        $notification = "Fuel Order Authorization";
        if($email){
            Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $fuel));
        }

      
        if ($fuel->authorization == "approved") {
            $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Fuel Order Approved Already"
            ]);
        }else {

        if ($this->authorize == "approved") {

            $container = Container::find($fuel->container_id);

            if (isset($container) || $fuel->source_horse_id) {

            if ($fuel->horse) {
                $horse = Horse::find($fuel->horse_id);
                if((isset($horse->fuel_balance) && is_numeric($horse->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
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
                if((isset($vehicle->fuel_balance) && is_numeric($vehicle->fuel_balance)) && (isset($fuel->quantity) && is_numeric($fuel->quantity))){
                    $vehicle->fuel_balance = $vehicle->fuel_balance + $fuel->quantity;
                }

                $current_mileage = $vehicle->mileage;
                if ($fuel->odometer >  $current_mileage) {
                    $vehicle->mileage = $fuel->odometer;
                }

                $vehicle->update();

            }

            if ($fuel->source_horse_id) {
                $source_horse = Horse::find($fuel->source_horse_id);
                if ($source_horse && isset($source_horse->fuel_balance) && is_numeric($source_horse->fuel_balance) && isset($fuel->quantity) && is_numeric($fuel->quantity)) {
                    $source_horse->fuel_balance = $source_horse->fuel_balance - $fuel->quantity;
                    $source_horse->update();
                }
            }

            $last_mileage = Mileage::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
            if(isset($last_mileage)){
                if($last_mileage < $fuel->odometer){
                    $mileage = new Mileage;
                    $mileage->user_id = Auth::user()->id;
                    $mileage->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                    $mileage->fuel_id = $fuel->id;
                    $mileage->horse_id = $fuel->horse_id;
                    $mileage->vehicle_id = $fuel->vehicle_id;
                    $mileage->mileage = $fuel->odometer;
                    $mileage->date = $fuel->date;
                    $mileage->category = "Fuel Order";
                    $mileage->save();
                }
            }
           
            $last_hours = Hour::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->first();
            if(isset($last_hours)){
                if($last_hours < $fuel->hours){
                    $hours = new Hour;
                    $hours->user_id = Auth::user()->id;
                    $hours->trip_id = $fuel->trip_id ? $fuel->trip_id : Null;
                    $hours->fuel_id = $fuel->id;
                    $hours->horse_id = $fuel->horse_id;
                    $hours->vehicle_id = $fuel->vehicle_id;
                    $hours->hours = $fuel->hours;
                    $hours->date = $fuel->date;
                    $hours->category = "Fuel Order";
                    $hours->save();
                }
            }
            
                if ($container) {
                if($container->purchase_type == "Bulk Buy"){

                        if ($fuel->deduct_from == "quantity") {
                            if($container->balance && is_numeric($container->balance) && ($fuel->quantity && is_numeric($fuel->quantity)) ){
                                if($container->balance >= $fuel->quantity){
                                    $container->balance = $container->balance - $fuel->quantity;
                                } 
                            }
                        }elseif($fuel->deduct_from == "account"){
                            if($container->account_balance && is_numeric($container->account_balance) && ($fuel->amount && is_numeric($fuel->amount)) ){
                                if($container->account_balance >= $fuel->amount){
                                    $container->account_balance = $container->account_balance - $fuel->amount;
                                }
                            }
                        }
                        
                        $container->update();
                    }
            

                $expense = Expense::where('name','Fuel Topup')->get()->first();
             

                $bill = new Bill;
                if($fuel->trip){
                    $bill->trip_id = $fuel->trip_id;
                    $trip_expense = $fuel->trip_expense;
                    if(isset($trip_expense)){
                        $bill->trip_expense_id = $trip_expense->id;
                    }
                    $bill->category = "Trip Expense - Fuel Order";
                    $account = Account::where('name','Trip Expense')->get()->first();
                }else{
                    $bill->category = "Fuel";
                    $account = Account::where('name','Fuel')->get()->first();
                }
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
              
                if (isset($account)) {
                    $bill->account_id = $account->id;
                    $bill->account_type_id = $account->account_type->id;
                }

                if($fuel->container->purchase_type == "Once Off Buy"){
                    $bill->to_be_paid = True;
                }else{
                    $bill->to_be_paid = False;
                }
               
               
                

                $bill->fuel_id = $fuel->id;
                $bill->bill_date = $fuel->date;
                $bill->currency_id = $fuel->currency_id;
                $bill->horse_id = $fuel->horse_id;
                $bill->vehicle_id = $fuel->vehicle_id;
                $bill->asset_id = $fuel->asset_id;
                $bill->total = $fuel->amount;
                $bill->subtotal = $fuel->amount;
                $bill->balance = $fuel->amount;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = $this->authorize;
                $bill->comments = $this->comments;
                $bill->save();

                $bill_expense = new BillExpense;
                $bill_expense->user_id = Auth::user()->id;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;

                if($fuel->trip){
                    if (isset($expense)) {
                        $bill_expense->expense_id = $expense->id;
                    }
                }
               
                if (isset($account)) {
                    $bill_expense->account_id = $account->id;
                    $bill_expense->account_type_id = $account->account_type->id;
                }
                $bill_expense->qty = $fuel->quantity;
                $bill_expense->amount = $fuel->unit_price;
                $bill_expense->subtotal = $fuel->amount;
                $bill_expense->subtotal_incl = $fuel->amount;
                $bill_expense->save();
       


            // sending fuel order email to station
            $trip = $fuel->trip;
            $this->station_email = $fuel->container ? $fuel->container->email : "";
            $this->station_name = $fuel->container ? $fuel->container->name : "";
            $this->date = $fuel->date;
            $this->order_number = $fuel->order_number;
            $this->driver = $fuel->driver;
            $this->horse = $fuel->horse;
            $this->vehicle = $fuel->vehicle;
            if (isset($trip)) {
                $this->collection_point = $trip->loading_point ? $trip->loading_point->name : "";
                $this->delivery_point = $trip->offloading_point ? $trip->offloading_point->name : "";
            }
         
            $this->fuel_type = $fuel->container ? $fuel->container->fuel_type : "";
            $this->quantity = $fuel->quantity;
            $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
            $this->checked_by = $fuel->user->employee->name . ' ' . $fuel->user->employee->surname;
            $this->regnumber = $fuel->horse ? $fuel->horse->registration_number : "";

           

            if ($this->station_email != "") {
            if (filter_var($this->station_email, FILTER_VALIDATE_EMAIL)) {
            $data = array(
                'station_email'=> $this->station_email,
                'station_name'=> $this->station_name,
                'date'=> $this->date,
                'order_number'=> $this->order_number,
                'driver'=> $this->driver,
                'horse'=> $this->horse,
                'vehicle'=> $this->vehicle,
                'regnumber'=> $this->regnumber,
                'authorized_by'=> $this->authorized_by,
                'checked_by'=> $this->checked_by,
                'collection_point'=> $this->collection_point,
                'delivery_point'=> $this->delivery_point,
                'fuel_type'=> $this->fuel_type,
                'quantity'=> $this->quantity,
               );

               if (isset(Auth::user()->company)) {
                $company = Auth::user()->company;
                }elseif (isset(Auth::user()->employee->company)) {
                    $company = Auth::user()->employee->company;
                }

               Mail::to($this->station_email)->send(new FuelOrderMail($data, $company));

               $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
               $this->dispatchBrowserEvent('alert',[
                   'type'=>'success',
                   'message'=>"Fuel Order Approved & Email to Station Sent Successfully"
               ]);
               return redirect()->route('fuels.approved');

            } else {
                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Fuel Order Approved But Email Not Sent."
                ]);
                return redirect()->route('fuels.approved');
            }

            }
            } else {
                $this->station_email = "";
            }

            $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Order Approved Successfully !!"
            ]);
            return redirect()->route('fuels.approved');

        }else{
            $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Please select fueling station to proceed!!"
            ]);
        }
        }else {


            $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Order Rejected Successfully"
            ]);
            return redirect()->route('fuels.rejected');
        }
  }
   
    });

      }



  



    
    public function render()
    {
        
        return view('livewire.fuels.rejected',[
            'fuels' => $this->fuels,
            'fuel_filter' => $this->fuel_filter,
        ]);
    }
}
