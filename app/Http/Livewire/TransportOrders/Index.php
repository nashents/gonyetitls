<?php

namespace App\Http\Livewire\TransportOrders;

use App\Mail\PendingNotificationEmails;
use App\Models\Cargo;
use App\Models\Company;
use App\Models\Consignee;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Destination;
use App\Models\ExchangeRate;
use App\Models\LoadingPoint;
use App\Models\Notification;
use App\Models\OffloadingPoint;
use App\Models\Quotation;
use App\Models\Rate;
use App\Models\TransportOrder;
use App\Models\TripDestination;
use App\Models\TripOrigin;
use App\Models\TripType;
use App\Models\UnitsOfMeasure;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $perPage = 10;
    protected $queryString = [
        'search', 'searchFrom','searchTo','searchLoadingPoint','searchOffloadingPoint',
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    public function paginationView()
    { 
        return 'vendor.pagination.bootstrap-custom';
    }

    private $transport_orders;
    public $transport_order_filter;
    public $from;
    public $to;
    public $search;
    public $transport_order_id;
    public $with_quotation = False;
    public $selectedQuotation;
    public $quotations;
    public $trip_types;
    public $selectedTripType;
    public $trip_type_name;
    public $custom_ref;
    public $volume;
    public $temparature;
    public $net_weight;
    public $bill_of_entry;
    public $seal_number;
    public $use_filters = False;

    public $exchange_rates;
    public $multiple_destinations = False;

    public $container_number;    
    public $filter_currency_id;
    public $filter_cargo_id;
    public $filter_trip_type_id;
    public $filter_customer_id;
    public $filter_consignee_id;
    public $filter_from;
    public $filter_to;
    public $filter_status;
    public $status;

    public $customers;
    public $customer_id;
    public $consignees;
    public $consignee_id;
    public $destinations;

    // location vars
    public $from_destinations;
    public $to_destinations;
    public $destination_id;
    public $selectedFrom;
    public $loading_points;
    public $loading_point_id;
    public $selectedTo;
    public $offloading_points;
    public $offloading_point_id;
    public $routes;
    public $selectedRoute;
    public $searchFrom;
    public $searchTo;
    public $searchLoadingPoint;
    public $searchOffloadingPoint;

    public $transporter_agreement = False;
    
    public $account;
    public $destinations_inputs = [];
    public $d = 1;
    public $e = 1;

    public function addDestination($d)
    {
        $d = $d + 1;
        $this->d = $d;
        array_push($this->destinations_inputs ,$d);
    }
    public function removeDestination($d)
    {
        unset($this->destinations_inputs[$d]);
    }

    public $origins_inputs = [];
    public $or = 1;
    public $r = 1;

    public function addOrigin($or)
    {
        $or = $or + 1;
        $this->or = $or;
        array_push($this->origins_inputs ,$or);
    }
    public function removeOrigin($or)
    {
        unset($this->origins_inputs[$or]);
    }

    public $destinations_selectedTo = [];
    public $destinations_offloading_point_id = [];
    public $offloaded_weight = [];
    public $offloaded_rate = [];
    public $offloaded_freight = [];
    public $offloaded_quantity = [];
    public $offloaded_litreage = [];

    public $destinations_selectedFrom = [];
    public $destinations_loading_point_id = [];
    public $loaded_weight = [];
    public $loaded_rate = [];
    public $loaded_freight = [];
    public $loaded_quantity = [];
    public $loaded_litreage = [];


    public $trip_destinations = [];
    public $trip_origins = [];

    public $start_date;
    public $end_date;
    public $distance;

    //cargo vars
    public $selectedCargo;
    public $cargos;
    public $cargo;
    public $cargo_type;
    public $cargo_details;
    public $weight;
    public $quantity;
    public $units_of_measures;
    public $units_of_measure_id;
    public $litreage;

    //freight vars
    public $freight_calculation;
    public $calculation_measurement;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $exchange_rate;
    public $exchange_customer_freight;
    public $exchange_transporter_freight;
    
    //rates
    public $with_customer_rates;
    public $with_transporter_rates;
    public $defined_customer_rates;
    public $selectedDefinedCustomerRate;
    public $selectedDefinedTransporterRate;
    public $rate;
    public $freight;
    public $transporter_rate;
    public $transporter_freight;
    public $driver;

    //Google Maps Api
    public $duration;

    public $rank_names;
    public $role_names;
    public $department_names;
    public $company;
    public $employee;
    public $user;
    public $deals;
    public $selectedDeal;
    public $with_deal;
 

    public function updatedSelectedCurrency($id){
        if(!is_null($id)){
            $this->selected_currency = Currency::find($id);
            if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                
                if ($predefined_exchange_rate) {   
                    $this->exchange_rate = $predefined_exchange_rate->exchange_rate;
                }
            }
        }
    }

    public function updatedSelectedDeal($id){
        if(is_null($id)){
            return;
        }
        $deal = Deal::find($id);
        $this->customer_id = $deal->customer_id;
        $this->selectedCargo = $deal->cargo_id;
        $cargo = Cargo::find($deal->cargo_id);
        $this->cargo_type = $cargo?->type;
        $this->weight = $deal->weight;
        $this->litreage = $deal->litreage;
        $this->quantity = $deal->quantity;
        $this->selectedCurrency = $deal->currency_id;
        $this->rate = $deal->rate;
        $this->freight = $deal->freight;
        $this->units_of_measure_id = $deal->units_of_measure_id;
    }

    public function updatedWithDeal($value){
        if($value == False){
            $this->selectedDeal = Null;
            $this->customer_id = Null;
            $this->selectedCargo = Null;
            $this->cargo_type = Null;
            $this->weight = Null;
            $this->litreage = Null;
            $this->quantity = Null;
            $this->units_of_measure_id =Null;
            $this->selectedCurrency =Null;
            $this->rate =Null;
            $this->freight =Null;
        }
    }

    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $this->cargo = Cargo::find($id);
                $this->cargo_type = $this->cargo->type;
            }
    }
  



    public function updatedSelectedQuotation($id){
            if (!is_null($id)) {
              $quotation = Quotation::find($id);
              if (isset($quotation)) {
                $this->customer_id = $quotation->customer ? $quotation->customer->id : "";
                $this->selectedCurrency = $quotation->currency_id;
              }
              
            }
    }
    
    
    public function transportOrderNumber(){

     if (isset($this->company)) {
            $str = $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
 
        $transport_order = TransportOrder::orderBy('id','desc')->first();

        if (!$transport_order) {
            $transport_order_number =  $initials .'TO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transport_order->id + 1;
            $transport_order_number =  $initials .'TO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transport_order_number;

    }


    public function mount(){
        $this->with_deal = False;
        $this->transport_order_filter = 'created_at';
        $this->freight_calculation = 'flat_rate';
        $this->user = Auth::user();
        $this->employee =  $this->user->employee;
        $this->driver =  $this->employee->driver;
        $this->deals = Deal::where('end_date', '>=', now())
                        ->where('status', 1)
                        ->where('is_closed', 0)
                        ->get();
        $this->company = Company::with('currency')->find($this->employee->company_id);
        $this->quotations = Quotation::with('customer')
                                    ->whereYear('date', date('Y'))
                                    ->whereMonth('date', date('m'))
                                    ->where('status', true)
                                    ->whereDate('expiry', '>=', now())
                                    ->latest()
                                    ->get();
       
        $this->exchange_rates = ExchangeRate::all(); 
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->defined_customer_rates = Rate::where('category','Customer')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->with_customer_rates = "custom";
        $this->with_transporter_rates = "custom";
        $this->rate = 0;
        $this->freight = 0;
        $this->trip_types = TripType::orderBy('name','asc')->get();
         $departments = $this->employee->departments;
         foreach($departments as $department){
             $this->department_names[] = $department->name;
         }
         $roles = Auth::user()->roles;
         foreach($roles as $role){
             $this->role_names[] = $role->name;
         }
         $ranks = $this->employee->ranks;
         foreach($ranks as $rank){
             $this->rank_names[] = $rank->name;
         }

        $this->consignees = Consignee::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
      
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->from_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->to_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
      }


      public function updated($value){
          $this->validateOnly($value);
      }

      protected $messages =[
        'customer_id.required' => 'Please select a customer',
        'selectedCargo.required' => 'Please select your cargo',
        'selectedTo.required' => 'Please select a starting point ',
        'selectedFrom.required' => 'Please select your destination ',

    ];
      protected $rules = [
          'customer_id' => 'required',
          'selectedTripType' => 'required',
          'selectedCargo' => 'required',
          'selectedCurrency' => 'required',
          'selectedFrom' => 'required',
          'selectedTo' => 'required',
          'weight' => 'nullable',
          'freight' => 'required',
          'start_date' => 'required',
          'start_date' => 'required',
         
      ];

        private function resetInputFields(){
        $this->selectedTripType = Null;
        $this->selectedCargo = Null;
        $this->selectedCurrency = Null;
        $this->selectedFrom = Null;
        $this->selectedDeal = Null;
        $this->selectedTo = Null;
        $this->weight = Null;
        $this->units_of_measure_id = Null;
        $this->quantity = Null;
        $this->litreage = Null;
        $this->with_quotation = False;
        $this->custom_ref = Null;
        $this->status = Null;
        $this->customer_id = Null;
        $this->consignee_id = Null;
        $this->cargo_details = Null;
        $this->exchange_rate = Null;
        $this->selectedDefinedCustomerRate = Null;
        $this->selectedDefinedTransporterRate = Null;
        $this->with_customer_rates = "custom";
        $this->freight_calculation = "flat_rate";
        $this->rate = Null;
        $this->freight = Null;
        $this->transporter_agreement = False;
        $this->with_transporter_rates = "custom";
        $this->transporter_rate = Null;
        $this->transporter_freight = Null;
        $this->multiple_destinations = False;
        $this->searchFrom = Null;
        $this->searchLoadingPoint = Null;
        $this->loading_point_id = Null;
        $this->searchOffloadingPoint = Null;
        $this->offloading_point_id = Null;
        $this->bill_of_entry = Null;
        $this->destinations_selectedTo = [];
        $this->destinations_offloading_point_id = [];
        $this->offloaded_weight = [];
        $this->offloaded_weight = [];
        $this->offloaded_quantity = [];
        $this->units_of_measure_id = Null;
        $this->offloaded_rate = Null;
        $this->offloaded_freight = Null;
        $this->start_date = Null;
        $this->end_date = Null;
        $this->distance = Null;
    }

     

    public function updatedSelectedDefinedCustomerRate($id){
            if(!is_null($id)){
                $defined_customer_rate = Rate::find($id);
                $this->rate = $defined_customer_rate->rate;
                $this->freight = $defined_customer_rate->customer_id;
                $this->customer_id = $defined_customer_rate->freight;
                $this->weight = $defined_customer_rate->weight;
                $this->litreage = $defined_customer_rate->litreage;
                $this->distance = $defined_customer_rate->distance;
                $this->selectedFrom = $defined_customer_rate->from;
                $this->selectedTo = $defined_customer_rate->to;
                $this->loading_point_id = $defined_customer_rate->loading_point_id;
                $this->offloading_point_id = $defined_customer_rate->offloading_point_id;
                $this->selectedCurrency = $defined_customer_rate->currency_id;
            }
      }
      public function updatedSelectedDefinedTransporterRate($id){
        if(!is_null($id)){
            $defined_transporter_rate = Rate::find($id);
            $this->transporter_rate = $defined_transporter_rate->rate;
            $this->transporter_freight = $defined_transporter_rate->freight;
        }
      }
    private function getBaseAmount($amount, $exchangeAmount, $currency)
    {
        return ($currency == $this->company->currency_id) ? $amount : $exchangeAmount;
    }

   
  private function calculateTimeDifference($start, $end)
    {
        if ($start && $end) {
            $start = $start instanceof Carbon ? $start : Carbon::parse($start);
            $end = $end instanceof Carbon ? $end : Carbon::parse($end);
            $diff = $end->diffInMinutes($start);
            return sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
        }
        return null;
    }

    public function addDestinations($transport_order)
    {

        TripDestination::where('transport_order_id', $transport_order->id)->delete();

        $userId = Auth::id();

        if ($this->multiple_destinations === true) {

            if (!empty($this->destinations_selectedTo)) {

                foreach ($this->destinations_selectedTo as $key => $destinationId) {

                    $offloadingPointId = $this->destinations_offloading_point_id[$key] ?? null;

                    TripDestination::updateOrCreate(
                        [
                            'transport_order_id'  => $transport_order->id,
                            'destination_id'      => $destinationId,
                            'offloading_point_id' => $offloadingPointId,
                        ],
                        [
                            'user_id'             => $userId,
                            'weight'              => $this->offloaded_weight[$key] ?? null,
                            'quantity'            => $this->offloaded_quantity[$key] ?? null,
                            'units_of_measure_id' => $this->units_of_measure_id,
                            'litreage'            => $this->offloaded_litreage[$key] ?? null,
                            'rate'                => $this->offloaded_rate[$key] ?? null,
                            'freight'             => $this->offloaded_freight[$key] ?? null,
                        ]
                    );

                }
            }

        } else {

            TripDestination::updateOrCreate(
                [
                    'transport_order_id'  => $transport_order->id,
                    'destination_id'      => $this->selectedTo,
                    'offloading_point_id' => $this->offloading_point_id,
                ],
                [
                    'user_id'             => $userId,
                    'weight'              => $this->weight,
                    'quantity'            => $this->quantity,
                    'units_of_measure_id' => $this->units_of_measure_id,
                    'litreage'            => $this->litreage,
                    'rate'                => $this->rate,
                    'freight'             => $this->freight,
                ]
            );
        }
    }
    
    public function addOrigins($transport_order)
    {

        TripOrigin::where('transport_order_id', $transport_order->id)->delete();

        $userId = Auth::id();

        if ($this->multiple_destinations === true) {

            if (!empty($this->destinations_selectedFrom)) {

                foreach ($this->destinations_selectedFrom as $key => $destinationId) {

                    $loadingPointId = $this->destinations_loading_point_id[$key] ?? null;

                    TripOrigin::updateOrCreate(
                        [
                            'transport_order_id'  => $transport_order->id,
                            'destination_id'      => $destinationId,
                            'loading_point_id' => $loadingPointId,
                        ],
                        [
                            'user_id'             => $userId,
                            'weight'              => $this->loaded_weight[$key] ?? null,
                            'quantity'            => $this->loaded_quantity[$key] ?? null,
                            'units_of_measure_id' => $this->units_of_measure_id,
                            'litreage'            => $this->loaded_litreage[$key] ?? null,
                            'rate'                => $this->loaded_rate[$key] ?? null,
                            'freight'             => $this->loaded_freight[$key] ?? null,
                        ]
                    );

                }
            }

        } else {

            TripOrigin::updateOrCreate(
                [
                    'transport_order_id'  => $transport_order->id,
                    'destination_id'      => $this->selectedFrom,
                    'loading_point_id' => $this->loading_point_id,
                ],
                [
                    'user_id'             => $userId,
                    'weight'              => $this->weight,
                    'quantity'            => $this->quantity,
                    'units_of_measure_id' => $this->units_of_measure_id,
                    'litreage'            => $this->litreage,
                    'rate'                => $this->rate,
                    'freight'             => $this->freight,
                ]
            );
        }
    }

public function getAuthorizer($id){
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
    }
   

    public function store(){

     
        DB::transaction(function () {

                $transport_order = new TransportOrder;
                $transport_order->transport_order_number = $this->transportOrderNumber();
                $transport_order->custom_ref = $this->custom_ref;
                $transport_order->bill_of_entry = $this->bill_of_entry;
                $transport_order->user_id =  $this->user->id ?: null;
                $transport_order->company_id = $this->company->id ?: null;
                $transport_order->quotation_id = $this->selectedQuotation ?: null;
                $transport_order->with_customer_rates = $this->with_customer_rates;
                $transport_order->with_transporter_rates = $this->with_transporter_rates;
                $transport_order->customer_id = $this->customer_id ?: null;
                $transport_order->consignee_id = $this->consignee_id ?: null;
                $transport_order->freight_calculation = $this->freight_calculation;
                $transport_order->calculation_measurement = $this->calculation_measurement;
                $transport_order->deal_id = $this->selectedDeal;
                $transport_order->currency_id = $this->selectedCurrency ?: null;
                $transport_order->cargo_id = $this->selectedCargo;
                $transport_order->trip_type_id = $this->selectedTripType;
                $transport_order->defined_customer_rate_id = $this->selectedDefinedCustomerRate;
                $transport_order->defined_transporter_rate_id = $this->selectedDefinedTransporterRate;
                $transport_order->from = $this->selectedFrom;
                $transport_order->to = $this->selectedTo;
                $transport_order->offloading_point_id = $this->offloading_point_id;
                $transport_order->loading_point_id = $this->loading_point_id;
                $transport_order->start_date = $this->start_date;
                $transport_order->cargo_details = $this->cargo_details;
                $transport_order->end_date = $this->end_date;
                $transport_order->multiple_destinations = $this->multiple_destinations;
                $transport_order->quantity = $this->quantity;
                $transport_order->litreage = $this->litreage;
                $transport_order->units_of_measure_id = $this->units_of_measure_id;
                $transport_order->weight = $this->weight;
                $transport_order->status = $this->status;
                $transport_order->rate = $this->rate;
                $transport_order->freight = $this->freight;
                $transport_order->transporter_rate = $this->transporter_rate;
                $transport_order->transporter_freight = $this->transporter_freight;
                $transport_order->exchange_rate = $this->exchange_rate;
                $transport_order->exchange_customer_freight = $this->exchange_customer_freight;
                $transport_order->exchange_transporter_freight = $this->exchange_transporter_freight;
                $transport_order->distance = $this->distance;
                $transport_order->save();

                $this->addDestinations($transport_order);
                $this->addOrigins($transport_order);

                $notifications = Notification::where('when','before')->where('category','Transport Order Authorization')->where('status',1)->get();
                
                if ($notifications->isNotEmpty()) {
                    foreach ($notifications as $notification) {
                        if($notification && isset($notification->category)){
                        $email = $notification->email ?? $notification->employee->email ?? null;
                        if($email){
                            Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $transport_order));
                        }
                        }
                    }
                }


                $this->dispatchBrowserEvent('hide-storeModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Transport Order Created Successfully!!"
                ]);
              

        });
            // end transport order creation logic
    }

    public function edit($id)
    {
        $transport_order = TransportOrder::with('trip_destinations')->findOrFail($id);

        // Core
        $this->transport_order_id = $transport_order->id;
        $this->custom_ref = $transport_order->custom_ref;
        $this->bill_of_entry = $transport_order->bill_of_entry;
        $this->company = Company::find($transport_order->company_id);
        $this->selectedQuotation = $transport_order->quotation_id;
        $this->with_customer_rates = $transport_order->with_customer_rates;
        $this->with_transporter_rates = $transport_order->with_transporter_rates;
        $this->customer_id = $transport_order->customer_id;
        $this->consignee_id = $transport_order->consignee_id;
        $this->freight_calculation = $transport_order->freight_calculation;
        $this->calculation_measurement = $transport_order->calculation_measurement;
        $this->selectedCurrency = $transport_order->currency_id;
        $this->selectedCargo = $transport_order->cargo_id;
        $this->cargo_type = Cargo::find($this->selectedCargo)?->type;
        $this->selectedTripType = $transport_order->trip_type_id;
        $this->selectedDefinedCustomerRate = $transport_order->defined_customer_rate_id;
        $this->selectedDefinedTransporterRate = $transport_order->defined_transporter_rate_id;
       
        // Routing
        $this->selectedFrom = $transport_order->from;
        $this->selectedTo = $transport_order->to;
        $this->offloading_point_id = $transport_order->offloading_point_id;
        $this->loading_point_id = $transport_order->loading_point_id;
      
        
        // Dates
        $this->start_date = $transport_order->start_date;
        $this->end_date = $transport_order->end_date;

        $this->selectedDeal = $transport_order->deal_id;
        if($transport_order->deal_id){
            $this->with_deal = True;
        }

        // Cargo
        $this->cargo_details = $transport_order->cargo_details;
        $this->multiple_destinations = $transport_order->multiple_destinations;

        // Quantities
        $this->quantity = $transport_order->quantity;
        $this->litreage = $transport_order->litreage;
        $this->units_of_measure_id = $transport_order->units_of_measure_id;
        $this->weight = $transport_order->weight;

        // Financials
        $this->status = $transport_order->status;
        $this->rate = $transport_order->rate;
        $this->freight = $transport_order->freight;
        $this->transporter_rate = $transport_order->transporter_rate;
        $this->transporter_freight = $transport_order->transporter_freight;
        $this->exchange_rate = $transport_order->exchange_rate;
        $this->exchange_customer_freight = $transport_order->exchange_customer_freight;
        $this->exchange_transporter_freight = $transport_order->exchange_transporter_freight;
        $this->distance = $transport_order->distance;

        // 🔥 DESTINATIONS (critical part)
        $this->destinations_selectedTo = [];
        $this->destinations_offloading_point_id = [];
        $this->offloaded_weight = [];
        $this->offloaded_quantity = [];
        $this->offloaded_litreage = [];
        $this->offloaded_rate = [];
        $this->offloaded_freight = [];

       
            $this->trip_destinations = TripDestination::where('transport_order_id', $transport_order->id)->get();

            if($this->trip_destinations){
                
                foreach($this->trip_destinations as $trip_destination){
                    $this->offloaded_weight[] = $trip_destination->weight;
                    $this->offloaded_quantity[] = $trip_destination->quantity;
                    $this->offloaded_litreage[] = $trip_destination->litreage;
                    $this->destinations_selectedTo[] = $trip_destination->destination_id;
                    $this->destinations_offloading_point_id[] = $trip_destination->offloading_point_id;
                    $this->offloaded_rate[] = $trip_destination->rate;
                    $this->offloaded_freight[] = $trip_destination->freight;
                }
            }
            
        
            $this->trip_origins = TripOrigin::where('transport_order_id', $transport_order->id)->get();
        
            if($this->trip_origins){
                foreach($this->trip_origins as $trip_origin){
                    $this->loaded_weight[] = $trip_origin->weight;
                    $this->loaded_quantity[] = $trip_origin->quantity;
                    $this->loaded_litreage[] = $trip_origin->litreage;
                    $this->destinations_selectedFrom[] = $trip_origin->destination_id;
                    $this->destinations_loading_point_id[] = $trip_origin->loading_point_id;
                    $this->loaded_rate[] = $trip_origin->rate;
                    $this->loaded_freight[] = $trip_origin->freight;
                }
            }

        $this->dispatchBrowserEvent('show-editModal');
    }
    
    public function update(){

     
        DB::transaction(function () {

                $transport_order =  TransportOrder::find($this->transport_order_id);
                $transport_order->custom_ref = $this->custom_ref;
                $transport_order->bill_of_entry = $this->bill_of_entry;
                $transport_order->company_id = $this->company->id ?: null;
                $transport_order->quotation_id = $this->selectedQuotation ?: null;
                $transport_order->with_customer_rates = $this->with_customer_rates;
                $transport_order->with_transporter_rates = $this->with_transporter_rates;
                $transport_order->customer_id = $this->customer_id ?: null;
                $transport_order->consignee_id = $this->consignee_id ?: null;
                $transport_order->freight_calculation = $this->freight_calculation;
                $transport_order->deal_id = $this->selectedDeal;
                $transport_order->calculation_measurement = $this->calculation_measurement;
                $transport_order->currency_id = $this->selectedCurrency ?: null;
                $transport_order->cargo_id = $this->selectedCargo;
                $transport_order->trip_type_id = $this->selectedTripType;
                $transport_order->defined_customer_rate_id = $this->selectedDefinedCustomerRate;
                $transport_order->defined_transporter_rate_id = $this->selectedDefinedTransporterRate;
                $transport_order->from = $this->selectedFrom;
                $transport_order->to = $this->selectedTo;
                $transport_order->offloading_point_id = $this->offloading_point_id;
                $transport_order->loading_point_id = $this->loading_point_id;
                $transport_order->start_date = $this->start_date;
                $transport_order->cargo_details = $this->cargo_details;
                $transport_order->end_date = $this->end_date;
                $transport_order->multiple_destinations = $this->multiple_destinations;
                $transport_order->quantity = $this->quantity;
                $transport_order->litreage = $this->litreage;
                $transport_order->units_of_measure_id = $this->units_of_measure_id;
                $transport_order->weight = $this->weight;
                $transport_order->status = $this->status;
                $transport_order->rate = $this->rate;
                $transport_order->freight = $this->freight;
                $transport_order->transporter_rate = $this->transporter_rate;
                $transport_order->transporter_freight = $this->transporter_freight;
                $transport_order->exchange_rate = $this->exchange_rate;
                $transport_order->exchange_customer_freight = $this->exchange_customer_freight;
                $transport_order->exchange_transporter_freight = $this->exchange_transporter_freight;
                $transport_order->distance = $this->distance;
                $transport_order->update();

                $this->addDestinations($transport_order);
                $this->addOrigins($transport_order);

               
                $this->dispatchBrowserEvent('hide-editModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Transport Order Updated Successfully!!"
                ]);
              

        });
            // end transport order creation logic
    }

    
   
   
      public function updatedRate(){
            $this->calculateFreight();
      }
    
      public function updatedWeight($value){

        $this->net_weight = $value;
        $this->calculateFreight();
         
      }
      public function updatedFreightCalculation(){
          $this->calculateFreight();
      }
      public function updatedCalculationMeasurement(){
          $this->calculateFreight();
      }
      public function updatedQuantity(){
          $this->calculateFreight();
      }
      public function updatedLitreageAt20(){
          $this->calculateFreight();
      }

      public function calculateFreight()
    {
        if ($this->freight_calculation == "rate_weight") {
            if ((isset($this->rate) && is_numeric($this->rate))  && ((isset($this->weight) && is_numeric($this->weight))  || (isset($this->litreage) && is_numeric($this->litreage)))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate * $this->weight;
                }elseif($this->cargo_type == "Liquid"){
                   $this->freight = $this->rate * $this->litreage;
                }
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if ((isset($this->rate)  && is_numeric($this->rate))  && ((isset($this->distance) && is_numeric($this->distance)) )) {
                $this->freight = $this->rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
          
            if ((isset($this->rate) && is_numeric($this->rate)) && ((isset($this->weight) && is_numeric($this->weight))  || (isset($this->litreage) && is_numeric($this->litreage))) && (isset($this->distance) && is_numeric($this->distance))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate * $this->weight * $this->distance;
                }elseif($this->cargo_type == "Liquid"){
                   $this->freight = $this->rate * $this->litreage * $this->distance ;
                   
                }
            }
            
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ((isset($this->rate)  && is_numeric($this->rate))) {
                if ($this->cargo_type == "Solid") {
                    $this->freight = $this->rate;
                }elseif($this->cargo_type == "Liquid"){
                    $this->freight = $this->rate ;
                }
            }
            
        }

        if ($this->freight_calculation == "rate_weight") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate)) && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20)  && is_numeric($this->litreage_at_20)) || (isset($this->litreage)  && is_numeric($this->litreage)))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate * $this->weight;
                }elseif($this->cargo_type == "Liquid"){
                    $this->transporter_freight = $this->transporter_rate * $this->litreage;
                 
                } 
            }
        }
        elseif ($this->freight_calculation == "rate_distance") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate))  && ((isset($this->distance) && is_numeric($this->distance)) )) {
                $this->transporter_freight = $this->transporter_rate * $this->distance;
            }
        }
        elseif ($this->freight_calculation == "rate_weight_distance") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate)) && ((isset($this->weight) && is_numeric($this->weight)) || (isset($this->litreage_at_20) && is_numeric($this->litreage_at_20)) || (isset($this->litreage) && is_numeric($this->litreage))) && (isset($this->distance) && is_numeric($this->distance))) {
                if ($this->cargo_type == "Solid") {
                    $this->transporter_freight = $this->transporter_rate * $this->weight * $this->distance;
                }elseif($this->cargo_type == "Liquid"){
                     $this->transporter_freight = $this->transporter_rate * $this->litreage * $this->distance;
                } 
            }
            
        }
        elseif ($this->freight_calculation == "flat_rate") {
            if ((isset($this->transporter_rate) && is_numeric($this->transporter_rate))) {
                $this->transporter_freight = $this->transporter_rate;
            }
            
        }


    }


    

    public function calculateDistance($from, $to, $category)
    {
       
        $from_location = null;
        $to_location = null;
    
        // Determine the locations based on category
        if ($category === "destinations") {
            $from_location = Destination::find($from);
            $to_location = Destination::find($to);
          
        } elseif ($category === "loading_points") {
            $from_location = LoadingPoint::find($from);
            $to_location = OffloadingPoint::find($to);
        }
    
        // Ensure we have valid locations
        if (!$from_location || !$to_location) {
            return response()->json(['error' => 'Invalid locations provided'], 400);
        }
    
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Google Maps API key is missing'], 500);
        }
    
        // Validate latitude and longitude
        if (!isset($from_location->lat, $from_location->long, $to_location->lat, $to_location->long)) {
            return response()->json(['error' => 'Invalid coordinates'], 400);
        }
    
        $origin = "{$from_location->lat},{$from_location->long}";
        $destination = "{$to_location->lat},{$to_location->long}";
    
        // Make API request
        $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'units' => 'metric',
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $apiKey,
        ]);
       
    
        if (!$response->successful()) {
            return response()->json(['error' => 'Error fetching data from Google Maps API'], 500);
        }
    
        $data = $response->json();
        $element = optional($data)['rows'][0]['elements'][0] ?? null;
    
        if (!$element) {
            return response()->json(['error' => 'No distance data available'], 404);
        }

       
    
        // Extract distance and duration
        $distance_in_meters = optional($element)['distance']['value'] ?? null;
        $duration_text = optional($element)['duration']['text'] ?? '';
    
        if (!is_numeric($distance_in_meters)) {
            return response()->json(['error' => 'Invalid distance data'], 500);
        }
    
        // Convert to kilometers
        $this->distance = ($distance_in_meters >= 1000) ? $distance_in_meters / 1000 : $distance_in_meters;
        $this->duration =  $duration_text;
        return response()->json([
            'distance' => $this->distance,
            'duration' => $duration_text
        ], 200);
    }
    
       
    public function updatedSearchFrom(){
            $this->from_destinations = Destination::query()->with('country')
            ->where('city', 'like', '%'.$this->searchFrom.'%')
            ->orWhereHas('country', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchFrom.'%');
            })
            ->get()->sortBy('city')->sortBy('country.name');
    }
    public function updatedSearchTo(){
            $this->to_destinations = Destination::query()->with('country')
            ->where('city', 'like', '%'.$this->searchTo.'%')
            ->orWhereHas('country', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchTo.'%');
            })
            ->get()->sortBy('city')->sortBy('country.name');
    }
    public function updatedSearchLoadingPoint(){
            $this->loading_points = LoadingPoint::query()
            ->where('name', 'like', '%'.$this->searchLoadingPoint.'%')->get();
    }
    public function updatedSearchOffloadingPoint(){
            $this->offloading_points = OffloadingPoint::query()
            ->where('name', 'like', '%'.$this->searchOffloadingPoint.'%')->get();
    }

   

  

  
    public function updatedExchangeRate(){
        $this->calculateForeignExchange();
    }
  
    public function updatedFreight(){
        $this->calculateForeignExchange();
    }
 
    
    public function calculateForeignExchange(){
       
        if ((isset($this->exchange_rate) && $this->exchange_rate > 0 && is_numeric($this->exchange_rate)) && (isset($this->freight) && $this->freight > 0 && is_numeric($this->freight)) ) {
            $this->exchange_customer_freight = $this->exchange_rate * $this->freight;
        }
       
    }


    public function updatedSelectedTo($id)
    {
        if (!is_null($this->selectedFrom) ) {
            if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
                $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
            }else{
              
                if(isset($this->selectedFrom) && isset($this->selectedTo)){
                    $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
                }
            }
          
        }
    }

    public function updatedSelectedFrom($id)
    {
        if (!is_null($id) ) {
            if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
                $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
            }else{
                if(isset($this->selectedFrom) && isset($this->selectedTo)){
                    $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
                }
            }
          
        }
    }


    public function updatedLoadingPointId(){
        if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
            $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
        }else{
            if(isset($this->selectedFrom) && isset($this->selectedTo)){
                $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
            }
        }
    }

   

    

    public function updatedOffloadingPointId(){
        if (isset($this->loading_point_id) && isset($this->offloading_point_id)) {
            $this->calculateDistance($this->loading_point_id, $this->offloading_point_id,"loading_points");
        }else{
            if(isset($this->selectedFrom) && isset($this->selectedTo)){
                $this->calculateDistance($this->selectedFrom, $this->selectedTo,"destinations");
            }
        }
    }


    public function refresh($category){

        if($category == 'customers'){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
        if($category == 'deals'){
           $this->deals = Deal::where('end_date', '<=', now())
                        ->where('status', 1)
                        ->where('is_closed', 0)
                        ->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Deals Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'consignees'){
            $this->consignees = Consignee::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Consignees Refreshed Successfully!!."
            ]);
        }
     
        elseif($category == 'currencies'){
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Currencies Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'destinations'){
            $this->from_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->to_destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'loading_points'){
            $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'offloading_points'){
            $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Points Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'cargos'){
            $this->cargos = Cargo::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Cargos Refreshed Successfully!!."
            ]);
        }
        elseif($category == 'rates'){
            $this->defined_customer_rates = Rate::where('category','Customer')->with('loading_point:id,name','offloading_point:id,name')->latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Rates Refreshed Successfully!!."
            ]);
        } 
    }

    public function getDestination($id){
        $destination = Destination::find($id);
        return $destination;
    }

    public function delete($id){
        $this->transport_order_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }
    public function destroy(){
        $transport_order = TransportOrder::find($this->transport_order_id);
        $transport_order->trip_destinations()->delete();
        $transport_order->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transport Order Deleted Successfully!!"
        ]);
    }

    public function render()
    {
       

         $withRelations = [
            'trip_type:id,name',
            'customer:id,name',
            'loading_point:id,name',
            'offloading_point:id,name',
            'cargo:id,name,group,risk,type',
            'currency:id,name,symbol',
        ];

        $transport_orders = TransportOrder::query()
        ->with($withRelations)
        ->when($this->driver?->id,
            fn ($q) => $q->whereHas('trips',
                fn ($t) => $t->where('driver_id', $this->driver->id)
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Search Logic
        |--------------------------------------------------------------------------
        */
        $applySearch = function ($query) {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('transport_order_number', 'like', "%{$search}%")
                    ->orWhere('authorization', 'like', "%{$search}%")
                    ->orWhereHas('trip_type', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('cargo', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereRaw("DATE_FORMAT(date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereHas('loading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('offloading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        };

        /*
        |--------------------------------------------------------------------------
        | Exact Filters
        |--------------------------------------------------------------------------
        | Remove "filter_" and use the matching DB column
        */
        $exactFilters = [
           
            'filter_currency_id'    => 'currency_id',
            'filter_cargo_id'       => 'cargo_id',
            'filter_trip_type_id'   => 'trip_type_id',
            'filter_customer_id'    => 'customer_id',
            'filter_consignee_id'   => 'consignee_id',
            'filter_from'   => 'from',
            'filter_to'   => 'to',
            'filter_status'   => 'status',
        ];

        foreach ($exactFilters as $property => $column) {
            if (filled($this->{$property})) {
                $transport_orders->where($column, $this->{$property});
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */
            if (filled($this->from) && filled($this->to)) {
                $transport_orders->whereBetween($this->transport_order_filter, [$this->from, $this->to]);
            } else {
                if (!filled($this->search)) {
                    $transport_orders->whereMonth($this->transport_order_filter, date('m'))
                        ->whereYear($this->transport_order_filter, date('Y'));
                }
            }

            if (filled($this->search)) {
                $applySearch($transport_orders);
            }

            $transport_orders->orderBy($this->transport_order_filter, 'desc');

        return view('livewire.transport-orders.index',[
            'transport_orders' => $transport_orders->paginate($this->perPage)
        ]);
    }
}
