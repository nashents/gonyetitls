<?php

namespace App\Http\Livewire\Retreads;

use App\Models\Retread;
use Livewire\Component;

class Show extends Component
{

    public $retread;

    public function mount($id){
        $this->retread = Retread::find($id);
    }

    public function render()
    {
        return view('livewire.retreads.show');
    }
}
