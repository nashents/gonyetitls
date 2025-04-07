<?php

namespace App\Http\Livewire\OffloadingPoints;

use Livewire\Component;
use App\Models\OffloadingPoint;

class Show extends Component
{

    public $offloading_points;
    public $offloading_point;
    public $offloading_point_id;

    public function mount($id){
        $this->offloading_point = OffloadingPoint::find($id);
    }
    
    public function render()
    {
        return view('livewire.offloading-points.show');
    }
}
