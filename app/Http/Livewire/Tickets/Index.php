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

        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            return Ticket::latest()->paginate(10);
          
        } else {
            return Ticket::where('user_id',Auth::user()->id)->latest()->paginate(10);
           
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
