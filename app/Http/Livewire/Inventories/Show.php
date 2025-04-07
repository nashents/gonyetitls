<?php

namespace App\Http\Livewire\Inventories;

use Livewire\Component;
use App\Models\Inventory;

class Show extends Component
{
    public $inventories;
    public $inventory_id;
    public $inventory;

    public function mount($id){
    $this->inventory = Inventory::find($id);
    }

    public function render()
    {
        return view('livewire.inventories.show');
    }
}
