<?php

namespace App\Http\Livewire\Corridors;

use App\Models\Corridor;
use Livewire\Component;

class Show extends Component
{
    public $corridor;
     
    public function mount($id){
        $this->corridor = Corridor::find($id);
    }
    public function render()
    {
        return view('livewire.corridors.show');
    }
}
