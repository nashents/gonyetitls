<?php

namespace App\Http\Livewire\Bookings;

use App\Exports\BookingsExport;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Horse;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Station;
use App\Models\Trailer;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selectedRows = [];
    public $selectPageRows = false;

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
    public $search_id;


    // private $bookings;
    public $booking;
    public $booking_id;
    public $service_types;
    public $service_type_id;
    public $stations;
    public $station_id;
    public $employees;
    public $employee_id;
    public $problem_categories;
    public $problem_category_id;
    public $status;
    public $comments;
    public $booking_status = "all";



    private function resetInputFields(){
        $this->status = '';
        $this->comments = '';
    }

    
    public function mount(){
        $this->resetPage();
        $this->horses = collect();
        $this->assets = collect();
        $this->trailers = collect();
        $this->vehicles = collect();
        $this->stations = Station::where('status',1)->orderBy('name','asc')->get();
        $this->service_types = ServiceType::where('status',1)->orderBy('name','asc')->get();
        $this->problem_categories = ProblemCategory::orderBy('name','asc')->get();
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


    public function exportBookingsCSV(Excel $excel){
        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to, $this->filter, $this->search_id, $this->station_id, $this->service_type_id, $this->employee_id,$this->problem_category_id), 'bookings_'.time().'.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){
        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to, $this->filter, $this->search_id, $this->station_id, $this->service_type_id, $this->employee_id,$this->problem_category_id), 'bookings_'.time().'.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){
        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to, $this->filter, $this->search_id, $this->station_id, $this->service_type_id, $this->employee_id,$this->problem_category_id), 'bookings_'.time().'.xlsx');
    }


    public function showBooking($id){
        $this->booking_id = $id;
        $this->booking = Booking::find($id);
        $this->status = $this->booking->status;
        $this->dispatchBrowserEvent('show-closeTicketModal');
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

           $selected_bookings = Booking::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_bookings)) {
                foreach($selected_bookings as $booking){
                    
                    $booking->status = $this->status;
                    $booking->update();
            
                    $ticket = $booking->ticket;
                    if (isset($ticket)) {
                        $ticket->closed_by_id = Auth::user()->id;
                         $ticket->closed_on = Carbon::now();
                        $ticket->status = $this->status;
                        $ticket->closed_comments = $this->comments;
                        $ticket->update();
                    }
                  
            
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
                    'message'=>"Booking(s) Status Updated Successfully!!"
                ]);

        

                
            
           }

      }


    public function closeBooking(){

        $booking = Booking::find($this->booking_id);
        $booking->status = $this->status;
        $booking->update();

        $ticket = $booking->ticket;
        if (isset($ticket)) {
            $ticket->closed_by_id = Auth::user()->id;
             $ticket->closed_on = Carbon::now();
            $ticket->status = $this->status;
            $ticket->closed_comments = $this->comments;
            $ticket->update();
        }
        
        // $inspection = $booking->inspection;
        // if (isset($inspection)) {
        //     $inspection->closed_by_id = Auth::user()->id;
        //     $inspection->status = $this->status;
        //     $inspection->closed_comments = $this->comments;
        //     $inspection->update();
        // }
      

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
            'message'=>"Booking Status Updated Successfully!!"
        ]);
    }


   
    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function getBookingsProperty()
    {
        $query = Booking::query()
            ->with(['ticket','inspection','horse','trailer','vehicle']);

        // Date window
        if ($this->from && $this->to) {
            $from = Carbon::parse($this->from)->startOfDay();
            $to   = Carbon::parse($this->to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        } else {
            $query->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }

        
        // Status (skip when "all")
        if ($this->booking_status !== 'all') {
            $query->where('status', $this->booking_status);
        }

        if ($this->station_id) {
           $query->where('station_id', $this->station_id);
        }
       
        if ($this->service_type_id) {
            $query->where('service_type_id', $this->service_type_id);
        }
        if ($this->problem_category_id) {
            $query->where('problem_category_id', $this->problem_category_id);
        }
        if ($this->employee_id) {
            $query->whereHas('employees', function ($q) {
                $q->where('employees.id', $this->employee_id);
            });
        }

       if ($this->filter) {
            switch ($this->filter) {
                case 'horse':
                    $this->search_id = $this->selectedHorse;
                    $query->where('horse_id', $this->selectedHorse);
                    break;

                case 'trailer':
                    $this->search_id = $this->selectedTrailer;
                    $query->where('trailer_id', $this->selectedTrailer);
                    break;

                case 'vehicle':
                    $this->search_id = $this->selectedVehicle;
                    $query->where('vehicle_id', $this->selectedVehicle);
                    break;

                case 'asset':
                    $this->search_id = $this->selectedAsset;
                    $query->where('asset_id', $this->selectedAsset);
                    break;
            }
        }

        // Search (GROUPED to avoid breaking previous filters)
        if (filled($this->search)) {
            $term = '%'.$this->search.'%';

            $query->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', $term)
                ->orWhere('transaction_type', 'like', $term)
                ->orWhereHas('employees', function ($qq) use ($term) {
                    $qq->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                })
                ->orWhereHas('ticket', function ($qq) use ($term) {
                    $qq->where('ticket_number', 'like', $term);
                })
                ->orWhereHas('transporter', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })
                ->orWhereHas('station', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })
                ->orWhereHas('inspection', function ($qq) use ($term) {
                    $qq->where('inspection_number', 'like', $term);
                });
            });
        }

        return $query
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.bookings.index',[
            'bookings' => $this->bookings
          
        ]);
           
    }
}
