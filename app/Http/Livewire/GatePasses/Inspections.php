<?php

namespace App\Http\Livewire\GatePasses;

use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Employee;
use App\Models\GatePass;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Auth;

class Inspections extends Component
{
    public $gate_pass_id;
    public $checklist_items;
    public $checklist_item_id;
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

    public $available = '1';
    public $notavailable = '0';
    public $comments;
    public $status;
 

    public function mount($id){
        $this->gate_pass_id = $id;
        $this->checklists = Checklist::where('gate_pass_id',$id)->get();
        $this->checklist_items = ChecklistItem::latest()->get();
        $this->vehicles = Vehicle::latest()->get();
        $this->drivers = Driver::latest()->get();
        $this->employees = Employee::latest()->get();
        $this->trailers = Trailer::latest()->get();
        $this->horses = Horse::latest()->get();
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
        $gate_pass = GatePass::find($this->gate_pass_id);
        $checklist = new Checklist;
        $checklist->user_id = Auth::user()->id;
        $checklist->checklist_number = $this->checklistNumber();
        $checklist->employee_id = $this->employee_id;
        $checklist->gate_pass_id = $this->gate_pass_id;
        $checklist->horse_id = $gate_pass->horse_id;
        $checklist->date = $this->date;
        $checklist->comments = $this->description;
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
        $this->checklists = Checklist::where('gate_pass_id',$this->gate_pass_id)->get();
        return view('livewire.gate-passes.inspections',[
            'checklists' => $this->checklists
        ]);
    }
}
