<?php

namespace App\Http\Livewire\Employees;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Dependant;
use Illuminate\Support\Facades\Auth;

class Dependants extends Component
{
    public $dependants;
    public $dependant_id;
    public $employee;
    public $employee_id;
    public $type;
    public $name;
    public $surname;
    public $dob;
    public $gender;

    public function mount($id){
        $this->employee_id = $id;
        $this->employee = Employee::find($id);
        $this->dependants = Dependant::where('employee_id',$this->employee_id)->orderBy('name','asc')->orderBy('surname','asc')->get();
      
    }

        public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'name' => 'required',
        'surname' => 'required',
        'gender' => 'required',
        'dob' => 'required',
    ];

    private function resetInputFields(){
        $this->type = '';
        $this->name = '';
        $this->surname = '';
        $this->dob = '';
        $this->gender = '';
        
    }

    public function store(){
        $dependant = new Dependant;
        $dependant->user_id = Auth::user()->id;
        $dependant->employee_id = $this->employee->id;
        $dependant->type = $this->type;
        $dependant->name = $this->name;
        $dependant->surname = $this->surname;
        $dependant->gender = $this->gender;
        $dependant->dob = $this->dob;
        $dependant->save();

         $this->dependants = Dependant::where('employee_id',$this->employee_id)->orderBy('name','asc')->orderBy('surname','asc')->get();

        $this->dispatchBrowserEvent('hide-dependantModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Dependant Added Successfully!!"
        ]);
    }

    public function edit($id){
        $this->dependant_id = $id;
        $dependant = Dependant::find($id);
        $this->type = $dependant->type;
        $this->name = $dependant->name;
        $this->surname = $dependant->surname;
        $this->dob = $dependant->dob;
        $this->gender = $dependant->gender;
         $this->dispatchBrowserEvent('show-dependantEditModal');

    }

    public function update(){
        $dependant = Dependant::find($this->dependant_id);
        $dependant->user_id = Auth::user()->id;
        $dependant->employee_id = $this->employee->id;
        $dependant->type = $this->type;
        $dependant->name = $this->name;
        $dependant->surname = $this->surname;
        $dependant->gender = $this->gender;
        $dependant->dob = $this->dob;
        $dependant->save();

         $this->dependants = Dependant::where('employee_id',$this->employee_id)->orderBy('name','asc')->orderBy('surname','asc')->get();
         
        $this->dispatchBrowserEvent('hide-dependantEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Dependant Updated Successfully!!"
        ]);
    }

    public function render()
    {

        return view('livewire.employees.dependants',[
            'dependants' => Dependant::where('employee_id',$this->employee_id)->orderBy('name','asc')->orderBy('surname','asc')->get()
        ]);
    }
}
