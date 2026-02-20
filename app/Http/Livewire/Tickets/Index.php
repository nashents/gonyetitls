<?php

namespace App\Http\Livewire\Tickets;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Ticket;
use App\Models\Station;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\ServiceType;
use App\Models\TripExpense;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\TicketsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

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
    public $ticket_id;
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
    public $ticket_status = "all";
    public $stations;
    public $station_id;
    public $employees;
    public $employee_id;
   


    public function mount(){
        
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

    public function exportTicketsCSV(Excel $excel){

        return $excel->download(new TicketsExport($this->ticket_status, $this->filter, $this->search, $this->from, $this->to), 'tickets_'.time().'.csv', Excel::CSV);
    }
    public function exportTicketsPDF(Excel $excel){

        return $excel->download(new TicketsExport($this->ticket_status, $this->filter, $this->search, $this->from, $this->to), 'tickets_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportTicketsExcel(Excel $excel){
        return $excel->download(new TicketsExport($this->ticket_status, $this->filter, $this->search, $this->from, $this->to), 'tickets_'.time().'.xlsx');
    }

    private function resetInputFields(){
        $this->status = '';
        $this->comments = '';
    }

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyCloseTicketModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->bookings->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      public function calTotals($relation, $ticket, $defaultColumn, $defaultCurrencyId){

            $default = $ticket->$relation()
                ->where('currency_id', $defaultCurrencyId)
                ->sum($defaultColumn);

            $exchange = $ticket->$relation()
                ->where('currency_id', '!=', $defaultCurrencyId)
                ->sum('exchange_amount');

            return (float) $default + (float) $exchange;

      }

      public function attachBreakdownExpenseIfAny($booking, float $total): ?\App\Models\TripExpense
        {
            // Eager load safely; ensures we have what we need in one go
            $booking->loadMissing('breakdown.trip');

            $breakdown = $booking->breakdown;
            $trip      = $breakdown?->trip;

            if (!$trip) {
                return null; // No trip linked to this booking’s breakdown—nothing to do
            }

            return $this->addTripExpense($trip, $booking, $breakdown, $total);
        }

        public function addTripExpense(
            \App\Models\Trip $trip,
            \App\Models\Booking $booking,
            ?\App\Models\Breakdown $breakdown,
            float $total
        ): \App\Models\TripExpense {
            if ($total <= 0) {
                // Don’t record junk. Adjust this rule if you deliberately allow zero amounts.
                throw new \InvalidArgumentException('Trip expense total must be greater than zero.');
            }

            $currencyId = $this->default_currency->id ?? null;
            if (!$currencyId) {
                throw new \RuntimeException('Default currency is not configured.');
            }

            return DB::transaction(function () use ($trip, $booking, $breakdown, $total, $currencyId) {
                // Idempotency: prevent duplicates for the same trip+booking+breakdown+currency
                // Add a DB unique index to enforce this (see note below).
                return $trip->trip_expenses()->updateOrCreate(
                    [
                        'booking_id'   => $booking->id,
                        'breakdown_id' => $breakdown?->id, // can be null
                        'currency_id'  => $currencyId,
                    ],
                    [
                        'user_id'      => Auth::id(),
                        'amount'       => round($total, 2),
                        // Optional: tag this as a breakdown-related expense
                        // 'type'      => 'breakdown',
                        // 'note'      => 'Auto-added from booking breakdown',
                    ]
                );
            });
        }

     


      public function authorizeSelectedRows(){

         DB::transaction(function () {


           $selected_tickets = Ticket::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_tickets)) {
                foreach($selected_tickets as $ticket){
                    
                    $ticket->closed_by_id = Auth::user()->id;
                    $ticket->status = $this->status;
                    $ticket->closed_comments = $this->comments;
                    $ticket->closed_on = Carbon::now();
                    $ticket->update();
                   
                    $total_spares = $this->calTotals('ticket_inventories',$ticket,'subtotal', $this->default_currency->id);
                    $total_other = $this->calTotals('ticket_expenses',$ticket,'subtotal_incl', $this->default_currency->id);
                    $total = $total_spares + $total_other;

                    $booking = $ticket->booking;
                    $booking->status = $this->status;
                    $booking->currency_id = $this->default_currency->id;
                    $booking->total_spares = $total_spares;
                    $booking->total_other = $total_other;
                    $booking->out_of_workshop_date = $this->out_of_workshop_date;
                    $booking->out_of_workshop_time = $this->out_of_workshop_time;
                    $booking->update();

                    $this->attachBreakdownExpenseIfAny($booking, $total);
                   

                    $horse = $booking->horse;
                    if (isset($horse)) {
                        $horse->service = 0;
                        $horse->update();
                    }

                    $vehicle = $booking->vehicle;
                    if (isset($vehicle)) {
                        $vehicle->service = 0;
                        $vehicle->update();
                    }
                
                    $trailer = $booking->trailer;
                    if (isset($trailer)) {
                        $trailer->service = 0;
                        $trailer->update();
                    }


                }

                $this->reset(['selectedRows','selectPageRows']);

                $this->dispatchBrowserEvent('hide-bulkyCloseTicketModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Ticket Status Updated Successfully!!"
                ]);

        

                
            
           }
        });

      }

      public function updatedFilter($value){
       
            if(is_null($value)){
                return ;
            }
            if ($value == "horse") {
                $this->horses =  Horse::where('archive',0)->orderBy('registration_number','asc')->get();
            }elseif($value == "vehicle"){
                $this->vehicles = Vehicle::where('archive',0)->orderBy('registration_number','asc')->get();
            }elseif($value == "trailer"){
                $this->trailers = Trailer::where('archive',0)->orderBy('registration_number','asc')->get();
            }elseif($value == "asset"){
                $this->assets = Asset::with('product')->get()->sortBy('product.name');
            }
      }


    public function getTicketsProperty()
    {
        $query = Ticket::query()
            ->with(['booking', 'inspection', 'horse', 'trailer', 'vehicle', 'service_type']);

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

    public function showTicket($id){
        $this->ticket_id = $id;
        $this->ticket = Ticket::find($id);
        $this->status = 0;
        $this->dispatchBrowserEvent('show-closeTicketModal');
    }

    public function closeTicket(){

        DB::transaction(function () {

        $ticket = Ticket::find($this->ticket_id);
        $ticket->closed_by_id = Auth::user()->id;
        $ticket->closed_on = Carbon::now();
        $ticket->status = $this->status;
        $ticket->closed_comments = $this->comments;
        $ticket->update();

        $total_spares = $this->calTotals('ticket_inventories',$ticket,'subtotal', $this->default_currency->id);
        $total_other = $this->calTotals('ticket_expenses',$ticket,'subtotal_incl', $this->default_currency->id);
        $total = $total_spares + $total_other;

        $booking = $ticket->booking;
        $booking->status = $this->status;
        $booking->currency_id = $this->default_currency->id;
        $booking->total_spares = $total_spares;
        $booking->total_other = $total_other;
        $booking->out_of_workshop_date = $this->out_of_workshop_date;
        $booking->out_of_workshop_time = $this->out_of_workshop_time;
        $booking->update();

        $this->attachBreakdownExpenseIfAny($booking, $total);

        $horse = $booking->horse;
        if (isset($horse)) {
            $horse->service = 0;
            $horse->update();
        }

        $vehicle = $booking->vehicle;
        if (isset($vehicle)) {
            $vehicle->service = 0;
            $vehicle->update();
        }
      
        $trailer = $booking->trailer;
        if (isset($trailer)) {
            $trailer->service = 0;
            $trailer->update();
        }

        $this->dispatchBrowserEvent('hide-closeTicketModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Ticket Status Updated Successfully!!"
        ]);
    });
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        return view('livewire.tickets.index',[
            'tickets' => $this->tickets
        ]);
       
   
    }
}
