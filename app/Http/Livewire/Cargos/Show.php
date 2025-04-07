<?php

namespace App\Http\Livewire\Cargos;

use App\Models\Cargo;
use Livewire\Component;

class Show extends Component
{
    public $transporters;
    public $transporter_id;
    public $cargo_transporters;
    public $cargo_transporter_id;
    public $cargos;
    public $cargo;
    public $cargo_id;

    public function mount($id){
        $this->cargo = Cargo::find($id);
        $this->cargo_transporters = $this->cargo->transporters;
    }
    public function render()
    {
        $this->cargo_transporters = $this->cargo->transporters;
        return view('livewire.cargos.show',[
            'cargo_transporters' => $this->cargo_transporters
        ]);
    }
}
