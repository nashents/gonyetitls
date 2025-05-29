<?php

namespace App\Http\Livewire\GoodsReceiveds;

use Livewire\Component;
use App\Models\GoodsReceived;

class Show extends Component
{
    public $goods_received;
    public $inventories;

    public function mount($id){
        $this->goods_received = GoodsReceived::find($id);
        $this->inventories = $this->goods_received->inventories;
    }
    public function render()
    {
        return view('livewire.goods-receiveds.show');
    }
}
