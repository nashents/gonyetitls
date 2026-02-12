<?php

namespace App\Http\Livewire\WasteCollections;

use App\Models\WasteCollection;
use Livewire\Component;

class Show extends Component
{

    public $waste_collection;
    public $waste_collection_items;

    public function mount($id){
        $this->waste_collection = WasteCollection::find($id);
        $this->waste_collection_items = $this->waste_collection->waste_collection_items;
    }

    public function render()
    {
        return view('livewire.waste-collections.show');
    }
}
