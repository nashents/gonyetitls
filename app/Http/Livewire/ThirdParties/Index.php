<?php

namespace App\Http\Livewire\ThirdParties;

use App\Models\Trip;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $trips;

    public function mount(){
        if (Auth::user()->customer) {
            $this->trips = Trip::where('customer_id',Auth::user()->customer->id)->get();
        }elseif (Auth::user()->transporter) {
            $this->trips = Trip::where('transporter_id',Auth::user()->transporter->id)->get();
        }elseif (Auth::user()->agent) {
            $this->trips = Trip::where('agent_id',Auth::user()->agent->id)->get();
        }elseif (Auth::user()->broker) {
            $this->trips = Trip::where('broker_id',Auth::user()->broker->id)->get();
        }
     
    }
    public function render()
    {
        return view('livewire.third-parties.index');
    }
}
