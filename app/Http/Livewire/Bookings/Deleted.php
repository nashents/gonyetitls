<?php

namespace App\Http\Livewire\Bookings;

use App\Models\Booking;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{
    public $bookings;
    public $booking_id;

    public function mount(){
        $this->bookings = Booking::onlyTrashed()->latest()->get();
    }

    public function restore($id){
        $this->booking_id = $id;
        $this->dispatchBrowserEvent('show-bookingRestoreModal');
    }
    public function update(){
        Booking::withTrashed()->find($this->booking_id)->restore();
        Session::flash('success','Booking Restored Successfully');
        $this->dispatchBrowserEvent('hide-bookingRestoreModal');
        return redirect()->route('bookings.deleted');
    }
    public function render()
    {
        return view('livewire.bookings.deleted');
    }
}
