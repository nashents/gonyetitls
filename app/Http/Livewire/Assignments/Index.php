<?php

namespace App\Http\Livewire\Assignments;

use App\Models\Horse;
use App\Models\Driver;
use App\Models\Mileage;
use Livewire\Component;
use App\Models\Assignment;
use App\Models\Transporter;
use Livewire\WithPagination;
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
    public $horses;
    public $selectedHorse;
    public $drivers;
    public $driver_id;
    public $starting_odometer;
    public $ending_odometer;
    public $end_date;
    public $start_date;
    public $comments;
    public $user_id;
    public $status;


    public function mount(){
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->horses = collect();
        $this->drivers = collect();
        $this->starting_odometer = 0;
    }

   

    private function resetInputFields(){
        $this->selectedTransporter = "";
        $this->selectedHorse = "";
        $this->driver_id = "";
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
        'selectedHorse' => 'required',
        'driver_id' => 'required',
        'selectedTransporter' => 'required',
        'starting_odometer' => 'required',
        'start_date' => 'required',
        'comments' => 'nullable|string',
    ];
    public function updatedSelectedTransporter($id){
        if (!is_null($id)) {
            $this->horses = Horse::query()
            ->with(['horse_make:id,name', 'horse_model:id,name'])
            ->where('transporter_id', $id)
            ->where('status', 1)
            ->where('service', 0)
           ->whereDoesntHave('assignments', function ($query) {
                $query->where('status', True); // or ->whereNull('end_date')
            })
            ->orderBy('registration_number', 'asc')
            ->get();

            $this->drivers = Driver::query()
            ->with('employee:id,name,surname')
            ->where('drivers.transporter_id', $id)   // ✅ qualify
            ->where('drivers.status', 1)             // ✅ qualify
            ->whereDoesntHave('assignments', function ($query) {
                $query->where('assignments.status', true); // ✅ qualify inside subquery too
                // or if “active” means no end date:
                // $query->whereNull('assignments.end_date');
            })
            ->join('employees', 'drivers.employee_id', '=', 'employees.id') // for sorting
            ->orderBy('employees.name', 'asc')
            ->orderBy('employees.surname', 'asc')
            ->select('drivers.*') // avoid column conflicts
            ->get();
        }
    }
    public function updatedSelectedHorse($horse){
        if (!is_null($horse)) {
            $this->starting_odometer = Horse::find($horse)->mileage;
        }
    }

    public function store(){

        $assignment = new Assignment;
        $assignment->user_id = Auth::user()->id;
        $assignment->transporter_id = $this->selectedTransporter;
        $assignment->driver_id = $this->driver_id;
        $assignment->horse_id = $this->selectedHorse;
        $assignment->starting_odometer = $this->starting_odometer;
        $assignment->start_date = $this->start_date;
        $assignment->comments = $this->comments;
        $assignment->status = 1;
        $assignment->save();

        $mileage = new Mileage;
        $mileage->user_id = Auth::user()->id;
        $mileage->assignment_id = $assignment->id;
        $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
        $mileage->mileage = $this->starting_odometer;
        $mileage->date = $this->start_date;
        $mileage->category = "Horse Assignment";
        $mileage->save();

        $this->dispatchBrowserEvent('hide-assignmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Driver - Horse Assignment Successful!!"
        ]);
      

    }

    public function edit($id){
        $assignment = Assignment::find($id);
        $this->user_id = $assignment->user_id;
        $this->selectedTransporter = $assignment->transporter_id;
        $this->selectedHorse = $assignment->horse_id;
        $this->driver_id = $assignment->driver_id;
        $this->starting_odometer = $assignment->starting_odometer;
        $this->ending_odometer = $assignment->ending_odometer;
        $this->start_date = $assignment->start_date;
        $this->end_date = $assignment->end_date;
        $this->comments = $assignment->comments;
        $this->horses = Horse::query()
            ->with(['horse_make:id,name', 'horse_model:id,name'])
            ->where('transporter_id', $this->selectedTransporter)
            ->where('status', 1)
            ->where('service', 0)
           ->whereDoesntHave('assignments', function ($query) {
                $query->where('status', True); // or ->whereNull('end_date')
            })
            ->orderBy('registration_number', 'asc')
            ->get();

            $this->drivers = Driver::query()
            ->with('employee:id,name,surname')
            ->where('drivers.transporter_id',  $this->selectedTransporter)   // ✅ qualify
            ->where('drivers.status', 1)             // ✅ qualify
            ->whereDoesntHave('assignments', function ($query) {
                $query->where('assignments.status', true); // ✅ qualify inside subquery too
                // or if “active” means no end date:
                // $query->whereNull('assignments.end_date');
            })
            ->join('employees', 'drivers.employee_id', '=', 'employees.id') // for sorting
            ->orderBy('employees.name', 'asc')
            ->orderBy('employees.surname', 'asc')
            ->select('drivers.*') // avoid column conflicts
            ->get();
        $this->status = $assignment->status;
        $this->assignment_id = $assignment->id;
        $this->dispatchBrowserEvent('show-assignmentEditModal');

        }


        public function update()
        {
            if ($this->assignment_id) {
                $assignment = Assignment::find($this->assignment_id);
                $assignment->update([
                    'user_id' => Auth::user()->id,
                    'horse_id' => $this->selectedHorse,
                    'transporter_id' => $this->selectedTransporter,
                    'driver_id' => $this->driver_id,
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
                    $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                    $mileage->mileage = $this->ending_odometer;
                    $mileage->date = $this->end_date;
                    $mileage->category = "Horse Assignment";
                    $mileage->save();
                }else{
                $mileage =  Mileage::where('assignment_id',$assignment->id)->first();
                if(isset($mileage)){
                    $mileage->assignment_id = $assignment->id;
                    $mileage->horse_id = $this->selectedHorse ? $this->selectedHorse : Null;
                    $mileage->mileage = $this->starting_odometer;
                    $mileage->date = $this->start_date;
                    $mileage->category = "Horse Assignment";
                    $mileage->update();
                }
                }
                $this->dispatchBrowserEvent('hide-assignmentEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Driver - Horse Assignment Updated Successfully!!"
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
            $assignment = Assignment::find($id);
            $this->assignment_id = $assignment->id;
            $this->starting_odometer = $assignment->starting_odometer;
            $this->dispatchBrowserEvent('show-unAssignmentModal');
        }

        public function updateAssignment(){
           $assignment = Assignment::find($this->assignment_id);
           $assignment->ending_odometer = $this->ending_odometer;
           $assignment->end_date = $this->end_date;
           $assignment->status = 0;
           $assignment->update();
           
           $this->dispatchBrowserEvent('hide-unAssignmentModal');
           $this->resetInputFields();
           $this->dispatchBrowserEvent('alert',[
               'type'=>'success',
               'message'=>"Driver - Horse UnAssignment Successful!!"
           ]);
        }

    public function render()
    {
        if (filled($this->search)) {
            return view('livewire.assignments.index',[
                'assignments' => Assignment::where('status',True)
                ->where('start_date','like', '%'.$this->search.'%')
                ->orWhere('end_date','like', '%'.$this->search.'%')
                ->orWhere('starting_odometer','like', '%'.$this->search.'%')
                ->orWhere('ending_odometer','like', '%'.$this->search.'%')
                ->orWhereHas('transporter', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->search.'%')
                                     ->orWhere('fleet_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('driver', function ($query) {
                        $query->whereHas('employee', function ($subQuery) {
                            $subQuery->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->search.'%');
                        });
                })->paginate(10),
                'starting_odometer' =>  $this->starting_odometer
            ]);
        }else{
             return view('livewire.assignments.index',[
                'assignments' => Assignment::where('status',True)->latest()->paginate(10),
                'starting_odometer' =>  $this->starting_odometer
            ]);
        }
       
    }
}
