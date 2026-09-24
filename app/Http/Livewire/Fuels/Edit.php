<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Container;
use App\Models\Customer;
use App\Models\TripExpense;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads;

    public $fuels;
    public $fuel_id;
    public $assets;
    public $categories;
    public $category_values;
    public $asset_id;
    public $trips;
    public $vehicle;
    public $currencies;
    public $fuel_tank_capacity;
    public $currency_id;
    public $selectedVehicle;
    public $vehicles;
    public $selected_horse;
    public $employees;
    public $employee;
    public $employee_id;
    public $horse;
    public $horses;
    public $selectedHorse;
    public $drivers;
    public $driver;
    public $driver_id;
    public $containers;
    public $container;
    public $type;
    public $horse_id;
    public $unit_price = 0;
    public $amount = 0;
    public $quantity = 0 ;
    public $mileage;
    public $date;
    public $fillup;
    public $invoice_number;
    public $fuel_consumption;
    public $distance;
    public $file;
    public $comments;

    public $user_id;
    public $selectedContainer;
    public $container_balance;
    public $selectedCategory;
    public $selectedCategoryValue;
    public $selectedTrip;

    // Funded by: '0' = company, '1' = customer (trip fuel only) - see CustomerFuelSupplyService.
    public $supplied_by_customer = '0';
    public $fuel_customer_id;
    public $customers;


    public function mount($id){

        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $period = Auth::user()->employee->company->period;
            if (isset( $period)) {
                if ($period != "all") {
                    $this->fuels = Fuel::whereYear('created_at',$period)->latest()->get();
                }else {
                    $this->fuels = Fuel::latest()->get();
                }
            }
        } else {
            $period = Auth::user()->employee->company->period;
            if (isset( $period)) {
                if ($period != "all") {
                    $this->fuels = Fuel::where('user_id', Auth::user()->id)->whereYear('created_at',$period)->latest()->get();
                }else {
                    $this->fuels = Fuel::where('user_id', Auth::user()->id)->latest()->get();
                }
            }

        }

        $this->horses = Horse::where('service',0)->orderByIdentifier('asc')->get();
        $this->vehicles = Vehicle::where('service',0)->orderByIdentifier('asc')->get();
        $this->assets = Asset::latest()->get();
        $this->categories = Category::latest()->get();
        $this->category_values = collect();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->trips = Trip::where('authorization','approved')->orderBy('trip_number','desc')->latest()->get();
        $this->containers = Container::where('balance','>',0)->orderBy('name','asc')->latest()->get();
        $this->drivers = Driver::latest()->get();
        $this->customers = Customer::orderBy('name','asc')->get(['id','name']);

        
        $fuel = Fuel::find($id);
        $this->user_id = $fuel->user_id;
        $this->selectedHorse = $fuel->horse_id;
        $this->selectedVehicle = $fuel->vehicle_id;
        $this->selectedTrip = $fuel->trip_id;
        $this->asset_id = $fuel->asset_id;
        $this->selectedContainer = $fuel->container_id;
        $this->driver_id = $fuel->driver_id;
        $this->date = $fuel->date;
        $this->fillup = $fuel->fillup;
        $this->container = Container::find($this->selectedContainer);
        $this->container_balance = $this->container->balance;
        $this->type = $fuel->type;
        $this->mileage = $fuel->odometer;
        $this->comments = $fuel->comments;
        $this->amount = $fuel->amount;
        $this->unit_price = $fuel->unit_price;
        $this->currency_id = $fuel->currency_id;
        $this->quantity = $fuel->quantity;
        $this->fuel_id = $fuel->id;
        $this->supplied_by_customer = $fuel->supplied_by_customer ? '1' : '0';
        $this->fuel_customer_id = $fuel->customer_id;
        if ($fuel->asset_id) {
            $this->selectedCategory = Asset::find($fuel->asset_id)->category_id;
        }
    }

    public function updatedSelectedHorse($horse)
    {
        if (!is_null($horse) ) {
            $this->horse = Horse::find($horse);
            $this->selected_horse = Horse::find($horse);
            $this->horse_id =$horse;
            $this->mileage = $this->horse->mileage;
            $this->fuel_tank_capacity = $this->horse->fuel_tank_capacity;
            }
    }
    public function updatedSelectedContainer($container)
    {
        if (!is_null($container) ) {
            $this->container = Container::find($container);
            $top_ups = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get();
            $topups_price_total = TopUp::where('container_id',$this->container->id)->where('rate','!=', NULL)->where('currency_id',$this->container->currency_id)->get()->sum('rate');
            $topups_count = $top_ups->count();
            if ((isset($topups_count) && $topups_count > 0) && (isset($topups_price_total) && $topups_price_total > 0)) {
                $this->unit_price = number_format($topups_price_total/$topups_count,2);
            }
        
            $this->container_balance = $this->container->balance;
            $this->currency_id = $this->container->currency_id;
            }
    }

    public function updatedSuppliedByCustomer($value)
    {
        if ($value === '1' && !$this->fuel_customer_id && $this->selectedTrip) {
            $this->fuel_customer_id = Trip::find($this->selectedTrip)?->customer_id;
        }
    }

    public function getIsBulkBuyProperty()
    {
        return optional(Container::find($this->selectedContainer))->purchase_type === 'Bulk Buy';
    }

    public function getIsCustomerSuppliedProperty()
    {
        return (string) $this->supplied_by_customer === '1' && $this->selectedTrip && !$this->isBulkBuy;
    }

    public function updatedSelectedVehicle($vehicle)
    {
        if (!is_null($vehicle) ) {
        $this->vehicle = Vehicle::find($vehicle);
        $this->mileage = $this->vehicle->mileage;
        }
    }
 
    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->category_values = CategoryValue::where('category_id', $category)->get();
        }
    }
    public function updatedSelectedCategoryValue($category_value)
    {
        if (!is_null($category_value) ) {
        $this->assets = Asset::where('category_value_id', $category_value)->get();
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedVehicle' => 'required',
        'selectedHorse' => 'required',
        // 'driver_id' => 'required',
        'selectedContainer' => 'required',
        // 'selectedTrip' => 'required',
        'selectedCategory' => 'required',
        'asset_id' => 'required',
        'date' => 'required',
        'mileage' => 'required',
        'quantity' => 'required',
        'amount' => 'required',
        'fillup' => 'required',
        'type' => 'required',
        'invoice_number' => 'nullable',
        'file' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
        'comments' => 'nullable',
    ];


    public function update()
    {
        // try{
 
        if ($this->fuel_id) {

            if ($this->isCustomerSupplied) {
                $this->validate(['fuel_customer_id' => 'required|exists:customers,id']);
            }

            $trip = Trip::find($this->selectedTrip);

            $fuel = Fuel::find($this->fuel_id);
            $fuel->vehicle_id = $this->selectedVehicle;
            $fuel->employee_id = $this->employee_id;
            $fuel->horse_id = $this->selectedHorse;
            $fuel->asset_id = $this->asset_id;
            // Was never persisted before — selecting a trip here had no effect at all.
            $fuel->trip_id = $this->selectedTrip ?: null;
            if (isset($trip)) {
                $fuel->driver_id = $trip->driver_id;
            }
            $fuel->container_id = $this->selectedContainer;
            $fuel->date = $this->date;
            $fuel->unit_price = $this->unit_price;
            $fuel->quantity = $this->quantity;
            $fuel->currency_id = $this->currency_id;
            $fuel->amount = $this->amount;
            $fuel->odometer = $this->mileage;
            $fuel->fillup = $this->fillup;
            $fuel->type = $this->type;
            $fuel->comments = $this->comments;
            $fuel->supplied_by_customer = $this->isCustomerSupplied;
            $fuel->customer_id = $this->isCustomerSupplied ? $this->fuel_customer_id : null;

            $fuel->update();

            if ($fuel->trip_id) {
                $this->syncTripExpense($fuel, $trip);
            }

            // Approved already - re-post so a change of funding (company <-> customer)
            // or amount reaches the ledger, same as Fuels\Index::update().
            if ($fuel->authorization == 'approved') {
                try {
                    app(\App\Services\Accounting\FuelJournalService::class)->postConsumption($fuel->fresh());
                } catch (\RuntimeException $e) {
                    $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $e->getMessage()]);
                    return;
                }
            }

            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Order Updated Successfully!!"
            ]);
            // 'fuels.manage' isn't a registered route (only fuels.index/edit/etc
            // exist from the resource route) — redirecting to it 500'd after every
            // save, right after the update above had already gone through.
            return redirect()->route('fuels.index');

        }
    // }
    //     catch(\Exception $e){
    //     return redirect()->back();
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while updating fuel order!!"
    //     ]);
    // }
    }

    /**
     * Keeps this fuel order's trip expense in step whenever it's attached (or
     * re-attached) to a trip — mirrors Fuels\Index::update()'s existing
     * find-or-create-by-fuel_id pattern for the same relationship.
     */
    protected function syncTripExpense(Fuel $fuel, ?Trip $trip): void
    {
        $fuel_expense = Expense::where('name', 'Fuel Topup')->first();

        $trip_expense = TripExpense::where('fuel_id', $fuel->id)
            ->where('trip_id', $fuel->trip_id)
            ->first() ?? new TripExpense;

        $trip_expense->user_id = $fuel->user_id;
        $trip_expense->trip_id = $fuel->trip_id;
        $trip_expense->fuel_id = $fuel->id;
        if ($fuel_expense) {
            $trip_expense->expense_id = $fuel_expense->id;
        }
        $trip_expense->currency_id = $fuel->currency_id;
        $trip_expense->category = $fuel->category ?: 'Self';
        // fuels.amount is nullable — fall back to quantity * unit_price rather
        // than risk copying a null/0 amount onto the trip expense.
        $trip_expense->amount = is_numeric($fuel->amount) && (float) $fuel->amount > 0
            ? (float) $fuel->amount
            : round((float) $fuel->quantity * (float) $fuel->unit_price, 2);
        $trip_expense->exchange_rate = $fuel->exchange_rate;
        $trip_expense->exchange_amount = $fuel->exchange_amount;
        $trip_expense->date = $trip?->start_date ?? $fuel->date;
        $trip_expense->save();
    }
    
    public function render()
    {
        $container = Container::find($this->selectedContainer);
        $this->containers = Container::where('balance','>',0)->orderBy('name','asc')->latest()->get();
      
        if(($this->unit_price != null) && ($this->quantity != null)){
            $this->amount = $this->unit_price * $this->quantity;
        }
        $this->selected_horse = Horse::find($this->horse_id);
        if ( $this->selected_horse) {
            $this->fuel_tank_capacity = $this->selected_horse->fuel_tank_capacity;
            // $this->mileage = $this->selected_horse->mileage;
        }
        return view('livewire.fuels.edit',[
            'amount' => $this->amount,
            'fuels' => $this->fuels,
            'unit_price' => $this->unit_price,
            'type' => $this->type,
            'containers' => $this->containers,
            'fuel_tank_capacity'=>$this->fuel_tank_capacity,
            'mileage'=>$this->mileage,
            'selected_horse'=>$this->selected_horse,
        ]);
    }
}
