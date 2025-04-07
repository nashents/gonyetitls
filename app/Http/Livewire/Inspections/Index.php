<?php

namespace App\Http\Livewire\Inspections;

use Livewire\Component;
use App\Models\Inspection;

class Index extends Component
{
    public $inspection_results;
    public $inspections;
    public $inspection_id;
    public $bookings;
    public $booking_id;


    public function mount(){
        $this->inspections = Inspection::latest()->get();
    }

    public function render()
    {
        return view('livewire.inspections.index');
    }
}
