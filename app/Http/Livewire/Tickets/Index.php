<?php

namespace App\Http\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;
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
    public $ticket;
    public $ticket_id;
    public $status;
    public $comments;
    public $ticket_status = "all";

    public function mount(){
        $this->resetPage();

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
    public function getTicketsProperty(){

 
        if ($this->ticket_status == "all") {
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )
                    ->where('ticket_number','like', '%'.$this->search.'%')
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
                    })->orderBy('ticket_number','desc')->paginate(10);
                }else {
                    return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )->orderBy('ticket_number','desc')->paginate(10);
                }
               
            }
            elseif (isset($this->search)) {
               
                return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->where('ticket_number','like', '%'.$this->search.'%')
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
                })->orderBy('ticket_number','desc')->paginate(10);
            }
            else {
               
                return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))->orderBy('ticket_number','desc')->paginate(10);
              
            }
        }else{
          
            if (isset($this->from) && isset($this->to)) {
                if (isset($this->search)) {
                    return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereBetween('created_at',[$this->from, $this->to] )
                    ->where('status',$this->ticket_status)
                    ->where('ticket_number','like', '%'.$this->search.'%')
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
                    })->orderBy('ticket_number','desc')->paginate(10);
                }else {
                    return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')
                    ->where('status',$this->ticket_status)
                    ->whereBetween('created_at',[$this->from, $this->to] )->orderBy('ticket_number','desc')->paginate(10);
                }
               
            }
            elseif (isset($this->search)) {
               
                return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->where('status',$this->ticket_status)
                ->whereYear('created_at', date('Y'))
                ->where('ticket_number','like', '%'.$this->search.'%')
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
                })->orderBy('ticket_number','desc')->paginate(10);
            }
            else {
               
                return Ticket::query()->with('booking','inspection','horse','trailer','vehicle')->whereMonth('created_at', date('m'))
                ->where('status',$this->ticket_status)
                ->whereYear('created_at', date('Y'))->orderBy('ticket_number','desc')->paginate(10);
              
            }
         
        }
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
        $ticket->status = $this->status;
        $ticket->closed_comments = $this->comments;
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
