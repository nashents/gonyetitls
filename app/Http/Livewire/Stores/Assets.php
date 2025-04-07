<?php

namespace App\Http\Livewire\Stores;

use App\Models\Asset;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class Assets extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $store;
    public $store_id;
    private $assets;

    public function mount($id){
        $this->store_id = $id;
        $this->store = Store::find($id);
    }

    public function render()
    {
        return view('livewire.stores.assets',[
            'assets' => Asset::where('store_id',$this->store_id)->where('disposed',0)->where('status',1)->paginate(10),
        ]);
    }
}
