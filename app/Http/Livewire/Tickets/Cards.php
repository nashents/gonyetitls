<?php

namespace App\Http\Livewire\Tickets;

use Carbon\Carbon;
use App\Models\Ticket;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Cards extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $tickets;
    public $ticket_id;
    public $mechanic_id;
    public $mechanic_ids;
    public $mechanics;
    public $ticket_status = "all";

    public function mount($id){
        $this->mechanic_id = $id;
        $this->resetPage();
    }


    public function render()
    {

        $query = Ticket::query()
            ->with(['booking','inspection','horse','trailer','vehicle'])
            ->whereHas('employees', function ($q) {
                $q->where('employees.id', $this->mechanic_id);   // 👈 Only tickets for this employee
        });

        // ✅ Status filter
        if ($this->ticket_status !== "all") {
            $query->where('status', $this->ticket_status);
        }

          if (!empty($this->from) && !empty($this->to)) {
            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();

            $query->whereBetween('created_at', [$from, $to]);
        } else {
            $query->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }

        // ✅ Search filter
        if (filled($this->search)) {

            $query->where(function($q) {
                $q->where('ticket_number','like', '%'.$this->search.'%')
                ->orWhereHas('horse', function ($qq) {
                    $qq->where('registration_number','like','%'.$this->search.'%')
                        ->orWhere('fleet_number','like','%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($qq) {
                    $qq->where('registration_number','like','%'.$this->search.'%')
                        ->orWhere('fleet_number','like','%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($qq) {
                    $qq->where('registration_number','like','%'.$this->search.'%')
                        ->orWhere('fleet_number','like','%'.$this->search.'%');
                })
                ->orWhereHas('inspection', function ($qq) {
                    $qq->where('inspection_number','like','%'.$this->search.'%');
                });
            });
            
        }

        // ✅ Final result with pagination
        $tickets = $query->orderBy('ticket_number','desc')->paginate(10);

        return view('livewire.tickets.cards', [
            'tickets' => $tickets
        ]);
    }
}
