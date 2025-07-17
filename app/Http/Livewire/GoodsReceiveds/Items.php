<?php

namespace App\Http\Livewire\GoodsReceiveds;

use Livewire\Component;
use App\Models\GoodsReceived;

class Items extends Component
{
    public $items;
    public $goods_received;
    public $goods_received_id;
    
    public function mount($id){
        $this->goods_received_id = $id;
        $this->goods_received = GoodsReceived::find($id);

        if ($this->goods_received->inventories->isNotEmpty()) {
            $this->items = $this->goods_received->inventories;
        }elseif ($this->goods_received->assets->isNotEmpty()) {
           $this->items = $this->goods_received->assets;
        }elseif ($this->goods_received->tyres->isNotEmpty()) {
            $this->items = $this->goods_received->tyres;
        }
    }

    public function render()
    {
        return view('livewire.goods-receiveds.items');
    }
}
