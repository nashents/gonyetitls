<?php

namespace App\Http\Livewire\Transfers;

use App\Models\Store;
use Livewire\Component;
use App\Models\Transfer;
use App\Models\Inventory;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

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
    public $inventories;
    public $inventory_id;
    

    public function mount(){
        $this->stores = Store::orderBy('name','asc')->where('status',1)->get();
        $this->inventories = collect();
        $this->resetPage();
        $this->search = "";
        $this->searchInventory = "";
    
    }


    public function updatedSelectedStore($id){

        if (!is_null($id)) {
             $this->inventories = Inventory::with('product')->where('store_id',$id)->where('status',1)->where('disposed',0)->get()->sortBy('product.name');
        }

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

        if (isset($this->inventory_id)) {
            foreach ($this->inventory_id as $key => $value) {
                $transfer = new Transfer;
                $transfer->user_id = Auth::user()->id;
                if (isset($this->inventory_id[$key])) {
                    $transfer->inventory_id = $this->inventory_id[$key];
                }
                $transfer->from = $this->selectedStore;
                $transfer->to = $this->to_store_id;
                $transfer->date = $this->date;
                $transfer->comments = $this->comments;
                $transfer->save();
        
                if (isset($this->inventory_id[$key])) {
                    $inventory = Inventory::find($this->inventory_id[$key]);
                    $inventory->store_id = $this->to;
                    $inventory->update();
                }

               
            }
        }

        $this->dispatchBrowserEvent('hide-transferModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Transfered Successfully!!"
        ]);
    
    }

    public function reverse(){

     
                $transfer = new Transfer;
                $transfer->user_id = Auth::user()->id;
                $transfer->inventory_id = $this->old_transfer->inventory_id;
                $transfer->from = $this->old_transfer->to;
                $transfer->to = $this->old_transfer->from;
                $transfer->date = date();
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
        $this->inventory_id = $this->old_transfer->inventory->id;
        $this->dispatchBrowserEvent('show-reverseModal');

    }
   
    public function updatingSearchInventory()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (isset($this->searchInventory) && isset($this->selectedStore)) {
            $this->inventories = Inventory::query()->with('product')->where('store_id', $this->selectedStore)->where('inventory_number', 'like', '%'.$this->searchInventory.'%')
            ->orWhere('serial_number', 'like', '%'.$this->searchInventory.'%')
            ->orWhereHas('product', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchInventory.'%');
            })
            ->orWhereHas('product', function ($query) {
                return $query->where('identification_number', 'like', '%'.$this->searchInventory.'%');
            })
            ->orWhereHas('product.brand', function ($query) {
                return $query->where('name', 'like', '%'.$this->searchInventory.'%');
            })->get()->sortBy('product.name');
        }

        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return view('livewire.transfers.index',[
                    'transfers' => Transfer::whereBetween('created_at',[$this->from, $this->to])
                    ->where('date', 'like', '%'.$this->search.'%')
                    ->orWhere('comments', 'like', '%'.$this->search.'%')
                    ->orWhereHas('inventory', function ($query) {
                        return $query->where('inventory_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inventory', function ($query) {
                        return $query->where('serial_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inventory.product', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inventory.product.brand', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy('created_at','desc')->paginate(10)
                ]);
            }else{
                return view('livewire.transfers.index',[
                    'transfers' => Transfer::whereBetween('created_at',[$this->from, $this->to])->orderBy('created_at','desc')->paginate(10)
                ]);
            }
           
        } elseif (isset($this->search)) {
            return view('livewire.transfers.index',[
                'transfers' => Transfer::where('date', 'like', '%'.$this->search.'%')
                ->orWhere('comments', 'like', '%'.$this->search.'%')
                ->orWhereHas('inventory', function ($query) {
                    return $query->where('inventory_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('inventory', function ($query) {
                    return $query->where('serial_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('inventory.product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('inventory.product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('tyre_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('width', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('diameter', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('aspect_ratio', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre', function ($query) {
                    return $query->where('serial_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre.product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('tyre.product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('created_at','desc')->paginate(10)
            ]);
        }
        else{
            return view('livewire.transfers.index',[
                'transfers' => Transfer::whereMonth('created_at',date('m'))->whereYear('created_at',date('Y'))->orderBy('created_at','desc')->paginate(10)
            ]);
        }
       
    }
}
