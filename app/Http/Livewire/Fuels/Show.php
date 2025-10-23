<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use Livewire\Component;

class Show extends Component
{

    public $fuel;
    public $fuel_id;
    public $trip_id;
    public $trip;
    public $authorize;
    public $comments;


    public function mount($fuel){
        $this->fuel = Fuel::query()->with(['container:id,name','horse','horse.horse_model','horse.horse_make', 'vehicle','vehicle.vehicle_model','vehicle.vehicle_make',
        ])->find($fuel->id);
    }


     public function authorize($id){
        $fuel = Fuel::find($id);
        $this->fuel_id = $fuel->id;
        $this->fuel = $fuel;
        $this->dispatchBrowserEvent('show-fuelAuthorizationModal');
      }

    public function authorizeFuel()
    {
        return redirect()->route('fuels.pending', [
            'fuel_id' => $this->fuel_id,
            'authorize'  => $this->authorize,
            'comments' => $this->comments,
        ]);
    }

    public function render()
    {
        return view('livewire.fuels.show');
    }
}
