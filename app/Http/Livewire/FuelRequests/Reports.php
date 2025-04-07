<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\FuelRequest;

class Reports extends Component
{
    public $search = NULL;
    public $to;
    public $from;
    public $fuel_requests;

    public function search(){

        $this->search = TRUE;
        $this->fuel_requests = FuelRequest::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.fuel-requests.reports');
    }
}
