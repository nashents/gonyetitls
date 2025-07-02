<?php

namespace App\Http\Livewire\Dispatches;

use Livewire\Component;
use App\Models\Dispatch;

class Items extends Component
{

    public $dispatch;
    public $items;

    public function mount($id){
        $this->dispatch = Dispatch::find($id);
        $this->items = $this->dispatch->dispatch_items;
    }
    public function render()
    {
        return view('livewire.dispatches.items');
    }
}
