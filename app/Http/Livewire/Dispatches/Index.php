<?php

namespace App\Http\Livewire\Dispatches;

use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\Ticket;
use App\Models\Product;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Department;
use App\Models\DispatchItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $searchProduct;
    public $searchTicket;
    protected $queryString = ['searchProduct','searchTicket'];
    public $dispatches;
    public $department;
    public $all_departments;
    public $asset_department_id;
    public $horse_id;
    public $trailer_id;
    public $vehicle_id;
    public $branches;
    public $branch_id;
    public $tickets;
    public $ticket;
    public $selectedTicket;
    public $inventories;
    public $tyres;
    public $assets;
    public $selectedTyre = [];
    public $selectedInventory = [];
    public $selectedAsset = [];
    public $products;
    public $qty = [];
    public $weight = [];
    public $selectedProduct = [];
    public $employees;
    public $company;
    public $max;
    public $description;
    public $selectedEmployee;
    public $requested_by_id;
    public $currency_id;
    public $date;
    public $expand = False;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        $this->inputs[] = $i;
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function updatedSearchProduct($value)
    {
        if (filled($value)) {
            if ($this->department == "tyre") {
                $this->products = Product::with('brand:id,name','tyres:id,product_id,tyre_number,serial_number,total,currency_id')
                                    ->where(function ($query) use ($value) {
                                        $query
                                        ->where('department', $this->department)
                                        ->whereHas('tyres', function ($query) {
                                            $query->where('status', 1);
                                        })
                                        ->where('name', 'like', '%'.$value.'%')
                                            ->orWhereHas('brand', function ($q) use ($value) {
                                                $q->where('name', 'like', '%'.$value.'%');
                                            })
                                            ->orWhere('identification_number', 'like', '%'.$value.'%')
                                            ->orWhere('product_number', 'like', '%'.$value.'%')
                                            
                                            ;
                                    })->get();
            }elseif ($this->department == "asset") {
                 $this->products = Product::with('brand:id,name','assets:id,product_id,asset_number,serial_number,balance,weight,total,currency_id')
                                    ->where(function ($query) use ($value) {
                                        $query
                                        ->where('department', $this->department)
                                        ->whereHas('assets', function ($query) {
                                            $query->where('status', 1)->where('balance', '>', 0);
                                        })
                                        ->where('name', 'like', '%'.$value.'%')
                                            ->orWhereHas('brand', function ($q) use ($value) {
                                                $q->where('name', 'like', '%'.$value.'%');
                                            })
                                            ->orWhere('identification_number', 'like', '%'.$value.'%')
                                            ->orWhere('product_number', 'like', '%'.$value.'%')
                                            
                                            ;
                                    })->get();
            }elseif ($this->department == "inventory") {
                 $this->products = Product::with('brand:id,name', 'inventories:id,product_id,inventory_number,serial_number,balance,weight,total,currency_id')
                                    ->where(function ($query) use ($value) {
                                        $query
                                        ->where('department', $this->department)
                                        ->whereHas('inventories', function ($query) {
                                            $query->where('status', 1)->where('balance', '>', 0);
                                        })
                                        ->where('name', 'like', '%'.$value.'%')
                                            ->orWhereHas('brand', function ($q) use ($value) {
                                                $q->where('name', 'like', '%'.$value.'%');
                                            })
                                            ->orWhere('identification_number', 'like', '%'.$value.'%')
                                            ->orWhere('product_number', 'like', '%'.$value.'%')
                                            
                                            ;
                                    })->get();
            }
           
        }else{
            if ($this->department == "asset") {
                $this->products = Product::with('brand','assets')
                ->where('department', $this->department)
                ->whereHas('assets', function ($query) {
                    $query->where('status', 1)
                        ->where('balance', '>', 0);
                })
                ->orderBy('name', 'asc')
                ->get();
            }elseif ($this->department == "inventory") {
                $this->products = Product::with('brand', 'inventories')
                ->where('department', $this->department)
                ->whereHas('inventories', function ($query) {
                    $query->where('status', 1)
                        ->where('balance', '>', 0);
                })
                ->orderBy('name', 'asc')
                ->get();
            }elseif ($this->department == "tyre") {
                $this->products = Product::with('brand','tyres')
                ->where('department', $this->department)
                ->whereHas('tyres', function ($query) {
                    $query->where('status', 1);
                })
                ->orderBy('name', 'asc')
                ->get();
            }
        }
           
    }

    public function updatedSearchTicket($query){
        if (filled($query)) {
           $this->tickets = Ticket::whereYear('created_at',date('Y'))
            ->where('status',1)
            ->where('ticket_number','like', '%'.$this->searchTicket.'%')
            ->orWhere('in_date','like', '%'.$this->searchTicket.'%')
            ->orWhereHas('booking', function ($query) {
                return $query->where('booking_number', 'like', '%'.$this->searchTicket.'%')
                            ->orWhereHas('employee', function ($q2) {
                            $q2->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchTicket."%");
                });
            })
            ->orWhereHas('booking', function ($q) {
                    $q->whereHas('employees', function ($query) {
                        $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->searchTicket."%");
                    });
                })
            ->orWhereHas('service_type', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchTicket.'%');
            })
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->searchTicket.'%')
                             ->orWhere('fleet_number', 'like', '%'.$this->searchTicket.'%');
            })
            ->orWhereHas('vehicle', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->searchTicket.'%')
                            ->orWhere('fleet_number', 'like', '%'.$this->searchTicket.'%');
            })
            ->orWhereHas('trailer', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->searchTicket.'%')
                             ->orWhere('fleet_number', 'like', '%'.$this->searchTicket.'%');
            })->orderBy('created_at','desc')
           ->get();
        }else{
             $this->tickets = Ticket::whereYear('created_at',date('Y'))->where('status',1)->orderBy('created_at','desc')->get();
        }
    }

    public function mount($department){
        $this->inventories = collect();
        $this->tyres = collect();
        $this->assets = collect();
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->employees = Employee::where('status',1)->where('archive',0)->orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->tickets = Ticket::whereYear('created_at',date('Y'))->where('status',1)->orderBy('created_at','desc')->get();
        $this->all_departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();

        if ($this->department == "asset") {
            $this->products = Product::with('brand','assets')
            ->where('department', $this->department)
            ->whereHas('assets', function ($query) {
                $query->where('status', 1)
                    ->where('balance', '>', 0);
            })
            ->orderBy('name', 'asc')
            ->get();
        }elseif ($this->department == "inventory") {
            $this->products = Product::with('brand', 'inventories')
            ->where('department', $this->department)
            ->whereHas('inventories', function ($query) {
                $query->where('status', 1)
                    ->where('balance', '>', 0);
            })
            ->orderBy('name', 'asc')
            ->get();
        }elseif ($this->department == "tyre") {
             $this->products = Product::with('brand','tyres')
            ->where('department', $this->department)
            ->whereHas('tyres', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('name', 'asc')
            ->get();
        }
       
       
     
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $employee = Employee::find($id);
            if ($employee) {
                $this->asset_department_id = $employee->departments->first()->id;
                $this->branch_id = $employee->branch_id;
            }
          
        }
    }
    public function updatedSelectedTicket($id){
        if (!is_null($id)) {
            $ticket = Ticket::find($id);
            if ($ticket) {
                $this->horse_id = $ticket->horse_id;
                $this->vehicle_id = $ticket->vehicle_id;
                $this->trailer_id = $ticket->trailer_id;
            }
           
        }
    }

    public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if ($inventory && $this->expand == True) {
                $this->weight[$key] = $inventory->balance;
            }
        }
         
    }
    public function updatedSelectedAsset($id, $key){
        if (!is_null($id)) {
            $asset = Asset::find($id);
            if ($asset && $this->expand == True) {
                $this->weight[$key] = $asset->balance;
            }
        }
    }
  
    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            if ($this->expand == False) {
                  $this->qty[$key] = 1;
            }
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->orderBy('created_at','asc')->get();
            $this->assets = Asset::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            
            $product = Product::find($id);

            if ($product && $this->department == "inventory") {
                $this->max[$key] = $product->inventories->count();
            }elseif ($product && $this->department == "tyre") {
                $this->max[$key] = $product->tyres->count();
            }elseif ($product && $this->department == "asset") {
                $this->max[$key] = $product->assets->count();
            }

        
            
        }
    }

     public function dispatchNumber(){

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

        $dispatch = Dispatch::latest()->orderBy('id','desc')->first();

        if (!$dispatch) {
            $dispatch_number =  $initials .'D'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $dispatch->id + 1;
            $dispatch_number =  $initials .'D'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $dispatch_number;


    }

    
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

    
    private function resetInputFields(){
        $this->date = '';
        $this->requested_by_id = '';
        $this->selectedEmployee = '';
        $this->branch_id = '';
        $this->asset_department_id = '';
        $this->selectedInventory = [];
        $this->searchTicket = [] ;
    }

    public function store(){

        DB::transaction(function () {
        
        $dispatch = new Dispatch;
        $dispatch->user_id = Auth::user()->id;
        $dispatch->dispatch_number = $this->dispatchNumber();
        $dispatch->horse_id = $this->horse_id ?: null;
        $dispatch->trailer_id = $this->trailer_id ?: null;
        $dispatch->vehicle_id = $this->vehicle_id ?: null;
        $dispatch->ticket_id = $this->selectedTicket ?: null;
        $dispatch->employee_id = $this->selectedEmployee ?: null;
        $dispatch->requested_by_id = $this->requested_by_id ?: null;
        $dispatch->department = $this->department;
        $dispatch->department_id = $this->asset_department_id ?: null;
        $dispatch->branch_id = $this->branch_id ?: null;
        $dispatch->currency_id = $this->company->currency_id ?: null;
        $dispatch->description = $this->description;
        $dispatch->date = $this->date;
        $dispatch->save();

        $dispatch_total = 0;

        if ($this->expand == True) {

            if ($this->department == "inventory") {
                foreach ($this->selectedInventory as $key => $id) {

                    $amount = 0;
                    $exchange_amount = 0;

                    $inventory = Inventory::find($id);

                    if ($inventory) {

                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $inventory->product_id;
                        $dispatch_item->currency_id = $inventory->currency_id;
                        $dispatch_item->inventory_id = $id;

                       

                        if (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && 
                            is_numeric($inventory->weight) && $inventory->weight > 0) {

                            $dispatch_item->weight = $this->weight[$key];
                            $ratio = $this->weight[$key] / $inventory->weight;

                            if ($inventory->currency_id != $this->company->currency_id) {
                                if (is_numeric($inventory->exchange_amount) && is_numeric($inventory->total)) {
                                    $exchange_amount = $ratio * $inventory->exchange_amount;
                                    $amount = $ratio * $inventory->total;
                                }
                            } else {
                                if (is_numeric($inventory->total)) {
                                    $amount = $ratio * $inventory->total;
                                }
                            }

                            $dispatch_item->amount = $amount;
                            $dispatch_item->exchange_amount = $exchange_amount;
                        }

                        $dispatch_item->exchange_rate = $inventory->exchange_rate;
                        $dispatch_item->save();

                        if(is_numeric($exchange_amount) || is_numeric($amount)){
                            $dispatch_total += $inventory->currency_id != $this->company->currency_id
                            ? $exchange_amount
                            : $amount;
                        }
                        

                    }
                }
            }elseif ($this->department == "tyre") {
                foreach ($this->selectedTyre as $key => $id) {  

                    $tyre = Tyre::find($id);

                    if ($tyre) {
                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $tyre->product_id;
                        $dispatch_item->currency_id = $tyre->currency_id;
                        $dispatch_item->amount = $tyre->total;
                        $dispatch_item->exchange_amount = $tyre->exchange_amount;
                        $dispatch_item->exchange_rate = $tyre->exchange_rate;
                        $dispatch_item->tyre_id = $this->selectedTyre[$key];
                        $dispatch_item->save();   

                        if(is_numeric($tyre->exchange_amount) || is_numeric($tyre->total)){
                            $dispatch_total += $tyre->currency_id != $this->company->currency_id
                            ? $tyre->exchange_amount
                            : $tyre->total;
                        }
                        
                    }
                }
            }elseif ($this->department == "asset") {

                foreach ($this->selectedAsset as $key => $id) {

                    $amount = 0;
                    $exchange_amount = 0;

                    $asset = Asset::find($id);

                    if ($asset) {

                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $asset->product_id;
                        $dispatch_item->currency_id = $asset->currency_id;
                        $dispatch_item->asset_id = $id;

                        if (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && 
                            is_numeric($asset->weight) && $asset->weight > 0) {

                            $dispatch_item->weight = $this->weight[$key];
                            $ratio = $this->weight[$key] / $asset->weight;

                            if ($asset->currency_id != $this->company->currency_id) {
                                if (is_numeric($asset->exchange_amount) && is_numeric($asset->total)) {
                                    $exchange_amount = $ratio * $asset->exchange_amount;
                                    $amount = $ratio * $asset->total;
                                }
                            } else {
                                if (is_numeric($asset->total)) {
                                    $amount = $ratio * $asset->total;
                                }
                            }

                            $dispatch_item->amount = $amount;
                            $dispatch_item->exchange_amount = $exchange_amount;
                        }

                        $dispatch_item->exchange_rate = $asset->exchange_rate;
                        $dispatch_item->save();

                        if(is_numeric($exchange_amount) || is_numeric($amount)){
                            $dispatch_total += $asset->currency_id != $this->company->currency_id
                            ? $exchange_amount
                            : $amount;
                        }
                       

                    }
                }
            }
        }elseif ($this->expand == False) {

            if ($this->selectedProduct) {

                foreach ($this->selectedProduct as $key => $productId) {

                    $qty = $this->qty[$key] ?? 0;
                    if (!$qty || $qty < 1) continue;

                    $product = Product::find($productId);
                    if (!$product) continue;

                    switch ($this->department) {
                        case 'inventory':
                            $items = Inventory::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->inventory_id = $item->id;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->weight = $item->balance;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }
                               
                                
                            }
                            break;

                        case 'asset':
                            $items = Asset::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->asset_id = $item->id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->weight = $item->balance;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }

                               
                            }
                            break;

                        case 'tyre':
                            $items = Tyre::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->tyre_id = $item->id;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }   

                               
                            }
                            break;
                    }
                }
            }
        }

        $dispatch->total = $dispatch_total;
        $dispatch->save();

        $this->dispatchBrowserEvent('hide-dispatchModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Items Dispatched Successfully!!"
        ]);

    });
    
    }

    public function edit($id){
        $dispatch = Dispatch::find($id);
        $this->horse_id = $dispatch->horse_id;
        $this->horse_id = $dispatch->trailer_id;
        $this->horse_id = $dispatch->vehicle_id;
        $this->selectedTicket = $dispatch->ticket_id;
        $this->selectedEmployee = $dispatch->ticket_id;
        $this->requested_by_id = $dispatch->requested_by_id;
        $this->department = $dispatch->department;
        $this->asset_department_id = $dispatch->department_id;
        $this->branch_id = $dispatch->branch_id;
        $this->currency_id = $dispatch->currency_id;
        $this->description = $dispatch->description;
        $this->date = $dispatch->date;
        $dispatch_items = $dispatch->dispatch_items;

        if($dispatch_items){
            foreach($dispatch_items as $dispatch_item){
                $this->selectedInventory[] = $dispatch_item->inventory_id; 
                $this->selectedProduct[] = $dispatch_item->inventory_id; 
                $this->selectedTyre[] = $dispatch_item->tyre_id; 
                $this->selectedAsset[] = $dispatch_item->tyre_id; 
                $this->weight[] = $dispatch_item->weight; 
                $this->qty[] = $dispatch_item->qty; 
            }
        }
        
        if (!empty($this->selectedInventory) || !empty($this->selectedAsset) || !empty($this->selectedTyre)) {
            $this->expand = true;
        }
         $this->dispatchBrowserEvent('show-dispatchEditModal');

    }

    public function update(){

        DB::transaction(function () {
        
        $dispatch = new Dispatch;
        $dispatch->user_id = Auth::user()->id;
        $dispatch->dispatch_number = $this->dispatchNumber();
        $dispatch->horse_id = $this->horse_id ?: null;
        $dispatch->trailer_id = $this->trailer_id ?: null ;
        $dispatch->vehicle_id = $this->vehicle_id ?: null;
        $dispatch->ticket_id = $this->selectedTicket ?: null;
        $dispatch->employee_id = $this->selectedEmployee ?: null;
        $dispatch->requested_by_id = $this->requested_by_id ?: null;
        $dispatch->department = $this->department;
        $dispatch->department_id = $this->asset_department_id ?: null;
        $dispatch->branch_id = $this->branch_id ?: null;
        $dispatch->currency_id = $this->company->currency_id ?: null;
        $dispatch->description = $this->description;
        $dispatch->date = $this->date;
        $dispatch->save();

        $dispatch_total = 0;

        if ($this->expand == True) {

            if ($this->department == "inventory") {
                foreach ($this->selectedInventory as $key => $id) {

                    $amount = 0;
                    $exchange_amount = 0;

                    $inventory = Inventory::find($id);

                    if ($inventory) {

                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $inventory->product_id;
                        $dispatch_item->currency_id = $inventory->currency_id;
                        $dispatch_item->inventory_id = $id;

                       

                        if (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && 
                            is_numeric($inventory->weight) && $inventory->weight > 0) {

                            $dispatch_item->weight = $this->weight[$key];
                            $ratio = $this->weight[$key] / $inventory->weight;

                            if ($inventory->currency_id != $this->company->currency_id) {
                                if (is_numeric($inventory->exchange_amount) && is_numeric($inventory->total)) {
                                    $exchange_amount = $ratio * $inventory->exchange_amount;
                                    $amount = $ratio * $inventory->total;
                                }
                            } else {
                                if (is_numeric($inventory->total)) {
                                    $amount = $ratio * $inventory->total;
                                }
                            }

                            $dispatch_item->amount = $amount;
                            $dispatch_item->exchange_amount = $exchange_amount;
                        }

                        $dispatch_item->exchange_rate = $inventory->exchange_rate;
                        $dispatch_item->save();

                        if(is_numeric($exchange_amount) || is_numeric($amount)){
                            $dispatch_total += $inventory->currency_id != $this->company->currency_id
                            ? $exchange_amount
                            : $amount;
                        }
                       

                    }
                }
            }elseif ($this->department == "tyre") {
                foreach ($this->selectedTyre as $key => $id) {  

                    $tyre = Tyre::find($id);

                    if ($tyre) {
                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $tyre->product_id;
                        $dispatch_item->currency_id = $tyre->currency_id;
                        $dispatch_item->amount = $tyre->total;
                        $dispatch_item->exchange_amount = $tyre->exchange_amount;
                        $dispatch_item->exchange_rate = $tyre->exchange_rate;
                        $dispatch_item->tyre_id = $this->selectedTyre[$key];
                        $dispatch_item->save();   

                        if(is_numeric($tyre->exchange_amount) || is_numeric($tyre->total)){
                             $dispatch_total += $tyre->currency_id != $this->company->currency_id
                            ? $tyre->exchange_amount
                            : $tyre->total;
                        }
                       
                    }
                }
            }elseif ($this->department == "asset") {

                foreach ($this->selectedAsset as $key => $id) {

                    $amount = 0;
                    $exchange_amount = 0;

                    $asset = Asset::find($id);

                    if ($asset) {

                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id = $dispatch->id;
                        $dispatch_item->product_id = $asset->product_id;
                        $dispatch_item->currency_id = $asset->currency_id;
                        $dispatch_item->asset_id = $id;

                        if (isset($this->weight[$key]) && is_numeric($this->weight[$key]) && 
                            is_numeric($asset->weight) && $asset->weight > 0) {

                            $dispatch_item->weight = $this->weight[$key];
                            $ratio = $this->weight[$key] / $asset->weight;

                            if ($asset->currency_id != $this->company->currency_id) {
                                if (is_numeric($asset->exchange_amount) && is_numeric($asset->total)) {
                                    $exchange_amount = $ratio * $asset->exchange_amount;
                                    $amount = $ratio * $asset->total;
                                }
                            } else {
                                if (is_numeric($asset->total)) {
                                    $amount = $ratio * $asset->total;
                                }
                            }

                            $dispatch_item->amount = $amount;
                            $dispatch_item->exchange_amount = $exchange_amount;
                        }

                        $dispatch_item->exchange_rate = $asset->exchange_rate;
                        $dispatch_item->save();

                         if(is_numeric($exchange_amount) || is_numeric($amount)){
                            $dispatch_total += $asset->currency_id != $this->company->currency_id
                            ? $exchange_amount
                            : $amount;
                        }

                    

                    }
                }
            }
        }elseif ($this->expand == False) {

            if ($this->selectedProduct) {

                foreach ($this->selectedProduct as $key => $productId) {

                    $qty = $this->qty[$key] ?? 0;
                    if (!$qty || $qty < 1) continue;

                    $product = Product::find($productId);
                    if (!$product) continue;

                    switch ($this->department) {
                        case 'inventory':
                            $items = Inventory::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->inventory_id = $item->id;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->weight = $item->balance;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }


                               
                                
                            }
                            break;

                        case 'asset':
                            $items = Asset::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->asset_id = $item->id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->weight = $item->balance;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }
                               
                            }
                            break;

                        case 'tyre':
                            $items = Tyre::where('product_id', $product->id)
                                ->orderBy('created_at', 'asc')
                                ->take($qty)
                                ->get();

                            foreach ($items as $item) {
                                $dispatch_item = new DispatchItem;
                                $dispatch_item->dispatch_id = $dispatch->id;
                                $dispatch_item->product_id = $product->id;
                                $dispatch_item->tyre_id = $item->id;
                                $dispatch_item->currency_id = $item->currency_id;
                                $dispatch_item->amount = $item->total;
                                $dispatch_item->exchange_amount = $item->exchange_amount;
                                $dispatch_item->exchange_rate = $item->exchange_rate;
                                $dispatch_item->save();

                                if(is_numeric($item->exchange_amount) || is_numeric($item->total)){
                                    $dispatch_total += $item->currency_id != $this->company->currency_id
                                    ? $item->exchange_amount
                                    : $item->total;
                                }

                                
                            }
                            break;
                    }
                }
            }
        }

        $dispatch->total = $dispatch_total;
        $dispatch->save();

        $this->dispatchBrowserEvent('hide-dispatchModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Items Dispatched Successfully!!"
        ]);

    });
    
    }
    public function render()
    {
        $this->dispatches = Dispatch::where('department',$this->department)->get();
        return view('livewire.dispatches.index');
    }
}
