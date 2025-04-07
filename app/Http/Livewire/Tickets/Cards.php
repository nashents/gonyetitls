<?php

namespace App\Http\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class Cards extends Component
{

    public $tickets;
    public $ticket_id;
    public $mechanic_id;
    public $mechanic_ids;
    public $mechanics;

    public function mount($id){
        $this->mechanic_id = $id;
        $this->tickets = Employee::find($id)->tickets;

    }
    public function render()
    {

        $this->tickets = Employee::find($this->mechanic_id)->tickets;

        return view('livewire.tickets.cards',
        [
            'tickets' => $this->tickets
        ]
    );
    }
}
