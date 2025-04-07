<?php

namespace App\Http\Livewire\Stores;

use App\Models\Store;
use Livewire\Component;
use App\Models\Transfer;
use App\Models\Inventory;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Inventories extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $stores;
    public $store;
    public $store_id;
    private $inventories;
    public $from;
    public $to;
    public $date;
    public $comments;

    public function mount($id){
        $this->store_id = $id;
        $this->store = Store::find($id);
        $this->stores = Store::orderBy('name','asc')->get();
    }

    public function showTransfer($id){

        $this->inventory_id = $id;
        $this->inventory = Inventory::find($id);
        $this->dispatchBrowserEvent('show-transferModal');

        
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to' => 'required',
        'date' => 'required',
    ];

    private function resetInputFields(){
        $this->to = '';
        $this->date = '';
        $this->comments = '';
    }

    public function transfer(){

        $transfer = new Transfer;
        $transfer->user_id = Auth::user()->id;
        $transfer->inventory_id = $this->inventory_id;
        $transfer->from = $this->store_id;
        $transfer->to = $this->to;
        $transfer->date = $this->date;
        $transfer->comments = $this->comments;
        $transfer->save();

        $inventory = Inventory::find($this->inventory_id);
        $inventory->store_id = $this->to;
        $inventory->update();

        $this->dispatchBrowserEvent('hide-transferModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Transfered Successfully!!"
        ]);
        return redirect()->route('transfers.index');
    }

    public function render()
    {
        return view('livewire.stores.inventories',[
            'inventories' => Inventory::where('store_id',$this->store_id)->where('disposed',0)->where('status',1)->paginate(10),
        ]);
    }
}
