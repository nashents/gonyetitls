<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Trip;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DriverTrips extends Component
{

    public $trips;

    public function mount(){
        $this->trips = Trip::where('driver_id', Auth::user()->driver->id)->whereYear('created_at',date('Y'))->get();
    }


    public function render()
    {
        return view('livewire.dashboard.driver-trips');
    }
}
