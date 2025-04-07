<?php

namespace App\Http\Livewire\LoadingPoints;

use Livewire\Component;
use App\Models\LoadingPoint;

class Show extends Component
{
    public $loading_points;
    public $loading_point;
    public $loading_point_id;

    public function mount($id){
        $this->loading_point = LoadingPoint::find($id);
    }
    public function render()
    {
        return view('livewire.loading-points.show');
    }
}
