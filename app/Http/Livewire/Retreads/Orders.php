<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Retread;
use Livewire\Component;

class Orders extends Component
{

    public $retreads;
    public $retread_id;

    public function mount(){
        $this->retreads = Retread::latest()->get();
    }
    public function render()
    {
        return view('livewire.retreads.orders');
    }
}
