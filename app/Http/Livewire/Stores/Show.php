<?php

namespace App\Http\Livewire\Stores;

use App\Models\Tyre;
use App\Models\Store;
use Livewire\Component;
use App\Models\Inventory;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $store;
    public $store_id;
    private $inventories;
    public $assets;
    private $tyres;

    public function mount($id){
        $this->store_id = $id;
        $this->store = Store::find($id);
    }

    public function render()
    {
        return view('livewire.stores.show',[
            'inventories' => Inventory::where('store_id',$this->store_id)->where('disposed',0)->where('status',1)->paginate(10),
            'tyres' => Tyre::where('store_id',$this->store_id)->where('disposed',0)->where('status',1)->paginate(10)
        ]);
    }
}
