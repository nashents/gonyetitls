<?php

namespace App\Http\Livewire\Dispatches;

use Carbon\Carbon;
use App\Models\Tyre;
use App\Models\Asset;
use App\Models\Store;
use App\Models\Branch;
use App\Models\Ticket;
use App\Models\Product;
use Livewire\Component;
use App\Models\Dispatch;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\Department;
use App\Models\DispatchItem;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $from;
    public $to;

    public $searchProduct;
    public $searchTicket;
    protected $queryString = ['searchProduct','searchTicket','search'];
    private $dispatches;
    public $department;
    public $dispatch_filter = "created_at";
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
    public $dispatch;
    public $selectedTyre = [];
    public $selectedInventory = [];
    public $selectedAsset = [];
    public $products;
    public $qty = [];
    public $weight = [];
    public $selectedProduct = [];
    public $requestedItem = [];
    public $ticket_requests;
    public $employees;
    public $company;
    public $max;
    public $max_weight;
    public $description;
    public $selectedEmployee;
    public $requested_by_id;
    public $currency_id;
    public $date;
    public $stores;
    public $selectedStore;
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
        $relation = $this->department === 'tyre' 
            ? 'tyres'
            : ($this->department === 'asset'
                ? 'assets'
                : 'inventories');

        $relationFields = [
            'tyre' => 'tyres:id,product_id,tyre_number,serial_number,total,currency_id,status',
            'asset' => 'assets:id,product_id,asset_number,serial_number,balance,weight,total,currency_id,status',
            'inventory' => 'inventories:id,product_id,inventory_number,serial_number,balance,weight,total,currency_id,status',
        ];

        // Base query
        $query = Product::with('brand:id,name', $relationFields[$this->department])
            ->where('department', $this->department)
            ->whereHas($relation, function ($q) {
                $q->where('status', 1);

                if ($this->department !== 'tyre') {
                    $q->where('balance', '>', 0);
                }
            });

        // If search term exists
        if (filled($value)) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                ->orWhere('product_number', 'like', "%{$value}%")
                ->orWhere('identification_number', 'like', "%{$value}%")
                ->orWhereHas('brand', function ($b) use ($value) {
                    $b->where('name', 'like', "%{$value}%");
                });
            });
        }

        $this->products = $query->orderBy('name', 'asc')->get();
    }

    public function updatedSearchTicket($value)
    {
        $search = trim($value);

        $baseQuery = Ticket::query()
            ->whereYear('created_at', date('Y'))
            ->where('status', 1);

        if (filled($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('in_date', 'like', "%{$search}%")
                ->orWhereHas('booking', function ($qb) use ($search) {
                    $qb->where('booking_number', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($q2) use ($search) {
                            $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                        })
                        ->orWhereHas('employees', function ($q3) use ($search) {
                            $q3->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                        });
                })
                ->orWhereHas('service_type', function ($qs) use ($search) {
                    $qs->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('horse', function ($qh) use ($search) {
                    $qh->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('vehicle', function ($qv) use ($search) {
                    $qv->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('trailer', function ($qt) use ($search) {
                    $qt->where('registration_number', 'like', "%{$search}%")
                        ->orWhere('fleet_number', 'like', "%{$search}%");
                });
            });
        }

        $this->tickets = $baseQuery
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadProducts()
    {
        $map = [
            'asset'     => 'assets',
            'inventory' => 'inventories',
            'tyre'      => 'tyres',
        ];

        $relation = $map[$this->department] ?? null;

        if (! $relation) {
            $this->products = collect();
            return;
        }

        $this->products = Product::with('brand', $relation)
            ->where('department', $this->department)
            ->whereHas($relation, function ($q) use ($relation) {
                $q->where('status', 1);

                // Only enforce balance for stock-type departments
                if (in_array($relation, ['assets', 'inventories'])) {
                    $q->where('balance', '>', 0);
                }
            })
            ->orderBy('name', 'asc')
            ->get();
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
        $this->stores = Store::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->reset(['searchProduct', 'searchTicket']);
        $this->loadProducts();
       
       
     
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $employee = Employee::find($id);
            if ($employee) {
                $this->asset_department_id = $employee->departments->first()?->id;
                $this->branch_id = $employee->branch_id;
            }
          
        }
    }
    public function updatedSelectedTicket($id){
        if (!is_null($id)) {
            $this->ticket = Ticket::find($id);
            $this->ticket_requests = $this->ticket?->ticket_requests;
            $this->horse_id = $this->ticket?->horse_id;
            $this->vehicle_id = $this->ticket?->vehicle_id;
            $this->trailer_id = $this->ticket?->trailer_id;
           
        }
    }

    public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if ($inventory && $this->expand == True) {
                $this->weight[$key] = $inventory->balance;
                $this->max_weight[$key] = $inventory->balance;
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
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->assets = Asset::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            
            $product = Product::find($id);

            if ($product && $this->department == "inventory") {
                $this->max[$key] = $product->inventories->where('status',1)->where('balance','>',0)->sum('balance');
            }elseif ($product && $this->department == "tyre") {
                $this->max[$key] = $product->tyres->where('status',1)->sum('balance');
            }elseif ($product && $this->department == "asset") {
                $this->max[$key] = $product->assets->where('status',1)->where('balance','>',0)->sum('balance');
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

        $this->validate([
            'selectedEmployee' => [
                'nullable',
                Rule::exists('employees', 'id'),
            ],
        ]);
        
        $dispatch = new Dispatch;
        $dispatch->user_id = Auth::user()->id;
        $dispatch->dispatch_number = $this->dispatchNumber();
        $dispatch->horse_id = $this->horse_id ?: null;
        $dispatch->trailer_id = $this->trailer_id ?: null;
        $dispatch->vehicle_id = $this->vehicle_id ?: null;
        $dispatch->ticket_id = $this->selectedTicket ?: null;
        $dispatch->store_id = $this->selectedStore ?: null;
        $dispatch->employee_id = $this->selectedEmployee ?: null;
        $dispatch->requested_by_id = $this->requested_by_id ?: null;
        $dispatch->department = $this->department;
        $dispatch->department_id = $this->asset_department_id ?: null;
        $dispatch->branch_id = $this->branch_id ?: null;
        $dispatch->currency_id = $this->company->currency_id ?: null;
        $dispatch->description = $this->description;
        $dispatch->date = $this->date;
        $dispatch->save();
       
        if ($this->expand == True) {

            $this->InventoryFIFO($dispatch);
            $this->InventoryAVCO($dispatch);

        }elseif ($this->expand == False) {

            if($this->company->valuation_method == "AVCO"){
                $dispatch_total = $this->ProductAVCO($dispatch);
            }elseif($this->company->valuation_method == "FIFO"){
                $dispatch_total = $this->ProductFIFO($dispatch);
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

    public function updatedSelectedStore($id){
        if(!is_null($id)){
            $store = Store::find($id);
        }
    }

    public function showDelete($id){
        if(!is_null($id)){

             $this->dispatch = Dispatch::find($id);
             $this->dispatchBrowserEvent('show-dispatchDeleteModal');

        }
    }

    public function destroy(){

        $dispatch_items = $this->dispatch->dispatch_items;
        if($dispatch_items){
            foreach($dispatch_items as $dispatch_item){
                $dispatch_item->delete();
            }
        }
        $this->dispatch->delete();
        $this->dispatchBrowserEvent('hide-dispatchDeleteModal');

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Dipatch Record Deleted Successfully!!"
        ]);

    }

   public function inventoryFIFO($dispatch)
    {
        $dispatch_total = 0;

        // 1) Resolve collection based on department
        if ($this->department === 'tyre') {
            $collection = $this->selectedTyre ?? [];
        } elseif ($this->department === 'asset') {
            $collection = $this->selectedAsset ?? [];
        } elseif ($this->department === 'inventory') {
            $collection = $this->selectedInventory ?? [];
        } else {
            return 0; // unknown department
        }

        if (empty($collection)) {
            return 0;
        }

        foreach ($collection as $key => $collectionId) {

            $requestedQty = (float)($this->qty[$key] ?? 0);
            if ($requestedQty <= 0) {
                continue;
            }

            // 2) Load correct model row
            if ($this->department === 'tyre') {
                $model = Tyre::find($collectionId);
            } elseif ($this->department === 'asset') {
                $model = Asset::find($collectionId);
            } else { // inventory
                $model = Inventory::find($collectionId);
            }

            if (!$model) {
                continue;
            }

            // 3) How much is available on this exact row
            $rowQtyAvailable = (float)($model->balance);
            if ($rowQtyAvailable <= 0) {
                continue;
            }


            // 5) Row cost in company currency
            $rowCostCompany = $model->currency_id != $this->company->currency_id
                ? (float)$model->exchange_amount
                : (float)$model->total;

            $unitCost      = $rowCostCompany / $rowQtyAvailable;
            $qtyFromRow    = min($requestedQty, $rowQtyAvailable);
            $amountFromRow = $qtyFromRow * $unitCost;

            // 6) Create dispatch item
            $dispatch_item               = new DispatchItem;
            $dispatch_item->dispatch_id  = $dispatch->id;
            $dispatch_item->product_id   = $model->product?->id;

            if (isset($this->requestedItem[$key])) {
                $dispatch_item->ticket_request_id = $this->requestedItem[$key];
            }

            $dispatch_item->qty         = $qtyFromRow;
            $dispatch_item->unit_cost   = $unitCost;
            $dispatch_item->amount      = $amountFromRow;
            $dispatch_item->currency_id = $this->company->currency_id;

            if ($this->department === 'tyre') {
                $dispatch_item->tyre_id = $model->id;
            } elseif ($this->department === 'asset') {
                $dispatch_item->asset_id = $model->id;
            } else {
                $dispatch_item->inventory_id = $model->id;
            }

            $dispatch_item->save();

            // 8) Add to overall dispatch total
            $dispatch_total += $amountFromRow;
        }

        return $dispatch_total;
    }


    public function ProductFIFO($dispatch){
       

        $dispatch_total = 0;

        if ($this->selectedProduct) {

            foreach ($this->selectedProduct as $key => $productId) {

                $requestedQty = (float)($this->qty[$key] ?? 0); // in litres/units to dispatch
                if ($requestedQty <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                // 1) Get source rows in FIFO order
                $items = collect();

                switch ($this->department) {
                    case 'inventory':
                        $items = Inventory::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)  // use balance as availability
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;

                    case 'asset':
                        $items = Asset::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;

                    case 'tyre':
                        $items = Tyre::where('product_id', $product->id)
                            ->where('status', 1)
                            ->where('balance', '>', 0)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        break;
                }

                if ($items->isEmpty()) {
                    continue;
                }
               
                $remainingQty        = $requestedQty;
                $totalQtyDispatched  = 0.0;
                $totalLineAmount     = 0.0;

                foreach ($items as $item) {

                    if ($remainingQty <= 0) {
                        break; // request satisfied
                    }

                    // --- 2) Determine how much is available on this row ---
                    
                    if ($this->department === 'inventory') {
                        // Liquids / contents:
                        // balance = remaining litres, weight = original litres (capacity)
                        $rowQtyAvailable = (float)$item->balance;
                        $rowCapacity     = (float)($item->balance); 
                    } else {
                        // For assets/tyres etc. you can treat balance as remaining units
                        $rowQtyAvailable = (float)($item->balance ?: $item->qty);
                        $rowCapacity     = (float)($rowQtyAvailable);
                    }

                    if ($rowQtyAvailable <= 0 || $rowCapacity <= 0) {
                        continue;
                    }

                    // --- 3) Row cost in company currency ---
                    $rowCostCompany = $item->currency_id != $this->company->currency_id
                        ? (float)$item->exchange_amount   // already converted
                        : (float)$item->total;            // native in company currency
                   
                    // Unit cost per litre/unit from THIS row
                    $unitCost = $rowCostCompany / $rowQtyAvailable;
                   

                    // --- 4) How much do we take from this row (FIFO) ---
                    $qtyFromRow = min($remainingQty, $rowQtyAvailable);
                   
                    // Amount for this portion
                    $amountFromRow = $qtyFromRow * $unitCost;
                    
                    // --- 5) Create a dispatch line referencing this source row ---
                    $dispatch_item               = new DispatchItem;
                    $dispatch_item->dispatch_id  = $dispatch->id;
                    if(isset($this->requestedItem[$key])){
                        $dispatch_item->ticket_request_id  = $this->requestedItem[$key];
                    }
                    $dispatch_item->product_id   = $product?->id;
                    $dispatch_item->qty          = $qtyFromRow;       // litres/units taken
                    $dispatch_item->unit_cost   = $unitCost;
                    $dispatch_item->amount       = $amountFromRow;
                    $dispatch_item->currency_id  = $this->company->currency_id;

                    // Link to the source row for later authorization reduction
                    if ($this->department === 'inventory') {
                        $dispatch_item->inventory_id = $item->id;
                    } elseif ($this->department === 'asset') {
                        $dispatch_item->asset_id = $item->id;
                    } elseif ($this->department === 'tyre') {
                        $dispatch_item->tyre_id = $item->id;
                    }

                    $dispatch_item->save();

                    // --- 6) Track totals on this dispatch ---
                    $totalQtyDispatched += $qtyFromRow;
                    $totalLineAmount    += $amountFromRow;
                    $remainingQty       -= $qtyFromRow;
                   
                }

                // If nothing could be dispatched, skip
                if ($totalQtyDispatched <= 0) {
                    continue;
                }

                // Add to overall dispatch total
                 $dispatch_total += $totalLineAmount;

                 return $dispatch_total;
            }
        }
    }


    public function ProductAVCO($dispatch){

        $dispatch_total = 0;

           if ($this->selectedProduct) {

                    foreach ($this->selectedProduct as $key => $productId) {

                        $requestedQty = (int)($this->qty[$key] ?? 0); // requested / intended qty
                        if ($requestedQty < 1) {
                            continue;
                        }

                        $product = Product::find($productId);
                        if (!$product) {
                            continue;
                        }

                        $items = collect();

                        switch ($this->department) {
                            case 'inventory':
                                $items = Inventory::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->where('balance', '>', 0)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;

                            case 'asset':
                                $items = Asset::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->where('balance', '>', 0)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;

                            case 'tyre':
                                $items = Tyre::where('product_id', $product->id)
                                    ->where('status', 1)
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                break;
                        }

                        if ($items->isEmpty()) {
                            continue;
                        }

                        // 1) Work out total available quantity & total cost in company currency
                        $totalQtyAvailable    = 0;
                        $totalCostCompanyCurr = 0.0;

                        foreach ($items as $item) {

                            // how many units does this row represent?
                            if ($this->department === 'inventory') {
                                // assume "balance" is the remaining litres/units
                                $itemQty = (float)$item->balance;
                            } else {
                                // asset / tyre: each row = 1 unit
                                $itemQty = 1.0;
                            }

                            if ($itemQty <= 0) {
                                continue;
                            }

                            // cost of this row in company currency
                            $rowCostCompany = $item->currency_id != $this->company->currency_id
                                ? (float)$item->exchange_amount
                                : (float)$item->total;

                            $totalQtyAvailable    += $itemQty;
                            $totalCostCompanyCurr += $rowCostCompany;
                        }

                        if ($totalQtyAvailable <= 0) {
                            continue;
                        }

                        // if user asks for more than available, either:
                        // - cap it, or
                        // - throw validation error. For now, cap it.
                        $dispatchQty = min($requestedQty, (int)$totalQtyAvailable);

                        // 2) Weighted average unit cost
                        $averageUnitCost = $totalCostCompanyCurr / $totalQtyAvailable;

                        // 3) Line total for this dispatch
                        $lineTotal = $averageUnitCost * $dispatchQty;

                        // 4) Create ONE dispatch line
                        $dispatch_item = new DispatchItem;
                        $dispatch_item->dispatch_id  = $dispatch->id;
                        $dispatch_item->product_id   = $product->id;
                        $dispatch_item->qty          = $dispatchQty;
                        $dispatch_item->unit_cost   = $averageUnitCost;
                        $dispatch_item->amount       = $lineTotal;
                        $dispatch_item->currency_id  = $this->company->currency_id;
                        $dispatch_item->save();

                        // 5) Update dispatch total
                        $dispatch_total += $lineTotal;

                        // ⚠️ NOTE:
                        // You still need separate logic to reduce Inventory/Asset/Tyre balances
                        // in FIFO order or however you decide to consume them physically.
                    }
                }
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
            $base = Dispatch::query()->with(['ticket','horse','vehicle','trailer','employee','department','branch'])
                        ->where('department',$this->department);

            $base->when(filled($this->from) && filled($this->to), function ($q) {
                    $q->whereDate($this->dispatch_filter, '>=', $this->from)
                    ->whereDate($this->dispatch_filter, '<=', $this->to);
                }, function ($q) {
                    $q->whereMonth($this->dispatch_filter, Carbon::now()->month)
                    ->whereYear($this->dispatch_filter, Carbon::now()->year);
                });

               // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('dispatch_number', 'like', $term)
                    ->orWhere('authorization', 'like', $term)
                    ->orWhere('date', 'like', $term)
                    ->orWhereHas('ticket', function ($sub) use ($term) {
                        $sub->where('ticket_number', 'like', $term);
                    })
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('store', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->orWhere('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term)
                        ->where('fleet_number', 'like', $term);
                    })
                    ->orWhereHas('employee', function ($sub) use ($term) {
                        $sub->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                    });
                });
            });

            $dispatches = $base
                ->orderByDesc($this->dispatch_filter)
                ->paginate(10);


       
        return view('livewire.dispatches.index',[
            'dispatches' => $dispatches
        ]);
    }
}
