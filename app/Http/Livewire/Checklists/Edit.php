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
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public $category_checklists;
    public $category_checklist_id;
    public $checklist_items;
    public $checklist_item_id = [];
    public $tyre_id = [];
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
    public $type;
    public $mileage;
    public $checklist_category_id;
    public $next_inspection_at;
    public $tyre_assignments;


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
 
 

    public function mount($id){

        $checklist = Checklist::find($id);
        $this->checklist_id = $id;
        if ($checklist->horse_id) {
            $this->type = 'Horse';
        }
        elseif($checklist->vehicle_id){
             $this->type = 'Vehicle';
        }
        elseif($checklist->trailer_id){
             $this->type = 'Trailer';
        }

        $this->employee_id = $checklist->employee_id;
        $this->driver_id = $checklist->driver_id;
        $this->horse_id = $checklist->horse_id;
        $this->vehicle_id = $checklist->vehicle_id;
        $this->trailer_id = $checklist->trailer_id;
        $this->selectedChecklistCategory = $checklist->checklist_category_id;
        $this->checklist_category = $checklist->checklist_category;
        $this->date = $checklist->date;
        $this->description = $checklist->comments;
        $this->mileage = $checklist->mileage;
        $this->next_inspection_at = $checklist->next_inspection_at;

        $results = $checklist->checklist_results; // ->with('checklist_item') if you need it
        foreach ($results as $result) {

            if ($checklist->checklist_category->name == "Tyre Inspection") {
                    $id = $result->tyre_id;
            }else{
                 $id = $result->checklist_item_id;
               
            }
            
            $this->status[$id] = $result->status; // e.g., 'Yes'/'No' or 1/0
            $this->comments[$id] = $result->comments; // e.g., 'Yes'/'No' or 1/0

            $this->tread_depth_mm[$id] = $result->tread_depth_mm ?? '';
            $this->pressure_psi[$id] = $result->pressure_psi ?? '';
            $this->valve_ok[$id] = $result->valve_ok ?? '';
            $this->sidewall_damage[$id] = $result->sidewall_damage ?? '';
            $this->wear_pattern[$id] = $result->wear_pattern ?? '';
            $this->rim_condition[$id] = $result->rim_condition ?? '';
            $this->wheel_nuts_torqued[$id] = $result->wheel_nuts_torqued ?? '';
            $this->axle_match[$id] = $result->axle_match ?? '';
            $this->action_required[$id] = $result->action_required ?? '';
            $this->rating[$id] = $result->rating ?? '';
            $this->notes[$id] = $result->notes ?? '';
            $this->tyre_assignment_id[$id] = $result->tyre_assignment_id ?? '';

            // $checklist->checklist_category->name == "Tyre Inspection" ? $this->tyre_id[$id] : $this->checklist_item_id[$id] = $id;  // optional hidden field you had
        }

        $this->checklist_categories = ChecklistCategory::orderBy('name','asc')->get();
        $this->checklist_sub_categories = ChecklistSubCategory::orderBy('name','asc')->get();
        $this->checklist_items = collect();
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

    
    public function updatedSelectedChecklistCategory($id){
        if (!is_null($id)) {
            $this->checklist_category_id = $id;
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

    
   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

    public function update(){
  
        $checklist =  Checklist::find($this->checklist_id);
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
        $checklist->update();

        if ($this->checklist_category->name != "Tyre Inspection") {
            if (isset($this->status)) {

                foreach ($this->status as $key => $value) {
                 
                    $category_checklist = CategoryChecklist::find($key);
                       
                    ChecklistResult::updateOrCreate(
                    [
                        'checklist_id'      => $checklist->id,
                        'category_checklist_id' => $key,
                    ],
                    [
                        'status'   => $this->status[$key],
                        'comments' => $this->comments[$key],
                        'checklist_item_id' => $category_checklist?->checklist_item_id,
                    ]);
            
                }
                
            }
              }else{

                if (isset($this->tread_depth_mm)) {
                    foreach ($this->tread_depth_mm as $key => $value) {

                        ChecklistResult::updateOrCreate(
                        [
                            'checklist_id'      => $checklist->id,
                            'tyre_id' => $key,
                        ],
                        [
                            'tread_depth_mm'   => $this->tread_depth_mm[$key],
                            'tyre_assignment_id' => $this->tyre_assignment_id[$key],
                            'pressure_psi' => $this->pressure_psi[$key],
                            'valve_ok' => $this->valve_ok[$key],
                            'sidewall_damage' => $this->sidewall_damage[$key],
                            'wear_pattern' => $this->wear_pattern[$key],
                            'rim_condition' => $this->rim_condition[$key],
                            'wheel_nuts_torqued' => $this->wheel_nuts_torqued[$key],
                            'axle_match' => $this->axle_match[$key],
                            'action_required' => $this->action_required[$key],
                            'rating' => $this->rating[$key],
                            'notes' => $this->notes[$key],
                        ]);
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

                if ( $this->tyre_assignments) {
                    foreach ($this->tyre_assignments as $ta) {
                        $id = $ta->tyre->id;
                        $this->tyre_assignment_id[$id] = $ta->id;
                    }
                }
            }

        if (isset($this->selectedChecklistCategory)) {

            if ($this->checklist_category->name == "Stock on board") {
                if ($this->type === "Horse") {
                   $this->category_checklists = CategoryChecklist::where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('horse_id',$this->horse_id)->get();
                }elseif ($this->type === "Vehicle") {
                   $this->category_checklists = CategoryChecklist::where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('vehicle_id',$this->vehicle_id)->get();
                }elseif ($this->type === "Trailer") {
                    $this->category_checklists = CategoryChecklist::where('checklist_category_id',$this->selectedChecklistCategory)
                   ->where('trailer_id',$this->trailer_id)->get();
                }
            }else{
                 $this->category_checklists = CategoryChecklist::where('checklist_category_id',$this->selectedChecklistCategory)->get();
            }
           
        }
      
        return view('livewire.checklists.edit',[
            'category_checklists' => $this->category_checklists
        ]);
    }
}
