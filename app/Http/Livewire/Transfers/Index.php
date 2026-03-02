<?php

namespace App\Http\Livewire\Transfers;

use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Tyre;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public $searchInventory;
    public $searchProducts;
    public $searchTyre;
    public $searchAsset;
    protected $queryString = ['search','searchInventory','searchInventory','searchAsset','searchProducts'];
    public $from;
    public $to;
    private $transfers;
    public $old_transfer;
    public $old_transfer_id;
    public $transfer_item;
    public $date;
    public $comments;
    public $notes;
    public $selectedStore;
    public $to_store_id;
    public $stores;
    public $products; 
    public $transfer_filter = "created_at"; 
    public $inventories;
    public $assets;
    public $tyres;
    public $selectedInventory = [];
    public $selectedTyre = [];
    public $selectedAsset = [];
    public $selectedProduct = [];
    public $qty = [];
   
    public $current_selectedInventory = [];
    public $current_selectedTyre = [];
    public $current_selectedAsset = [];
    public $current_selectedProduct = [];
    public $current_qty = [];
    public $max;
    public $department;
    public $transfer_id;
    public $transfer_items = [];
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
    

    public function mount($department){
       
        $this->department = $department;
        $this->stores = Store::orderBy('name','asc')->where('status',1)->get();
        $this->products = collect();
        $this->tyres = collect();
        $this->inventories = collect();
        $this->assets = collect();
        $this->resetPage();
        $this->reset(['search','searchInventory','searchAsset','searchTyre','searchProducts']);
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
        
            if ($this->expand == False) {
                  $this->qty[$key] = 1;
            }
            $product = Product::find($id);
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->assets = Asset::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
            $this->tyres = Tyre::where('product_id',$id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
        
            if ($product && $this->department == "inventory") {
                $this->max[$key] = $product->inventories->where('status',1)->where('balance','>',0)->sum('balance');
            }
            elseif ($product && $this->department == "tyre") {
                $this->max[$key] = $product->tyres->where('status',1)->sum('balance');
            }
            elseif ($product && $this->department == "asset") {
                $this->max[$key] = $product->assets->where('status',1)->sum('balance');
            }


        }
    }

     public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if ($inventory && $this->expand == True) {
                $this->qty[$key] = $inventory->balance;
                $this->max[$key] = $inventory->balance;
            }
        }
         
    }
    
    public function updatedSelectedAsset($id, $key){
        if (!is_null($id)) {
            $asset = Asset::find($id);
            if ($asset && $this->expand == True) {
                $this->qty[$key] = $asset->balance;
                $this->max[$key] = $asset->balance;
            }
        }
         
    }
  
    public function updatedSelectedTyre($id, $key){
        if (!is_null($id)) {
            $tyre = Tyre::find($id);
            if ($tyre && $this->expand == True) {
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
            ->where('department', $this->department)
            ->where('status', 1)
            ->whereHas($relation, function ($q) use ($store_id) {
                $q->where('status', 1)
                ->where('balance', '>', 0)
                ->when(! is_null($store_id), fn($qq) => $qq->where('store_id', $store_id));
            })
            ->when($this->searchProducts, function ($q) {
                $search = $this->searchProducts;
                $q->where(function ($inner) use ($search) {
                    $inner->where('product_number', 'like', '%'.$search.'%')
                        ->orWhere('identification_number', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhereHas('brand', fn($bq) => $bq->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('name', 'asc')
            ->get();
    }

    public function updatedSearchProducts($value)
    {
        $this->loadProducts($this->selectedStore ?? null);
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
        $this->to_store_id = Null;
        $this->selectedStore = Null;
        $this->date = '';
        $this->comments = '';
        $this->notes = '';
        $this->inputs = [];
        $this->expand = false;
        $this->transfer_items = [];
        $this->selectedAsset = [];
        $this->selectedProduct = [];
        $this->selectedTyre = [];
        $this->selectedInventory = [];
        $this->qty = [];
        $this->current_selectedAsset = [];
        $this->current_selectedProduct = [];
        $this->current_selectedTyre = [];
        $this->current_selectedInventory = [];
        $this->current_qty = [];
        $this->reset(['search','searchInventory','searchAsset','searchTyre','searchProducts']);
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
            $transfer->expand = $this->expand;
            $transfer->comments = $this->comments;
            $transfer->notes = $this->notes;
            $transfer->authorization = "pending";
            $transfer->save();

            if (isset($this->selectedProduct)) {
                foreach ($this->selectedProduct as $key => $productId) {
                
                    $transfer_item = new TransferItem;
                    $transfer_item->transfer_id = $transfer->id;

                    $transfer_item->product_id = $productId;
                    if (isset($this->selectedInventory[$key])) {
                        $transfer_item->inventory_id = $this->selectedInventory[$key];
                    }
                    if (isset($this->selectedTyre[$key])) {
                        $transfer_item->tyre_id = $this->selectedTyre[$key];
                    }
                    if (isset($this->selectedAsset[$key])) {
                        $transfer_item->asset_id = $this->selectedAsset[$key];
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
                'message'=>"Transfer Record Created Successfully!!"
            ]);

        });
    
    }

    public function delete($id){
        $this->transfer_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }
   
    public function destroy(){
        $transfer = Transfer::find($this->transfer_id);
        $transfer->transfer_items()?->delete();
        $transfer?->delete();
        $this->dispatchBrowserEvent('hide-deleteModal');
      
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transfer Record Deleted Successfully!!"
        ]);
    }

    public function edit($id){
        $this->transfer_id = $id;
        $transfer = Transfer::find($id);
        $this->selectedStore = $transfer->from;
        $this->to_store_id = $transfer->to;
        $this->date = $transfer->date;
        $this->comments = $transfer->comments;
        $this->expand = $transfer->expand;
        $this->notes = $transfer->notes;
        $this->transfer_items = $transfer->transfer_items;
        $this->loadProducts($this->selectedStore);
        if ($this->transfer_items) {
            foreach($this->transfer_items as $key => $item){
                $this->current_selectedProduct[] = $item->product_id;
                $this->current_selectedInventory[] = $item->inventory_id;
                $this->current_selectedTyre[] = $item->tyre_id;
                $this->current_selectedAsset[] = $item->asset_id;
                $this->current_qty[] = $item->qty;
                $this->inventories = Inventory::where('product_id',$item->product_id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
                $this->assets = Asset::where('product_id',$item->product_id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
                $this->tyres = Tyre::where('product_id',$item->product_id)->where('status',1)->where('balance','>',0)->orderBy('created_at','asc')->get();
                $product = Product::find($item->product_id);
                    if ($product && $this->department == "inventory") {
                        $this->max[$key] = $product->inventories->where('status',1)->where('balance','>',0)->sum('balance');
                    }
                    elseif ($product && $this->department == "tyre") {
                        $this->max[$key] = $product->tyres->where('status',1)->sum('balance');
                    }
                    elseif ($product && $this->department == "asset") {
                        $this->max[$key] = $product->assets->where('status',1)->sum('balance');
                    }
            }
        }

        
        $this->dispatchBrowserEvent('show-editModal');
    }
   
        public function removeShow($id){
        $this->transfer_item = TransferItem::find($id);
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeItem(){ 

  
        $this->transfer_item->delete();
      
        $this->resetInputFields();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);
       
    }
  
    public function update(){

        DB::transaction(function () {

            $transfer =  Transfer::find($this->transfer_id);
            $transfer->from = $this->selectedStore;
            $transfer->to = $this->to_store_id;
            $transfer->department = $this->department;
            $transfer->date = $this->date;
            $transfer->comments = $this->comments;
            $transfer->expand = $this->expand;
            $transfer->notes = $this->notes;
            $transfer->authorization = "pending";
            $transfer->update();

            if (isset($this->selectedProduct)) {
                foreach ($this->selectedProduct as $key => $productId) {
                
                    $transfer_item = new TransferItem;
                    $transfer_item->transfer_id = $transfer->id;
                    $transfer_item->product_id = $productId;

                    if (isset($this->selectedInventory[$key])) {
                        $transfer_item->inventory_id = $this->selectedInventory[$key];
                    }
                    if (isset($this->selectedTyre[$key])) {
                        $transfer_item->tyre_id = $this->selectedTyre[$key];
                    }
                    if (isset($this->selectedAsset[$key])) {
                        $transfer_item->asset_id = $this->selectedAsset[$key];
                    }
                    if (isset($this->qty[$key])) {
                        $transfer_item->qty = $this->qty[$key];
                    }
                    $transfer_item->save();   
                }
            }
           
            if (isset($this->transfer_items)) {
                foreach ($this->transfer_items as $key => $item) {
                
                    $transfer_item = TransferItem::find($item->id);
                    $transfer_item->transfer_id = $transfer->id;

                    if (isset($this->current_selectedProduct[$key])) {
                        $transfer_item->product_id = $this->current_selectedProduct[$key];
                    }
                    if (isset($this->current_selectedInventory[$key])) {
                        $transfer_item->inventory_id = $this->current_selectedInventory[$key];
                    }
                    if (isset($this->current_selectedTyre[$key])) {
                        $transfer_item->tyre_id = $this->current_selectedTyre[$key];
                    }
                    if (isset($this->current_selectedAsset[$key])) {
                        $transfer_item->asset_id = $this->current_selectedAsset[$key];
                    }
                    if (isset($this->current_qty[$key])) {
                        $transfer_item->qty = $this->current_qty[$key];
                    }
                    $transfer_item->update();   
                }
            }

            $this->dispatchBrowserEvent('hide-editModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transfer Record Updated Successfully!!"
            ]);

        });
    
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
   
    
    public function updatingSearchInventory()
    {
        $this->resetPage();
    }
    public function updatingSearchTyre()
    {
        $this->resetPage();
    }
    public function updatingSearchAsset()
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
                    'asset.product.brand',
                ])
                ->where('department',$this->department)
                ->when($from && $to, fn (Builder $q) => $q->whereBetween($this->transfer_filter, [$from, $to]))
                ->when(!($from && $to), fn (Builder $q) => $q->whereMonth($this->transfer_filter, now()->month)->whereYear($this->transfer_filter, now()->year))
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
                        ->orWhereHas('asset', function (Builder $inv) use ($like) {
                            $inv->where(function (Builder $i) use ($like) {
                                $i->where('asset_number', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })
                        ->orWhereHas('tyre', function (Builder $t) use ($like) {
                            $t->where(function (Builder $tt) use ($like) {
                                $tt->where('tyre_number', 'like', $like)
                                    ->orWhere('width', 'like', $like)
                                    ->orWhere('diameter', 'like', $like)
                                    ->orWhere('aspect_ratio', 'like', $like)
                                    ->orWhere('serial_number', 'like', $like);
                            });
                        })

                        ->orWhereHas('inventory.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('inventory.product.brand', fn (Builder $b) => $b->where('name', 'like', $like))
                        ->orWhereHas('asset.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('asset.product.brand', fn (Builder $b) => $b->where('name', 'like', $like))
                        ->orWhereHas('tyre.product', fn (Builder $p) => $p->where('name', 'like', $like))
                        ->orWhereHas('tyre.product.brand', fn (Builder $b) => $b->where('name', 'like', $like));
                    });
                })
                ->orderByDesc($this->transfer_filter);

            return view('livewire.transfers.index', [
                'transfers' => $query->paginate(10),
            ]);
       
    }
}
