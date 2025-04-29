<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Country;
use App\Models\Trailer;
use Livewire\Component;
use App\Models\Breakdown;
use App\Models\Transporter;
use App\Models\BreakdownAssignment;
use Illuminate\Support\Facades\Auth;

class Breakdowns extends Component
{
    public $breakdown_assignments;
    public $breakdown_assignment;
    public $breakdown_assignment_id;
    public $breakdowns;
    public $breakdown;
    public $breakdown_id;
    public $trip;
    public $trips;
    public $transporters;
    public $selectedTransporter;
    public $drivers;
    public $driver_id;
    public $trailers;
    public $trailer_id;
    public $assignment_horses;
    public $horses;
    public $horse_id;
    public $trip_id;
    public $description;
    public $status;
    public $location;
    public $date;
    public $country_id;

    private function resetInputFields(){
        $this->trip_id = "" ;
        $this->selectedTransporter = "" ;
        $this->horse_id = "" ;
        $this->driver_id = "" ;
        $this->trailer_id = "" ;
        $this->status = "";
        $this->description = "";
        $this->location = "";
        $this->date = "";

    }

    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->trips = Trip::select('id', 'trip_number', 'trip_ref', 'customer_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->orderBy('start_date', 'desc')
                    ->get();
                    
        $this->breakdowns = $this->trip->breakdowns;
        $this->breakdown_assignments = $this->trip->breakdown_assignments;
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->horses = collect();
        $this->drivers = collect();
        $this->trailers = collect();
    }

    public function updatedSelectedTransporter($transporter){
        if (!is_null($transporter)) {
            $this->horses = Horse::where('transporter_id',$transporter)
            ->where('status', 1)
            ->where('service',0)
            ->orderBy('registration_number','asc')->latest()->get();
            $this->trailers = Trailer::where('transporter_id',$transporter)
            ->where('status', 1)
            ->where('service',0)
            ->orderBy('registration_number','asc')->latest()->get();
            $this->drivers = Driver::where('transporter_id',$transporter)
            ->withAggregate('employee','name')
            ->where('status', 1)
            ->orderBy('employee_name','asc')->latest()->get();
        }
    }

    public function breakdownNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $breakdown = Breakdown::orderBy('id','desc')->first();

        if (!$breakdown) {
            $breakdown_number =  $initials .'TI'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $breakdown->id + 1;
            $breakdown_number =  $initials .'TI'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $breakdown_number;
    }

