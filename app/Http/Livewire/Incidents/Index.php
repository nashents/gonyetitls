<?php

namespace App\Http\Livewire\Incidents;

use Livewire\Component;
use App\Models\Incident;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;


    private $incidents;
    public $incident;
    public $incident_id;
    public $status;
    public $comments;



    private function resetInputFields(){
        $this->status = '';
        $this->comments = '';
    }

    public function mount(){
        $this->resetPage();
      
      }


    //   public function showincident($id){
    //     $this->incident_id = $id;
    //     $this->incident = Incident::find($id);
    //     $this->status = $this->incident->status;
    //     $this->dispatchBrowserEvent('show-closeTicketModal');
    // }

    // public function closeincident(){

    //     $incident = Incident::find($this->incident_id);
    //     $incident->status = $this->status;
    //     $incident->update();

    //     $ticket = $incident->ticket;
    //     if (isset($ticket)) {
    //         $ticket->closed_by_id = Auth::user()->id;
    //         $ticket->status = $this->status;
    //         $ticket->closed_comments = $this->comments;
    //         $ticket->update();
    //     }
      

    //     $horse = $incident->horse;
    //     if (isset($horse)) {
    //         $horse->service = 0;
    //         $horse->update();
    //     }

    //     $vehicle = $incident->vehicle;
    //     if (isset($vehicle)) {
    //         $vehicle->service = 0;
    //         $vehicle->update();
    //     }
      
    //     $trailer = $incident->trailer;
    //     if (isset($trailer)) {
    //         $trailer->service = 0;
    //         $trailer->update();
    //     }

    //     $this->dispatchBrowserEvent('hide-closeTicketModal');
    //     $this->resetInputFields();
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'success',
    //         'message'=>"incident Closing Decision Created Successfully!!"
    //     ]);
    // }


    public function dateRange(){
 
        // $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $department_names = $user->employee?->departments?->pluck('name')->toArray() ?? [];
        $role_names       = $user->roles?->pluck('name')->toArray() ?? [];
        $rank_names       = $user->employee?->ranks?->pluck('name')->toArray() ?? [];

        $isAdmin = in_array('Admin', $role_names) || in_array('Super Admin', $role_names);

        $query = Incident::query()
            ->with(['customer', 'trip', 'horse', 'trailer', 'vehicle']);

        // Access control
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Date filter
        if (!empty($this->from) && !empty($this->to)) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        } else {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        // Search filter
        if (!empty($this->search)) {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($q) use ($search) {
                $q->where('incident_number', 'like', $search)
                    ->orWhereHas('horse', function ($q) use ($search) {
                        $q->where('registration_number', 'like', $search)
                        ->orWhere('fleet_number', 'like', $search);
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('ticket_number', 'like', $search);
                    })
                    ->orWhereHas('trip', function ($q) use ($search) {
                        $q->where('inspection_number', 'like', $search);
                    })
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('registration_number', 'like', $search)
                        ->orWhere('fleet_number', 'like', $search);
                    })
                    ->orWhereHas('trailer', function ($q) use ($search) {
                        $q->where('registration_number', 'like', $search)
                        ->orWhere('fleet_number', 'like', $search);
                    });
            });
        }

        $incidents = $query->orderByDesc('incident_number')->paginate(10);

        return view('livewire.incidents.index', [
            'incidents' => $incidents,
            'department_names' => $department_names,
            'role_names' => $role_names,
            'rank_names' => $rank_names,
        ]);

    }
}
