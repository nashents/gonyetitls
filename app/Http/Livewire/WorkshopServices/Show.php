<?php

namespace App\Http\Livewire\WorkshopServices;

use Livewire\Component;
use App\Models\WorkshopService;

class Show extends Component
{

    public $workshop_service;

    public function mount($id){
        $this->workshop_service = WorkshopService::find($id);
    }


    public function render()
    {
        return view('livewire.workshop-services.show');
    }
}
