<?php

namespace App\Http\Livewire\DepartmentHeads;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DepartmentHead;

class Index extends Component
{

    public $departments;
    public $department_id;
    public $department_heads;
    public $department_head_id;
    public $employees;
    public $employee_id;

    public function mount(){
        $this->departments = Department::latest()->get();
        $this->department_heads = DepartmentHead::latest()->get();
        $this->employees = Employee::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->employee_id = '';
        $this->department_id = '';
    }
    public function store(){
        $department_head = new DepartmentHead;
        $department_head->employee_id = $this->employee_id;
        $department_head->department_id = $this->department_id;
        $department_head->save();
        $this->dispatchBrowserEvent('hide-department_headModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Employee Assigned Successfully!!"
        ]);
    }
   public function edit($id){

    $department_head = DepartmentHead::find($id);
    $this->employee_id = $department_head->employee_id;
    $this->department_id = $department_head->department_id;
    $this->department_head_id = $department_head->id;
    $this->dispatchBrowserEvent('show-department_headEditModal');

   }
    public function update(){
        $department_head =  DepartmentHead::find($this->department_head_id);
        $department_head->employee_id = $this->employee_id;
        $department_head->department_id = $this->department_id;
        $department_head->update();
        $this->dispatchBrowserEvent('hide-department_headEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Employee Assignment Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->department_heads = DepartmentHead::latest()->get();
        return view('livewire.department-heads.index',[
            'department_heads' => $this->department_heads
        ]);
    }
}
