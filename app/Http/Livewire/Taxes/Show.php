<?php

namespace App\Http\Livewire\Taxes;

use App\Models\Tax;
use Livewire\Component;

class Show extends Component
{

    public $tax;

    public function mount($id){
        $this->tax = Tax::find($id);
    }

    public function render()
    {
        return view('livewire.taxes.show');
    }
}
