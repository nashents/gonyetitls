<?php

namespace App\Http\Livewire\Tickets;

use Carbon\Carbon;
use App\Models\Asset;
use App\Models\Horse;
use App\Models\Ticket;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
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
    protected $queryString = ['search'];
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
    public $ticket_status = "all";

    public function mount(){
        $this->resetPage();

        $this->horses = Horse::where('status',1)->orderby('registration_number')->get();
        $this->assets = Asset::where('status',1)->get();
        $this->trailers = Trailer::where('status',1)->orderby('registration_number')->get();
        $this->vehicles = Vehicle::where('status',1)->orderby('registration_number')->get();

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


      public function authorizeSelectedRows(){

           $selected_tickets = Ticket::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_tickets)) {
                foreach($selected_tickets as $ticket){
                    
                    $ticket->closed_by_id = Auth::user()->id;
                    $ticket->status = $this->status;
                    $ticket->closed_comments = $this->comments;
                    $ticket->closed_on = Carbon::now();
                    $ticket->update();

                    $booking = $ticket->booking;
                    $booking->status = $this->status;
                    $booking->update();

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

      }


    public function getTicketsProperty()
    {
        $query = Ticket::query()
            ->with(['booking', 'inspection', 'horse', 'trailer', 'vehicle','service_type']);

        // ✅ Status filter
        if ($this->ticket_status !== 'all') {
            $query->where('status', $this->ticket_status);
        }

        // ✅ Extra filters
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

        // ✅ Date filter
        if (!empty($this->from) && !empty($this->to)) {
            $query->whereBetween('created_at', [$this->from, $this->to]);
        } else {
            $query->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'));
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
                    ->orWhereHas('employee', function ($q3) use ($search) {
                        $q3->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%");
                    });
            });
        });

    }

        // ✅ Order + paginate
        return $query->orderBy('ticket_number', 'desc')->paginate(10);
    }

    public function showTicket($id){
        $this->ticket_id = $id;
        $this->ticket = Ticket::find($id);
        $this->status = $this->ticket->status;
        $this->dispatchBrowserEvent('show-closeTicketModal');
    }

    public function closeTicket(){

        $ticket = Ticket::find($this->ticket_id);
        $ticket->closed_by_id = Auth::user()->id;
        $ticket->closed_on = Carbon::now();
        $ticket->status = $this->status;
        $ticket->closed_comments = $this->comments;
        $ticket->update();

        // $inspection = $ticket->inspection;
        // if (isset($inspection)) {
        //     $inspection->closed_by_id = Auth::user()->id;
        //     $inspection->status = $this->status;
        //     $inspection->closed_comments = $this->comments;
        //     $inspection->update();
        // }

        $booking = $ticket->booking;
        $booking->status = $this->status;
        $booking->update();

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
