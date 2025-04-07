<?php

namespace App\Http\Livewire\Checklists;

use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\GatePass;
use App\Models\Checklist;
use App\Models\ChecklistResult;
use App\Models\CategoryChecklist;
use App\Models\ChecklistCategory;
use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Auth;

class Add extends Component
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
    public $gate_pass_id;
    public $gate_pass;
    public $horses;
    public $horse_id;
    public $description;
    public $date;
    public $type;
    public $mileage;


    public $yes = '1';
    public $no = '0';

    public $hours;
    public $cost;
    public $status = [];
    public $inputs = [];
    
    public $comments;
 
 

    public function mount($id){
        $this->gate_pass_id = $id;
        $this->type = "Horse";
        $this->gate_pass = GatePass::find($id);
        if (isset($this->gate_pass)) {
            $this->horse_id = $this->gate_pass->horse_id;
            $this->driver_id = $this->gate_pass->driver_id;
        }
      
        $this->checklist_categories = ChecklistCategory::latest()->get();
        $this->checklist_sub_categories = ChecklistSubCategory::latest()->get();
        $this->checklist_items = collect();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->drivers = Driver::latest()->get();
        $this->employees = Employee::latest()->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
    }

    
    public function updatedSelectedChecklistCategory($id){
        if (!is_null($id)) {
            $this->checklist_category_id = $id;
            $this->checklist_category = ChecklistCategory::find($id);
            $this->category_checklists = CategoryChecklist::where('checklist_category_id',$id)->get();
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

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
    ];

    public function store(){
        try{
        $checklist = new Checklist;
        $checklist->user_id = Auth::user()->id;
        $checklist->checklist_number = $this->checklistNumber();
        $checklist->employee_id = $this->employee_id;
        $checklist->gate_pass_id = $this->gate_pass_id;
        $checklist->driver_id = $this->driver_id;
        $checklist->vehicle_id = $this->vehicle_id;
        $checklist->trailer_id = $this->trailer_id;
        $checklist->horse_id = $this->horse_id;
        $checklist->date = $this->date;
        $checklist->comments = $this->description;
        $checklist->mileage = $this->mileage;
        $checklist->save();

        
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
            $result->checklist_item_id = $key;
            $result->save();
    
              }
              
         
            }
            $this->dispatchBrowserEvent('hide-checklistModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Checklist Completed Successfully!!"
            ]);

            return redirect(route('gate_passes.show',$this->gate_pass_id));

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating checklist!!"
        ]);
    }
    }

    public function render()
    {
        if (isset($this->checklist_category_id)) {
            $this->category_checklists = CategoryChecklist::where('checklist_category_id',$this->checklist_category_id)->get();
        }
      
        return view('livewire.checklists.add',[
            'category_checklists' => $this->category_checklists
        ]);
    }
}
