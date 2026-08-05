<?php

namespace App\Http\Livewire\Routes;

use App\Models\Route;
use App\Models\Border;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Currency;
use Livewire\Component;
use App\Models\Destination;
use App\Models\RouteExpense;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function paginationView()
    {
        return 'vendor.pagination.bootstrap-custom';
    }

    public $perPage = 10;

    public $search;
    protected $queryString = ['search'];

    public $destinations;
    public $currencies;
    public $from;
    public $to;
    public $rank;
    public $distance;
    public $status;
    public $tollgates;
    public $expiry_date;
    public $borders;
    public $border_id;
    public $name;
    public $description;
    public $fuel_consumption_rate;
    public $fuel_price_per_litre;
    public $fuel_currency_id;

    public $route_id;
    public $user_id;

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

    public function mount(){
        $this->borders = Border::latest()->get();
        $this->destinations = Destination::all();
        $this->currencies = Currency::orderBy('name', 'asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to' => 'required',
        'from' => 'required',
        'rank' => 'required',
        'description' => 'required',
        'name' => 'required|unique:routes,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->to = '';
        $this->from = '';
        $this->name = '';
        $this->rank = '';
        $this->description = '';
        $this->expiry_date = '';
        $this->tollgates = '';
        $this->distance = '';
        $this->status = '';
        $this->border_id = Null;
        $this->fuel_consumption_rate = '';
        $this->fuel_price_per_litre = '';
        $this->fuel_currency_id = '';
    }

    private function getFuelExpenseCatalogItem()
    {
        $account = Account::where('name', 'Trip Expense')->first();

        if (!$account) {
            return null;
        }

        return Expense::firstOrCreate(
            ['name' => 'Fuel', 'account_id' => $account->id],
            ['user_id' => Auth::id(), 'status' => 1]
        );
    }

    private function syncFuelExpense(Route $route){
        $existing = RouteExpense::where('route_id', $route->id)->where('source', 'fuel')->first();

        $hasFuelInputs = $route->distance && $this->fuel_consumption_rate && $this->fuel_price_per_litre;

        if (!$hasFuelInputs) {
            if ($existing) {
                $existing->delete();
            }
            return;
        }

        $fuelExpense = $this->getFuelExpenseCatalogItem();

        if (!$fuelExpense) {
            return;
        }

        $amount = round(((float) $route->distance / 100) * (float) $this->fuel_consumption_rate * (float) $this->fuel_price_per_litre, 2);

        $route_expense = $existing ?: new RouteExpense;
        $route_expense->route_id = $route->id;
        $route_expense->user_id = Auth::id();
        $route_expense->expense_id = $fuelExpense->id;
        $route_expense->category = 'Self';
        $route_expense->currency_id = $this->fuel_currency_id ?: null;
        $route_expense->amount = $amount;
        $route_expense->source = 'fuel';
        $route_expense->status = 1;
        $route_expense->save();
    }

    public function store(){
        try{
        $route = new Route;
        $route->user_id = Auth::user()->id;
        $route->name = $this->name;
        $route->distance = $this->distance;
        $route->tollgates = $this->tollgates;
        $route->description = $this->description;
        $route->from = $this->from;
        $route->expiry_date = $this->expiry_date;
        $route->to = $this->to;
        $route->rank = $this->rank;
        $route->fuel_consumption_rate = $this->fuel_consumption_rate ?: null;
        $route->fuel_price_per_litre = $this->fuel_price_per_litre ?: null;
        $route->fuel_currency_id = $this->fuel_currency_id ?: null;
        $route->status = 1;
        $route->save();
        $route->borders()->sync($this->border_id);
        $this->syncFuelExpense($route);

        $this->dispatchBrowserEvent('hide-routeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Route Created Successfully!!"
        ]);

        // return redirect()->route('routes.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating route!!"
        ]);
    }
    }

    public function edit($id){
    $route = Route::find($id);
    $this->user_id = $route->user_id;
    $this->name = $route->name;
    $this->description = $route->description;
    $this->tollgates = $route->tollgates;
    $this->distance = $route->distance;
    $this->status = $route->status;
    foreach ($route->borders as $border) {
        $this->border_id[] = $border->id;
    }
    $this->expiry_date = $route->expiry_date;
    $this->from = $route->from;
    $this->to = $route->to;
    $this->rank = $route->rank;
    $this->fuel_consumption_rate = $route->fuel_consumption_rate;
    $this->fuel_price_per_litre = $route->fuel_price_per_litre;
    $this->fuel_currency_id = $route->fuel_currency_id;
    $this->route_id = $route->id;
    $this->dispatchBrowserEvent('show-routeEditModal');

    }

       public function refresh($category){

        if($category == "destinations"){
            $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
        elseif($category == "borders"){
            $this->borders = Border::with('clearing_agents:id,name')->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Borders Refreshed Successfully!!."
            ]);
        }
    }


    public function update()
    {
        if ($this->route_id) {
            try{
            $route = Route::find($this->route_id);
            $route->user_id = Auth::user()->id;
            $route->name = $this->name;
            $route->description = $this->description;
            $route->tollgates = $this->tollgates;
            $route->distance = $this->distance;
            $route->status = $this->status;
            $route->expiry_date = $this->expiry_date;
            $route->from = $this->from;
            $route->to = $this->to;
            $route->rank = $this->rank;
            $route->fuel_consumption_rate = $this->fuel_consumption_rate ?: null;
            $route->fuel_price_per_litre = $this->fuel_price_per_litre ?: null;
            $route->fuel_currency_id = $this->fuel_currency_id ?: null;
            $route->update();
            $route->borders()->detach();
            $route->borders()->sync($this->border_id);
            $this->syncFuelExpense($route);

            $this->dispatchBrowserEvent('hide-routeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Route Updated Successfully!!"
            ]);


            // return redirect()->route('routes.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-routeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating route!!"
            ]);
          }
        }
    }


    public function updatingSearch(){
        $this->resetPage();
    }

    public function render()
    {
        $query = Route::with(['route_expenses.expense', 'borders', 'fuel_currency'])->latest();

        if (filled($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('rank', 'like', '%'.$this->search.'%');
            });
        }

        $routes = $query->paginate($this->perPage);

        $destinations = Destination::with('country')->get()->keyBy('id');

        return view('livewire.routes.index',[
            'routes'=> $routes,
            'destinationsById'=> $destinations,
        ]);
    }
}
