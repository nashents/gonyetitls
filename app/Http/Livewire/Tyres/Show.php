<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use Livewire\Component;

class Show extends Component
{
    public $tyre;

    public function mount($id){
        $this->tyre = Tyre::find($id);
    }
    public function render()
    {
        return view('livewire.tyres.show');
    }
}
