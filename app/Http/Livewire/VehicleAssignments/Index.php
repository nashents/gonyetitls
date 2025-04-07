<?php

namespace App\Http\Livewire\VehicleAssignments;


use App\Models\Mileage;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
  
    public $vehicle_assignments;
    public $vehicle_assignment;
    public $vehicle_assignment_id;
    public $vehicles;
    public $selectedVehicle;
    public $employees;
    public $employee_id;
    public $starting_odometer;
    public $ending_odometer;
    public $end_date;
    public $start_date;
    public $comments;


    public function mount(){
     
        $this->vehicle_assignments = VehicleAssignment::latest()->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->starting_odometer = 0;
    }

    private function resetInputFields(){
        $this->selectedVehicle = "";
        $this->employee_id = "";
        $this->starting_odometer = "";
        $this->start_date = "";
        $this->ending_odometer = "";
        $this->end_date= "";
        $this->comments = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedVehicle' => 'required|unique:vehicle_assignments,vehicle_id,NULL,id,deleted_at,NULL',
        'employee_id' => 'required|unique:vehicle_assignments,employee_id,NULL,id,deleted_at,NULL',
        'starting_odometer' => 'required',
        'start_date' => 'required',
        'comments' => 'nullable|string',
    ];
  
    public function updatedSelectedVehicle($vehicle){
        if (!is_null($vehicle)) {
            $this->starting_odometer = Vehicle::find($vehicle)->mileage;
        }
    }

    public function store(){
        $vehicle_assignment = new VehicleAssignment;
        $vehicle_assignment->user_id = Auth::user()->id;
        $vehicle_assignment->employee_id = $this->employee_id;
        $vehicle_assignment->vehicle_id = $this->selectedVehicle;
        $vehicle_assignment->starting_odometer = $this->starting_odometer;
        $vehicle_assignment->start_date = $this->start_date;
        $vehicle_assignment->comments = $this->comments;
        $vehicle_assignment->status = 1;
        $vehicle_assignment->save();

        $mileage = new Mileage;
        $mileage->user_id = Auth::user()->id;
        $mileage->vehicle_assignment_id = $vehicle_assignment->id;
        $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
        $mileage->mileage = $this->starting_odometer;
        $mileage->date = $this->start_date;
        $mileage->category = "Vehicle Assignment";
        $mileage->save();

        $this->dispatchBrowserEvent('hide-vehicle_assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Employee - Vehicle Assigned Successful!!"
        ]);
      

    }

    public function edit($id){
        $vehicle_assignment = VehicleAssignment::find($id);
        $this->user_id = $vehicle_assignment->user_id;
        $this->selectedVehicle = $vehicle_assignment->vehicle_id;
        $this->employee_id = $vehicle_assignment->employee_id;
        $this->starting_odometer = $vehicle_assignment->starting_odometer;
        $this->ending_odometer = $vehicle_assignment->ending_odometer;
        $this->start_date = $vehicle_assignment->start_date;
        $this->end_date = $vehicle_assignment->end_date;
        $this->comments = $vehicle_assignment->comments;
        $this->status = $vehicle_assignment->status;
        $this->vehicle_assignment_id = $vehicle_assignment->id;
        $this->dispatchBrowserEvent('show-vehicle_assignmentEditModal');

        }


        public function update()
        {
            if ($this->vehicle_assignment_id) {
                $vehicle_assignment = VehicleAssignment::find($this->vehicle_assignment_id);
                $vehicle_assignment->update([
                    'user_id' => Auth::user()->id,
                    'vehicle_id' => $this->selectedVehicle,
                    'employee_id' => $this->employee_id,
                    'starting_odometer' => $this->starting_odometer,
                    'ending_odometer' => $this->ending_odometer,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'comments' => $this->comments,
                    'status' => '1',
                ]);
                if (isset($this->ending_odometer)) {
                    $mileage = new Mileage;
                    $mileage->user_id = Auth::user()->id;
                    $mileage->vehicle_assignment_id = $vehicle_assignment->id;
                    $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $mileage->mileage = $this->ending_odometer;
                    $mileage->date = $this->end_date;
                    $mileage->category = "Vehicle Assignment";
                    $mileage->save();
                }else{
                $mileage =  Mileage::where('vehicle_assignment_id',$vehicle_assignment->id)->first();
                if(isset($mileage)){
                    $mileage->vehicle_assignment_id = $vehicle_assignment->id;
                    $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $mileage->mileage = $this->starting_odometer;
                    $mileage->date = $this->start_date;
                    $mileage->category = "Vehicle Assignment";
                    $mileage->update();
                }
                }
                $this->dispatchBrowserEvent('hide-vehicle_assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"employee - vehicle vehicle_assignment Updated Successfully!!"
                ]);

            }else {
                $this->dispatchBrowserEvent('hide-vehicle_assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"vehicle_assignment not found!!"
                ]);
            }
        }

        public function unAssignment($id){
            $vehicle_assignment = VehicleAssignment::find($id);
            $this->vehicle_assignment_id = $vehicle_assignment->id;
            $this->dispatchBrowserEvent('show-unAssignmentModal');
        }

        public function updateAssignment(){
           $vehicle_assignment = VehicleAssignment::find($this->vehicle_assignment_id);
           $vehicle_assignment->ending_odometer = $this->ending_odometer;
           $vehicle_assignment->end_date = $this->end_date;
           $vehicle_assignment->status = 0;
           $vehicle_assignment->update();
           
           $this->dispatchBrowserEvent('hide-unAssignmentModal');
           $this->resetInputFields();
           $this->dispatchBrowserEvent('alert',[
               'type'=>'success',
               'message'=>"Employee - Vehicle UnAssignment Successful!!"
           ]);
        }

    public function render()
    {
        $this->vehicle_assignments = VehicleAssignment::latest()->get();
        return view('livewire.vehicle-assignments.index',[
            'vehicle_assignments' => $this->vehicle_assignments,
            'starting_odometer' =>  $this->starting_odometer
        ]);
    }
}
