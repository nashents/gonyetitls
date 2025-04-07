<?php

namespace App\Http\Livewire\Inspections;

use App\Models\Ticket;
use Livewire\Component;
use App\Models\Inspection;
use App\Models\InspectionGroup;
use App\Models\InspectionResult;

class Show extends Component
{

    public $ticket;
    public $ticket_id;
    public $inspection_groups;
    public $inspection;
    public $inspection_id;
    public $inspection_type;
    public $inspection_type_id;
    public $inspection_services;
    public $inspection_service_id;
    public $service_types;
    public $service_type_id;
    public $service_type;
  

    public $green = 'green';
    public $red = 'red';
    public $yellow = 'yellow';
    public $comments;
    public $hours;
    public $cost;
    public $status = [];
    public $inputs = [];

    public function mount($id){
        $this->inspection = Inspection::find($id);
        $horse = $this->inspection->horse;
        $vehicle = $this->inspection->vehicle;
        $trailer = $this->inspection->trailer;
        if (isset($horse)) {
            $this->service_type = $this->inspection->service_type;
            if (isset( $this->service_type)) {
                $this->inspection_services = $this->service_type->inspection_services->where('category','Horse');
            }
            
        }elseif (isset($vehicle)) {
            $this->service_type = $this->inspection->service_type;
            if ($this->service_type) {
                $this->inspection_services = $this->service_type->inspection_services->where('category','Vehicle');
            }
           
        }elseif (isset($trailer)) {
            $this->service_type = $this->inspection->service_type;
            if (isset( $this->service_type)) {
                $this->inspection_services = $this->service_type->inspection_services->where('category','Trailer');
            }
           
        }
        
       
        $this->inspection_groups = InspectionGroup::latest()->get();
    }

    public function save(){

        if (isset($this->status)) {

        foreach ($this->status as $key => $value) {
        $result = new InspectionResult;
        $result->inspection_id = $this->inspection->id;
        $result->service_type_id = $this->service_type->id;
        $result->inspection_type_id =  $key;
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

        $inspection = Inspection::find($this->inspection->id);
        $inspection->status = 0;
        $inspection->update();
          }
          $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inpection Results Saved Successfully!!"
        ]);
        return redirect(route('tickets.show',$this->inspection->ticket->id));
        }else {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Complete the form before you save!!"
            ]);
        }
    }
    public function render()
    {
        return view('livewire.inspections.show');
    }
}
