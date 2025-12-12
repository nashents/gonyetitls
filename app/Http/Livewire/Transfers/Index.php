<?php

namespace App\Http\Livewire\Transfers;

use Carbon\Carbon;
use App\Models\Tyre;
use App\Models\Store;
use App\Models\Product;
use Livewire\Component;
use App\Models\Transfer;
use App\Models\Inventory;
use App\Models\TransferItem;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $searchInventory;
    protected $queryString = ['search','searchInventory'];
    public $from;
    public $to;
    private $transfers;
    public $old_transfer;
    public $old_transfer_id;
    public $date;
    public $comments;
    public $selectedStore;
    public $to_store_id;
    public $stores;
    public $products;
    public $selectedProduct;
    public $inventories;
    public $selectedInventory;
    public $tyres;
    public $selectedTyre;
    public $qty;
    public $max;
    public $department;

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
    

    public function mount($department){
        $this->department = $department;
        $this->stores = Store::orderBy('name','asc')->where('status',1)->get();
        $this->products = collect();
        $this->tyres = collect();
        $this->inventories = collect();
        $this->resetPage();
        $this->search = "";
        $this->searchInventory = "";
    
    }

       public function transferNumber(){

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

        $transfer = Transfer::latest()->orderBy('id','desc')->first();

        if (!$transfer) {
            $transfer_number =  $initials .'D'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transfer->id + 1;
            $transfer_number =  $initials .'D'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transfer_number;


    }


    public function updatedselectedProduct($id, $key){
        if (!is_null($id) && !is_null($key) ) {
        
            $product = Product::find($id);
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
        
            if ($product && $this->department == "inventory") {
                $this->max[$key] = $product->inventories->where('status',1)->where('balance','>',0)->sum('balance');
            }elseif ($product && $this->department == "tyre") {
                $this->max[$key] = $product->tyres->where('status',1)->sum('balance');
            }


        }
    }

     public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if ($inventory) {
                $this->qty[$key] = $inventory->balance;
                $this->max[$key] = $inventory->balance;
            }
        }
         
    }
  
    public function updatedSelectedTyre($id, $key){
        if (!is_null($id)) {
            $tyre = Tyre::find($id);
            if ($tyre) {
                $this->qty[$key] = $tyre->balance;
                $this->max[$key] = $tyre->balance;
            }
        }
    }

    public function updatedSelectedStore($id){
        if(!is_null($id)){
            $store = Store::find($id);
            $this->loadProducts($store->id);
        }
    }

    public function loadProducts($store_id = null)
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

        $this->products = Product::with(['brand', $relation])
            ->where('status', 1)
            ->where('department', $this->department)
            ->whereHas($relation, function ($q) use ($store_id) {
                $q->where('status', 1)
                ->where('balance', '>', 0)
                ->when(!is_null($store_id), function ($qq) use ($store_id) {
                    $qq->where('store_id', $store_id);
                });
            })
            ->orderBy('name', 'asc')
            ->get();
    }




   
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to_store_id' => 'required',
        'selectedStore' => 'required',
        'date' => 'required',
    ];

    private function resetInputFields(){
        $this->to_store_id = '';
        $this->selectedStore = '';
        $this->date = '';
        $this->comments = '';
    }

    public function store(){

        DB::transaction(function () {

            $transfer = new Transfer;
            $transfer->user_id = Auth::user()->id;
            $transfer->transfer_number = $this->transferNumber();
            $transfer->from = $this->selectedStore;
            $transfer->to = $this->to_store_id;
            $transfer->department = $this->department;
            $transfer->date = $this->date;
            $transfer->comments = $this->comments;
            $transfer->authorization = "pending";
            $transfer->save();

            if (isset($this->selectedInventory)) {
                foreach ($this->selectedInventory as $key => $value) {
                
                    $transfer_item = new TransferItem;
                    $transfer_item->transfer_id = $transfer->id;

                    if (isset($this->selectedProduct[$key])) {
                        $transfer_item->product_id = $this->selectedProduct[$key];
                    }
                    if (isset($this->selectedInventory[$key])) {
                        $transfer_item->inventory_id = $this->selectedInventory[$key];
                    }
                    if (isset($this->selectedTyre[$key])) {
                        $transfer_item->tyre_id = $this->selectedTyre[$key];
                    }
                    if (isset($this->qty[$key])) {
                        $transfer_item->qty = $this->qty[$key];
                    }
                    $transfer_item->save();   
                }
            }

            $this->dispatchBrowserEvent('hide-transferModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item(s) Transfered Successfully!!"
            ]);

        });
    
    }

    public function reverse(){

     
                $transfer = new Transfer;
                $transfer->user_id = Auth::user()->id;
                $transfer->inventory_id = $this->old_transfer->inventory_id;
                $transfer->from = $this->old_transfer->to;
                $transfer->to = $this->old_transfer->from;
                $transfer->date = date('Y-m-d');
                $transfer->comments = 'reversal';
                $transfer->reversal = 1;
                $transfer->save();
        
                $inventory = $this->old_transfer->inventory;
                $inventory->store_id = $this->old_transfer->from;
                $inventory->update();

       

        $this->dispatchBrowserEvent('hide-reverseModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Transfer Reversed Successfully!!"
        ]);
    
    }
   

    public function updatingSearch()
    {
        $this->resetPage();
    }
   
    public function showReverse($id)
    {

        $this->old_transfer = Transfer::find($id);
        $this->old_transfer_id = $id;
        $this->selectedProduct = $this->old_transfer->inventory->id;
        $this->dispatchBrowserEvent('show-reverseModal');

    }
   
    public function updatingSearchInventory()
    {
        $this->resetPage();
    }

    public function render()
    {
      
         $from   = filled($this->from) ? Carbon::parse($this->from)->startOfDay() : null;
            $to     = filled($this->to)   ? Carbon::parse($this->to)->endOfDay()   : null;
            $search = trim((string) ($this->search ?? ''));

            $query = Transfer::query()
                ->with([
                    'inventory.product.brand',
                    'tyre.product.brand',
                ])
                ->when($from && $to, fn (Builder $q) => $q->whereBetween('created_at', [$from, $to]))
                ->when(!($from && $to), fn (Builder $q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                ->when($search !== '', function (Builder $q) use ($search) {
                    // IMPORTANT: group OR conditions so they don't break your date filters
                    $q->where(function (Builder $qq) use ($search) {
                        $like = "%{$search}%";

                        $qq->where('date', 'like', $like)
                        ->orWhere('comments', 'like', $like)

                        ->orWhereHas('inventory', function (Builder $inv) use ($like) {
                            $inv->where(function (Builder $i) use ($like) {
                                $i->where('inventory_number', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('inventory.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('inventory.product.brand', fn (Builder $b) => $b->where('name', 'like', $like))

                        ->orWhereHas('tyre', function (Builder $t) use ($like) {
                            $t->where(function (Builder $tt) use ($like) {
                                $tt->where('tyre_number', 'like', $like)
                                    ->orWhere('width', 'like', $like)
                                    ->orWhere('diameter', 'like', $like)
                                    ->orWhere('aspect_ratio', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('tyre.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('tyre.product.brand', fn (Builder $b) => $b->where('name', 'like', $like));
                    });
                })
                ->orderByDesc('created_at');

            return view('livewire.transfers.index', [
                'transfers' => $query->paginate(10),
            ]);
       
    }
}
