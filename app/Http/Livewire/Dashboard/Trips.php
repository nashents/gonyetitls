<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Trip;
use Livewire\Component;

class Trips extends Component
{
    public $trips;

    public function mount(){
        $this->trips = Trip::whereYear('created_at',date('Y'))->orderBy('created_at','desc')->take(5)->get();
    }

    public function render()
    {
        return view('livewire.dashboard.trips');
    }
}
