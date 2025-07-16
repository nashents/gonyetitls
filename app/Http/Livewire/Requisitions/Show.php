<?php

namespace App\Http\Livewire\Requisitions;

use Livewire\Component;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{

    public $requisition;
    public $company;

    public function mount($id){
        $this->requisition = Requisition::find($id);
        $this->company = Auth::user()->employee->company;
    }

    public function render()
    {
        return view('livewire.requisitions.show');
    }
}
