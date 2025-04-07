<?php

namespace App\Http\Livewire\Logs;

use App\Models\Log;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use Livewire\WithFileUploads;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithFileUploads;
    
    public $employees;
    public $selectedEmployee;
    public $vehicles;
    public $selectedVehicle;
    public $logs;
    public $log_id;
    public $from;
    public $to;
    public $distance;
    public $starting_mileage_image;
    public $selected_starting_mileage_image;
    public $starting_mileage;
    public $ending_mileage_image;
    public $selected_ending_mileage_image;
    public $ending_mileage;
    public $departure_datetime;
    public $arrival_datetime;
    public $notes;

    public $searchVehicle;
    
    protected $queryString = ['searchVehicle'];

    public function mount(){
        $this->logs = Log::where('employee_id',Auth::user()->employee->id)->latest()->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->where('status', 1)
        ->where('service',0)->get();
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->selectedEmployee = Auth::user()->employee->id;
        $assignment = VehicleAssignment::where('employee_id', $this->selectedEmployee)->get()->first();
        if (isset($assignment)) {
            $this->selectedVehicle = $assignment->vehicle_id;
            $this->starting_mileage = Vehicle::find($this->selectedVehicle)->mileage;
        }
    }

    public function updatedSelectedVehicle($vehicle){
        if (!is_null($vehicle)) {
            $this->starting_mileage = Vehicle::find($vehicle)->mileage;
        }
    }
    public function updatedSelectedEmployee($employee){
        if (!is_null($employee)) {
            $assignment = VehicleAssignment::where('employee_id',$employee)->get()->first();
            if (isset($assignment)) {
                $this->selectedVehicle = $assignment->vehicle_id;
                $this->starting_mileage = Vehicle::find($this->selectedVehicle)->mileage;
            }
        }
    }

    
    public function logNumber(){
       
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

            $log = Log::orderBy('id', 'desc')->first();

        if (!$log) {
            $log_number =  $initials .'TL'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $log->id + 1;
            $log_number =  $initials .'TL'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $log_number;


    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedVehicle' => 'required',
        'selectedEmployee' => 'required',
        'starting_mileage' => 'required',
        'departure_datetime' => 'required',
        'from' => 'required',
        'to' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedVehicle = '';
        $this->selectedEmployee = '';
        $this->arrival_datetime = '';
        $this->departure_datetime = '';
        $this->from = '';
        $this->to = '';
        $this->starting_mileage = '';
        $this->ending_mileage = '';
        $this->distance = '';
    }

    public function store(){
        try{
            if (isset($this->selectedVehicle)) {
            if (isset($this->starting_mileage_image)) {
                    $image = $this->starting_mileage_image;
                    $fileNameWithExt = $image->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $image->getClientOriginalExtension();
                    //file name to store
                    $startingfileNameToStore= $filename.'_'.time().'.'.$extention;
                    $image->storeAs('/uploads', $startingfileNameToStore, 'path');
                   
    
            }
            if (isset($this->ending_mileage_image)) {
                    $image = $this->ending_mileage_image;
                    $fileNameWithExt = $image->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $image->getClientOriginalExtension();
                    //file name to store
                    $endingfileNameToStore= $filename.'_'.time().'.'.$extention;
                    $image->storeAs('/uploads', $endingfileNameToStore, 'path');    
            }

        $log = new Log;
        $log->user_id = Auth::user()->id;
        $log->employee_id = $this->selectedEmployee;
        $log->log_number = $this->logNumber();
        $log->vehicle_id = $this->selectedVehicle;
        $log->starting_mileage = $this->starting_mileage;
        if (isset($startingfileNameToStore)) {
            $log->starting_mileage_image = $startingfileNameToStore;
        }
        if (isset($endingfileNameToStore)) {
            $log->ending_mileage_image = $endingfileNameToStore;
        }
      
        $log->ending_mileage = $this->ending_mileage;
        $log->departure_datetime = $this->departure_datetime;
        $log->arrival_datetime = $this->arrival_datetime;
        $log->notes = $this->notes;
        $log->from = $this->from;
        $log->to = $this->to;
        if ((isset($this->starting_mileage) && $this->starting_mileage != "") && (isset($this->ending_mileage) && $this->ending_mileage !="" )) {
            $this->distance = $this->ending_mileage - $this->starting_mileage;
            $log->distance = $this->distance;
        }

        $log->save();

        if(isset($log->vehicle_id)){
            $vehicle = Vehicle::find($log->vehicle_id);
            $current_mileage  = $vehicle->mileage;
            if ($log->starting_mileage > $current_mileage ) {
                $vehicle->mileage = $log->starting_mileage;
            }
          
            $vehicle->update();
        }

        $this->dispatchBrowserEvent('hide-logModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Log Created Successfully!!"
        ]);
        }else{
            $this->dispatchBrowserEvent('hide-logModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Vehicle not assigned to employee!!"
            ]);
        }

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating log!!"
        ]);
    }
    }

    public function edit($id){
        $this->log = Log::find($id);
        $this->from = $this->log->from;
        $this->to = $this->log->to;
        $this->departure_datetime = $this->log->departure_datetime;
        $this->arrival_datetime = $this->log->arrival_datetime;
        $this->starting_mileage = $this->log->starting_mileage;
        $this->ending_mileage = $this->log->ending_mileage;
        $this->distance = $this->log->distance;
        $this->notes = $this->log->notes;
        $this->selectedEmployee = $this->log->employee_id;
        $this->selectedVehicle = $this->log->vehicle_id;
        $this->log_id = $this->log->id;
        $this->dispatchBrowserEvent('show-logEditModal');
    }
    
    public function editUpdate($id){
        
        $this->log = Log::find($id);
        $this->from = $this->log->from;
        $this->to = $this->log->to;
        $this->departure_datetime = $this->log->departure_datetime;
        $this->arrival_datetime = $this->log->arrival_datetime;
        $this->starting_mileage = $this->log->starting_mileage;
        $this->ending_mileage = $this->log->ending_mileage;
        $this->distance = $this->log->distance;
        $this->notes = $this->log->notes;
        $this->selectedEmployee = $this->log->employee_id;
        $this->selectedVehicle = $this->log->vehicle_id;
        $this->log_id = $this->log->id;
        $this->dispatchBrowserEvent('show-logUpdateModal');
    }


    public function update(){
        try{
            $log =  Log::find($this->log_id);
            $log->employee_id = $this->selectedEmployee;
            $log->vehicle_id = $this->selectedVehicle;
            $log->starting_mileage = $this->starting_mileage;
            $log->ending_mileage = $this->ending_mileage;
            $log->departure_datetime = $this->departure_datetime;
            $log->arrival_datetime = $this->arrival_datetime;
            $log->notes = $this->notes;
            $log->from = $this->from;
            $log->to = $this->to;
            if ((isset($this->starting_mileage) && $this->starting_mileage != "") && (isset($this->ending_mileage) && $this->ending_mileage !="" )) {
                $this->distance = $this->ending_mileage - $this->starting_mileage;
                $log->distance = $this->distance;
            }
    
            $log->update();
            
            $this->dispatchBrowserEvent('hide-logEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Log Updated Successfully!!"
            ]);
    
            }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating log!!"
            ]);
        }
    }
    public function updateLog(){
        try{
            $log =  Log::find($this->log_id);
            if ($log->status == 1) {
            
            $log->employee_id = $this->selectedEmployee;
            $log->vehicle_id = $this->selectedVehicle;
            $log->starting_mileage = $this->starting_mileage;
            $log->ending_mileage = $this->ending_mileage;
            $log->departure_datetime = $this->departure_datetime;
            $log->arrival_datetime = $this->arrival_datetime;
            $log->notes = $this->notes;
            $log->from = $this->from;
            $log->to = $this->to;
            if ((isset($this->starting_mileage) && $this->starting_mileage != "") && (isset($this->ending_mileage) && $this->ending_mileage !="" )) {
                $this->distance = $this->ending_mileage - $this->starting_mileage;
                $log->distance = $this->distance;
            }
    
            $log->status = 0;
            $log->update();

            if ((isset($this->ending_mileage) && $this->ending_mileage !="") &&  (isset($this->distance) &&  $this->distance !="")) {
                $vehicle = Vehicle::find($this->selectedVehicle);
                $current_mileage = $vehicle->mileage;
                $vehicle->mileage = $current_mileage + $this->distance;
                $vehicle->update();
            }
    
            $this->dispatchBrowserEvent('hide-logUpdateModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Log Updated Successfully!!"
            ]);
              
            }else {
                $this->dispatchBrowserEvent('hide-logUpdateModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Trip Log Closed Already!!"
                ]);
            }
    
            }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating log!!"
            ]);
        }
    }

    public function render()
    {

        if (isset($this->searchVehicle)) {
            $this->vehicles = Vehicle::query()->with('vehicle_make:id,name','vehicle_model:id,name')   ->where('status', 1)
            ->where('service',0)->where('registration_number', 'like', '%'.$this->searchVehicle.'%')->get();  
        }

        if ((isset($this->starting_mileage) && $this->starting_mileage != "") && (isset($this->ending_mileage) && $this->ending_mileage !="" )) {
            $this->distance = $this->ending_mileage - $this->starting_mileage;
        }
        $this->logs = Log::where('employee_id',Auth::user()->employee->id)->latest()->get();
        return view('livewire.logs.index',[
            'logs' => $this->logs, 
            'distance' => $this->distance, 
        ]);
    }
}
