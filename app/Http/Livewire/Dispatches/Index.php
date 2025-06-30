<?php

namespace App\Http\Livewire\Dispatches;

use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Ticket;
use App\Models\Product;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\DispatchItem;
use Illuminate\Support\Facades\DB;

class Index extends Component
{

    public $searchProduct;
    public $searchTicket;
    protected $queryString = ['searchProduct','searchTicket'];
    public $dispatches;
    public $department;
    public $departments;
    public $department_id;
    public $horse_id;
    public $trailer_id;
    public $vehicle_id;
    public $branches;
    public $branch_id;
    public $tickets;
    public $ticket;
    public $selectedTicket;
    public $inventories;
    public $selectedInventory = [];
    public $products;
    public $qty = [];
    public $weight = [];
    public $selectedProduct = [];
    public $employees;
    public $max;
    public $discription;
    public $selectedEmployee;
    public $requested_by_id;
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
            $this->products = Product::with('brand', 'inventories')
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
            })
            ->get();
        }else{
            $this->products = Product::with('brand', 'inventories')
            ->where('department', $this->department)
            ->whereHas('inventories', function ($query) {
                $query->where('status', 1)
                    ->where('balance', '>', 0);
            })
            ->orderBy('name', 'asc')
            ->get();
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
            })
           ->get();
        }else{
             $this->tickets = Ticket::whereYear('created_at',date('Y'))->where('status',1)->get();
        }
    }

    public function mount($department){
        $this->inventories = collect();
        $this->department = $department;
        $this->employees = Employee::where('status',1)->where('archive',0)->orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->tickets = Ticket::whereYear('created_at',date('Y'))->where('status',1)->get();
       

         $this->products = Product::with('brand', 'inventories')
        ->where('department', $this->department)
        ->whereHas('inventories', function ($query) {
            $query->where('status', 1)
                ->where('balance', '>', 0);
        })
        ->orderBy('name', 'asc')
        ->get();
       
     
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $employee = Employee::find($id);
            if ($employee) {
                $this->department_id = $employee->departments->first()->id;
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
            if ($this->inventories) {
                $this->max[$key] = $this->inventories->count();
            }elseif ($this->tyres) {
                $this->max[$key] = $this->tyres->count();
            }elseif ($this->assets) {
                $this->max[$key] = $this->assets->count();
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
            $dispatch_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $dispatch->id + 1;
            $dispatch_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $dispatch_number;


    }

    public function store(){
        
        $dispatch = new Dispatch;
        $dispatch->user_id = Auth::user()->id;
        $dispatch->dispatch_number = $this->selectedTicket;
        $dispatch->ticket_id = $this->selectedTicket;
        $dispatch->employee_id = $this->employee_id;
        $dispatch->requested_by_id = $this->requested_by_id;
        $dispatch->department = $this->department;
        $dispatch->department_id = $this->department_id;
        $dispatch->branch_id = $this->branch_id;
        $dispatch->save();

        if ($this->expand == True) {
            if ($this->selectedInventory) {
                foreach ($this->selectedInventory as $key => $value) {
                    $dispatch_item = new DispatchItem;
                    $dispatch_item->dispatch_id = $dispatch->id;
                    if (isset($this->selectedTyre[$key])) {
                        $tyre = Tyre::find($this->selectedTyre[$key]);
                        if ($tyre) {
                            $dispatch_item->product_id = $tyre->product_id;
                        }
                        $dispatch_item->tyre_id = $this->selectedTyre[$key];
                    }
                    if (isset($this->selectedAsset[$key])) {
                        $asset = Asset::find($this->selectedAsset[$key]);
                        if ($asset) {
                            $dispatch_item->product_id = $asset->product_id;
                        }
                        $dispatch_item->asset_id = $this->selectedAsset[$key];
                    }
                    if (isset($this->selectedInventory[$key])) {
                        $inventory = Inventory::find($this->selectedInventory[$key]);
                        if ($inventory) {
                            $dispatch_item->product_id = $inventory->product_id;
                        }
                        $dispatch_item->inventory_id = $this->selectedInventory[$key];
                    }
                    if (isset($this->weight[$key])) {
                        $dispatch_item->weight = $this->weight[$key];
                    }
                        
                }
            }
        }elseif ($this->expand == False) {
                if ($this->selectedProduct) {
                foreach ($this->selectedProduct as $key => $value) {
                    if (isset($this->qty[$key])) {
                            if (isset($this->selectedProduct[$key])) {
                                $product = Product::find($this->selectedProduct[$key]);
                                if ($product) {
                                    if ($this->department == 'inventory') {
                                        $inventories = $product->inventories->orderBy('created_at','asc')->take($this->qty[$key]);
                                        if ($inventories) {
                                            foreach ($inventories as $inventory) {
                                                $dispatch_item = new DispatchItem;
                                                $dispatch_item->dispatch_id = $dispatch->id;
                                                $dispatch_item->product_id = $this->selectedProduct[$key];
                                                $dispatch_item->inventory_id = $inventory->id;
                                                $dispatch_item->weight = $inventory->balance;
                                                $dispatch_item->save();
                                            }
                                        }
                                    }elseif ($this->department == 'asset') {
                                        $assets = $product->assets->orderBy('created_at','asc')->take($this->qty[$key]);
                                        if ($assets) {
                                            foreach ($assets as $asset) {
                                                $dispatch_item = new DispatchItem;
                                                $dispatch_item->dispatch_id = $dispatch->id;
                                                $dispatch_item->product_id = $this->selectedProduct[$key];
                                                $dispatch_item->asset_id = $asset->id;
                                                $dispatch_item->weight = $asset->balance;
                                                $dispatch_item->save();
                                            }
                                        }
                                    }elseif ($this->department == 'tyre') {
                                        $tyres = $product->tyres->orderBy('created_at','asc')->take($this->qty[$key]);
                                        if ($tyres) {
                                            foreach ($tyres as $tyre) {
                                                $dispatch_item = new DispatchItem;
                                                $dispatch_item->dispatch_id = $dispatch->id;
                                                $dispatch_item->product_id = $this->selectedProduct[$key];
                                                $dispatch_item->tyre_id = $tyre->id;
                                                $dispatch_item->save();
                                            }
                                        }
                                    }
                                }
                               
                            }   
                    }
               
                        
                }
            }
        }

    
    }
    public function render()
    {
        $this->dispatches = Dispatch::where('department',$this->department)->get();
        return view('livewire.dispatches.index');
    }
}
