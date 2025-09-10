<?php

namespace App\Http\Livewire\Drivers;

use Livewire\Component;

class Inspections extends Component
{
    public $checklists;
    public $driver;

    public function mount($driver){
        $this->driver = $driver;
        $this->checklists = $this->driver->checklists;
    }

    public function render()
    {
        return view('livewire.drivers.inspections');
    }
}
