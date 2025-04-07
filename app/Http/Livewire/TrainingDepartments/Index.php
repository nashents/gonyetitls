<?php

namespace App\Http\Livewire\TrainingDepartments;

use Livewire\Component;
use App\Models\TrainingDepartment;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $training_departments;
    public $training_department_id;
    public $training_department;
    public $name;
  

  

    public function mount(){
        $this->training_departments = TrainingDepartment::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:training_departments,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
      
        $this->name = '';
     
    }

   

    public function store(){
        try{

        $training_department = new TrainingDepartment;
        $training_department->user_id = Auth::user()->id;
        $training_department->name = $this->name;
        $training_department->save();

        $this->dispatchBrowserEvent('hide-training_departmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Training Department Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating training department!!"
        ]);
    }
    }

    public function edit($id){
    $training_department = TrainingDepartment::find($id);
    $this->user_id = $training_department->user_id;
    $this->name = $training_department->name;
    $this->training_department_id = $training_department->id;
    $this->dispatchBrowserEvent('show-training_departmentEditModal');

    }


    public function update()
    {
        if ($this->training_department_id) {
            try{
            $training_department = TrainingDepartment::find($this->training_department_id);
            $training_department->name = $this->name;
            $training_department->update();

            $this->dispatchBrowserEvent('hide-training_departmentEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Department Updated Successfully!!"
            ]);


            // return redirect()->route('training_departments.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-training_departmentEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating training department!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->training_departments = TrainingDepartment::orderBy('name','asc')->get();
        return view('livewire.training-departments.index',[
            'training_departments' =>   $this->training_departments
        ]);
    }
}