    public function store(){
        $breakdown = new Breakdown;
        $breakdown->user_id = Auth::user()->id;
        $breakdown->trip_id = $this->trip_id;
        $breakdown->breakdown_number = $this->breakdownNumber();
        $breakdown->transporter_id = $this->trip->transporter_id;
        $breakdown->horse_id = $this->trip->horse_id;
        $breakdown->driver_id = $this->trip->driver_id;
        $breakdown->date = $this->date;
        $breakdown->location = $this->location;
        $breakdown->description = $this->description;
        $breakdown->save();
        $breakdown->trailers()->sync($this->trip->trailers);
        

        $this->dispatchBrowserEvent('hide-breakdownModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Incident Created Successfully!!"
        ]);

    }

    public function showAssignment($id){
        $breakdown = Breakdown::find($id);
        $this->trip_id = $breakdown->trip_id;
        $this->trip= Trip::find($this->trip_id);
        $this->breakdown_id = $breakdown->id;
        $this->dispatchBrowserEvent('show-breakdown_assignmentModal');

      
    }

    public function storeAssignment(){

       
        $breakdown_assignment = new BreakdownAssignment;
        $breakdown_assignment->user_id = Auth::user()->id;
        $breakdown_assignment->trip_id = $this->trip_id;
        $breakdown_assignment->breakdown_id = $this->breakdown_id;
        $breakdown_assignment->transporter_id = $this->selectedTransporter;
        $breakdown_assignment->horse_id = $this->horse_id;
        $breakdown_assignment->driver_id = $this->driver_id;
        $breakdown_assignment->date = $this->date;
        $breakdown_assignment->description = $this->description;
        $breakdown_assignment->save();
        $breakdown_assignment->trailers()->sync($this->trailer_id);

    
        $horse = Horse::withTrashed()->find($this->horse_id);
        if (isset($horse)) {
            $horse->status = 0;
            $horse->update();
        }
        

        $driver = Driver::withTrashed()->find($this->driver_id);
        if (isset($driver)) {
            $driver->status = 0;
            $driver->update();
        }
        

        if ($breakdown_assignment->trailers->count()>0) {
            foreach ($breakdown_assignment->trailers as $trailer) {
                $trailer = Trailer::withTrashed()->find($trailer->id);
                $trailer->status = 0;
                $trailer->update();
            }
        }

        $this->dispatchBrowserEvent('hide-breakdown_assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Assignment Completed Successfully!!"
        ]);
    }

    public function edit($id){

        $breakdown = Breakdown::find($id);
        $this->trip_id = $breakdown->trip_id;
        $this->location = $breakdown->location;
        $this->description = $breakdown->description;
        $this->status = $breakdown->status;
        $this->date = $breakdown->date;
        $this->breakdown_id = $breakdown->id;
      
        $this->dispatchBrowserEvent('show-breakdownEditModal');

    }
    public function editAssignment($id){

        $breakdown_assignment = BreakdownAssignment::find($id);
        $this->trip_id = $breakdown_assignment->trip_id;
        $this->trip = Trip::find($this->trip_id);
        $this->selectedTransporter = $breakdown_assignment->transporter_id;
        $this->assignment_horses = Horse::where('transporter_id',$this->selectedTransporter)->get();
        $this->trailers = Trailer::where('transporter_id',$this->selectedTransporter)->get();
        $this->drivers = Driver::where('transporter_id',$this->selectedTransporter)->get();
        $this->driver_id = $breakdown_assignment->driver_id;
        $this->horse_id = $breakdown_assignment->horse_id;
        $this->description = $breakdown_assignment->description;
        $this->status = $breakdown_assignment->status;
        $this->date = $breakdown_assignment->date;
        $this->breakdown_assignment_id = $breakdown_assignment->id;
        $this->dispatchBrowserEvent('show-breakdown_assignmentEditModal');

    }

    public function updateAssignment(){
      
        $breakdown_assignment =  BreakdownAssignment::find($this->breakdown_assignment_id);
        $breakdown_assignment->transporter_id = $this->selectedTransporter;
        $breakdown_assignment->horse_id = $this->horse_id;
        $breakdown_assignment->driver_id = $this->driver_id;
        $breakdown_assignment->date = $this->date;
        $breakdown_assignment->description = $this->description;
        $breakdown_assignment->status = $this->status;
        $breakdown_assignment->update();
        $breakdown_assignment->trailers()->detach();
        $breakdown_assignment->trailers()->sync($this->trailer_id);

        $this->dispatchBrowserEvent('hide-breakdown_assignmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Assignment Updated Successfully!!"
        ]);
    }


    public function update(){
        if ($this->breakdown_id) {
            $breakdown = Breakdown::find($this->breakdown_id);
            $this->trip = Trip::find($this->trip_id);
            $breakdown->trip_id = $this->trip_id;
            $breakdown->transporter_id = $this->trip->transporter_id;
            $breakdown->horse_id = $this->trip->horse_id;
            $breakdown->driver_id = $this->trip->driver_id;
            $breakdown->date = $this->date;
            $breakdown->location = $this->location;
            $breakdown->description = $this->description;
            $breakdown->status = $this->status;
            $breakdown->update();
            $breakdown->trailers()->detach();
            $breakdown->trailers()->sync($this->trip->trailers);

            $this->dispatchBrowserEvent('hide-breakdownEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Incident Updated Successfully!!"
            ]);
        }
    }

    public function render()
    {
        $this->breakdowns = Breakdown::where('trip_id', $this->trip->id)->latest()->get();
        $this->breakdown_assignments = BreakdownAssignment::where('trip_id', $this->trip->id)->latest()->get();
        return view('livewire.trips.breakdowns',[
            'breakdowns' => $this->breakdowns,
            'breakdown_assignments' => $this->breakdown_assignments
        ]);
    }
}
