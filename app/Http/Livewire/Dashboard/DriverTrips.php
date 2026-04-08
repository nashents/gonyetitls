<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Trip;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DriverTrips extends Component
{

    public $trips;

    public function mount(){
        $this->trips = Trip::where('driver_id', Auth::user()->driver->id)->whereYear('start_date',date('Y'))->orderBy('start_date','desc')->get();
    }


    public function render()
    {
        return view('livewire.dashboard.driver-trips');
    }
}
