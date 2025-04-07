<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use Livewire\Component;

class Orders extends Component
{
    public $tyres;

    public $update = NULL;

    public function mount(){
        $this->tyres = Tyre::latest()->get();
    }

    public function render()
    {
        return view('livewire.tyres.orders');
    }
}
