<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\FuelRequest;

class Deleted extends Component
{
    public $fuel_requests;
    public $fuel_request_id;

    public function mount(){
        $this->fuel_requests = FuelRequest::onlyTrashed()->get();
    }
    public function render()
    {
        return view('livewire.fuel-requests.deleted');
    }
}
