<?php

namespace App\Http\Livewire\Checklists;

use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Checklist;
use App\Models\Assignment;
use App\Models\ChecklistItem;
use App\Models\TyreAssignment;
use App\Models\ChecklistResult;
use App\Models\CategoryChecklist;
use App\Models\ChecklistCategory;
use App\Models\VehicleAssignment;
use Illuminate\Support\Facades\DB;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $category_checklists;
    public $category_checklist_id;
    public $checklist_items;
    public $checklist_item_id;
    public $checklist_categories;
    public $checklist_category;
    public $selectedChecklistCategory;
    public $checklist_sub_categories;
    public $checklist_sub_category_id;
    public $checklists;
    public $checklist_id;
    public $trailers;
    public $trailer_id;
    public $vehicles;
    public $vehicle_id;
    public $drivers;
    public $driver_id;
    public $employees;
    public $employee_id;
    public $horses;
    public $horse_id;
    public $description;
    public $date;
    public $type = "Horse";
    public $mileage;
    public $tyre_assignments;
    public $next_inspection_at;



    public $yes = '1';
    public $no = '0';

    public $hours;
    public $cost;
    public $status = [];
    public $inputs = [];
    
    public $comments;
    public $tread_depth_mm = [];
    public $pressure_psi = [];
    public $valve_ok = [];
    public $sidewall_damage = [];
    public $wear_pattern = [];
    public $rim_condition = [];
    public $wheel_nuts_torqued = [];
    public $axle_match = [];
    public $action_required = [];
    public $rating = [];
    public $notes = [];
    public $tyre_assignment_id = [];
 
 

    public function mount(){
        $this->checklist_categories = ChecklistCategory::orderBy('name','asc')->get();
        $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
        $this->checklist_items = collect();
        $this->category_checklists = collect();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->drivers = Driver::query()
                            ->join('employees', 'drivers.employee_id', '=', 'employees.id')
                            ->orderBy('employees.name', 'asc')
                            ->orderBy('employees.surname', 'asc')
                            ->with('employee') // keep eager loading intact
                            ->select('drivers.*') // prevent column conflicts
                            ->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
    }

    
    public function updatedSelectedChecklistCategory($id){
        if (!is_null($id)) {
            $this->checklist_category = ChecklistCategory::find($id);
        }
    }

    private function resetInputFields(){
        $this->date = '';
        $this->horse_id = '';
        $this->employee_id = '';
        $this->vehicle_id = '';
        $this->trailer_id = '';
        $this->description = '';
        $this->status = '';
        $this->comments = '';
    }

    public function refresh($category){

        if($category == "checklist_categories"){
            $this->checklists = Checklist::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Tracking Groups Refreshed Successfully!!."
            ]);
        }
       
        
    }

    
    public function checklistNumber(){
       
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

            $checklist = Checklist::orderBy('id', 'desc')->first();

        if (!$checklist) {
            $checklist_number =  $initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $checklist->id + 1;
            $checklist_number =  $initials .'I'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $checklist_number;


    }

    public function updatedHorseId($id){
        if(!is_null($id)){
           $horse = Horse::find($id);
           $this->mileage = $horse->mileage;
           $assignment = Assignment::where('horse_id',$id)->where('status',1)->first();
           if ($assignment) {
                $this->driver_id = $assignment->driver_id;
           }
        }
    }
    public function updatedVehicleId($id){
        if(!is_null($id)){
           $vehicle = Vehicle::find($id);
           $this->mileage = $vehicle->mileage;
           $assignment = VehicleAssignment::where('vehicle_id',$id)->where('status',1)->first();
           if ($assignment) {
                $this->employee_id = $assignment->employee_id;
           }
        }
    }
    public function updatedTrailerId($id){
        if(!is_null($id)){
           $trailer = Trailer::find($id);
           $this->mileage = $trailer->mileage;
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

    public function store(){

        DB::transaction(function () {

        try{

            $checklist = new Checklist;
            $checklist->user_id = Auth::user()->id;
            $checklist->checklist_number = $this->checklistNumber();
            $checklist->checklist_category_id = $this->selectedChecklistCategory;
            $checklist->employee_id = $this->employee_id;
            $checklist->driver_id = $this->driver_id;
            $checklist->vehicle_id = $this->vehicle_id;
            $checklist->trailer_id = $this->trailer_id;
            $checklist->horse_id = $this->horse_id;
            $checklist->date = $this->date;
            $checklist->next_inspection_at = $this->next_inspection_at;
            $checklist->comments = $this->description;
            $checklist->mileage = $this->mileage;
            $checklist->save();

            if ($this->checklist_category->name != "Tyre Inspection") {

                    if (isset($this->status)) {
                        foreach ($this->status as $key => $value) {
                            $result = new ChecklistResult;
                            $result->checklist_id = $checklist->id;
                            if (isset($this->status[$key])) {
                                $result->status = $this->status[$key];
                            }
                            if (isset($this->comments[$key])) {
                                $result->comments = $this->comments[$key];
                            }
                            $result->category_checklist_id = $key;
                            $category_checklist = CategoryChecklist::find($key);
                            $result->checklist_item_id = $category_checklist?->checklist_item_id;
                            $result->save();
                        }
                    }
            }else{
            
                if (isset($this->tread_depth_mm)) {
                    foreach ($this->tread_depth_mm as $key => $value) {

                        $result = new ChecklistResult;
                        $result->checklist_id = $checklist->id;
                    
                        if (isset($this->tread_depth_mm[$key])) {
                            $result->tread_depth_mm = $this->tread_depth_mm[$key];
                        }
                        if (isset($this->tyre_assignment_id[$key])) {
                            $result->tyre_assignment_id = $this->tyre_assignment_id[$key];
                        }
                        if (isset($this->pressure_psi[$key])) {
                            $result->pressure_psi = $this->pressure_psi[$key];
                        }
                        if (isset($this->valve_ok[$key])) {
                            $result->valve_ok = $this->valve_ok[$key];
                        }
                        if (isset($this->sidewall_damage[$key])) {
                            $result->sidewall_damage = $this->sidewall_damage[$key];
                        }
                        if (isset($this->wear_pattern[$key])) {
                            $result->wear_pattern = $this->wear_pattern[$key];
                        }
                        if (isset($this->rim_condition[$key])) {
                            $result->rim_condition = $this->rim_condition[$key];
                        }
                        if (isset($this->wheel_nuts_torqued[$key])) {
                            $result->wheel_nuts_torqued = $this->wheel_nuts_torqued[$key];
                        }
                        if (isset($this->axle_match[$key])) {
                            $result->axle_match = $this->axle_match[$key];
                        }
                        if (isset($this->action_required[$key])) {
                            $result->action_required = $this->action_required[$key];
                        }
                        if (isset($this->rating[$key])) {
                            $result->rating = $this->rating[$key];
                        }
                        if (isset($this->notes[$key])) {
                            $result->notes = $this->notes[$key];
                        }
                        $result->tyre_assignment_id = $key;
                        $tyre_assignment = TyreAssignment::find($key);
                        $result->tyre_id = $tyre_assignment?->tyre_id;
                        $result->save();
                    }
                }
            }
     
            if ($this->type == "Horse") {
                $horse = Horse::find($this->horse_id);
                if ($this->mileage > $horse->mileage) {
                    $horse->mileage = $this->mileage;
                    $horse->update();
                }
            }elseif($this->type == "Vehicle"){
                $vehicle = Vehicle::find($this->vehicle_id);
                if ($this->mileage > $vehicle->mileage) {
                    $vehicle->mileage = $this->mileage;
                    $vehicle->update();
                }
            }elseif($this->type == "Trailer"){
                $trailer = Trailer::find($this->trailer_id);
                if ($this->mileage > $trailer->mileage) {
                    $trailer->mileage = $this->mileage;
                    $trailer->update();
                }
            }

            $this->dispatchBrowserEvent('hide-checklistModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Completed Successfully!!"
            ]);

            return redirect(route('checklists.index'));

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating checklist!!"
        ]);
        }
        });
    }

    public function render()
    {

          if ($this->checklist_category && $this->checklist_category->name == "Tyre Inspection") {
            

                if ($this->type == "Horse" && isset($this->horse_id)) {
                  
                    $this->tyre_assignments = TyreAssignment::query()
                    ->with('tyre')               // eager load tyre details
                    ->where('horse_id', $this->horse_id)  // or $horseId
                    ->where('status',1)                   // status = 1
                    ->orderBy('axle')            // optional: stable ordering
                    ->orderBy('position')        // optional
                    ->get();

                }elseif ($this->type == "Vehicle" && isset($this->vehicle_id)) {

                    $this->tyre_assignments = TyreAssignment::query()
                    ->with('tyre')               // eager load tyre details
                    ->where('vehicle_id', $this->vehicle_id)  // or $vehicle_id
                    ->where('status',1)                   // status = 1
                    ->orderBy('axle')            // optional: stable ordering
                    ->orderBy('position')        // optional
                    ->get();

                }elseif ($this->type == "Trailer" && isset($this->trailer_id)) {
                      $this->tyre_assignments = TyreAssignment::query()
                    ->with('tyre')               // eager load tyre details
                    ->where('trailer_id', $this->trailer_id)  // or $trailer_id
                    ->where('status',1)                   // status = 1
                    ->orderBy('axle')            // optional: stable ordering
                    ->orderBy('position')        // optional
                    ->get();
                }

                // if ( $this->tyre_assignments) {
                //     foreach ($this->tyre_assignments as $ta) {
                //         $id = $ta->tyre->id;
                //         $this->tyre_assignment_id[$id] = $ta->id;
                //     }
                // }
            }

        if (isset($this->selectedChecklistCategory)) {

            if ($this->checklist_category->name == "Stock on board") {

                if ($this->type === "Horse" && filled($this->horse_id)) {
                   $this->category_checklists = CategoryChecklist::query()->where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('horse_id',$this->horse_id)->get();
                }elseif ($this->type === "Vehicle" && filled($this->vehicle_id)) {
                   $this->category_checklists = CategoryChecklist::query()->where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('vehicle_id',$this->vehicle_id)->get();
                }elseif ($this->type === "Trailer" && filled($this->trailer_id)) {
                    $this->category_checklists = CategoryChecklist::query()->where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('trailer_id',$this->trailer_id)->get();
                }

            }else{
                 $this->category_checklists = CategoryChecklist::query()->where('checklist_category_id',$this->selectedChecklistCategory)->get();
            }
           
        }
      
        return view('livewire.checklists.create',[
            'category_checklists' => $this->category_checklists
        ]);
    }
}
