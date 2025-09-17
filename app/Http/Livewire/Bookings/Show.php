<?php

namespace App\Http\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Station;
use Livewire\Component;

class Show extends Component
{
    public $booking;
    public $bookings;
    public $booking_id;
    public $work_station;

    public function mount($id){
        $this->booking = Booking::find($id);
        $this->work_station = Station::find($this->booking->station_id);
    }
    public function render()
    {
        return view('livewire.bookings.show');
    }
}
