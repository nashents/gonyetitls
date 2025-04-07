<?php

namespace App\Http\Livewire\Transporters;

use App\Models\Driver;
use Livewire\Component;
use App\Models\Transporter;

class Drivers extends Component
{

    public $drivers;
    public $transporter;
    public $transporter_id;

    public function mount($id){
        $this->transporter_id = $id;
        $this->transporter = Transporter::find($id);
        $this->drivers = $this->transporter->drivers;
    }

    public function render()
    {
        return view('livewire.transporters.drivers');
    }
}
