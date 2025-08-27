<?php

namespace App\Http\Livewire\Tickets;

use App\Models\User;
use Livewire\Component;

class Preview extends Component
{

    public $ticket;
    public $company;
    public $authorizer;


    public function mount($company, $ticket){
        $this->ticket = $ticket;
        $booking  = $ticket->booking;
        $this->company = $company;
        $this->authorizer = User::find($booking->authorized_by_id);
    }

    public function render()
    {
        return view('livewire.tickets.preview');
    }
}
