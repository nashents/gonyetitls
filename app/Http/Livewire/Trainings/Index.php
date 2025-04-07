<?php

namespace App\Http\Livewire\Trainings;

use App\Models\Driver;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Training;
use App\Models\TrainingItem;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $trainings;
    public $training_id;
    public $training;
    public $training_items;
    public $training_item_id;
    public $training_item;
    public $employees;
    public $employee_id;
    public $date;
    public $participation;
    public $comments;
  

  

    public function mount(){
        $this->trainings = Training::latest()->get();
        $this->training_items = TrainingItem::latest()->get();
        $this->employees = Employee::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'training_item_id' => 'required',
        'employee_id' => 'required',
    ];

    private function resetInputFields(){
      
        $this->training_item_id = '';
        $this->employee_id = '';
        $this->date = '';
        $this->participation = '';
        $this->comments = '';
     
    }

   

    public function store(){
        try{

        $training = new Training;
        $training->user_id = Auth::user()->id;
        $training->date = $this->date;
        $training->training_item_id = $this->training_item_id;
        $training->employee_id = $this->employee_id;
        $training->participation = $this->participation;
        $training->comments = $this->comments;
        $training->save();

        $this->dispatchBrowserEvent('hide-trainingModal');
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
    $training = Training::find($id);
    $this->date = $training->date;
    $this->training_item_id = $training->training_item_id;
    $this->employee_id = $training->employee_id;
    $this->participation = $training->participation;
    $this->comments = $training->comments;
    $this->training_id = $training->id;
    $this->dispatchBrowserEvent('show-trainingEditModal');

    }


    public function update()
    {
        if ($this->training_id) {
            try{
            $training = Training::find($this->training_id);
            $training->date = $this->date;
            $training->training_item_id = $this->training_item_id;
            $training->employee_id = $this->employee_id;
            $training->comments = $this->comments;
            $training->participation = $this->participation;
            $training->update();

            $this->dispatchBrowserEvent('hide-trainingEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Requirement Updated Successfully!!"
            ]);


            // return redirect()->route('trainings.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trainingEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating training requirement!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->trainings = Training::latest()->get();
        $this->training_items = TrainingItem::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->get();
      
        return view('livewire.trainings.index',[
            'trainings' =>   $this->trainings,
            'training_items' =>   $this->training_items,
            'employees' =>   $this->employees,
        ]);
    }
}
