<?php

namespace App\Http\Livewire\Tickets;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\InspectionResult;
use App\Models\InspectionService;
use Illuminate\Support\Facades\Auth;

class Jobcard extends Component
{
    public $ticket;
    public $booking;
    public $inspection;
    public $service_type;
    public $inspection_services;
    public $company; 
    public $closed_by; 
    public $ticket_inventories; 
    public $inspection_results; 
    public $in_date; 
    public $in_time; 
    public $out_date; 
    public $out_time; 
    public $total_parts = 0; 
    public $total_expenses = 0; 
    public $total = 0; 

    public function mount($ticket){
        $this->ticket = $ticket;
        $this->ticket_inventories = $ticket->ticket_inventories;
        $this->total_parts = $ticket->ticket_inventories->sum('amount');
        $this->total_expenses = $ticket->ticket_expenses->sum('subtotal');
        if (is_numeric(  $this->total_parts) && is_numeric($this->total_expenses)) {
            $this->total = $this->total_parts + $this->total_expenses;
        }
        $this->booking = $ticket->booking;
        $this->in_date = $this->booking->in_date;
        $this->in_time = $this->booking->in_time;
        $this->out_date = $this->booking->out_date;
        $this->out_time = $this->booking->out_time;
        $this->inspection = $this->booking->inspection;
        $this->inspection_results = InspectionResult::with('inspection_type')->where('inspection_id',$this->inspection->id)->get();
        $this->service_type = $this->booking->service_type;
        $this->inspection_services = InspectionService::with('inspection_type')->where("service_type_id", $this->service_type->id)->get();
        $this->company = $this->booking->company ? $this->booking->company : Auth::user()->employee->company;
        $this->closed_by = User::find($ticket->closed_by_id);
    }

   

    public function render()
    {
        return view('livewire.tickets.jobcard');
    }
}
