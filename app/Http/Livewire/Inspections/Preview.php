<?php

namespace App\Http\Livewire\Inspections;

use Livewire\Component;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{
    public $inspection;
    public $company;
    public $inspection_results;
    public $booking;
    public $ticket;
    public $service_type;


    public function mount($inspection){
        
        $this->inspection = $inspection;
        $this->ticket = $inspection->ticket;
        $this->booking = $this->ticket?->booking;
        $this->service_type  = $this->booking?->service_type;
        $this->inspection_results = $inspection->inspection_results;
        $this->company = Auth::user()->employee->company;

    }
    public function render()
    {
        return view('livewire.inspections.preview');
    }
}
