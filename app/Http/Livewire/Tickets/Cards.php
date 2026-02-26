<?php

namespace App\Http\Livewire\Tickets;

use App\Models\Employee;
use App\Models\ServiceType;
use App\Models\Station;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Cards extends Component
{

    use WithPagination;
    public $ticket_id;
    public $mechanic_id;
    public $mechanic_ids;
    public $mechanics;
    public $ticket_status = "all";

    use WithPagination;
    public $selectedRows = [];
    public $selectPageRows = false;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $overdueOnly = false;
    protected $queryString = ['search', 'overdueOnly' => ['as' => 'overdue', 'except' => false]];
    public $from;
    public $to;

    // private $tickets;
    public $filter;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $trailers;
    public $selectedTrailer;
    public $assets;
    public $selectedAsset;
    public $ticket;
    public $status;
    public $comments;
    public $user;
    public $employee;
    public $company;
    public $service_types;
    public $service_type_id;
    public $default_currency;
    public $out_of_workshop_time;
    public $out_of_workshop_date;
    public $stations;
    public $station_id;
    public $employees;
    public $employee_id;

    public function mount($id){
        $this->mechanic_id = $id;
        $this->resetPage();
         $this->overdueOnly = request()->boolean('overdue', false);
        $this->resetPage();
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
        $this->default_currency = $this->company->currency;
        $this->horses = collect();
        $this->assets = collect();
        $this->trailers = collect();
        $this->vehicles = collect();
        $this->service_types = ServiceType::where('status',1)->orderBy('name','asc')->get();
        $this->stations = Station::where('status',1)->orderBy('name','asc')->get();
        $this->employees = Employee::query()
        ->whereHas('departments', fn ($q) => 
            $q->where('departments.name', 'Workshop')
        )
        ->with('departments:id,name')
        ->orderBy('name', 'asc')
        ->orderBy('surname', 'asc')
        ->distinct()
        ->get();

    }



       public function getTicketsProperty()
    {
        $query = Ticket::query()
            ->with(['booking', 'inspection', 'horse', 'trailer', 'vehicle', 'service_type'])
            ->whereHas('booking.employees', function ($q) {
                $q->where('employees.id', $this->mechanic_id);
            });
        // ✅ Status vs overdue logic
        if ($this->overdueOnly) {
            // All overdue logic lives on ticket.booking
            $now = now();

            $query->whereHas('booking', function ($q) use ($now) {
                $q->where('status', 1)                      // booking status = open
                ->where('authorization', 'approved')      // only approved bookings
                ->whereYear('in_date', $now->year)        // same year as now
                ->whereNotNull('estimated_out_date')
                ->whereNotNull('estimated_out_time')
                ->whereRaw(
                    "TIMESTAMP(estimated_out_date, estimated_out_time) < ?",
                    [$now->toDateTimeString()]           // 'Y-m-d H:i:s'
                );
            });

        } else {

            // ✅ Date filter by created_at (normal mode only)
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

            // ✅ Normal status filter (on ticket)
            if ($this->ticket_status !== 'all') {
                $query->where('status', $this->ticket_status);
            }
        }

        // ✅ Service type filter (ticket column)
        if ($this->service_type_id) {
            $query->where('service_type_id', $this->service_type_id);
        }

        // ✅ Employee filter (via booking.employees)
        if ($this->employee_id) {
            $query->whereHas('booking.employees', function ($q) {
                $q->where('employees.id', $this->employee_id);
            });
        }

        // ✅ Station filter (via booking)
        if ($this->station_id) {
            $query->whereHas('booking', function ($q) {
                $q->where('station_id', $this->station_id);
            });
        }

        // ✅ Extra filters (horse / trailer / asset / vehicle)
        if (!empty($this->filter)) {
            switch ($this->filter) {
                case "horse":
                    $query->where('horse_id', $this->selectedHorse);
                    break;
                case "trailer":
                    $query->where('trailer_id', $this->selectedTrailer);
                    break;
                case "asset":
                    $query->where('asset_id', $this->selectedAsset);
                    break;
                case "vehicle":
                    $query->where('vehicle_id', $this->selectedVehicle);
                    break;
            }
        }

        // ✅ Search filter
        if (($search = trim((string) $this->search)) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('in_date', 'like', "%{$search}%")
                    ->orWhere('in_time', 'like', "%{$search}%")
                    ->orWhere('out_date', 'like', "%{$search}%")
                    ->orWhere('out_time', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('station', 'like', "%{$search}%")
                    ->orWhereHas('inspection', function ($q2) use ($search) {
                        $q2->where('inspection_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('service_type', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('booking', function ($q2) use ($search) {
                        $q2->where('booking_number', 'like', "%{$search}%")
                            ->orWhereHas('employees', function ($q3) use ($search) {
                                $q3->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                            });
                    });
            });
        }

        // ✅ Order + paginate
        return $query->orderByDesc('created_at')->paginate(10);
    }

    public function render()
    {

        

        return view('livewire.tickets.cards', [
            'tickets' => $this->tickets
        ]);
    }
}
