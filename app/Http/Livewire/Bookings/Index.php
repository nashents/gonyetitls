<?php

namespace App\Http\Livewire\Bookings;

use Carbon\Carbon;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\BookingsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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


    // private $bookings;
    public $booking;
    public $booking_id;
    public $status;
    public $comments;
    public $booking_status = "all";



    private function resetInputFields(){
        $this->status = '';
        $this->comments = '';
    }

    public function mount(){
        $this->resetPage();
      
      }

      public function exportBookingsCSV(Excel $excel){

        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to), 'garage_bookings.csv', Excel::CSV);
    }
    public function exportBookingsPDF(Excel $excel){

        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to), 'garage_bookings.pdf', Excel::DOMPDF);
    }
    public function exportBookingsExcel(Excel $excel){
        return $excel->download(new BookingsExport($this->booking_status, $this->search, $this->from, $this->to), 'garage_bookings.xlsx');
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


    public function getBookingsProperty(){

 
        if ($this->booking_status == "all") {
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )
                    ->where('booking_number','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inspection', function ($query) {
                        return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })->orderBy('booking_number','desc')->paginate(10);
                }else {
                    return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->orderBy('booking_number','desc')->paginate(10);
                }
               
            }
            elseif (isset($this->search)) {
               
                return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('booking_number','like', '%'.$this->search.'%')
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('ticket', function ($query) {
                    return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('inspection', function ($query) {
                    return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })->orderBy('booking_number','desc')->paginate(10);
            }
            else {
               
                return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->orderBy('booking_number','desc')->paginate(10);
              
            }
        }else{
          
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )
                    ->where('status',$this->booking_status)
                    ->where('booking_number','like', '%'.$this->search.'%')
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('ticket', function ($query) {
                        return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('inspection', function ($query) {
                        return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                    })->orderBy('booking_number','desc')->paginate(10);
                }else {
                    return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')
                    ->where('status',$this->booking_status)
                    ->whereBetween('created_at',[$this->from, $this->to] )->orderBy('booking_number','desc')->paginate(10);
                }
               
            }
            elseif (isset($this->search)) {
               
                return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->where('status',$this->booking_status)
                ->whereYear('created_at', date('Y'))
                ->where('booking_number','like', '%'.$this->search.'%')
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('ticket', function ($query) {
                    return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('inspection', function ($query) {
                    return $query->where('inspection_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trailer', function ($query) {
                    return $query->where('fleet_number', 'like', '%'.$this->search.'%');
                })->orderBy('booking_number','desc')->paginate(10);
            }
            else {
               
                return Booking::query()->with('ticket','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->where('status',$this->booking_status)
                ->whereYear('created_at', date('Y'))->orderBy('booking_number','desc')->paginate(10);
              
            }
         
        }

       
         

       
    }

    public function render()
    {
        return view('livewire.bookings.index',[
            'bookings' => $this->bookings
          
        ]);
           
    }
}
