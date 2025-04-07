<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Departments extends Component
{
    public $name;
    public $user_id;
    public $department_code;
    public $description;
    public $departments;
    public $department_id;
    public $updateMode;

    public function mount(){
        $this->departments = Department::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
        $this->department_code = '';
        $this->description = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:departments,name,NULL,id,deleted_at,NULL|string|min:2',
        'department_code' => 'required|string|max:5',
        'description' => 'nullable|string',
    ];

    public function store(){
        try{
        $department = new Department;
        $department->user_id = Auth::user()->id;
        $department->name = $this->name;
        $department->department_code = $this->department_code;
        $department->description = $this->description;
        $department->save();
        $this->dispatchBrowserEvent('hide-departmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Department Created Successfully!!"
        ]);
        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating department!!"
            ]);
        }
    }
    public function edit($id){
        $department = Department::find($id);
        $this->updateMode = true;
        $this->user_id = $department->user_id;
        $this->name = $department->name;
        $this->department_id = $department->id;
        $this->description = $department->description;
        $this->department_code = $department->department_code;
        $this->dispatchBrowserEvent('show-departmentEditModal');

        }

        public function update()
    {
        if ($this->department_id) {
            try{
            $department = Department::find($this->department_id);
            $department->update([
                'name' => $this->name,
                'department_code' => $this->department_code,
                'description' => $this->description,
            ]);
            $this->dispatchBrowserEvent('hide-departmentEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Department Updated Successfully!!"
            ]);

            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating department!!"
                ]);
            }
        }
    }

    public function render()
    {
        $this->departments = Department::latest()->get();
        return view('livewire.departments',[
            'departments' => $this->departments
        ]);
    }
}
