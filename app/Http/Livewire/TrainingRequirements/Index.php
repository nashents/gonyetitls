<?php

namespace App\Http\Livewire\TrainingRequirements;

use Livewire\Component;
use App\Models\TrainingItem;
use PharIo\Manifest\Requirement;
use App\Models\TrainingDepartment;
use App\Models\TrainingRequirement;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $training_requirements;
    public $training_requirement_id;
    public $training_requirement;
    public $training_items;
    public $training_item_id;
    public $training_item;
    public $training_departments;
    public $training_department_id;
    public $training_department;
    public $required;
  

  

    public function mount(){
        $this->training_requirements = TrainingRequirement::latest()->get();
        $this->training_items = TrainingItem::latest()->get();
        $this->training_departments = TrainingDepartment::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'required' => 'required',
        'training_item_id' => 'required',
        'training_department_id' => 'required',
    ];

    private function resetInputFields(){
      
        $this->training_item_id = '';
        $this->training_department_id = '';
        $this->required = '';
     
    }

   

    public function store(){
        try{

        $training_requirement = new TrainingRequirement;
        $training_requirement->user_id = Auth::user()->id;
        $training_requirement->required = $this->required;
        $training_requirement->training_item_id = $this->training_item_id;
        $training_requirement->training_department_id = $this->training_department_id;
        $training_requirement->save();

        $this->dispatchBrowserEvent('hide-training_requirementModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Training Requirement Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating training requirement!!"
        ]);
    }
    }

    public function edit($id){
    $training_requirement = TrainingRequirement::find($id);
    $this->required = $training_requirement->required;
    $this->training_item_id = $training_requirement->training_item_id;
    $this->training_department_id = $training_requirement->training_department_id;
    $this->training_requirement_id = $training_requirement->id;
    $this->dispatchBrowserEvent('show-training_requirementEditModal');

    }


    public function update()
    {
        if ($this->training_requirement_id) {
            try{
            $training_requirement = TrainingRequirement::find($this->training_requirement_id);
            $training_requirement->required = $this->required;
            $training_requirement->training_item_id = $this->training_item_id;
            $training_requirement->training_department_id = $this->training_department_id;
            $training_requirement->update();

            $this->dispatchBrowserEvent('hide-training_requirementEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Requirement Updated Successfully!!"
            ]);


            // return redirect()->route('training_requirements.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-training_requirementEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating training requirement!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->training_requirements = TrainingRequirement::latest()->get();
        $this->training_items = TrainingItem::orderBy('name','asc')->get();
        $this->training_departments = TrainingDepartment::orderBy('name','asc')->get();
        return view('livewire.training-requirements.index',[
            'training_requirements' =>   $this->training_requirements,
            'training_items' =>   $this->training_items,
            'training_departments' =>   $this->training_departments,
        ]);
    }
}
