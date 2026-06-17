<?php

namespace App\Http\Livewire\Fuels;

use App\Exports\FuelsExport;
use App\Mail\PendingNotificationEmails;
use App\Models\Asset;
use App\Models\CashFlow;
use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Container;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\FuelRequest;
use App\Models\Horse;
use App\Models\Hour;
use App\Models\Mileage;
use App\Models\Notification;
use App\Models\TopUp;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\Vehicle;
use Carbon\Carbon;
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

    private $fuels;
    public $fuel_id;
    public $assets;
    public $categories;
    public $category_values;
    public $asset_id;
    public $trips;
    public $vehicle;
    public $currencies;
    public $selectedCurrency;
    public $selectedVehicle;
    public $vehicles;
    public $employees;
    public $employee;
    public $employee_id;
    public $deduct_from;
    public $horse;
    public $horses;
    public $selectedHorse;
    public $drivers;
    public $driver;
    public $fuel_tank_capacity;
    public $driver_id;
    public $containers;
    public $selected_horse;
    public $horse_id;
    public $container;
    public $container_id;
    public $type = "Horse";
    public $attach_fuel_request = False;
    public $fuel_requests;
    public $selectedFuelRequest;

    public $unit_price = 0;
    public $amount = 0;
    public $quantity = 0 ;
    public $mileage;
    public $hours;
    public $date;
    public $fillup;
    public $invoice_number;
    public $fuel_consumption;
    public $distance;
    public $file;
    public $comments;
    public $fuel_trip;
    public $selected_currency;
    public $cost_of_sales;
    public $turnover;


    public $user_id;
    public $selectedContainer;
    public $container_balance;
    public $account_balance;
    public $selectedCategory;
    public $selectedCategoryValue;
    public $selectedTrip;
    public $selected_trip;
    public $selected_container;
    public $previous_mileage;
    public $previous_hours;
    public $previous_quantity;
    public $fuel_category = "Self";
    public $trip_expenses;

    public $selectedDriver;
    public $vehicle_id;
    public $selected_vehicle;
    public $net_profit;
    public $fuel_type;
    public $is_full_tank;

    public $search;
    public $searchRequest;
    public $searchTrip;
    public $searchHorse;
    public $searchEmployee;
    public $searchVehicle;
    public $searchAsset;
    protected $queryString = ['search','searchRequest','searchTrip'];
    public $fuel_filter;
    public $from;
    public $to;
    public $company;
    
    public $role_names;
    public $department_names;
    public $rank_names;

    public $exchange_rate;
    public $exchange_amount;
    public $category;
    public $total_transporter_expenses = 0;
    public $total_customer_expenses = 0;
    public $total_expenses = 0;

    public function exportFuelsCSV(Excel $excel){

        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.csv', Excel::CSV);
    }
    public function exportFuelsPDF(Excel $excel){

        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportFuelsExcel(Excel $excel){
        return $excel->download(new FuelsExport($this->from, $this->to, $this->fuel_filter,$this->container_id), 'fuel_orders_' .time().'.xlsx');
    }

    public function mount(){
        $this->deduct_from = "quantity";
        $this->employee = Auth::user()->employee;

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

        $this->resetPage();
        $this->company = Auth::user()->employee->company;
        $this->fuel_filter = "created_at";

        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->assets = Asset::query()
            ->join('products', 'assets.product_id', '=', 'products.id')
            ->select('assets.*')
            ->orderBy('products.name', 'asc')
            ->orderBy('assets.created_at', 'desc')
            ->get();
        $this->fillup = 1;
        $this->categories = Category::latest()->get();
        $this->fuel_requests = FuelRequest::where('authorization','approved')->doesntHave('fuel')->orderBy('created_at','desc')->get();
        $this->category_values = collect();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->trips = collect();
        $this->containers = Container::orderBy('name','asc')->get();
        $this->drivers = Driver::latest()->get();
    

    }

    public function updatedSearchHorse()
    {
        $term = trim((string) $this->searchHorse);

        $query = Horse::query()
            ->with('horse_make:id,name', 'horse_model:id,name')
            ->where('registration_number', 'like', "%{$term}%")
            ->where('archive', 0);
            // Order by registration number
            $this->horses = $query
                ->orderBy('registration_number', 'asc')
                ->get();
    }

    public function updatedSearchVehicle()
    {
        $term = trim((string) $this->searchVehicle);

        $query = Vehicle::query()
            ->with('vehicle_make:id,name', 'vehicle_model:id,name')
            ->where('registration_number', 'like', "%{$term}%")
            ->where('archive', 0);
            // Order by registration number (ascending)
            $this->vehicles = $query
                ->orderBy('registration_number', 'asc')
                ->get();
    }
    public function updatedSearchEmployee()
    {
        $term = trim((string) $this->searchEmployee);

        $query = Employee::query()
            ->where('archive', 0);

        // Flexible search: name, surname, or "name surname"
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('surname', 'like', "%{$term}%")
                    ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$term}%"])
                    ->orWhere('employee_number', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phonenumber', 'like', "%{$term}%");
            });
        }

        $this->employees = $query
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get();
    }

    public function updatedSearchAsset()
    {
        $term = trim((string) $this->searchAsset);

        $query = Asset::query()
            ->with([
                'product.brand',
            ])
            ->join('products', 'assets.product_id', '=', 'products.id') // for sorting
            ->select('assets.*') // avoid column conflicts
            ->where('assets.archive', 0);

        // Optional category filter
        if (!empty($this->selectedCategory)) {
            $query->where('assets.category_id', $this->selectedCategory);
        }

        // Flexible search
        if ($term !== '') {

            $search = "%{$term}%";

            $query->where(function ($q) use ($search) {

                // Asset serial number
                $q->where('assets.serial_number', 'like', $search)

                    // Product name / identification number / brand
                    ->orWhereHas('product', function ($productQuery) use ($search) {

                        $productQuery->where('name', 'like', $search)

                            ->orWhere('identification_number', 'like', $search)

                            ->orWhereHas('brand', function ($brandQuery) use ($search) {
                                $brandQuery->where('name', 'like', $search);
                            });

                    });

            });
        }

        $this->assets = $query
            ->orderBy('products.name', 'asc')
            ->get();
    }

    public function updatedSearchTrip()
    {
        $search = '%' . $this->searchTrip . '%';

        $this->trips = Trip::query()
            ->with([
                'customer:id,name',
                'horse:id,registration_number',
                'vehicle:id,registration_number',
                'loading_point:id,name',
                'offloading_point:id,name',
            ])

            ->when($this->selectedHorse, function ($query) {
                $query->where('horse_id', $this->selectedHorse);
            })

            ->when($this->selectedVehicle, function ($query) {
                $query->where('vehicle_id', $this->selectedVehicle);
            })

            ->where(function ($query) use ($search) {

                $query->where('trip_number', 'like', $search)
                    ->orWhere('trip_ref', 'like', $search)

                    ->orWhereHas('horse', function ($horseQuery) use ($search) {
                        $horseQuery->where('registration_number', 'like', $search);
                    })

                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('registration_number', 'like', $search);
                    })

                    ->orWhereHas('loading_point', function ($loadingPointQuery) use ($search) {
                        $loadingPointQuery->where('name', 'like', $search);
                    })

                    ->orWhereHas('offloading_point', function ($offloadingPointQuery) use ($search) {
                        $offloadingPointQuery->where('name', 'like', $search);
                    });

            })

            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function updatedSearchRequest($value){
        $query = FuelRequest::query()
        ->with([
            'employee',
            'user',
            'horse',
            'vehicle',
            'asset',
        ])
        ->where('authorization', 'approved')
        ->doesntHave('fuel');

        $query->when($this->searchRequest, function ($q) {

        $search = '%' . $this->searchRequest . '%';

        $q->where(function ($subQ) use ($search) {

            $subQ->where('reason', 'like', $search)
                ->orWhere('fuel_type', 'like', $search)
                ->orWhere('quantity', 'like', $search)

                ->orWhereHas('employee', function ($employeeQ) use ($search) {
                    $employeeQ->where('name', 'like', $search)
                        ->orWhere('surname', 'like', $search)
                        ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", [$search]);
                })

                ->orWhereHas('user', function ($userQ) use ($search) {
                    $userQ->where('name', 'like', $search)
                    ->orWhere('surname', 'like', $search)
                    ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", [$search]);
                })

                ->orWhereHas('horse', function ($horseQ) use ($search) {
                    $horseQ->where('registration_number', 'like', $search)
                            ->orWhere('fleet_number', 'like', $search);
                })

                ->orWhereHas('vehicle', function ($vehicleQ) use ($search) {
                    $vehicleQ->where('registration_number', 'like', $search)
                            ->orWhere('fleet_number', 'like', $search);
                })

               ->orWhereHas('asset', function ($assetQ) use ($search) {
                    $assetQ->where('serial_number', 'like', $search)
                        ->orWhereHas('product', function ($productQ) use ($search) {
                            $productQ->where('name', 'like', $search);
                        });
                });
            });
        });

        $this->fuel_requests = $query
            ->latest()
            ->get();
    }

    public function updatedSelectedFuelRequest($id){
       
        if(is_null($id)){
            return;
        }
        $fuel_request = FuelRequest::find($id);
        $this->selectedHorse = $fuel_request->horse_id;
        $this->selectedVehicle = $fuel_request->vehicle_id;
        $this->employee_id = $fuel_request->employee_id;
        $this->asset_id = $fuel_request->asset_id;

        if($this->selectedHorse){
            $horse = Horse::find($this->selectedHorse);
            $this->mileage = $horse?->mileage;
            $this->hours = $horse?->hours;
            $this->type = "Horse";
            $this->trips = Trip::with('horse','destination')->where('authorization','approved')->where('trip_status','!=','Cancelled')->whereYear('start_date',date('Y'))->where('horse_id',$this->selectedHorse)->orderBy('created_at','desc')->take(100)->get();
        }elseif($this->selectedVehicle){
            $vehicle = Vehicle::find($this->selectedVehicle);
            $this->mileage = $vehicle?->mileage;
            $this->hours = $vehicle?->hours;
            $this->type = "Vehicle";
            $this->trips = Trip::with('vehicle','destination')->where('authorization','approved')->where('trip_status','!=','Cancelled')->whereYear('start_date',date('Y'))->where('vehicle_id',$this->selectedVehicle)->orderBy('created_at','desc')->take(100)->get();
        }elseif($this->asset_id){
             $this->type = "Asset";
        }else{
             $this->type = "Other";
        }
        $fuel_type = $fuel_request->fuel_type;
        if($fuel_type){
             $this->containers = Container::where('fuel_type', $fuel_type)->orderBy('name','asc')->get();
        }
       
        $this->quantity = $fuel_request->quantity;
    }
    
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

    public function updatedSelectedHorse($id)
    {
        if (!is_null($id) ) {
        $this->horse = Horse::find($id);
        $this->selected_horse = Horse::find($id);
        $this->trips = Trip::with('horse','destination')->where('authorization','approved')->where('trip_status','!=','Cancelled')->whereYear('start_date',date('Y'))->where('horse_id',$id)->orderBy('created_at','desc')->take(100)->get();
        $this->horse_id = $id;
        $this->mileage = $this->horse->mileage;
        $this->hours = $this->horse->hours;
        $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
        }
    }

    public function updatedSelectedTrip($id)
    {
        if (!is_null($id) ) {
        $this->fuel_trip = Fuel::where('trip_id',$id)
        ->where('fillup', 1)->first();

        if (isset($this->fuel_trip)) {
           $this->fillup = 0;
        }
        $trip = Trip::find($id);
        if (isset($trip)) {
            $this->selected_trip = $trip;
            $this->horse = $trip->horse;
            $this->driver = $trip->driver;
            $this->selectedHorse = $this->horse?->id;
            $this->selectedDriver = $this->driver?->id;
            $this->distance = $trip->distance;
            $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
            $this->fuel_consumption = $this->horse->fuel_consumption;
        }
    
        // $this->quantity = $this->fuel_consumption * $this->distance;
        }
    }

    public function updatedSelectedContainer($id)
    {
        if (!is_null($id) ) {
            $this->container = Container::find($id);
            $this->selected_container = Container::find($id);
            $top_ups = TopUp::where('container_id',$id)->where('rate','!=', NULL)->where('rate','!=',"")->where('currency_id',$this->container->currency_id)->get();
            
            $topups_price_total = TopUp::where('container_id',$this->container?->id)->where('rate','!=', NULL)->where('rate','!=',"")->where('rate', 'REGEXP', '^[0-9]+$')->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            
            $topups_count = $top_ups->count();
            
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
        
            $this->container_balance = $this->container ? $this->container->balance : "";
            $this->account_balance = $this->container ? $this->container->account_balance : "";
            $this->fuel_type = $this->container?->fuel_type;
           
            $this->selectedCurrency = $this->container->currency_id;
           
            }
    }

    public function updatedSelectedVehicle($id)
    {
        if (!is_null($id) ) {
            $this->vehicle = Vehicle::find($id);
            $this->selected_vehicle = Vehicle::find($id);
            $this->trips = Trip::with('vehicle','destination')->where('authorization','approved')->where('trip_status','!=','Cancelled')->whereYear('start_date',date('Y'))->where('vehicle_id',$id)->orderBy('created_at','desc')->take(100)->get();
            $this->vehicle_id = $id;
            $this->mileage = $this->vehicle->mileage;
            $this->hours = $this->vehicle->hours;
            $this->fuel_tank_capacity = $this->vehicle->fuel_tank_capacity;
        }
    }
 
    public function updatedSelectedCategory($id)
    {
        if (!is_null($id) ) {
        $this->category_values = CategoryValue::where('category_id', $id)->get();
        }
    }
    public function updatedSelectedCategoryValue($id)
    {
        if (!is_null($id) ) {
        $this->assets = Asset::where('category_value_id', $id)->get();
        }
    }




    public function updated($propertyName)
    {
        $this->calculateFuelTotals();
        $this->validateOnly($propertyName, $this->rules());
    }

    protected function rules()
    {
        $rules = [
            'type' => 'required|in:Horse,Vehicle,Asset,Other',
            'selectedContainer' => 'required',
            'selectedCurrency' => 'required',
            'date' => 'required',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'required|numeric|min:0.01',
            'fillup' => 'required|in:0,1',
            'is_full_tank' => 'required|in:0,1',
            'deduct_from' => $this->isBulkBuy ? 'required|in:account,quantity' : 'nullable',
            'invoice_number' => 'nullable',
            'file' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
            'comments' => 'nullable',
        ];

        if ($this->type == 'Horse') {
            $rules['selectedHorse'] = 'required';
            $rules['employee_id'] = 'required';
            $rules['mileage'] = 'required|numeric|min:0';
        }

        if ($this->type == 'Vehicle') {
            $rules['selectedVehicle'] = 'required';
            $rules['employee_id'] = 'required';
            $rules['mileage'] = 'required|numeric|min:0';
        }

        if ($this->type == 'Asset') {
            $rules['asset_id'] = 'required';
        }

        if ($this->attach_fuel_request == true || $this->attach_fuel_request == 'True') {
            $rules['selectedFuelRequest'] = 'required';
        }

        if ($this->selectedCurrency && $this->company && $this->selectedCurrency != $this->company->currency_id) {
            $rules['exchange_rate'] = 'required|numeric|min:0.0001';
        }

        return $rules;
    }

    public function getIsBulkBuyProperty()
    {
        return isset($this->selected_container)
            && $this->selected_container
            && $this->selected_container->purchase_type == 'Bulk Buy';
    }

    public function getEffectiveContainerBalanceProperty()
    {
        $balance = (float) ($this->container_balance ?? 0);

        if ($this->fuel_id && $this->deduct_from == 'quantity') {
            $fuel = Fuel::find($this->fuel_id);

            if ($fuel && $fuel->authorization == 'approved' && $fuel->container_id == $this->selectedContainer && $fuel->deduct_from == 'quantity') {
                $balance += (float) ($this->previous_quantity ?? $fuel->quantity ?? 0);
            }
        }

        return $balance;
    }

    public function getEffectiveAccountBalanceProperty()
    {
        $balance = (float) ($this->account_balance ?? 0);

        if ($this->fuel_id && $this->deduct_from == 'account') {
            $fuel = Fuel::find($this->fuel_id);

            if ($fuel && $fuel->authorization == 'approved' && $fuel->container_id == $this->selectedContainer && $fuel->deduct_from == 'account') {
                $balance += (float) ($fuel->amount ?? 0);
            }
        }

        return $balance;
    }

    public function getFuelOrderIssuesProperty()
    {
        $issues = [];
        $quantity = (float) ($this->quantity ?? 0);
        $amount = (float) ($this->amount ?? 0);

        if ($quantity <= 0) {
            $issues[] = 'Quantity must be greater than zero.';
        }

        if ($amount <= 0) {
            $issues[] = 'Total amount must be greater than zero.';
        }

        if ($this->isBulkBuy) {
            if (!$this->deduct_from) {
                $issues[] = 'Select whether to deduct from account balance or quantity balance.';
            }

            if ($this->deduct_from == 'quantity' && $quantity > $this->effectiveContainerBalance) {
                $issues[] = 'Fuel quantity exceeds available fueling station quantity balance.';
            }

            if ($this->deduct_from == 'account' && $amount > $this->effectiveAccountBalance) {
                $issues[] = 'Fuel total exceeds available fueling station account balance.';
            }
        }

        if (isset($this->fuel_tank_capacity) && (float) $this->fuel_tank_capacity > 0 && $quantity > (float) $this->fuel_tank_capacity) {
            $issues[] = 'Fuel quantity exceeds the selected unit fuel tank capacity.';
        }

        return $issues;
    }

    public function getFuelOrderBlockedProperty()
    {
        return count($this->fuelOrderIssues) > 0;
    }

    private function calculateFuelTotals()
    {
        if ($this->unit_price !== null && $this->unit_price !== '' && $this->quantity !== null && $this->quantity !== '') {
            $this->amount = round((float) $this->unit_price * (float) $this->quantity, 2);
        }

        if ($this->exchange_rate !== null && $this->exchange_rate !== '' && (float) $this->exchange_rate > 0 && (float) ($this->amount ?? 0) > 0) {
            $this->exchange_amount = round((float) $this->exchange_rate * (float) $this->amount, 2);
        }
    }

    private function guardFuelOrderBeforeSave()
    {
        $this->calculateFuelTotals();

        if (!$this->fuelOrderBlocked) {
            return true;
        }

        foreach ($this->fuelOrderIssues as $issue) {
            $this->addError('fuel_guard', $issue);
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => 'Fuel order cannot be saved. Please correct the highlighted balance or validation issues.'
        ]);

        return false;
    }

    private function resetInputFields(){

        $this->selectedVehicle = Null;
        $this->selectedHorse = Null;
        $this->selectedTrip = Null;
        $this->deduct_from = "quantity";
        $this->selectedContainer = Null;
        $this->selectedCategory = Null;
        $this->selectedFuelRequest = Null;
        $this->selectedCategoryValue = Null;
        $this->asset_id = Null;
        $this->date = Null;
        $this->quantity = Null;
        $this->unit_price = Null;
        $this->mileage = Null;
        $this->hours = Null;
        $this->fillup = 1; 
        $this->attach_fuel_request = False; 
        $this->type = "Horse";
        $this->invoice_number = Null;
        $this->amount = 0;
        $this->exchange_rate = Null;
        $this->exchange_amount = Null;
        $this->is_full_tank = Null;
        $this->comments = Null;
    }
    public function orderNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset( $this->company)) {
            $str =  $this->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
        $fuel = Fuel::orderBy('id', 'desc')->first();
        if(!$fuel){
        $order_number =  $initials .'FO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else{
        $number = $fuel->id + 1 ;
        $order_number = $initials .'FO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
        return $order_number;
    }

    public function updatedAttachFuelRequest($value){
        
        if(is_null($value)){
            return ;
        }

        if($value == False){
            $this->resetInputFields();
        }
    }


    public function store(){

    $this->validate($this->rules());

    if (!$this->guardFuelOrderBeforeSave()) {
        return;
    }

    DB::transaction(function () {

        $fuel = new Fuel;
        $fuel->user_id = Auth::user()->id;
        $fuel->order_number = $this->orderNumber();
        $fuel->employee_id = $this->employee_id ?? Null;
        $fuel->deduct_from = $this->deduct_from;
        $fuel->fuel_request_id = $this->selectedFuelRequest ?? Null;
        $fuel->fuel_type = $this->fuel_type;
        $fuel->is_full_tank = $this->is_full_tank;

        if (isset($this->selectedTrip)) {
            $trip = Trip::find($this->selectedTrip);
            $fuel->trip_id = $trip?->id;
            $fuel->driver_id = $trip->driver_id ?? Null;
        }

        if ($this->type == "Horse") {
            $fuel->horse_id = $this->selectedHorse;
            $fuel->vehicle_id = Null;
            $fuel->asset_id = Null;
        }
        elseif($this->type == "Vehicle"){
            $fuel->vehicle_id = $this->selectedVehicle;
            $fuel->asset_id = Null;
            $fuel->horse_id = Null;
        }
        elseif($this->type == "Asset"){
            $fuel->asset_id = $this->asset_id;
            $fuel->horse_id = Null;
            $fuel->vehicle_id = Null;
            
        }
        elseif($this->type == "Other"){
            $fuel->asset_id = Null;
            $fuel->horse_id = Null;
            $fuel->vehicle_id = Null;
        }
      
        $fuel->container_id = $this->selectedContainer ?? Null;
        $fuel->date = $this->date;
        $fuel->unit_price = $this->unit_price;
        $fuel->quantity = $this->quantity;
        $fuel->currency_id = $this->selectedCurrency ?? Null;
        $fuel->amount = $this->amount;
        $fuel->exchange_rate = $this->exchange_rate;
        $fuel->exchange_amount = $this->exchange_amount;
        $fuel->odometer = $this->mileage;
        $fuel->hours = $this->hours;
        $fuel->fillup = $this->fillup;
        $fuel->type = $this->type;
        $fuel->comments = $this->comments;
        $fuel->save();

        if ($fuel->trip) {

            $fuel_expense = Expense::where('name','Fuel Topup')->get()->first();
        
            $trip_expense = new TripExpense;
            $trip_expense->user_id =  $fuel->user_id;
            $trip_expense->trip_id = $fuel->trip_id;
            $trip_expense->fuel_id = $fuel->id;
            if (isset($fuel_expense)) {
                $trip_expense->expense_id = $fuel_expense->id;
            }
            $trip_expense->currency_id = $fuel->currency_id;
            $trip_expense->category = $this->fuel_category;
            $trip_expense->amount = $fuel->amount;
            $trip_expense->exchange_rate = $this->exchange_rate;
            $trip_expense->exchange_amount = $this->exchange_amount;
           
            
            $trip_expense->save();

            $trip =  Trip::find($fuel->trip_id);
            $this->trip_expenses = TripExpense::where('trip_id',$fuel->trip_id)->get();

            if(isset($this->trip_expenses)){
                foreach($this->trip_expenses as $expense){
                    if ($expense->currency_id == Auth::user()->employee->company->currency_id) {
                        if ($expense->category == "Transporter") {
                            if (isset($this->total_transporter_expenses) && is_numeric($expense->amount) ) {
                                $this->total_transporter_expenses = $this->total_transporter_expenses + $expense->amount;
                            }
                        }
                        elseif ($expense->category == "Customer") {
                            if (isset($this->total_customer_expenses) && is_numeric($expense->amount) ) {
                                $this->total_customer_expenses = $this->total_customer_expenses + $expense->amount;
                            }
                           
                        }
                        elseif ($expense->category == "Self") {
                            if (isset($this->total_expenses) && is_numeric($expense->amount) ) {
                                $this->total_expenses = $this->total_expenses + $expense->amount;
                            }
                           
                        }
                    }else{
                        if ($expense->category == "Transporter") {
                            if (isset($this->total_transporter_expenses) && is_numeric($expense->exchange_amount) ) {
                                $this->total_transporter_expenses = $this->total_transporter_expenses + $expense->exchange_amount;
                            }
                            
                         }
                         elseif ($expense->category == "Customer") {
                            if (isset($this->total_customer_expenses) && is_numeric($expense->exchange_amount) ) {
                                $this->total_customer_expenses = $this->total_customer_expenses + $expense->exchange_amount;
                            }
                            
                         }
                         elseif ($expense->category == "Self") {
                            if (isset($this->total_expenses) && is_numeric($expense->exchange_amount) ) {
                                $this->total_expenses = $this->total_expenses + $expense->exchange_amount;
                         }
                            }
                    }
                }
            }

            $this->cost_of_sales = $this->total_expenses;
            $trip->cost_of_sales = $this->total_expenses;
            
            $this->turnover = $trip->turnover;
        
            if ((isset($this->cost_of_sales) && is_numeric($this->cost_of_sales) && $this->cost_of_sales > 0) && (isset($this->turnover) && is_numeric($this->turnover) && $this->turnover > 0)) {
            
                    $trip->net_profit = $this->turnover - $this->cost_of_sales;
                    $this->net_profit = $this->turnover - $this->cost_of_sales;
        
                    if((is_numeric($this->net_profit) && $this->net_profit > 0) && (is_numeric($this->turnover) && $this->turnover > 0)){
                        $trip->markup_percentage = (($this->net_profit/$this->cost_of_sales) * 100);
                        $trip->net_profit_percentage = (($this->net_profit/$this->turnover) * 100);
                    }
            
            }else {
    
                $trip->net_profit_percentage = 100 ;
                $trip->markup_percentage = 100 ;
            }
    
    
            $trip->update();
        }



        $notifications = Notification::where('when','Before')->where('category','Fuel Order Authorization')->where('status',1)->get();
      
        
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                     $email = $notification->email ?? $notification->employee->email ?? null;
                    if($email){
                        Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $fuel));
                    }
                }
            }
        }
       

        $this->dispatchBrowserEvent('hide-fuelModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Order Created Successfully!!"
        ]);
        
        return redirect(request()->header('Referer'));

        });

    }

    public function edit($id){
    $fuel = Fuel::find($id);
    $this->fuel_requests = FuelRequest::where('authorization','approved')->orderBy('created_at','desc')->get();
    $this->user_id = $fuel->user_id;
    $this->selectedHorse = $fuel->horse_id;
    $this->horse_id = $fuel->horse_id;
    $this->deduct_from = $fuel->deduct_from ?: "quantity";
    $this->employee_id = $fuel->employee_id;
    $this->selectedVehicle = $fuel->vehicle_id;
    $this->vehicle_id = $fuel->vehicle_id;
    $this->is_full_tank = $fuel->is_full_tank;
    $this->selectedTrip = $fuel->trip_id;
    $this->fuel_type = $fuel->fuel_type;
    $this->trips = Trip::where('trip_status','!=','Cancelled')->orderBy('created_at','desc')->orderBy('created_at','desc')->get();
    $this->selectedCurrency = $fuel->currency_id;
    $this->asset_id = $fuel->asset_id;
    $this->selectedContainer = $fuel->container_id;
    $this->container = Container::find($fuel->container_id);
    $this->selected_container = Container::find($fuel->container_id);
    $this->container_balance = $this->container ? $this->container->balance : "";
    $this->account_balance = $this->container ? $this->container->account_balance : "";
    $this->driver_id = $fuel->driver_id;
    $this->date = $fuel->date;
    $this->selectedFuelRequest = $fuel->fuel_request_id;
    $this->attach_fuel_request = $fuel->fuel_request_id ? True : False;
    $this->fillup = $fuel->fillup;
    $this->type = $fuel->type;
    $this->mileage = $fuel->odometer;
    $this->hours = $fuel->hours;
    $this->previous_mileage = $fuel->odometer;
    $this->previous_hours = $fuel->hours;
    $this->comments = $fuel->comments;
    $this->amount = $fuel->amount;
    $this->unit_price = $fuel->unit_price;
  
  
    $this->quantity = $fuel->quantity;
    $this->previous_quantity = $fuel->quantity;
    $this->fuel_id = $fuel->id;
    if ($fuel->asset_id) {
        $this->selectedCategory = Asset::find($fuel->asset_id)->category_id;
        $this->selectedCategoryValue = Asset::find($fuel->asset_id)->category_value_id;
    }
    $this->dispatchBrowserEvent('show-fuelEditModal');

    }

    

    public function update()
    {
        $this->validate($this->rules());

        if (!$this->guardFuelOrderBeforeSave()) {
            return;
        }

        try{

        if ($this->fuel_id) {
            $fuel = Fuel::find($this->fuel_id);
            $fuel->employee_id = $this->employee_id ?? Null;
            if (isset($this->selectedTrip)) {
            $trip = Trip::find($this->selectedTrip);
            $fuel->trip_id = $trip->id;
            $fuel->driver_id = $trip->driver_id ?? Null;
        }

        if ($this->type == "Horse") {
            $fuel->horse_id = $this->selectedHorse ?? Null;
            $fuel->vehicle_id = Null;
            $fuel->asset_id = Null;
        }
        elseif($this->type == "Vehicle"){
            $fuel->vehicle_id = $this->selectedVehicle ?? Null;
            $fuel->asset_id = Null;
            $fuel->horse_id = Null;
        }
        elseif($this->type == "Asset"){
            $fuel->asset_id = $this->asset_id ?? Null;
            $fuel->horse_id = Null;
            $fuel->vehicle_id = Null;
            
        }
        elseif($this->type == "Other"){
            $fuel->asset_id = Null;
            $fuel->horse_id = Null;
            $fuel->vehicle_id = Null;
        }
           
            $fuel->is_full_tank = $this->is_full_tank;
            $fuel->container_id = $this->selectedContainer ?? Null;
            $fuel->deduct_from = $this->deduct_from;
            $fuel->date = $this->date;
            $fuel->fuel_request_id = $this->selectedFuelRequest ?? Null;
            $fuel->unit_price = $this->unit_price;
            $fuel->fuel_type = $this->fuel_type;
            
            $fuel->quantity = $this->quantity;
            $fuel->currency_id = $this->selectedCurrency ?? Null;
            $fuel->amount = $this->amount;
            $fuel->exchange_rate = $this->exchange_rate;
            $fuel->exchange_amount = $this->exchange_amount;
            $fuel->odometer = $this->mileage;
            $fuel->hours = $this->hours;
            $fuel->fillup = $this->fillup;
            $fuel->type = $this->type;
            $fuel->comments = $this->comments;

            $fuel->update();

            if ($fuel->trip) {
                
                $trip_expense = TripExpense::where('fuel_id',$fuel->id)->where('trip_id',$fuel->trip_id)->first();
                if(isset($trip_expense)){
                    $fuel_expense = Expense::where('name','Fuel Topup')->first();
            
                    $trip_expense->user_id =  $fuel->user_id;
                    $trip_expense->trip_id = $fuel->trip_id;
                    $trip_expense->fuel_id = $fuel->id;
                    if (isset($fuel_expense)) {
                        $trip_expense->expense_id = $fuel_expense->id;
                    }
                    $trip_expense->currency_id = $fuel->currency_id;
                    $trip_expense->category = $this->fuel_category;
                    $trip_expense->amount = $fuel->amount;
                    $trip_expense->exchange_rate = $this->exchange_rate;
                    $trip_expense->exchange_amount = $this->exchange_amount;
                    $trip_expense->update();
                }else{
                    $fuel_expense = Expense::where('name','Fuel Topup')->get()->first();
            
                    $trip_expense = new TripExpense;
                    $trip_expense->user_id =  $fuel->user_id;
                    $trip_expense->trip_id = $fuel->trip_id;
                    $trip_expense->fuel_id = $fuel->id;
                    if (isset($fuel_expense)) {
                        $trip_expense->expense_id = $fuel_expense->id;
                    }
                    $trip_expense->currency_id = $fuel->currency_id;
                    $trip_expense->category = $this->fuel_category;
                    $trip_expense->amount = $fuel->amount;
                    $trip_expense->exchange_rate = $this->exchange_rate;
                    $trip_expense->exchange_amount = $this->exchange_amount;
                   
                    $trip_expense->save();
                }

                $trip =  Trip::find($fuel->trip_id);
                $this->trip_expenses = TripExpense::where('trip_id',$fuel->trip_id)->get();
    
                if(isset($this->trip_expenses)){
                    foreach($this->trip_expenses as $expense){
                        if ($expense->currency_id == Auth::user()->employee->company->currency_id) {
                            if ($expense->category == "Transporter") {
                                if (isset($this->total_transporter_expenses) && is_numeric($expense->amount) ) {
                                    $this->total_transporter_expenses = $this->total_transporter_expenses + $expense->amount;
                                }
                            }
                            elseif ($expense->category == "Customer") {
                                if (isset($this->total_customer_expenses) && is_numeric($expense->amount) ) {
                                    $this->total_customer_expenses = $this->total_customer_expenses + $expense->amount;
                                }
                               
                            }
                            elseif ($expense->category == "Self") {
                                if (isset($this->total_expenses) && is_numeric($expense->amount) ) {
                                    $this->total_expenses = $this->total_expenses + $expense->amount;
                                }
                               
                            }
                        }else{
                            if ($expense->category == "Transporter") {
                                if (isset($this->total_transporter_expenses) && is_numeric($expense->exchange_amount) ) {
                                    $this->total_transporter_expenses = $this->total_transporter_expenses + $expense->exchange_amount;
                                }
                                
                             }
                             elseif ($expense->category == "Customer") {
                                if (isset($this->total_customer_expenses) && is_numeric($expense->exchange_amount) ) {
                                    $this->total_customer_expenses = $this->total_customer_expenses + $expense->exchange_amount;
                                }
                                
                             }
                             elseif ($expense->category == "Self") {
                                if (isset($this->total_expenses) && is_numeric($expense->exchange_amount) ) {
                                    $this->total_expenses = $this->total_expenses + $expense->exchange_amount;
                             }
                                }
                        }
                    }
                }
    
          
              
    
                $this->cost_of_sales = $this->total_expenses;
                $trip->cost_of_sales = $this->total_expenses;
                
                $this->turnover = $trip->turnover;
            
                if ((isset($this->cost_of_sales) && is_numeric($this->cost_of_sales) && $this->cost_of_sales > 0) && (isset($this->turnover) && is_numeric($this->turnover) && $this->turnover > 0)) {
                
                        $trip->net_profit = $this->turnover - $this->cost_of_sales;
                        $this->net_profit = $this->turnover - $this->cost_of_sales;
            
                        if((is_numeric($this->net_profit) && $this->net_profit > 0) && (is_numeric($this->turnover) && $this->turnover > 0)){
                            $trip->markup_percentage = (($this->net_profit/$this->cost_of_sales) * 100);
                            $trip->net_profit_percentage = (($this->net_profit/$this->turnover) * 100);
                        }
                
                }else {
        
                    $trip->net_profit_percentage = 100 ;
                    $trip->markup_percentage = 100 ;
                }
        
        
                $trip->update();

            }

            $mileage = Mileage::where('fuel_id',$fuel->id)->first();
            if(isset($mileage)){
                $mileage->fuel_id = $fuel->id;
                $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                $mileage->mileage = $this->mileage;
                $mileage->date = $this->date;
                $mileage->category = "Fuel Order";
                $mileage->update();
            }
            $hours = Hour::where('fuel_id',$fuel->id)->first();
            if(isset($hours)){
                $hours->fuel_id = $fuel->id;
                $hours->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                $hours->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                $hours->hours = $this->hours;
                $hours->date = $this->date;
                $hours->category = "Fuel Order";
                $hours->update();
            }
            

            if($fuel->authorization == "approved"){
                if ($fuel->horse) {
                    $horse = Horse::find($fuel->horse_id);
                    $horse->fuel_balance = $horse->fuel_balance + $fuel->quantity;
                    $current_mileage = $horse->mileage - $this->previous_mileage;
                    $current_hours = $horse->hours - $this->previous_hours;
                    if ($fuel->odometer >  $current_mileage) {
                        $horse->mileage = $fuel->odometer;
                    }
                    if ($fuel->hours >  $current_hours) {
                        $horse->hours = $fuel->hours;
                    }
                  
                    $horse->update();
                }
                if ($fuel->vehicle) {
                    $vehicle = Vehicle::find($fuel->vehicle_id);
                    $vehicle->fuel_balance = $vehicle->fuel_balance + $fuel->quantity;
                    $current_mileage = $vehicle->mileage - $this->previous_mileage;
                    $current_hours = $vehicle->hours - $this->previous_hours;
                    if ($fuel->odometer >  $current_mileage) {
                        $vehicle->mileage = $fuel->odometer;
                    }
                    if ($fuel->hours >  $current_hours) {
                        $vehicle->hours = $fuel->hours;
                    }
                  
                    $vehicle->update();
    
                }
            }
        
            $this->dispatchBrowserEvent('hide-fuelEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Order Updated Successfully!!"
            ]);
            return redirect(request()->header('Referer'));

        }
    }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-fuelEditModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while updating fuel order!!"
        ]);
    }
    }

    public function delete($id){
        
        $this->fuel_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');        
    }

    public function destroy(){
        $fuel = Fuel::find($this->fuel_id);

        if($fuel->authorization == "approved"){

            $container = $fuel->container;
            
            if($container->purchase_type == "Bulk Buy"){
                            
                        if($container->balance && is_numeric($container->balance) && ($fuel->quantity && is_numeric($fuel->quantity)) ){
                            $container->balance = $container->balance + $fuel->quantity;
                        }
                        if($container->account_balance && is_numeric($container->account_balance) && ($fuel->amount && is_numeric($fuel->amount)) ){
                           $container->account_balance = $container->account_balance + $fuel->amount;
                        }
                        $container->update();
                    }
        }
        
        $fuel->delete();

        
        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Order Deleted Successfully!!"
        ]);
        
    }



    public function dateRange(){
 
      
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {

        $this->calculateFuelTotals();

       

       
        $this->selected_horse = Horse::find($this->horse_id ?: $this->selectedHorse);
        if ( $this->selected_horse) {
            $this->fuel_tank_capacity = $this->selected_horse->fuel_tank_capacity;
          
        }
       

        $query = Fuel::query()
                ->with([
                    'user',
                    'fuel_request.employee',
                    'fuel_request.horse',
                    'fuel_request.vehicle',
                    'fuel_request.asset.product.brand',
                    'horse',
                    'vehicle',
                    'asset.product.brand',
                    'trip.fromDestination.country',
                    'trip.toDestination.country',
                    'container',
                    'currency',
                ]);

            // Date filter: from/to if set, otherwise current month
            if ($this->from && $this->to) {
                $query->whereBetween($this->fuel_filter, [$this->from, $this->to]);
            } else {
                $query->whereMonth($this->fuel_filter, now()->month)
                    ->whereYear($this->fuel_filter, now()->year);
            }

            // Container filter
            if ($this->container_id) {
                $query->where('container_id', $this->container_id);
            }

            // Search filter
            if ($this->search) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', $search)
                        ->orWhere('quantity', 'like', $search)
                        ->orWhere('comments', 'like', $search)
                        ->orWhereHas('horse', function ($q) use ($search) {
                            $q->where('registration_number', 'like', $search)
                            ->orWhere('fleet_number', 'like', $search);
                        })
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where(DB::raw("concat(name, ' ', surname)"), 'like', $search);
                        })
                        ->orWhereHas('vehicle', function ($q) use ($search) {
                            $q->where('registration_number', 'like', $search);
                        })
                        ->orWhereHas('asset.product.brand', function ($q) use ($search) {
                            $q->where('name', 'like', $search);
                        })
                        ->orWhereHas('container', function ($q) use ($search) {
                            $q->where('name', 'like', $search);
                        })
                        ->orWhereHas('trip', function ($q) use ($search) {
                            $q->where('trip_number', 'like', $search)
                            ->orWhere('trip_ref', 'like', $search);
                        });
                });
            }

            return view('livewire.fuels.index', [
                'fuels' => $query->orderBy('order_number', 'desc')->paginate(10),
                'unit_price' => $this->unit_price,
                'amount' => $this->amount,
                'type' => $this->type,
                'containers' => $this->containers,
                'fuel_tank_capacity' => $this->fuel_tank_capacity,
                'mileage' => $this->mileage,
                'hours' => $this->hours,
                'selected_horse' => $this->selected_horse,
                'fuel_filter' => $this->fuel_filter,
            ]);
        
    }
}
