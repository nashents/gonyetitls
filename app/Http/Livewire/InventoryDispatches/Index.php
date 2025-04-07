<?php

namespace App\Http\Livewire\InventoryDispatches;

use Livewire\Component;
use App\Models\InventoryDispatch;

class Index extends Component
{

    public $inventory_dispatches;

    public function mount(){
        $this->inventory_dispatches = InventoryDispatch::all();
    }

    public function render()
    {
        return view('livewire.inventory-dispatches.index');
    }
}
