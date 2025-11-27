<?php

namespace App\Http\Livewire\Tickets;

use Carbon\Carbon;
use App\Models\Ticket;
use Livewire\Component;
use App\Models\Inventory;
use App\Models\Inspection;
use App\Models\TicketImage;
use Livewire\WithFileUploads;
use App\Models\InspectionType;
use App\Models\InspectionGroup;
use App\Models\TicketInventory;
use App\Models\InspectionResult;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithFileUploads;

    public $ticket;
    public $ticket_id;
    public $inspection_groups;
    public $inspection;
    public $inspection_id;
    public $before_attachments;
    public $after_attachments;
    public $image;
    public $timeframe;
    public $out_date;
    public $out_time;
    public $next_service;
    public $equipment;
    public $service_history;

    public $inspection_type;
    public $inspection_results;
    public $inspection_type_id;
    public $user;
    public $employee;
    public $company;
    public $role_names;
    public $rank_names;
    public $department_names;
    public $ranks;
    public $roles;
    public $departments;

    public $employees;
    public $employee_ids = [];

    public $green = 'green';
    public $red = 'red';
    public $yellow = 'yellow';
    public $comments;
    public $hours;
    public $cost;
    public $status;
    public $notes;
    public $initial_diagnosis;
    public $acknowledgement = False;

    private function resetInputFields(){
        $this->timeframe = '';
        $this->image = '';
    }



    public function store($id){
        
        $this->inspection = Inspection::find($id);

        if (isset($this->status)) {

        foreach ($this->status as $key => $value) {

        $result = new InspectionResult;
        $result->inspection_id = $this->inspection->id;
        if (isset($this->inspection_type_id)) {
            $result->inspection_type_id = $this->inspection_type_id;
        }
        if (isset($this->status[$key])) {
            $result->status = $this->status[$key];
        }
        if (isset($this->comments[$key])) {
            $result->comments = $this->comments[$key];
        }
        if (isset($this->hours[$key])) {
            $result->hours = $this->hours[$key];
        }
        if (isset($this->cost[$key])) {
            $result->cost = $this->cost[$key];
        }

        $result->save();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inpection Results Saved Successfully!!"
        ]);

          }
        }else {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Complete the form before you save!!"
            ]);
        }
    }
    public function updateTicketCard(){

        $this->validate([
            'out_time' => 'required',
            'out_date' => 'required',
            'notes' => 'required',
            'acknowledgement' => 'required',
        ]);
     
        $ticket = $this->ticket;
        $ticket->report = $this->notes;
        $ticket->reported_by_id = Auth::id();
        $ticket->reported_on = Carbon::now();
        $ticket->out_time = $this->out_time;
        $ticket->out_date = $this->out_date;
        $ticket->acknowledgement = $this->acknowledgement;
        $ticket->next_service = $this->next_service;
        $ticket->update();

        $booking = $ticket->booking;
        $booking->out_date = $this->out_date;
        $booking->out_time = $this->out_time;
        $booking->next_service = $this->next_service;
        $booking->update();

        if (isset($this->next_service)) {
            if ($this->ticket->booking->horse) {
                $horse =$this->ticket->booking->horse;
                $horse->prev_service = $this->ticket->booking ? $this->ticket->booking->odometer : null;
                $horse->prev_service_date = $this->ticket->booking ? $this->ticket->booking->in_date : null;
                $horse->next_service = $this->next_service;
                $horse->update();
            }elseif($this->ticket->booking->trailer){
                $trailer = $this->ticket->booking->trailer;
                $trailer->prev_service = $this->ticket->booking ? $this->ticket->booking->odometer : null;
                $trailer->prev_service_date = $this->ticket->booking ? $this->ticket->booking->in_date : null;
                $trailer->next_service = $this->next_service;
                $trailer->update();
            }elseif($this->ticket->booking->vehicle){
                $vehicle = $this->ticket->booking->vehicle;
                $vehicle->prev_service = $this->ticket->booking ? $this->ticket->booking->odometer : null;
                $vehicle->prev_service_date = $this->ticket->booking ? $this->ticket->booking->in_date : null;
                $vehicle->next_service = $this->next_service;
                $vehicle->update();
            }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Ticket Updated Successfully!!"
        ]);
    }

    public function showInitialDiagnosis($id){
        $this->ticket_id = $id;
        $this->dispatchBrowserEvent('show-initialDiagnosisModal');
    }
   
    public function updateDiagnosis(){
       
        $ticket = Ticket::find($this->ticket_id);
        $ticket->initial_diagnosis = $this->initial_diagnosis;
        $ticket->update();

        $this->dispatchBrowserEvent('hide-initialDiagnosisModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Initial Diagnosis Updated Successfully!!"
        ]);

    }

    public function addAttachments(){

        if ($this->image) {
            foreach ($this->image as $image) {
            $fileNameWithExt = $image->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $image->getClientOriginalExtension();
            //file name to store
            $imageNameToStore = $filename.'_'.time().'.'.$extention;
            $image->storeAs('/uploads', $imageNameToStore, 'path');

            $ticket_image = new TicketImage;
            $ticket_image->ticket_id  = $this->ticket->id;
            $ticket_image->timeframe  = $this->timeframe;
            $ticket_image->filename  = $imageNameToStore;
            $ticket_image->save();
            }

        }
        $this->dispatchBrowserEvent('hide-attachmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Image Attachment(s) Uploaded Successfully!!"
        ]);
        // return redirect(request()->header('Referer'));
    }

    public function mount($id){

        $this->ticket_id = $id;
        $this->ticket = Ticket::with(['booking'])->find($id);
        $this->employees = $this->ticket->booking->employees;
                    foreach ($this->employees as $employee) {
                        $this->employee_ids[] = $employee->id;
                    }

        $equipment = null;
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;

        $this->departments = $this->employee->departments;
        foreach($this->departments as $department){
            $this->department_names[] = $department->name;
        }
        $this->roles = $this->user->roles;
        foreach($this->roles as $role){
            $this->role_names[] = $role->name;
        }
        $this->ranks = $employee->ranks;
        foreach($this->ranks as $rank){
            $this->rank_names[] = $rank->name;
        }
        $this->equipment = "";

        if ($this->ticket->horse) {
            $equipment = $this->ticket->horse;
            $this->equipment = $this->ticket->horse->registration_number . 
                ($this->ticket->horse->fleet_number ? " (" . $this->ticket->horse->fleet_number . ")" : "");
        } elseif ($this->ticket->trailer) {
            $equipment = $this->ticket->trailer;
            $this->equipment = $this->ticket->trailer->registration_number . 
                ($this->ticket->trailer->fleet_number ? " (" . $this->ticket->trailer->fleet_number . ")" : "");
        } elseif ($this->ticket->vehicle) {
            $equipment = $this->ticket->vehicle;
            $this->equipment = $this->ticket->vehicle->registration_number . 
                ($this->ticket->vehicle->fleet_number ? " (" . $this->ticket->vehicle->fleet_number . ")" : "");
        } elseif ($this->ticket->asset) {
            $equipment = $this->ticket->asset;
            $product_name = $this->ticket->asset->product?->name ?? "";
            $identification_number = $this->ticket->asset->product?->identification_number ?? "";
            $this->equipment = $product_name . " " . $identification_number;
        }

        // Only try service history if $equipment is an Eloquent model with tickets relation
        $this->service_history = $equipment?->tickets()->where('id', '!=', $id)->latest()->take(10)->get() ?? collect();

        $this->status = $this->ticket->status;
        $this->inspection_groups = InspectionGroup::latest()->get();
        $this->after_attachments = TicketImage::where('ticket_id',$this->ticket->id)
        ->where('timeframe','After')->latest()->get();
        $this->before_attachments = TicketImage::where('ticket_id',$this->ticket->id)
        ->where('timeframe','Before')->latest()->get();
        $this->notes = $this->ticket->report;
        $this->next_service = $this->ticket->next_service;
        $this->out_date = $this->ticket->out_date;
        $this->out_time = $this->ticket->out_time;
        $this->inspection = Inspection::find($this->ticket->inspection->id);

        $this->inspection_results = $this->inspection->inspection_results;

    }

    public function render()
    {
        $this->ticket;
        $this->after_attachments = TicketImage::where('ticket_id',$this->ticket->id)
        ->where('timeframe','After')->latest()->get();
        $this->before_attachments = TicketImage::where('ticket_id',$this->ticket->id)
        ->where('timeframe','Before')->latest()->get();
        return view('livewire.tickets.show',[
            'after_attachments' => $this->after_attachments,
            'before_attachments' => $this->before_attachments,
            'notes' => $this->notes,
            'ticket' => $this->ticket,
        ]);
    }
}
