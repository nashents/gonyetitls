<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use Livewire\Component;
use App\Models\TyreAssignment;

class Show extends Component
{
    public $tyre;
    public $tyre_assignments;

    public function mount($id){
        $this->tyre = Tyre::find($id);
        $this->tyre_assignments = TyreAssignment::query()->where('tyre_id',$id)->orderBy('created_at','desc')->get();
    }
    public function render()
    {
        return view('livewire.tyres.show');
    }
}
