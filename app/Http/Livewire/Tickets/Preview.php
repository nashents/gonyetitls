<?php

namespace App\Http\Livewire\Tickets;

use Livewire\Component;

class Preview extends Component
{

    public $ticket;
    public $company;


    public function mount($company, $ticket){
        $this->ticket = $ticket;
        $this->company = $company;
    }

    public function render()
    {
        return view('livewire.tickets.preview');
    }
}
