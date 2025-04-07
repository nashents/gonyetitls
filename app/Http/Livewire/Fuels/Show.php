<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use Livewire\Component;

class Show extends Component
{

    public $fuel;

    public function mount($fuel){
        $this->fuel = Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
        ])->find($fuel->id);
    }
    public function render()
    {
        return view('livewire.fuels.show');
    }
}
