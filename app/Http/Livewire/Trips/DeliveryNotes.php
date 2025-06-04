<?php

namespace App\Http\Livewire\Trips;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Currency;
use App\Models\TripStatus;
use App\Models\Measurement;
use App\Models\DeliveryNote;
use App\Mail\TripUpdatesMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DeliveryNotes extends Component
{
    public $trip;
    public $cargo_type;
    public $delivery_note;
    public $user;
    public $employee;
    public $company;
    public $weight_loss;
    public $quantity_loss;
    public $litreage_loss;
    public $litreage_at_20_loss;
    public $freight_loss;
    public $chargeable_weight_loss;
    public $chargeable_quantity_loss;
    public $chargeable_litreage_loss;
    public $pattern;

    public $trip_id;


    public $trip_number;

    public $driver_id;
    public $horse_id;

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
    public $ending_hours;
    public $starting_hours;
    public $checked_by;
    public $start_date;
    public $transporter_id;
    public $subtotal;
    public $cpk;
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
    public $actual_distance;
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
    public $total_expenses = 0;
    public $total_customer_expenses = 0;
    public $total_transporter_expenses = 0;
    public $cost_of_sales = 0;
    public $grossprofit;
    public $turnover = 0;

    public $role_names;
    public $department_names;
    public $rank_names;

    public $active_tab;


    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->delivery_note = $this->trip->delivery_note;
        $this->cargo_type = $this->trip->cargo ? $this->trip->cargo->type : "";
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->company = $this->employee->company;

        $this->currency = Currency::with('trips')->find($this->trip->currency_id); 
        $this->currency_id = $this->trip->currency_id; 
        $this->currencies = Currency::with('trips')->orderBy('name','asc')->get(); 
        $this->measurements = Measurement::orderBy('name','asc')->get(); 
        $this->measurement = $this->trip->measurement; 

        $departments = $this->employee->departments;
        foreach($departments as $department){
            $this->department_names[] = $department->name;
        }
        $roles = $this->user->roles;
        foreach($roles as $role){
            $this->role_names[] = $role->name;
        }
        $ranks = $this->employee->ranks;
        foreach($ranks as $rank){
            $this->rank_names[] = $rank->name;
        }
    }


    private function resetInputFields(){

        $this->trip_status = Null;
        $this->selectedStatus = Null;
        $this->currency_id = Null;
        $this->turnover = Null;
        $this->cost_of_sales = Null;
        $this->trip_status_date = Null;
        $this->selectedDeliveryNote = Null;
        $this->trip_status_description = Null;
        $this->customer_updates = Null;
        $this->freight_calculation = Null;
        $this->cargo_type = Null;
        $this->ending_mileage = Null;
        $this->starting_mileage = Null;
        $this->ending_hours = Null;
        $this->starting_hours = Null;
        $this->measurement = Null;
        $this->distance = Null;
        $this->loaded_quantity = Null;
        $this->loaded_litreage = Null;
        $this->loaded_litreage_at_20 = Null;
        $this->loaded_weight = Null;
        $this->loaded_rate = Null;
        $this->loaded_freight = Null;
        $this->transporter_loaded_rate = Null;
        $this->transporter_loaded_freight = Null;
        $this->loaded_date = Null;
        $this->transporter_loaded_rate = Null;
        $this->transporter_loaded_freight = Null;
        $this->offloaded_quantity = Null;
        $this->offloaded_litreage = Null;
        $this->offloaded_litreage_at_20 = Null;
        $this->offloaded_weight = Null;
        $this->offloaded_distance = Null;
        $this->offloaded_rate = Null;
        $this->offloaded_freight = Null;
        $this->transporter_offloaded_rate = Null;
        $this->transporter_offloaded_freight = Null;
        $this->offloaded_rate = Null;
        $this->offloaded_freight = Null;
        $this->transporter_offloaded_rate = Null;
        $this->transporter_offloaded_freight = Null;
        $this->offloaded_date = Null;

    }


    
    public function updatedSelectedStatus($status)
    {
    
        if (!is_null($status) ) {
            if ($status != $this->trip_status) {    
                $this->trip_status_date = Null;
                $this->trip_status_description = Null;
            }

            if ($status == "Offloaded" || $status == "Loaded") {
                $this->selectedDeliveryNote = TRUE;
            }else {
                $this->selectedDeliveryNote = NULL;
            }
        }

    }

    public function status($id){
    
      $trip = Trip::withTrashed()->find($id);
      $this->trip_id = $id;
      $this->trip_status = $trip->trip_status;
      $this->selectedStatus = $trip->trip_status;
      $this->currency_id = $trip->currency_id;
      $this->turnover = $trip->freight;
      $this->cost_of_sales = $trip->transporter_freight;
      $this->calculation_measurement = $trip->calculation_measurement;
      $this->trip_status_date = $trip->trip_status_date;
      if (isset( $this->selectedStatus) && ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded")) {
          $this->selectedDeliveryNote = TRUE;
      }
      $this->trip_status_description = $trip->trip_status_description;
      $this->customer_updates = $trip->customer_updates;
    
      $this->freight_calculation = $trip->freight_calculation;
      $this->cargo_type = $trip->cargo ? $trip->cargo->type : "";
      $this->ending_mileage = $trip->ending_mileage;
      $this->starting_mileage = $trip->starting_mileage;
      $this->ending_hours = $trip->ending_hours;
      $this->starting_hours = $trip->starting_hours;
      $delivery_note = $this->delivery_note;

      if ($delivery_note) {
          $this->measurement = $delivery_note->measurement;
          $this->distance = $delivery_note->distance;
          $this->loaded_quantity = $delivery_note->loaded_quantity;
          $this->loaded_litreage = $delivery_note->loaded_litreage;
          $this->loaded_litreage_at_20 = $delivery_note->loaded_litreage_at_20;
          $this->loaded_weight = $delivery_note->loaded_weight;
          $this->loaded_rate = $delivery_note->loaded_rate;
          $this->loaded_freight = $delivery_note->loaded_freight;
          $this->transporter_loaded_rate = $delivery_note->transporter_loaded_rate;
          $this->transporter_loaded_freight = $delivery_note->transporter_loaded_freight;
          $this->loaded_date = $delivery_note->loaded_date;

          if (!isset($this->transporter_loaded_rate) && !isset($this->transporter_loaded_freight)) {
              $delivery_note->transporter_loaded_rate = $trip->transporter_rate;
              $delivery_note->transporter_loaded_freight = $trip->transporter_freight;
              $this->transporter_loaded_rate = $trip->transporter_rate;
              $this->transporter_loaded_freight = $trip->transporter_freight;
              $delivery_note->update();
          }

          $this->offloaded_quantity = $delivery_note->offloaded_quantity;
          $this->offloaded_litreage = $delivery_note->offloaded_litreage;
          $this->offloaded_litreage_at_20 = $delivery_note->offloaded_litreage_at_20;
          $this->offloaded_weight = $delivery_note->offloaded_weight;
          $this->offloaded_distance = $delivery_note->offloaded_distance;

          if ($delivery_note->status == FALSE) {
              $this->offloaded_rate = $delivery_note->loaded_rate;
              $this->offloaded_freight = $delivery_note->loaded_freight;
              $this->transporter_offloaded_rate = $delivery_note->transporter_loaded_rate;
              $this->transporter_offloaded_freight = $delivery_note->transporter_loaded_freight;
          }else{
              $this->offloaded_rate = $delivery_note->offloaded_rate;
              $this->offloaded_freight = $delivery_note->offloaded_freight;
              $this->transporter_offloaded_rate = $delivery_note->transporter_offloaded_rate;
              $this->transporter_offloaded_freight = $delivery_note->transporter_offloaded_freight;
          }
       
          $this->offloaded_date = $delivery_note->offloaded_date;

         
      }else{
          $delivery_note = new DeliveryNote;
          $delivery_note->user_id = Auth::user()->id;
          $delivery_note->trip_id = $trip->id;
          $delivery_note->measurement = $trip->measurement;
          $delivery_note->distance = $trip->distance;
          $delivery_note->loaded_quantity = $trip->quantity;
          $delivery_note->loaded_litreage = $trip->litreage;
          $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
          $delivery_note->loaded_weight = $trip->weight;
          $delivery_note->loaded_rate = $trip->rate;
          $delivery_note->loaded_freight = $trip->freight;
          $delivery_note->transporter_loaded_rate = $trip->transporter_rate;
          $delivery_note->transporter_loaded_freight = $trip->transporter_freight;
          $delivery_note->loaded_date = $trip->start_date;
          $delivery_note->offloaded_quantity = $this->offloaded_quantity;
          $delivery_note->offloaded_litreage = $this->offloaded_litreage;
          $delivery_note->offloaded_litreage_at_20 = $this->offloaded_litreage_at_20;
          $delivery_note->offloaded_distance = $this->offloaded_distance;
          $delivery_note->offloaded_weight = $this->offloaded_weight;
          $delivery_note->offloaded_rate = $trip->rate;
          $delivery_note->offloaded_freight = $this->offloaded_freight;
          $delivery_note->transporter_offloaded_rate = $trip->transporter_rate;
          $delivery_note->transporter_offloaded_freight = $this->transporter_offloaded_freight;
          $delivery_note->offloaded_date = $this->offloaded_date;
          $delivery_note->comments = $this->comments;
          $delivery_note->save();

          $this->measurement = $delivery_note->measurement;
          $this->distance = $delivery_note->distance;
          $this->loaded_quantity = $delivery_note->loaded_quantity;
          $this->loaded_litreage = $delivery_note->loaded_litreage;
          $this->loaded_litreage_at_20 = $delivery_note->loaded_litreage_at_20;
          $this->loaded_weight = $delivery_note->loaded_weight;
          $this->loaded_rate = $delivery_note->loaded_rate;
          $this->loaded_freight = $delivery_note->loaded_freight;
          $this->transporter_loaded_rate = $delivery_note->transporter_loaded_rate;
          $this->transporter_loaded_freight = $delivery_note->transporter_loaded_freight;
          $this->loaded_date = $delivery_note->loaded_date;
          $this->offloaded_quantity = $delivery_note->offloaded_quantity;
          $this->offloaded_litreage = $delivery_note->offloaded_litreage;
          $this->offloaded_litreage_at_20 = $delivery_note->offloaded_litreage_at_20;
          $this->offloaded_distance = $delivery_note->offloaded_distance;
          $this->offloaded_weight = $delivery_note->offloaded_weight;
          
          if ($delivery_note->status == FALSE) {
              $this->offloaded_rate = $delivery_note->loaded_rate;
              $this->offloaded_freight = $delivery_note->loaded_freight;
              $this->transporter_offloaded_rate = $delivery_note->transporter_loaded_rate;
              $this->transporter_offloaded_freight = $delivery_note->transporter_loaded_freight;
          }else{
              $this->offloaded_rate = $delivery_note->offloaded_rate;
              $this->offloaded_freight = $delivery_note->offloaded_freight;
              $this->transporter_offloaded_rate = $delivery_note->transporter_offloaded_rate;
              $this->transporter_offloaded_freight = $delivery_note->transporter_offloaded_freight;
          }
    

          $this->offloaded_date = $delivery_note->offloaded_date;
          $this->transporter_loaded_rate = $trip->transporter_rate;
          $this->transporter_loaded_freight = $trip->transporter_freight;
      }

      $trip_destinations = $trip->trip_destinations;

      if(isset($trip_destinations)){
          $total_weight = $trip_destinations->where('weight','!=','')->where('weight','!=',Null)->sum('weight');
          if (isset($total_weight) && is_null($this->offloaded_weight)) {
              $this->offloaded_weight = $total_weight;
          }
          $total_quantity = $trip_destinations->where('quantity','!=','')->where('quantity','!=',Null)->sum('quantity');
          if (isset($total_quantity) && is_null($this->offloaded_quantity)) {
              $this->offloaded_quantity = $total_quantity;
          }
          $total_litreage = $trip_destinations->where('litreage','!=','')->where('litreage','!=',Null)->sum('litreage');
          if (isset($total_litreage) && is_null($this->offloaded_litreage)) {
              $this->offloaded_litreage = $total_litreage;
          }
          $total_litreage_at_20 = $trip_destinations->where('litreage_at_20','!=','')->where('litreage_at_20','!=',Null)->sum('litreage_at_20');
          if (isset($total_litreage_at_20) && is_null($this->offloaded_litreage_at_20)) {
              $this->offloaded_litreage_at_20 = $total_litreage_at_20;
          }
      }


      $this->dispatchBrowserEvent('show-statusModal');
    }


 

    public function update(){

      $trip = Trip::find($this->trip_id);
      $trip->trip_status = $this->selectedStatus;
      $trip->trip_status_date = $this->trip_status_date;
      $trip->trip_status_description = $this->trip_status_description;
      if ($this->selectedStatus == "Offloaded") {
      $trip->end_date = $this->offloaded_date;
      }
      $trip->ending_mileage = $this->ending_mileage;
      $trip->starting_mileage = $this->starting_mileage;
      $trip->ending_hours = $this->ending_hours;
      $trip->starting_hours = $this->starting_hours;
      $trip->update();

      if (isset($trip->vehicle_id)) {
          $vehicle = Vehicle::find($trip->vehicle_id);
            if ($vehicle) {
                $current_mileage = $vehicle->mileage;
                if($this->ending_mileage > $current_mileage){
                    $vehicle->mileage = $this->ending_mileage;
                }
                $vehicle->update();
            }

      }elseif(isset($trip->horse_id)){
            $horse = Horse::find($trip->horse_id);
            if ($horse) {
                $current_mileage = $horse->mileage;
                if($this->ending_mileage > $current_mileage){
                    $horse->mileage = $this->ending_mileage;
                }
                $horse->update();
            }
      }

      $trip_status = new TripStatus;
      $trip_status->user_id = Auth::user()->id;
      $trip_status->trip_id = $trip->id;
      $trip_status->status = $this->selectedStatus;
      $trip_status->date = $this->trip_status_date;
      $trip_status->description = $this->trip_status_description;
      $trip_status->save();
      
      if ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded") {

          $delivery_note = $this->delivery_note;
          if (isset($delivery_note)) {
              $delivery_note->measurement = $this->measurement;
              $delivery_note->loaded_quantity = $this->loaded_quantity;
              $delivery_note->distance = $this->distance;
              $delivery_note->loaded_litreage = $this->loaded_litreage;
              $delivery_note->loaded_litreage_at_20 = $this->loaded_litreage_at_20;
              $delivery_note->loaded_rate = $this->loaded_rate;
              $delivery_note->loaded_weight = $this->loaded_weight;
              $delivery_note->loaded_freight = $this->loaded_freight;
              $delivery_note->transporter_loaded_rate = $this->transporter_loaded_rate;
              $delivery_note->transporter_loaded_freight = $this->transporter_loaded_freight;
              $delivery_note->loaded_date = $this->loaded_date;
              $delivery_note->offloaded_quantity = $this->offloaded_quantity;
              $delivery_note->offloaded_litreage = $this->offloaded_litreage;
              $delivery_note->offloaded_litreage_at_20 = $this->offloaded_litreage_at_20;
              $delivery_note->offloaded_weight = $this->offloaded_weight;
              $delivery_note->offloaded_rate = $this->offloaded_rate;
              $delivery_note->offloaded_freight = $this->offloaded_freight;
              $delivery_note->offloaded_distance = $this->offloaded_distance;
              $delivery_note->transporter_offloaded_rate = $this->transporter_offloaded_rate;
              $delivery_note->transporter_offloaded_freight = $this->transporter_offloaded_freight;
              $delivery_note->offloaded_date = $this->offloaded_date;
              $delivery_note->comments = $this->comments;
              $delivery_note->status = 1;
              $delivery_note->update();
          }else {
              $delivery_note = new DeliveryNote;
              $delivery_note->user_id = Auth::user()->id;
              $delivery_note->trip_id = $trip->id;
              $delivery_note->measurement = $this->measurement;
              $delivery_note->loaded_quantity = $this->loaded_quantity;
              $delivery_note->distance = $this->distance;
              $delivery_note->loaded_litreage = $this->loaded_litreage;
              $delivery_note->loaded_litreage_at_20 = $this->loaded_litreage_at_20;
              $delivery_note->loaded_rate = $this->loaded_rate;
              $delivery_note->loaded_weight = $this->loaded_weight;
              $delivery_note->loaded_freight = $this->loaded_freight;
              $delivery_note->transporter_loaded_rate = $this->transporter_loaded_rate;
              $delivery_note->transporter_loaded_freight = $this->transporter_loaded_freight;
              $delivery_note->loaded_date = $this->loaded_date;
              $delivery_note->offloaded_quantity = $this->offloaded_quantity;
              $delivery_note->offloaded_litreage = $this->offloaded_litreage;
              $delivery_note->offloaded_litreage_at_20 = $this->offloaded_litreage_at_20;
              $delivery_note->offloaded_weight = $this->offloaded_weight;
              $delivery_note->offloaded_rate = $this->offloaded_rate;
              $delivery_note->offloaded_freight = $this->offloaded_freight;
              $delivery_note->offloaded_distance = $this->offloaded_distance;
              $delivery_note->transporter_offloaded_rate = $this->transporter_offloaded_rate;
              $delivery_note->transporter_offloaded_freight = $this->transporter_offloaded_freight;
              $delivery_note->offloaded_date = $this->offloaded_date;
              $delivery_note->comments = $this->comments;
              $delivery_note->status = 1;
              $delivery_note->save();
          }



      
      if ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Cancelled" || $this->selectedStatus == "Scheduled") {
          
          $horse = Horse::withTrashed()->find($trip->horse_id);
          $vehicle = Vehicle::withTrashed()->find($trip->vehicle_id);
          if (isset($horse)) {
              $horse->status = 1;

              if ($this->selectedStatus == "Offloaded") {
                  if ($horse->mileage != NULL && $trip->distance != NULL) {
                
                      if ($trip->breakdown_assignments->count() <= 0) {
                          if((is_numeric($horse->mileage) && $horse->mileage != "" && $horse->mileage != Null && $horse->mileage > 0) && (is_numeric($trip->distance) && $trip->distance != "" && $trip->distance != Null && $trip->distance > 0)){
                              $horse->mileage = $horse->mileage + $trip->distance; 
                          }
                        
                      }
                    
                  }
                  if ((isset($horse->fuel_balance) && $horse->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                      if ($trip->breakdown_assignments->count() <= 0) {
                          if((is_numeric($horse->fuel_balance) && $horse->fuel_balance != "" && $horse->fuel_balance != Null && $horse->fuel_balance > 0) && (is_numeric($trip->trip_fuel) && $trip->trip_fuel != "" && $trip->trip_fuel != Null && $trip->trip_fuel > 0 )){
                              $horse->fuel_balance = $horse->fuel_balance - $trip->trip_fuel;
                          }
                         
                      }
                    
                  }
              }
        
              $horse->update();
          }

          if (isset($vehicle)) {
              $vehicle->status = 1;
              if ($this->selectedStatus == "Offloaded") {
                  if ($vehicle->mileage != NULL && $trip->distance != NULL) {
                      if ($trip->breakdown_assignments->count() <= 0) {

                          if((is_numeric($vehicle->mileage) && $vehicle->mileage >0 ) && (is_numeric($trip->distance) && $trip->distance >0 ) ){
                              $vehicle->mileage = $vehicle->mileage + $trip->distance; 
                          }
                         
                      }
                    
                  }
                  if ((isset($vehicle->fuel_balance) && $vehicle->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                      if ($trip->breakdown_assignments->count() <= 0) {
                         
                          if((is_numeric($vehicle->fuel_balance) && $vehicle->fuel_balance >0 ) && (is_numeric($trip->trip_fuel) && $trip->trip_fuel >0 ) ){
                              $vehicle->fuel_balance = $vehicle->fuel_balance - $trip->trip_fuel;
                          }
                        
                      }
                    
                  }
              }
        
              $vehicle->update();
          }


          $driver = Driver::withTrashed()->find($trip->driver_id);
          if (isset($driver)) {
              $driver->status = 1;
              $driver->update();
          }
         
          if ($trip->trailers->count()>0) {
              foreach ($trip->trailers as $trailer) {
                  $trailer = Trailer::withTrashed()->find($trailer->id);
                  $trailer->status = 1;
                  $trailer->update();
              }
          }

          $breakdown_assignments = $trip->breakdown_assignments;
          if ($breakdown_assignments->count()>0) {
          
          foreach ($breakdown_assignments as $breakdown_assignment) {
              $horse = Horse::withTrashed()->find($breakdown_assignment->horse_id);
              $horse->status = 1;
              $horse->update();
  
              $driver = Driver::withTrashed()->find($breakdown_assignment->driver_id);
              $driver->status = 1;
              $driver->update();
  
              if ($breakdown_assignment->trailers->count()>0) {
                  foreach ($trip->trailers as $trailer) {
                      $trailer = Trailer::withTrashed()->find($trailer->id);
                      $trailer->status = 1;
                      $trailer->update();
                  }
              }
          }
              # code...
          }
      }

      
      if (isset(Auth::user()->company)) {
          $this->company = Auth::user()->company;
          }elseif (isset(Auth::user()->employee->company)) {
              $this->company = Auth::user()->employee->company;
          }
        
          if ($this->customer_updates == TRUE) {
              $this->customer_email = $trip->customer ? $trip->customer->email : "";
              if (isset($this->customer_email) && $this->customer_email != "") {
              Mail::to($this->customer_email)->send(new TripUpdatesMail($this->trip, $this->company));
                  }
              }

    }

   

    $this->resetInputFields();
    $this->dispatchBrowserEvent('hide-statusModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'success',
        'message'=>"Trip Status Updated Successfully!!"
    ]);
  //   return redirect(request()->header('Referer'));

  }

    public function render()
    {

        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        
        $this->delivery_note = DeliveryNote::where('trip_id',$this->trip->id)->first();
        $this->cargo_type = $this->trip->cargo ? $this->trip->cargo->type : "";

        if ($this->delivery_note){

            if ($this->delivery_note->offloaded_date){

                if (preg_match($this->pattern, $this->delivery_note->offloaded_date)){
                    $this->actual_offloading_date = Carbon::parse($this->delivery_note->offloaded_date)->format('d M Y g:i A');
                }else{
                    $this->actual_offloading_date = $this->delivery_note->offloaded_date;
                }
               

            }
        }
        
        if ((is_numeric($this->delivery_note->loaded_weight) && $this->delivery_note->loaded_weight > 0 ) && ( is_numeric($this->delivery_note->offloaded_weight) && $this->delivery_note->offloaded_weight > 0 )) {
            $this->weight_loss = $this->delivery_note->loaded_weight - $this->delivery_note->offloaded_weight;
            if ((is_numeric($this->weight_loss) && $this->weight_loss > 0) && (is_numeric($this->trip->allowable_loss_weight) && $this->trip->allowable_loss_weight > 0)) {
                $this->chargeable_weight_loss =   $this->weight_loss - $this->trip->allowable_loss_weight;
            }
        }

       

        if ((is_numeric($this->delivery_note->loaded_quantity) && $this->delivery_note->loaded_quantity > 0 ) && (is_numeric($this->delivery_note->offloaded_quantity) && $this->delivery_note->offloaded_quantity > 0) ) {
            $this->quantity_loss = $this->delivery_note->loaded_quantity - $this->delivery_note->offloaded_quantity;
        }

        if ((is_numeric($this->quantity_loss) && $this->quantity_loss > 0) && (is_numeric($this->trip->allowable_loss_quantity) && $this->trip->allowable_loss_quantity > 0)) {
            $this->chargeable_quantity_loss =   $this->quantity_loss - $this->trip->allowable_loss_quantity;
        }

        if ((is_numeric($this->delivery_note->loaded_litreage) && $this->delivery_note->loaded_litreage > 0) && (is_numeric($this->delivery_note->offloaded_litreage) && $this->delivery_note->offloaded_litreage > 0)) {
            $this->litreage_loss = $this->delivery_note->loaded_litreage - $this->delivery_note->offloaded_litreage;
        }

        if ((is_numeric($this->delivery_note->loaded_litreage_at_20) && $this->delivery_note->loaded_litreage_at_20 > 0 ) && (is_numeric($this->delivery_note->offloaded_litreage_at_20) && $this->delivery_note->offloaded_litreage_at_20 > 0)) {
            $this->litreage_at_20_loss = $this->delivery_note->loaded_litreage_at_20 - $this->delivery_note->offloaded_litreage_at_20;
        }

        if ((is_numeric($this->litreage_at_20_loss) && $this->litreage_at_20_loss > 0) && (is_numeric($this->trip->allowable_loss_litreage) && $this->trip->allowable_loss_litreage > 0)) {
            $this->chargeable_litreage_loss =   $this->litreage_at_20_loss - $this->trip->allowable_loss_litreage;
           }

        if ((is_numeric($this->delivery_note->loaded_freight) && $this->delivery_note->loaded_freight > 0) && (is_numeric($this->delivery_note->offloaded_freight) && $this->delivery_note->offloaded_freight > 0)) {
            $this->freight_loss = $this->delivery_note->loaded_freight - $this->delivery_note->offloaded_freight;
        }
     
        return view('livewire.trips.delivery-notes');
    }
}
