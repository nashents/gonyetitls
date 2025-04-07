<?php

namespace App\Http\Livewire\Bookings;

use App\Models\Booking;
use Livewire\Component;

class Show extends Component
{
    public $booking;
    public $bookings;
    public $booking_id;

    public function mount($id){
        $this->booking = Booking::find($id);
    }
    public function render()
    {
        return view('livewire.bookings.show');
    }
}
