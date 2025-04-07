<?php

namespace App\Http\Livewire\Transporters;

use App\Models\Horse;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Transporter;

class Horses extends Component
{

    public $horses;
    public $transporter;
    public $transporter_id;
    public $currencies;

    public function mount($id){
        $this->transporter_id = $id;
        $this->transporter = Transporter::find($id);
        $this->currencies = Currency::all();
        $this->horses = $this->transporter->horses;
    }

    public function render()
    {
        return view('livewire.transporters.horses');
    }
}
