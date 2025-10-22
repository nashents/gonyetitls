<?php

namespace App\Http\Livewire\VehicleAssignments;


use App\Models\Mileage;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Transporter;
use Livewire\WithPagination;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

     use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
  
    public $transporters;
    public $selectedTransporter;
    private $assignments;
    public $assignment;
    public $assignment_id;
    public $vehicles;
    public $selectedVehicle;
    public $employees;
    public $employee_id;
    public $starting_odometer;
    public $ending_odometer;
    public $end_date;
    public $start_date;
    public $comments;
    public $user_id;
    public $status;


    public function mount(){
     
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->assignments = VehicleAssignment::latest()->get();
        $this->vehicles = collect();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->starting_odometer = 0;
    }

    private function resetInputFields(){
        $this->selectedTransporter = "";
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
        'selectedVehicle' => 'required',
        'employee_id' => 'required',
        'starting_odometer' => 'required',
        'start_date' => 'required',
        'comments' => 'nullable|string',
    ];

     public function updatedSelectedTransporter($id){
        if (!is_null($id)) {
            $this->vehicles = Vehicle::query()
            ->where('transporter_id', $id)
            ->where('status', 1)
            ->where('service', 0)
            ->orderBy('registration_number', 'asc')
            ->get();

        
        }
    }
  
    public function updatedSelectedVehicle($vehicle){
        if (!is_null($vehicle)) {
            $this->starting_odometer = Vehicle::find($vehicle)->mileage;
        }
    }

    public function store(){
        $assignment = new VehicleAssignment;
        $assignment->user_id = Auth::user()->id;
        $assignment->employee_id = $this->employee_id;
        $assignment->vehicle_id = $this->selectedVehicle;
        $assignment->transporter_id = $this->selectedTransporter;
        $assignment->starting_odometer = $this->starting_odometer;
        $assignment->start_date = $this->start_date;
        $assignment->comments = $this->comments;
        $assignment->status = 1;
        $assignment->save();

        $mileage = new Mileage;
        $mileage->user_id = Auth::user()->id;
        $mileage->assignment_id = $assignment->id;
        $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
        $mileage->mileage = $this->starting_odometer;
        $mileage->date = $this->start_date;
        $mileage->category = "Vehicle Assignment";
        $mileage->save();

        $this->dispatchBrowserEvent('hide-assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Employee - Vehicle Assigned Successful!!"
        ]);
      

    }

    public function edit($id){
        $assignment = VehicleAssignment::find($id);
        $this->user_id = $assignment->user_id;
        $this->selectedTransporter = $assignment->transporter_id;
        $this->selectedVehicle = $assignment->vehicle_id;
        $this->employee_id = $assignment->employee_id;
        $this->vehicles = Vehicle::query()
            ->where('transporter_id', $this->selectedTransporter)
            ->where('status', 1)
            ->where('service', 0)
            ->orderBy('registration_number', 'asc')
            ->get();

        $this->starting_odometer = $assignment->starting_odometer;
        $this->ending_odometer = $assignment->ending_odometer;
        $this->start_date = $assignment->start_date;
        $this->end_date = $assignment->end_date;
        $this->comments = $assignment->comments;
        $this->status = $assignment->status;
        $this->assignment_id = $assignment->id;
        $this->dispatchBrowserEvent('show-assignmentEditModal');

        }


        public function update()
        {
            if ($this->assignment_id) {
                $assignment = VehicleAssignment::find($this->assignment_id);
                $assignment->update([
                    'user_id' => Auth::user()->id,
                    'transporter_id' => $this->selectedTransporter,
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
                    $mileage->assignment_id = $assignment->id;
                    $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $mileage->mileage = $this->ending_odometer;
                    $mileage->date = $this->end_date;
                    $mileage->category = "Vehicle Assignment";
                    $mileage->save();
                }else{
                $mileage =  Mileage::where('assignment_id',$assignment->id)->first();
                if(isset($mileage)){
                    $mileage->assignment_id = $assignment->id;
                    $mileage->vehicle_id = $this->selectedVehicle ? $this->selectedVehicle : Null;
                    $mileage->mileage = $this->starting_odometer;
                    $mileage->date = $this->start_date;
                    $mileage->category = "Vehicle Assignment";
                    $mileage->update();
                }
                }
                $this->dispatchBrowserEvent('hide-assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Employee - Vehicle Assignment Updated Successfully!!"
                ]);

            }else {
                $this->dispatchBrowserEvent('hide-assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Assignment not found!!"
                ]);
            }
        }

        public function unAssignment($id){
            $assignment = VehicleAssignment::find($id);
            $this->assignment_id = $assignment->id;
            $this->dispatchBrowserEvent('show-unAssignmentModal');
        }

        public function updateAssignment(){
           $assignment = VehicleAssignment::find($this->assignment_id);
           $assignment->ending_odometer = $this->ending_odometer;
           $assignment->end_date = $this->end_date;
           $assignment->status = 0;
           $assignment->update();
           
           $this->dispatchBrowserEvent('hide-unAssignmentModal');
           $this->resetInputFields();
           $this->dispatchBrowserEvent('alert',[
               'type'=>'success',
               'message'=>"Employee - Vehicle UnAssignment Successful!!"
           ]);
        }

    public function render()
    {

          if (filled($this->search)) {
            return view('livewire.vehicle-assignments.index',[
                'assignments' => VehicleAssignment::where('status',True)->where('start_date','like', '%'.$this->search.'%')
                ->orWhere('end_date','like', '%'.$this->search.'%')
                ->orWhere('starting_odometer','like', '%'.$this->search.'%')
                ->orWhere('ending_odometer','like', '%'.$this->search.'%')
                ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                     ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('employee', function ($query) {
                        $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                })->paginate(10),
                'starting_odometer' =>  $this->starting_odometer
            ]);
        }else{
             return view('livewire.vehicle-assignments.index',[
                'assignments' => VehicleAssignment::where('status',True)->latest()->paginate(10),
                'starting_odometer' =>  $this->starting_odometer
            ]);
        }
        
       
    }
}
