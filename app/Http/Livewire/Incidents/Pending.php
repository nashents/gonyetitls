<?php

namespace App\Http\Livewire\Incidents;

use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $incident_filter;
    private $incidents;
    public $incident_id;
    public $authorize;
    public $comments;
    public $incident;
 


    public function authorize($id){
        $incident = Incident::find($id);
        $this->incident_id = $incident->id;
        $this->incident = $incident;
        $this->dispatchBrowserEvent('show-authorizationModal');
    }

      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

          return DB::transaction(function () {

            $incident = Incident::find($this->incident_id);
            $incident->authorized_by_id = Auth::user()->id;
            $incident->authorization = $this->authorize;
            $incident->authorization_date = now();
            $incident->comments = $this->comments;
            $incident->update();

            if ($this->authorize == "approved") {

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Incident Approved Successfully"
                ]);
                return $this->redirectOrStay('incidents.approved');

            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Incident Rejected Successfully"
                ]);
                return $this->redirectOrStay('incidents.rejected');
            }

    });

      }

    public function render()
    {
        $user = Auth::user();

        $department_names = $user->employee?->departments?->pluck('name')->toArray() ?? [];
        $role_names       = $user->roles?->pluck('name')->toArray() ?? [];
        $rank_names       = $user->employee?->ranks?->pluck('name')->toArray() ?? [];

        $isAdmin = in_array('Admin', $role_names) || in_array('Super Admin', $role_names);

        $query = Incident::query()
            ->with(['customer', 'trip', 'horse', 'trailer', 'vehicle'])
            ->where('authorization','pending');

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

        return view('livewire.incidents.pending', [
            'incidents' => $incidents,
            'department_names' => $department_names,
            'role_names' => $role_names,
            'rank_names' => $rank_names,
        ]);
    }
}
