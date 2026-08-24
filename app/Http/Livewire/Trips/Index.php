<?php

namespace App\Http\Livewire\Trips;

use App\Exports\PodTracker;
use App\Exports\TripsReportExport;
use App\Imports\TripsImport;
use App\Mail\TripUpdatesMail;
use App\Models\Bill;
use App\Models\Cargo;
use App\Models\Consignee;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Mileage;
use App\Models\Route;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\TripLocation;
use App\Models\TripStatus;
use App\Models\TripType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Sage\SageSyncService;
use App\Services\Sage\SageIntegration;
use App\Services\TripCompletionCascadeService;
use App\Jobs\Sage\SyncTripToSageJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    { 
        return 'vendor.pagination.bootstrap-custom';
    }
    

    private $trips;
    public $trip_id;
    public $status;
    public $units_of_measure_id;

    // Sage sync — selected trip ids for bulk sync.
    public $sageSelected = [];

    // Bulk mark-completed — selected trip ids and the decision applied to all of them.
    public $bulkCompleteSelected = [];
    public $bulk_mark_completed;

    /** Whether the acting user's company has an active Sage integration. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /** Sync one trip to Sage Intacct (Project) inline; also used for retry. */
    public function syncToSage($id)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $trip   = Trip::findOrFail($id);
        $result = app(SageSyncService::class)->syncTrip($trip);

        $this->dispatchBrowserEvent('alert', [
            'type'    => ! empty($result['success']) ? 'success' : (! empty($result['skipped']) ? 'warning' : 'error'),
            'message' => ! empty($result['success'])
                ? 'Trip synced to Sage (project ' . ($result['external_id'] ?? '') . ').'
                : 'Sage sync: ' . ($result['error'] ?? 'unknown error'),
        ]);
    }

    public function retrySync($id)
    {
        $this->syncToSage($id);
    }

    /** Bulk sync the selected trips via queued jobs. */
    public function bulkSyncToSage()
    {
        if (! $this->sageEnabled) {
            return;
        }

        $ids = array_filter($this->sageSelected);

        foreach ($ids as $id) {
            SyncTripToSageJob::dispatch((int) $id);
        }

        $this->sageSelected = [];

        $this->dispatchBrowserEvent('alert', [
            'type'    => count($ids) ? 'success' : 'warning',
            'message' => count($ids)
                ? count($ids) . ' trip(s) queued for Sage sync.'
                : 'Select at least one trip to sync.',
        ]);
    }

    public array $deliveryNotes = [];
 
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

    public $trip_transport_orders;

    public $date;
    public $countries;
    public $country_id;
    public $horse_id;
    public $city;
    public $description;
    public $suburb;
    public $street_address;
    public $clear_filters;

    public $user;
    public $employee;
    public $perPage = 10;


    public $search;
    protected $queryString = [
        'search'                 => ['except' => ''],
        'trip_filter'            => ['except' => ''],
        'from'                   => ['except' => ''],
        'to'                     => ['except' => ''],
        'perPage'                => ['except' => 10],
        'page'                   => ['except' => 1],
        'filter_transporter_id'  => ['except' => ''],
        'filter_horse_id'        => ['except' => ''],
        'filter_driver_id'       => ['except' => ''],
        'filter_currency_id'     => ['except' => ''],
        'filter_cargo_id'        => ['except' => ''],
        'filter_route_id'        => ['except' => ''],
        'filter_trip_type_id'    => ['except' => ''],
        'filter_customer_id'     => ['except' => ''],
        'filter_consignee_id'    => ['except' => ''],
        'filter_from'            => ['except' => ''],
        'filter_to'              => ['except' => ''],
        'filter_invoice_status'  => ['except' => ''],
        'filter_pod_status'      => ['except' => ''],
    ];

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


    
    public $trip_filter;
    public $from;
    public $to;
    public $transporters;
 
    public $horses;
    public $filter_transporter_id;
    public $filter_horse_id;
    public $filter_driver_id;
    public $filter_currency_id;
    public $filter_cargo_id;
    public $filter_route_id;
    public $filter_trip_type_id;
    public $filter_customer_id;
    public $filter_consignee_id;
    public $filter_from;
    public $filter_to;
    public $filter_invoice_status;
    public $filter_pod_status;
    public $drivers;
    public $currencies;
    public $cargos;
    public $destinations;
    public $routes;
  
    
    public $trip_types;
   
    public $filter_trip_status;
    public $customers;
    
    public $consignees;
    

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

    public $expenseTotalsByCurrency;
    public $totalsByCurrency;
    public $expense_currencies;
    public $trips_currencies;
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

    public $use_filters = False;
  

    public $loaded_quantity;
    public $loaded_litreage;
    public $loaded_litreage_at_20;
    public $loaded_weight;
    public $loaded_rate;
    public $loaded_freight;
    public $ending_mileage;
    public $starting_mileage;
    public $ending_hours;
    public $starting_hours;

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
    public $employee_department;

    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];
   
  
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
    public $mark_completed;

    protected $listeners = ['tripStatusUpdated' => '$refresh'];

    public function clearFilters(): void
    {
        $this->search              = Null;
        $this->trip_filter         = 'created_at'; // ← reset to default, not ''
        $this->from                = Null;
        $this->to                  = Null;
        $this->filter_transporter_id = null;
        $this->filter_horse_id       = null;
        $this->filter_driver_id      = null;
        $this->filter_currency_id    = null;
        $this->filter_cargo_id       = null;
        $this->filter_route_id       = null;
        $this->filter_trip_type_id   = null;
        $this->filter_customer_id    = null;
        $this->filter_consignee_id   = null;
        $this->filter_from           = null;
        $this->filter_to             = null;
        $this->filter_trip_status    = null;
        $this->filter_invoice_status = null;
        $this->filter_pod_status     = null;

        $this->resetPage();

         $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Filters Cleared Successfully!!"
        ]);
    }

    public function showCompleted($id){

        if(is_null($id)){
            return;
        }

        $this->trip_id = $id;
        $this->trip = Trip::find($id);
        $this->dispatchBrowserEvent('show-completedModal');
    }

    public function markCompleted(){

        $trip = Trip::find($this->trip_id);

        if ((int) $this->mark_completed === 1 && $trip->trip_status !== 'Offloaded') {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Only Offloaded trips can be marked Completed."
            ]);
            return;
        }

        DB::transaction(function () use ($trip) {
            $trip->status = $this->mark_completed;
            $trip->closed_by = Auth::user()->id;
            $trip->update();

            app(TripCompletionCascadeService::class)->syncForTrip($trip);
        });

        $this->dispatchBrowserEvent('hide-completedModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Completed Successfully!!"
        ]);
    }

    public function showBulkMarkCompleted(){
        if (empty(array_filter($this->bulkCompleteSelected))) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'warning',
                'message'=>"Select at least one trip to mark completed."
            ]);
            return;
        }

        $this->bulk_mark_completed = null;
        $this->dispatchBrowserEvent('show-bulkCompletedModal');
    }

    public function bulkMarkCompleted(){

        $this->validate([
            'bulk_mark_completed' => 'required|in:0,1',
        ], [
            'bulk_mark_completed.required' => 'Please select a status.',
        ]);

        $ids = array_filter($this->bulkCompleteSelected);

        $completed = 0;
        $skipped = 0;

        DB::transaction(function () use ($ids, &$completed, &$skipped) {
            $trips = Trip::whereIn('id', $ids)->get();

            foreach ($trips as $trip) {

                if ((int) $this->bulk_mark_completed === 1 && $trip->trip_status !== 'Offloaded') {
                    $skipped++;
                    continue;
                }

                $trip->status = $this->bulk_mark_completed;
                $trip->closed_by = Auth::user()->id;
                $trip->update();

                app(TripCompletionCascadeService::class)->syncForTrip($trip);
                $completed++;
            }
        });

        $this->bulkCompleteSelected = [];

        $this->dispatchBrowserEvent('hide-bulkCompletedModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=> "{$completed} trip(s) updated"
                . ($skipped ? ", {$skipped} skipped (not Offloaded)" : '')
                . '.'
        ]);
    }


    public function exportPodTrackerExcel(Excel $excel){

        return $excel->download(new PodTracker($this->from, $this->to, $this->trip_filter, $this->search,), 'pod_tracking_' .time().'.xlsx');
    }
    public function exportTripsCSV(Excel $excel){
   
        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search,
         [
            'horse_id'            => $this->filter_horse_id,
            'driver_id'           => $this->filter_driver_id,
            'customer_id'         => $this->filter_customer_id,
            'cargo_id'            => $this->filter_cargo_id,
            'from_destination_id' => $this->filter_from,
            'to_destination_id'   => $this->filter_to,
            'consignee_id'             => $this->filter_consignee_id,
            'trip_type_id'        => $this->filter_trip_type_id,
            'transporter_id'      => $this->filter_transporter_id,
            'route_id'            => $this->filter_route_id,
            'trip_status'         => $this->filter_trip_status,
        ]
        ), 'trips_' .time().'.csv', Excel::CSV);
    }

    public function exportTripsPDF(Excel $excel){

        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search,
         [
             'horse_id'            => $this->filter_horse_id,
            'driver_id'           => $this->filter_driver_id,
            'customer_id'         => $this->filter_customer_id,
            'cargo_id'            => $this->filter_cargo_id,
            'from_destination_id' => $this->filter_from,
            'to_destination_id'   => $this->filter_to,
            'consignee_id'             => $this->filter_consignee_id,
            'trip_type_id'        => $this->filter_trip_type_id,
            'transporter_id'      => $this->filter_transporter_id,
            'route_id'            => $this->filter_route_id,
            'trip_status'         => $this->filter_trip_status,
        ]
        ), 'trips_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new TripsReportExport($this->from, $this->to, $this->trip_filter, $this->search,
         [
            'horse_id'            => $this->filter_horse_id,
            'driver_id'           => $this->filter_driver_id,
            'customer_id'         => $this->filter_customer_id,
            'cargo_id'            => $this->filter_cargo_id,
            'from_destination_id' => $this->filter_from,
            'to_destination_id'   => $this->filter_to,
            'consignee_id'             => $this->filter_consignee_id,
            'trip_type_id'        => $this->filter_trip_type_id,
            'transporter_id'      => $this->filter_transporter_id,
            'route_id'            => $this->filter_route_id,
            'trip_status'         => $this->filter_trip_status,
        ]
        ), 'trips_' .time().'.xlsx');
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

     public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingRangeFrom()
    {
        $this->resetPage();
    }

    public function updatingRangeTo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function gotoPageNumber($page)
    {
        $page = (int) $page;

        if ($page > 0) {
            $this->gotoPage($page);
        }
    }
    

    public function getAuthorizer($id){
        
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
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
        $this->ending_hours = Null;
        $this->starting_hours = Null;
        $this->units_of_measure_id = Null;
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
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->driver = $this->employee->driver;
        $this->employee_department = $this->employee->departments->first();
        $this->company = $this->employee->company;
         foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }

        
        
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->drivers = Driver::latest()->get();
        $this->currencies = Currency::latest()->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->consignees = Consignee::orderBy('name','asc')->get();
        $this->trip_types = TripType::orderBy('name','asc')->get();
        $this->routes = Route::orderBy('name','asc')->get();
     
      }

      public function updatedTripStatusDate($date){
        if(isset($this->selectedStatus) && ($this->selectedStatus == "Offloaded" || $this->selectedStatus == "Loaded")){
            $this->offloaded_date = $date;
        }
    }

        public function updateTripStatus(){

       DB::transaction(function () {

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
       });
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


      /**
       * One-off data fix for the historical bug where a foreign-currency trip's
       * exchange_customer_freight/exchange_transporter_freight (and turnover) were
       * never (re)calculated when freight was derived via rate/weight/distance
       * instead of being typed directly into the freight field.
       */
      public function fixForexAmounts()
      {
          $canFix = !$this->company->rates_managed_by_finance
              || in_array('Finance', $this->department_names)
              || in_array('Super Admin', $this->role_names);

          if (!$canFix) {
              $this->dispatchBrowserEvent('alert', [
                  'type'    => 'danger',
                  'message' => 'You are not authorized to run this fix.',
              ]);
              return;
          }

          $baseCurrencyId = $this->company->currency_id;

          $trips = Trip::query()
              ->whereNotNull('currency_id')
              ->where('currency_id', '!=', $baseCurrencyId)
              ->get(['id', 'currency_id', 'freight', 'transporter_freight', 'exchange_rate', 'exchange_customer_freight', 'exchange_transporter_freight', 'turnover']);

          $fixed = 0;

          foreach ($trips as $trip) {
              $rate = is_numeric($trip->exchange_rate) ? (float) $trip->exchange_rate : null;

              if (!$rate || $rate <= 0) {
                  continue;
              }

              $changed = false;

              if (is_numeric($trip->freight) && (float) $trip->freight > 0) {
                  $correctCustomerFx = round($rate * (float) $trip->freight, 2);
                  $currentCustomerFx = is_numeric($trip->exchange_customer_freight) ? round((float) $trip->exchange_customer_freight, 2) : null;

                  if ($currentCustomerFx !== $correctCustomerFx) {
                      $trip->exchange_customer_freight = $correctCustomerFx;
                      $trip->turnover = $correctCustomerFx;
                      $changed = true;
                  }
              }

              if (is_numeric($trip->transporter_freight) && (float) $trip->transporter_freight > 0) {
                  $correctTransporterFx = round($rate * (float) $trip->transporter_freight, 2);
                  $currentTransporterFx = is_numeric($trip->exchange_transporter_freight) ? round((float) $trip->exchange_transporter_freight, 2) : null;

                  if ($currentTransporterFx !== $correctTransporterFx) {
                      $trip->exchange_transporter_freight = $correctTransporterFx;
                      $changed = true;
                  }
              }

              if ($changed) {
                  $trip->save();
                  $fixed++;
              }
          }

          $this->dispatchBrowserEvent('alert', [
              'type'    => $fixed ? 'success' : 'info',
              'message' => $fixed
                  ? "Fixed forex amounts on {$fixed} trip(s)."
                  : 'No trips needed a forex amount fix.',
          ]);
      }

      public function editLocations(){
        $this->intransit_trips = Trip::with(['breakdowns','breakdown_assignments','trip_destinations','trip_expenses','trip_locations','delivery_note','fuel:id,order_number','transporter:id,name','trip_type:id,name','border:id,name',
        'clearing_agent:id,name','trip_group:id,name','broker:id,name','customer:id,name','horse','horse.horse_make','horse.horse_model',
        'trailers:id,make,model,registration_number','driver.employee:id,name,surname','loading_point:id,name','offloading_point:id,name',
        'route:id,name,rank','truck_stops:id,name','cargo:id,name,group,risk,type','currency:id,name,symbol','agent:id,name','commission:id,commission,amount'])->where('trip_status','!=','Offloaded')->where('trip_status','!=','Cancelled')->where('trip_status','!=','Scheduled')->where('authorization','approved')->orderBy($this->trip_filter,'desc')->get();
        $this->dispatchBrowserEvent('show-locationsEditModal');
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

    private function updateAssetMileage(Trip $trip): void
    {
        $targetModel = $trip->horse_id
            ? Horse::find($trip->horse_id)
            : ($trip->vehicle_id ? Vehicle::find($trip->vehicle_id) : null);

        if (! $targetModel) return;

        if ($this->ending_mileage > $targetModel->mileage) {
            $targetModel->mileage = $this->ending_mileage;
            $targetModel->save();
        }
    }

    private function releaseAssets(Trip $trip): void
    {
        $isOffloaded = $this->selectedStatus === 'Offloaded';
        $noBreakdown = $trip->breakdown_assignments->isEmpty();

        foreach ([
            Horse::withTrashed()->find($trip->horse_id),
            Vehicle::withTrashed()->find($trip->vehicle_id),
        ] as $asset) {
            if (! $asset) continue;

            $asset->status = 1;

            if ($isOffloaded && $noBreakdown) {
                if ($asset->mileage > 0 && $trip->distance > 0) {
                    $asset->mileage += $trip->distance;
                }
                if ($asset->fuel_balance > 0 && $trip->trip_fuel > 0) {
                    $asset->fuel_balance -= $trip->trip_fuel;
                }
            }

            $asset->save();
        }

        if ($trip->driver_id) {
            Driver::withTrashed()->find($trip->driver_id)?->update(['status' => 1]);
        }

        foreach ($trip->trailers as $trailer) {
            Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);
        }

        foreach ($trip->breakdown_assignments as $ba) {
            Horse::withTrashed()->find($ba->horse_id)?->update(['status' => 1]);
            Driver::withTrashed()->find($ba->driver_id)?->update(['status' => 1]);
            foreach ($ba->trailers as $trailer) {
                Trailer::withTrashed()->find($trailer->id)?->update(['status' => 1]);
            }
        }
    }

    private function sendCustomerNotification(Trip $trip): void
    {
        if (! $this->customer_updates) return;

        $company = Auth::user()->company ?? Auth::user()->employee?->company;
        $email   = $trip->customer?->email;

        if ($email && $company) {
            Mail::to($email)->send(new TripUpdatesMail($trip, $company));
        }
    }

    



    private function aggregateDestinationTotals(Trip $trip): array
    {
        $destinations = $trip->trip_destinations ?? collect();

        return [
            'weight'        => $destinations->whereNotNull('weight')->where('weight', '!=', '')->sum('weight') ?: null,
            'quantity'      => $destinations->whereNotNull('quantity')->where('quantity', '!=', '')->sum('quantity') ?: null,
            'litreage'      => $destinations->whereNotNull('litreage')->where('litreage', '!=', '')->sum('litreage') ?: null,
            'litreage_at_20'=> $destinations->whereNotNull('litreage_at_20')->where('litreage_at_20', '!=', '')->sum('litreage_at_20') ?: null,
        ];
    }

    private function resolveDeliveryNote(Trip $trip, $tto): DeliveryNote
    {
        // Use stored delivery_note_id first — fastest and most reliable
        $storedId = $this->deliveryNotes[$tto->id]['delivery_note_id'] ?? null;

        if ($storedId) {
            $dn = DeliveryNote::find($storedId);
            if ($dn) return $dn;
        }

        // Fall back to DB query with dual-FK fallback
        $dn = DeliveryNote::where('trip_id', $trip->id)
            ->where(function ($q) use ($tto) {
                $q->where('trip_transport_order_id', $tto->id)
                ->orWhere(function ($q2) use ($tto) {
                    $q2->whereNull('trip_transport_order_id')
                        ->where('transport_order_id', $tto->transport_order_id);
                });
            })
            ->latest()
            ->first();

        if (! $dn) {
            $dn = new DeliveryNote();
            $dn->user_id = Auth::id();
            $dn->trip_id = $trip->id;
        }

        // Always normalise both FKs
        $dn->trip_transport_order_id = $tto->id;
        $dn->transport_order_id      = $tto->transport_order_id;

        return $dn;
    }

    public function status(int $id): void
    {
        $trip = Trip::withTrashed()
            ->with([
                'trip_transport_orders.transport_order.cargo',
                'trip_transport_orders.transport_order',
                'trip_destinations',
            ])
            ->findOrFail($id);

        $this->trip                    = $trip;
        $this->trip_id                 = $trip->id;
        $this->trip_status             = $trip->trip_status;
        $this->selectedStatus          = $trip->trip_status;
        $this->currency_id             = $trip->currency_id;
        $this->freight_calculation     = $trip->freight_calculation;
        $this->trip_status_date        = $trip->trip_status_date;
        $this->trip_status_description = $trip->trip_status_description;
        $this->customer_updates        = $trip->customer_updates;
        $this->ending_mileage          = $trip->ending_mileage;
        $this->starting_mileage        = $trip->starting_mileage;
        $this->ending_hours            = $trip->ending_hours;
        $this->starting_hours          = $trip->starting_hours;
        $this->trip_transport_orders   = $trip->trip_transport_orders;
        $this->freight_calculation     = $trip->freight_calculation;
        $this->calculation_measurement = $trip->calculation_measurement ?? '';

        $destinationTotals   = $this->aggregateDestinationTotals($trip);
        $this->deliveryNotes = [];

        foreach ($trip->trip_transport_orders as $tto) {

            // Always query directly — don't rely on the eager-loaded relationship
            // since it only matches on trip_transport_order_id
            $dn = DeliveryNote::where('trip_id', $trip->id)
                ->where(function ($q) use ($tto) {
                    $q->where('trip_transport_order_id', $tto->id)
                    ->orWhere(function ($q2) use ($tto) {
                        $q2->whereNull('trip_transport_order_id')
                            ->where('transport_order_id', $tto->transport_order_id);
                    });
                })
                ->latest()
                ->first();

            if (! $dn) {
                // Create and immediately persist a seeded DN
                $to = $tto->transport_order;

                $dn = new DeliveryNote();
                $dn->user_id                    = Auth::id();
                $dn->trip_id                    = $trip->id;
                $dn->trip_transport_order_id    = $tto->id;
                $dn->transport_order_id         = $tto->transport_order_id;
                $dn->units_of_measure_id        = $to->units_of_measure_id  ?? $trip->units_of_measure_id;
                $dn->distance                   = $trip->distance;
                $dn->loaded_quantity            = $to->quantity              ?? $trip->quantity;
                $dn->loaded_litreage            = $to->litreage              ?? $trip->litreage;
                $dn->loaded_litreage_at_20      = $to->litreage_at_20        ?? $trip->litreage_at_20;
                $dn->loaded_weight              = $to->weight                ?? $trip->weight;
                $dn->loaded_rate                = $to->rate                  ?? $trip->rate;
                $dn->loaded_freight             = $to->freight               ?? $trip->freight;
                $dn->transporter_loaded_rate    = $trip->transporter_rate;
                $dn->transporter_loaded_freight = $trip->transporter_freight;
                $dn->loaded_date                = $trip->start_date;
                $dn->save();

            } else {
                // Normalise legacy rows missing trip_transport_order_id
                $needsSave = false;

                if (is_null($dn->trip_transport_order_id)) {
                    $dn->trip_transport_order_id = $tto->id;
                    $needsSave = true;
                }

                // Backfill transporter values if missing
                if (! $dn->transporter_loaded_rate && ! $dn->transporter_loaded_freight) {
                    $dn->transporter_loaded_rate    = $trip->transporter_rate;
                    $dn->transporter_loaded_freight = $trip->transporter_freight;
                    $needsSave = true;
                }

                if ($needsSave) $dn->save();
            }

            // Populate the Livewire array — stored DN values take priority,
            // destination totals only fill in when DN offloaded fields are genuinely null
            $this->deliveryNotes[$tto->id] = [
                'delivery_note_id'             => $dn->id,
                'units_of_measure_id'          => $dn->units_of_measure_id,
                'distance'                     => $dn->distance,
                'loaded_date'                  => $dn->loaded_date,
                'loaded_quantity'              => $dn->loaded_quantity,
                'loaded_litreage'              => $dn->loaded_litreage,
                'loaded_litreage_at_20'        => $dn->loaded_litreage_at_20,
                'loaded_weight'                => $dn->loaded_weight,
                'loaded_rate'                  => $dn->loaded_rate,
                'loaded_freight'               => $dn->loaded_freight,
                'transporter_loaded_rate'      => $dn->transporter_loaded_rate,
                'transporter_loaded_freight'   => $dn->transporter_loaded_freight,
                'offloaded_date'               => $dn->offloaded_date,
                // Only fall back to destination totals when the DN field is strictly null
                'offloaded_quantity'           => $dn->offloaded_quantity    ?? $destinationTotals['quantity'],
                'offloaded_litreage'           => $dn->offloaded_litreage    ?? $destinationTotals['litreage'],
                'offloaded_litreage_at_20'     => $dn->offloaded_litreage_at_20 ?? $destinationTotals['litreage_at_20'],
                'offloaded_weight'             => $dn->offloaded_weight      ?? $destinationTotals['weight'],
                'offloaded_distance'           => $dn->offloaded_distance,
                // If DN is already completed (status=1) use its own offload rates,
                // otherwise default to the loaded rates so the fields pre-populate
                'offloaded_rate'               => $dn->status
                                                    ? $dn->offloaded_rate
                                                    : ($dn->offloaded_rate ?? $dn->loaded_rate),
                'offloaded_freight'            => $dn->status
                                                    ? $dn->offloaded_freight
                                                    : ($dn->offloaded_freight ?? $dn->loaded_freight),
                'transporter_offloaded_rate'   => $dn->status
                                                    ? $dn->transporter_offloaded_rate
                                                    : ($dn->transporter_offloaded_rate ?? $dn->transporter_loaded_rate),
                'transporter_offloaded_freight'=> $dn->status
                                                    ? $dn->transporter_offloaded_freight
                                                    : ($dn->transporter_offloaded_freight ?? $dn->transporter_loaded_freight),
                'comments'                     => $dn->comments,
            ];
        }

        $this->dispatchBrowserEvent('show-statusModal');
    }

    public function update(): void
    {
        $this->validate([
            'selectedStatus'                    => 'required',
            'freight_calculation' => 'required|in:flat_rate,rate_weight,rate_weight_distance,rate_distance',
            'trip_status_date'                  => 'required|date',
            'trip_status_description'           => 'nullable|string',
            'deliveryNotes.*.loaded_date'       => 'required_if:selectedStatus,Loaded,Offloaded|nullable|date',
            'deliveryNotes.*.loaded_rate'       => 'required_if:selectedStatus,Loaded,Offloaded|nullable|numeric',
            'deliveryNotes.*.loaded_freight'    => 'required_if:selectedStatus,Loaded,Offloaded|nullable|numeric',
            'deliveryNotes.*.offloaded_date'    => 'required_if:selectedStatus,Offloaded|nullable|date',
            'deliveryNotes.*.offloaded_rate'    => 'required_if:selectedStatus,Offloaded|nullable|numeric',
            'deliveryNotes.*.offloaded_freight' => 'required_if:selectedStatus,Offloaded|nullable|numeric',
        ]);

        DB::transaction(function () {

            $trip = Trip::withTrashed()
                ->with(['trip_transport_orders', 'trailers', 'breakdown_assignments.trailers'])
                ->findOrFail($this->trip_id);

            // --- Trip header ---
            $trip->trip_status             = $this->selectedStatus;
            $trip->trip_status_date        = $this->trip_status_date;
            $trip->trip_status_description = $this->trip_status_description;
            $trip->ending_mileage          = $this->ending_mileage;
            $trip->starting_mileage        = $this->starting_mileage;
            $trip->ending_hours            = $this->ending_hours;
            $trip->starting_hours          = $this->starting_hours;
            $trip->freight_calculation     = $this->freight_calculation;      // ← add
            $trip->calculation_measurement = $this->calculation_measurement;  // ← add

            if ($this->selectedStatus === 'Offloaded') {
                $firstNote      = collect($this->deliveryNotes)->first();
                $trip->end_date = $firstNote['offloaded_date'] ?? null;
            }

            $trip->save();

            $this->updateAssetMileage($trip);

            TripStatus::create([
                'user_id'     => Auth::id(),
                'trip_id'     => $trip->id,
                'status'      => $this->selectedStatus,
                'date'        => $this->trip_status_date,
                'description' => $this->trip_status_description,
            ]);

            // --- Delivery notes ---
            if (in_array($this->selectedStatus, ['Loaded', 'Offloaded'])) {

                foreach ($trip->trip_transport_orders as $tto) {

                    $data = $this->deliveryNotes[$tto->id] ?? null;
                    if (! $data) continue;

                    // Resolve by stored ID first — avoids a second query in most cases
                    $dn = isset($data['delivery_note_id'])
                        ? DeliveryNote::find($data['delivery_note_id'])
                        : null;

                    if (! $dn) {
                        // Fallback dual-FK query
                        $dn = DeliveryNote::where('trip_id', $trip->id)
                            ->where(function ($q) use ($tto) {
                                $q->where('trip_transport_order_id', $tto->id)
                                ->orWhere(function ($q2) use ($tto) {
                                    $q2->whereNull('trip_transport_order_id')
                                        ->where('transport_order_id', $tto->transport_order_id);
                                });
                            })
                            ->latest()
                            ->first();
                    }

                    if (! $dn) {
                        $dn          = new DeliveryNote();
                        $dn->user_id = Auth::id();
                        $dn->trip_id = $trip->id;
                    }

                    // Always stamp both FKs
                    $dn->trip_transport_order_id = $tto->id;
                    $dn->transport_order_id      = $tto->transport_order_id;

                    $dn->units_of_measure_id          = $data['units_of_measure_id'];
                    $dn->distance                     = $data['distance'];
                    $dn->loaded_date                  = $data['loaded_date'];
                    $dn->loaded_quantity              = $data['loaded_quantity'];
                    $dn->loaded_litreage              = $data['loaded_litreage'];
                    $dn->loaded_litreage_at_20        = $data['loaded_litreage_at_20'];
                    $dn->loaded_weight                = $data['loaded_weight'];
                    $dn->loaded_rate                  = $data['loaded_rate'];
                    $dn->loaded_freight               = $data['loaded_freight'];
                    $dn->transporter_loaded_rate      = $data['transporter_loaded_rate'];
                    $dn->transporter_loaded_freight   = $data['transporter_loaded_freight'];
                    $dn->offloaded_date               = $data['offloaded_date'];
                    $dn->offloaded_quantity           = $data['offloaded_quantity'];
                    $dn->offloaded_litreage           = $data['offloaded_litreage'];
                    $dn->offloaded_litreage_at_20     = $data['offloaded_litreage_at_20'];
                    $dn->offloaded_weight             = $data['offloaded_weight'];
                    $dn->offloaded_distance           = $data['offloaded_distance'];
                    $dn->offloaded_rate               = $data['offloaded_rate'];
                    $dn->offloaded_freight            = $data['offloaded_freight'];
                    $dn->transporter_offloaded_rate   = $data['transporter_offloaded_rate'];
                    $dn->transporter_offloaded_freight= $data['transporter_offloaded_freight'];
                    $dn->comments                     = $data['comments'];
                    $dn->status                       = 1;
                    $dn->freight_calculation          = $this->freight_calculation;      // ← add
                    $dn->calculation_measurement      = $this->calculation_measurement;  // ← add
                    $dn->save();

                    // Reflect the saved ID back into the component array
                    // so a second save in the same session uses the fast path
                    $this->deliveryNotes[$tto->id]['delivery_note_id'] = $dn->id;
                }
            }

            if (in_array($this->selectedStatus, ['Offloaded', 'Cancelled', 'Scheduled'])) {
                $this->releaseAssets($trip);
            }

            $this->sendCustomerNotification($trip);
        });

        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-statusModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Trip Status Updated Successfully!!',
        ]);
    }


  

  
    private function recalculateDeliveryNoteFreight(int $ttoId): void
    {
        $dn = &$this->deliveryNotes[$ttoId];
        if (! $dn) return;

        // Resolve cargo type for this specific TTO
        $cargoType = null;
        foreach ($this->trip_transport_orders as $tto) {
            if ($tto->id === $ttoId) {
                $cargoType = $tto->transport_order?->cargo?->type;
                break;
            }
        }

        // Recalculate loaded freight
        $dn['loaded_freight'] = $this->calculateFreight(
            $dn['loaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'],
            $dn['loaded_litreage'],
            $dn['distance']
        );

        // Recalculate offloaded freight
        $dn['offloaded_freight'] = $this->calculateFreight(
            $dn['offloaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'],
            $dn['offloaded_litreage'],
            $dn['offloaded_distance']
        );

        // Recalculate transporter loaded freight
        $dn['transporter_loaded_freight'] = $this->calculateFreight(
            $dn['transporter_loaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['loaded_weight'],
            $dn['loaded_litreage_at_20'],
            $dn['loaded_litreage'],
            $dn['distance']
        );

        // Recalculate transporter offloaded freight
        $dn['transporter_offloaded_freight'] = $this->calculateFreight(
            $dn['transporter_offloaded_rate'],
            $cargoType,
            $this->freight_calculation,
            $this->calculation_measurement,
            $dn['offloaded_weight'],
            $dn['offloaded_litreage_at_20'],
            $dn['offloaded_litreage'],
            $dn['offloaded_distance']
        );
    }

    public function updated(string $propertyName): void
    {
        // Trip-level freight_calculation change — recalc ALL TTOs
        if ($propertyName === 'freight_calculation') {
            foreach ($this->trip_transport_orders as $tto) {
                $this->recalculateDeliveryNoteFreight($tto->id);
            }
            return;
        }

        // deliveryNotes.{ttoId}.{field} changes
        if (str_starts_with($propertyName, 'deliveryNotes.')) {
            // Extract ttoId and field from property path
            // e.g. "deliveryNotes.42.loaded_weight" → ttoId=42, field=loaded_weight
            $parts = explode('.', $propertyName);
            // $parts[0] = 'deliveryNotes', $parts[1] = ttoId, $parts[2] = field
            if (count($parts) < 3) return;

            $ttoId = (int) $parts[1];
            $field = $parts[2];

            $freightTriggers = [
                'loaded_rate',
                'loaded_weight',
                'loaded_litreage',
                'loaded_litreage_at_20',
                'distance',
                'offloaded_rate',
                'offloaded_weight',
                'offloaded_litreage',
                'offloaded_litreage_at_20',
                'offloaded_distance',
                'transporter_loaded_rate',
                'transporter_offloaded_rate',
            ];

            if (in_array($field, $freightTriggers)) {
                $this->recalculateDeliveryNoteFreight($ttoId);
            }
        }
    }

    private function calculateFreight(
        $rate,
        $cargoType,
        $freightCalculation,
        $measurement,
        $weight,
        $litreageAt20,
        $litreage,
        $distance
    ): ?float {
        if (!is_numeric($rate)) return null;

        switch ($freightCalculation) {
            case 'rate_weight':
                if ($cargoType === 'Solid' && is_numeric($weight)) {
                    return $rate * $weight;
                }
                if ($cargoType === 'Liquid') {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20)) {
                        return $rate * $litreageAt20;
                    }
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage)) {
                        return $rate * $litreage;
                    }
                }
                break;

            case 'rate_distance':
                if (is_numeric($distance)) {
                    return $rate * $distance;
                }
                break;

            case 'rate_weight_distance':
                if ($cargoType === 'Solid' && is_numeric($weight) && is_numeric($distance)) {
                    return $rate * $weight * $distance;
                }
                if ($cargoType === 'Liquid' && is_numeric($distance)) {
                    if ($measurement === 'litreage_at_20' && is_numeric($litreageAt20)) {
                        return $rate * $litreageAt20 * $distance;
                    }
                    if ($measurement === 'litreage_at_ambient' && is_numeric($litreage)) {
                        return $rate * $litreage * $distance;
                    }
                }
                break;

            case 'flat_rate':
                return $rate;

            default:
                return null;
        }

        return null;
    }
    
    public function render()
    {

        if(isset($this->selectedStatus) && $this->selectedStatus == "Offloaded"){
            $this->offloaded_date = $this->trip_status_date;
        }
        if(isset($this->selectedStatus) && $this->selectedStatus == "Loaded"){
            $this->loaded_date = $this->trip_status_date;
        }

     
       
        $withRelations = [
            'podDocument',
            'breakdowns',
            'breakdown_assignments',
            'trip_origins.destination.country',
            'trip_origins.loading_point',
            'trip_destinations.destination.country',
            'trip_destinations.offloading_point',
            'trip_expenses',
            'trip_locations',
            'delivery_note',
            'fuel:id,order_number',
            'transporter:id,name',
            'trip_type:id,name',
            'border:id,name',
            'clearing_agent:id,name',
            'trip_group:id,name',
            'broker:id,name',
            'customer:id,name',
            'horse',
            'horse.horse_make',
            'horse.horse_model',
            'vehicle',
            'vehicle.vehicle_make',
            'vehicle.vehicle_model',
            'trailers:id,make,model,registration_number',
            'driver.employee:id,name,surname',
            'loading_point:id,name',
            'offloading_point:id,name',
            'route:id,name,rank',
            'truck_stops:id,name',
            'cargo:id,name,group,risk,type',
            'currency:id,name,symbol',
            'agent:id,name',
            'commission:id,commission,amount',
            'fromDestination.country',
            'toDestination.country',
            'transport_orders.fromDestination.country',
            'transport_orders.loading_point',
            'transport_orders.trip_destinations.destination.country',
            'transport_orders.trip_destinations.offloading_point',
            'trip_transport_orders.transport_order.customer',
            'trip_transport_orders.transport_order.cargo',
        ];

        // Only load the Sage mapping when the company actually uses Sage.
        if ($this->sageEnabled) {
            $withRelations[] = 'sageMapping';
        }

        $trips = Trip::query()->with($withRelations)->when($this->driver?->id, function ($q) {
        $q->where('driver_id', $this->driver->id);
        });

        /*
        |--------------------------------------------------------------------------
        | Search Logic
        |--------------------------------------------------------------------------
        */
        $applySearch = function ($query) {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('trip_number', 'like', "%{$search}%")
                    ->orWhere('trip_status', 'like', "%{$search}%")
                    ->orWhere('trip_ref', 'like', "%{$search}%")
                    ->orWhere('authorization', 'like', "%{$search}%")
                    ->orWhereHas('horse', function ($q2) use ($search) {
                        $q2->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('fleet_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trip_type', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('cargo', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereRaw("DATE_FORMAT(start_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('delivery_note', function ($q2) use ($search) {
                        $q2->whereRaw("DATE_FORMAT(offloaded_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"]);
                    })
                    ->orWhereHas('user.employee', function ($q2) use ($search) {
                        $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver.employee', function ($q2) use ($search) {
                        $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                    })
                    ->orWhereHas('transporter', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', function ($q2) use ($search) {
                        $q2->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('fleet_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trailers', function ($q2) use ($search) {
                        $q2->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('fleet_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('loading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('offloading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('trip_documents', fn ($q2) => $q2->where('document_number', 'like', "%{$search}%"));
            });
        };

        /*
        |--------------------------------------------------------------------------
        | Exact Filters
        |--------------------------------------------------------------------------
        | Remove "filter_" and use the matching DB column
        */
        $exactFilters = [
            'filter_transporter_id' => 'transporter_id',
            'filter_horse_id'       => 'horse_id',
            'filter_driver_id'      => 'driver_id',
            'filter_currency_id'    => 'currency_id',
            'filter_cargo_id'       => 'cargo_id',
            'filter_route_id'       => 'route_id',
            'filter_trip_type_id'   => 'trip_type_id',
            'filter_customer_id'    => 'customer_id',
            'filter_consignee_id'   => 'consignee_id',
            'filter_from'   => 'from',
            'filter_to'   => 'to',
            'filter_trip_status'   => 'trip_status',
        ];

        foreach ($exactFilters as $property => $column) {
            if (filled($this->{$property})) {
                $trips->where($column, $this->{$property});
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Invoice / POD Status Filters
        |--------------------------------------------------------------------------
        */
        if ($this->filter_invoice_status === 'invoiced') {
            $trips->whereHas('invoices');
        } elseif ($this->filter_invoice_status === 'not_invoiced') {
            $trips->whereDoesntHave('invoices');
        }

        if ($this->filter_pod_status === 'uploaded') {
            $trips->whereHas('pod');
        } elseif ($this->filter_pod_status === 'pending') {
            $trips->whereDoesntHave('pod');
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */
        if ($this->trip_filter === 'offloaded_date') {
            $trips->whereHas('delivery_note', function ($q) {
                if (filled($this->from) && filled($this->to)) {
                    $q->whereBetween('offloaded_date', [$this->from, $this->to]);
                } else {
                    $q->whereMonth('offloaded_date', date('m'))
                    ->whereYear('offloaded_date', date('Y'));
                }
            });

            if (filled($this->search)) {
                $applySearch($trips);
            }

            // join only for sorting
            $trips->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                ->select('trips.*')
                ->orderBy('delivery_notes.offloaded_date', 'desc');
        } else {
            if (filled($this->from) && filled($this->to)) {
                $trips->whereBetween($this->trip_filter, [$this->from, $this->to]);
            } else {
                if (!filled($this->search)) {
                    $trips->whereMonth($this->trip_filter, date('m'))
                        ->whereYear($this->trip_filter, date('Y'));
                }
            }

            if (filled($this->search)) {
                $applySearch($trips);
            }

            $trips->orderBy($this->trip_filter, 'desc');
        }

        $all_trips = $trips->get();

        $this->totalsByCurrency = $all_trips
        ->whereNotNull('freight')
        ->filter(fn ($trip) => $trip->freight !== '')
        ->groupBy('currency_id')
        ->map(fn ($group) => $group->sum('freight'));

        // Pull only currencies that actually exist in your trips:
        $this->trips_currencies = \App\Models\Currency::whereIn('id', $this->totalsByCurrency->keys())->get();

       

        $trip_with_expenses = $trips->get();
   
        // Make sure expenses are loaded (avoid N+1)
        $trip_with_expenses->load('trip_expenses');

        $all_expenses = $trip_with_expenses->flatMap(fn ($trip) => $trip->trip_expenses);

        $this->expenseTotalsByCurrency = $all_expenses
            ->filter(fn ($e) => !is_null($e->amount) && $e->amount !== '')
            ->groupBy('currency_id')
            ->map(fn ($group) => $group->sum(fn ($e) => (float) $e->amount));

        // Pull only currencies that actually exist in those expenses:
        $this->expense_currencies = \App\Models\Currency::whereIn('id', $this->expenseTotalsByCurrency->keys())->get();

            return view('livewire.trips.index', [
                'trips' => $trips->paginate($this->perPage),
                'trip_filter' => $this->trip_filter,
                'totalsByCurrency' => $this->totalsByCurrency,
                'trips_currencies' => $this->trips_currencies,
                'expenseTotalsByCurrency' => $this->expenseTotalsByCurrency,
                'expense_currencies' => $this->expense_currencies
            ]);
        
    }
}
