<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Country;
use App\Models\Mileage;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\TripStatus;
use App\Exports\PodTracker;
use App\Exports\TripsExport;
use App\Imports\TripsImport;
use App\Models\DeliveryNote;
use App\Models\TripDocument;
use App\Models\TripLocation;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Mail\TripUpdatesMail;
use Livewire\WithFileUploads;
use App\Exports\TripsReportExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $trips;
    public $trip_id;
    public $status;
    public $measurement;
    public $trip_filter;
    public $from;
    public $to;
    public $sea;
    public $loaded;
    public $loaded_date;
    public $offloaded;
    public $offloaded_date;
    public $payment_status;
    public $selectedStatus = NULL;
    public $selectedDeliveryNote = NULL;
    public $intransit_trips;
    public $calculation_measurement;

    public $date;
    public $countries;
    public $country_id;
    public $horse_id;
    public $city;
    public $description;
    public $suburb;
    public $street_address;

    public $search;
    protected $queryString = ['search'];

    public $title;
    public $file;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

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
   
    public $company;
    public $trip;
    public $trip_number;

    public $driver_id;

 
    public $trailer_regnumbers;
    public $trailer_reg_numbers;
    public $collection_point;
    public $deliver_point;
    public $weight;
    public $cargo;
 
    public $litreage;
    public $quantity;
    public $authorized_by;
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
    public $customer_updates;
    public $customer_id;
    public $currency_id;
    public $currency;
    public $trailers;
    public $fuel_order_date;
    public $from_destination;
    public $to_destination;
    public $from_destination_country;
    public $to_destination_country;
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
    public $authorize;
    public $comments;
    public $customer_total;
    public $transporter_total;
    public $currencies;

    public $loaded_quantity;
    public $loaded_litreage;
    public $loaded_litreage_at_20;
    public $loaded_weight;
    public $loaded_rate;
    public $loaded_freight;
    public $ending_mileage;
    public $starting_mileage;

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
   
  
    public $trip_status_date;
    public $trip_status_description;
  
    public $freight_calculation;
    public $total_customer_expenses = 0;
    public $total_transporter_expenses = 0;
    public $cost_of_sales = 0;
    public $grossprofit;
    public $turnover = 0;
    public $cargo_type;
    public $importFile;
   
        
    public function exportPodTrackerExcel(Excel $excel){

        return $excel->download(new PodTracker($this->from, $this->to, $this->trip_filter, $this->search), 'pod_tracking_' .time().'.xlsx');
    }
    public function exportTripsCSV(Excel $excel){

        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search), 'trips_' .time().'.csv', Excel::CSV);
    }

    public function exportTripsPDF(Excel $excel){

        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search), 'trips_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search), 'trips_' .time().'.xlsx');
    }
    

   
    public function importTrips(){
      
        $file = $this->importFile;
        $import = new TripsImport;
        $import->import($file);

        $this->dispatchBrowserEvent('hide-tripsImportModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip(s) Imported Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
    }
 
    private function resetInputFields(){

        $this->trip_id = Null;
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
    
    
    public function mount(){
        $this->resetPage();
        $this->trip_filter = "created_at";
        $this->countries = Country::orderBy('name','asc')->get();
     
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
        $this->trip_id = $trip->id;
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
        $delivery_note = $trip->delivery_note;
        $this->freight_calculation = $trip->freight_calculation;
        $this->cargo_type = $trip->cargo ? $trip->cargo->type : "";
        $this->ending_mileage = $trip->ending_mileage;
        $this->starting_mileage = $trip->starting_mileage;

        if (isset($delivery_note)) {
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

        $trip = Trip::withTrashed()->find($this->trip_id);
        $trip->trip_status = $this->selectedStatus;
        $trip->trip_status_date = $this->trip_status_date;
        $trip->trip_status_description = $this->trip_status_description;
        if ($this->selectedStatus == "Offloaded") {
        $trip->end_date = $this->offloaded_date;
        }
        $trip->ending_mileage = $this->ending_mileage;
        $trip->starting_mileage = $this->starting_mileage;
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

            $delivery_note = $trip->delivery_note;
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

      public function editLocations(){
        $this->intransit_trips = Trip::with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->where('trip_status','!=','Offloaded')->where('trip_status','!=','Cancelled')->where('trip_status','!=','Scheduled')->where('authorization','approved')->orderBy($this->trip_filter,'desc')->get();
        $this->dispatchBrowserEvent('show-locationsEditModal');
      }

      public function updateTripStatus(){

        if (isset($this->status)) {
            foreach ($this->status as $key => $value) {
              
                $trip = Trip::withTrashed()->find($key);
             
                if (isset($this->status[$key])) {
                    $trip->trip_status = $this->status[$key];

                    if ( $this->status[$key] == "Offloaded" || $this->status[$key] == "Cancelled" || $this->status[$key] == "Scheduled") {
                       
                        $horse = Horse::withTrashed()->find($trip->horse_id);
                        if (isset($horse)) {
                            $horse->status = 1;
                            if (($trip->trip_status != "Offloaded" && $this->status[$key] == "Offloaded")) {
                                if ($horse->mileage != NULL && $trip->distance != NULL) {
                              
                                    if ($trip->breakdown_assignments->count() <= 0) {
                                        if(($horse->mileage != "" && $horse->mileage != Null && $horse->mileage >= 0) && ($trip->distance != "" && $trip->distance != Null && $trip->distance >= 0)){
                                            $horse->mileage = $horse->mileage + $trip->distance; 
                                        }
                                    }
                                  
                                }
                                if ((isset($horse->fuel_balance) && $horse->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                                    if ($trip->breakdown_assignments->count() <= 0) {
                                        if(($horse->fuel_balance != "" && $horse->fuel_balance != Null && $horse->fuel_balance >= 0) && ($trip->trip_fuel != "" && $trip->trip_fuel != Null && $trip->trip_fuel >= 0)){
                                            $horse->fuel_balance = $horse->fuel_balance - $trip->trip_fuel;
                                        }
                                    }
                                  
                                }
                            }
                     

                            $horse->update();
                        }

                        $vehicle = Vehicle::withTrashed()->find($trip->vehicle_id);
                        if (isset($vehicle)) {
                            $vehicle->status = 1;

                            if (($trip->trip_status != "Offloaded" && $this->status[$key] == "Offloaded")) {
                                if ($vehicle->mileage != NULL && $trip->distance != NULL) {
                              
                                    if ($trip->breakdown_assignments->count() <= 0) {
                                        if(($vehicle->mileage != "" && $vehicle->mileage != Null && $vehicle->mileage >= 0) && ($trip->distance != "" && $trip->distance != Null && $trip->distance >= 0)){
                                            $vehicle->mileage = $vehicle->mileage + $trip->distance; 
                                        }
                                    }
                                  
                                }
                                if ((isset($vehicle->fuel_balance) && $vehicle->fuel_balance > 0) && $trip->trip_fuel != NULL) {
                                    if ($trip->breakdown_assignments->count() <= 0) {
                                        if(($vehicle->fuel_balance != "" && $vehicle->fuel_balance != Null && $vehicle->fuel_balance >= 0) && ($trip->trip_fuel != "" && $trip->trip_fuel != Null && $trip->trip_fuel >= 0)){
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
                }
              
                if (isset($this->date[$key])) {
                    $trip->trip_status_date = $this->date[$key];
                    if(isset($this->status[$key]) && $this->status[$key] == "Offloaded"){
                        $trip->end_date = $this->date[$key];
                    }
                }
                
                if (isset($this->description[$key])) {
                    $trip->trip_status_description = $this->description[$key];
                }
               
                $trip->update();
        
                $trip_status = new TripStatus;
                $trip_status->user_id = Auth::user()->id;
                $trip_status->trip_id = $trip->id;

                if (isset($this->status[$key])) {
                    $trip_status->status = $this->status[$key];
                }
                if (isset($this->date[$key])) {
                    $trip_status->date = $this->date[$key];
                }
                if (isset($this->description[$key])) {
                    $trip_status->description = $this->description[$key];
                }

                $trip_status->save();
    

                $trip_location = new TripLocation;
                $trip_location->user_id = Auth::user()->id;
                $trip_location->trip_id = $trip->id;
                $trip_location->horse_id = $trip->horse_id;
                $trip_location->driver_id = $trip->driver_id;
           
                if (isset($this->country_id[$key])) {
                    $trip_location->country_id = $this->country_id[$key];
                }
                if (isset($this->description[$key])) {
                    $trip_location->description = $this->description[$key];
                }
                $trip_location->save();

                $this->dispatchBrowserEvent('hide-locationsEditModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Trip Statuses Updated Successfully!! "
                ]);
            }
        }
      }

    
   
    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function calculateCPK($id){

        $cpk = Null;
        $expenses = Null;
        $distance = Null;
        $bills = Bill::where('trailer_id',$id)->where('authorization','approved')->whereYear('created_at',date('Y'))->get();

        if (isset($bills)) {
            foreach ($bills as $bill) {
                if ($bill->currency_id == Auth::user()->employee->company->currency_id) {
                    $expenses = $expenses + $bill->total;
                }elseif($bill->currency_id != Auth::user()->employee->company->currency_id){
                    $expenses = $expenses + $bill->exchange_amount;
                }else{
                    $expenses = Null;
                }
               
            }
        }else{
            $expenses = Null;
        }

        $last_mileage = Mileage::where('trailer_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','desc')->first();
        $first_mileage = Mileage::where('trailer_id',$id)->whereYear('created_at', date('Y'))->orderBy('created_at','asc')->first();
        
        if ((isset($last_mileage) && is_numeric($last_mileage)) && (isset($first_mileage) && is_numeric($first_mileage))) {

            if ($last_mileage > $first_mileage) {
                $distance = $last_mileage - $first_mileage;
            }else{
                $distance = Null;
            }

           
        }else {
            $distance = Null;
        }
       
        if ((isset($expenses) && is_numeric($expenses)) && (isset($distance) && is_numeric($distance)  )  ) {
            $cpk = $expenses / $distance;
            return $cpk;
        }else{
            return $cpk;
        }
      

       

}

   public function updatedTripStatusDate($date){
        if(isset($this->selectedStatus) && ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded")){
            $this->offloaded_date = $date;
        }
      }

    
    public function render()
    {

        if(isset($this->selectedStatus) && $this->selectedStatus == "Offloaded"){
            $this->offloaded_date = $this->trip_status_date;
        }
        if(isset($this->selectedStatus) && $this->selectedStatus == "Loaded"){
            $this->loaded_date = $this->trip_status_date;
        }

        if ($this->freight_calculation == "rate_weight") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->offloaded_rate) && is_numeric($this->offloaded_weight)){
                    $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_weight;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->offloaded_rate) && (is_numeric($this->offloaded_litreage_at_20) || is_numeric($this->offloaded_litreage))){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_litreage;
                    }
                   
                }
                
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if (is_numeric($this->offloaded_rate)  && is_numeric($this->offloaded_distance)) {
                $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->offloaded_rate) && is_numeric($this->offloaded_weight) && is_numeric($this->offloaded_distance)){
                    $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_weight * $this->offloaded_distance;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->offloaded_rate) && (is_numeric($this->offloaded_litreage_at_20) || is_numeric($this->offloaded_litreage) ) && is_numeric($this->offloaded_distance)){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_litreage_at_20 * $this->offloaded_distance ;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->offloaded_freight = $this->offloaded_rate * $this->offloaded_litreage * $this->offloaded_distance ;
                    }
                   
                }
               
            }
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->offloaded_rate)){
                    $this->offloaded_freight = $this->offloaded_rate;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->offloaded_rate)){
                    $this->offloaded_freight = $this->offloaded_rate;
                }
            }   
        }

        if ($this->freight_calculation == "rate_weight") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->transporter_offloaded_rate) && is_numeric($this->offloaded_weight)){
                    $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_weight;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->transporter_offloaded_rate) && (is_numeric($this->offloaded_litreage_at_20) || is_numeric($this->offloaded_litreage) )){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_litreage;
                    }
                 
                }
              
            } 
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if(is_numeric($this->transporter_offloaded_rate) && is_numeric($this->offloaded_distance)){
                $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->transporter_offloaded_rate) && is_numeric($this->offloaded_weight) && is_numeric($this->offloaded_distance)){
                    $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_weight * $this->offloaded_distance;
                }
              
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->transporter_offloaded_rate) && (is_numeric($this->offloaded_litreage_at_20) || is_numeric($this->offloaded_litreage) ) && is_numeric($this->offloaded_distance)){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_litreage_at_20 * $this->offloaded_distance;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_offloaded_freight = $this->transporter_offloaded_rate * $this->offloaded_litreage * $this->offloaded_distance;
                    } 
                }
              
            } 
        }
        elseif ($this->freight_calculation == "flat_rate") {
            $this->transporter_offloaded_freight = $this->transporter_offloaded_rate;
        }

        ///loaded calculations

        if ($this->freight_calculation == "rate_weight") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->loaded_rate) && is_numeric($this->loaded_weight)){
                    $this->loaded_freight = $this->loaded_rate * $this->loaded_weight;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->offloaded_rate) && (is_numeric($this->loaded_litreage_at_20) || is_numeric($this->loaded_litreage))){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->loaded_freight = $this->loaded_rate * $this->loaded_litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->loaded_freight = $this->loaded_rate * $this->loaded_litreage;
                    } 
                   
                }
                
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if (is_numeric($this->loaded_rate)  && is_numeric($this->distance)) {
                $this->loaded_freight = $this->loaded_rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->loaded_rate) && is_numeric($this->loaded_weight) && is_numeric($this->distance)){
                    $this->loaded_freight = $this->loaded_rate * $this->loaded_weight * $this->distance;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->loaded_rate) && (is_numeric($this->loaded_litreage_at_20) || is_numeric($this->loaded_litreage)) && is_numeric($this->distance)){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->loaded_freight = $this->loaded_rate * $this->loaded_litreage_at_20 * $this->distance ;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->loaded_freight = $this->loaded_rate * $this->loaded_litreage * $this->distance ;
                    } 
                   
                }
               
            }
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->loaded_rate)){
                    $this->loaded_freight = $this->loaded_rate;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->offloaded_rate)){
                    $this->loaded_freight = $this->loaded_rate;
                }
            }   
        }

        if ($this->freight_calculation == "rate_weight") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->transporter_loaded_rate) && is_numeric($this->loaded_weight)){
                    $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_weight;
                }
               
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->transporter_loaded_rate) && (is_numeric($this->loaded_litreage_at_20) || is_numeric($this->loaded_litreage))){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_litreage_at_20;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_litreage;
                    } 
                   
                }
              
            } 
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if(is_numeric($this->transporter_loaded_rate) && is_numeric($this->distance)){
                $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ($this->cargo_type == "Solid") {
                if(is_numeric($this->transporter_loaded_rate) && is_numeric($this->loaded_weight) && is_numeric($this->distance)){
                    $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_weight * $this->distance;
                }
              
            }elseif($this->cargo_type == "Liquid"){
                if(is_numeric($this->transporter_loaded_rate) && (is_numeric($this->loaded_litreage_at_20) || is_numeric($this->loaded_litreage)) && is_numeric($this->distance)){
                    if($this->calculation_measurement == "litreage_at_20"){
                        $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_litreage_at_20 * $this->distance;
                    }elseif($this->calculation_measurement == "litreage_at_ambient"){
                        $this->transporter_loaded_freight = $this->transporter_loaded_rate * $this->loaded_litreage * $this->distance;
                    } 
                   
                }
              
            } 
        }
        elseif ($this->freight_calculation == "flat_rate") {
            $this->transporter_loaded_freight = $this->transporter_loaded_rate;
        }

        if($this->trip_filter == "offloaded_date"){
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.trips.index',[
                        'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])
                        ->whereHas('delivery_note', function ($query) {
                            return $query->whereBetween($this->trip_filter,[$this->from, $this->to] );
                        })
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereDate('start_date','like', '%'.$this->search.'%')
                        ->orWhereDate('end_date','like', '%'.$this->search.'%')
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->whereDate('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('cargo', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('user.employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('driver.employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
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
                        ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                        ->orderBy('delivery_notes.offloaded_date','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else {
                    return view('livewire.trips.index',[
                        'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])
                        ->whereHas('delivery_note', function ($query) {
                            return $query->whereBetween($this->trip_filter,[$this->from, $this->to] );
                        })
                        ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                        ->orderBy('delivery_notes.offloaded_date','desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.trips.index',[
                    'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                    'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                    'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                    'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])
                    ->whereHas('delivery_note', function ($query) {
                        return $query->whereMonth($this->trip_filter, date('m'))->whereYear($this->trip_filter, date('Y'));
                    })
                    
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('cargo', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereDate('start_date','like', '%'.$this->search.'%')
                    ->orWhereDate('end_date','like', '%'.$this->search.'%')
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->whereDate('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('driver.employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip_documents', function ($query) {
                        return $query->where('document_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                    ->orderBy('delivery_notes.offloaded_date','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {
               
                return view('livewire.trips.index',[
                    'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                    'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                    'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                    'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])
                    ->whereHas('delivery_note', function ($query) {
                        return $query->whereMonth($this->trip_filter, date('m'))->whereYear($this->trip_filter, date('Y'));
                    })
                    ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                    ->orderBy('delivery_notes.offloaded_date','desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
              
            }

            // end of offloaded date

        }else{
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return view('livewire.trips.index',[
                        'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->whereBetween($this->trip_filter,[$this->from, $this->to] )
                        ->where('trip_number','like', '%'.$this->search.'%')
                        ->orWhere('trip_status','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereDate('start_date','like', '%'.$this->search.'%')
                        ->orWhereDate('end_date','like', '%'.$this->search.'%')
                        ->orWhereHas('delivery_note', function ($query) {
                            return $query->whereDate('offloaded_date', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('cargo', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('registration_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('horse', function ($query) {
                            return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                        })
                       ->orWhereHas('user.employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('driver.employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
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
                        ->orderBy($this->trip_filter,'desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }else {
                    return view('livewire.trips.index',[
                        'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->whereBetween($this->trip_filter,[$this->from, $this->to] )->orderBy($this->trip_filter,'desc')->paginate(10),
                        'trip_filter' => $this->trip_filter
                    ]);
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.trips.index',[
                    'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                    'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                    'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                    'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))
                    ->where('trip_number','like', '%'.$this->search.'%')
                    ->orWhere('trip_status','like', '%'.$this->search.'%')
                    ->orWhere('authorization','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('customer', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('cargo', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereDate('start_date','like', '%'.$this->search.'%')
                    ->orWhereDate('end_date','like', '%'.$this->search.'%')
                    ->orWhereHas('delivery_note', function ($query) {
                        return $query->whereDate('offloaded_date', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('user.employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        })
                    ->orWhereHas('driver.employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('loading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trip_documents', function ($query) {
                        return $query->where('document_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('offloading_point', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->trip_filter,'desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
            }
            else {
               
                return view('livewire.trips.index',[
                    'trips' => Trip::query()->with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
                    'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
                    'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
                    'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->whereMonth($this->trip_filter, date('m'))
                    ->whereYear($this->trip_filter, date('Y'))->orderBy($this->trip_filter,'desc')->paginate(10),
                    'trip_filter' => $this->trip_filter
                ]);
              
            }
        }
   
     
          

      
    }
}
