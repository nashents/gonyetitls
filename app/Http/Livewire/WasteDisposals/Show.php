<?php

namespace App\Http\Livewire\WasteDisposals;


use App\Models\WasteDisposal;
use Livewire\Component;

class Show extends Component
{
    public $waste_disposal;
    public $waste_disposal_items;

    public function mount($id){
        $this->waste_disposal = WasteDisposal::find($id);
        $this->waste_disposal_items = $this->waste_disposal->waste_disposal_items;
    }
    public function render()
    {
        return view('livewire.waste-disposals.show');
    }
}
