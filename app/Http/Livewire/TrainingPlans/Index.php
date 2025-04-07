<?php

namespace App\Http\Livewire\TrainingPlans;

use Livewire\Component;
use App\Models\TrainingItem;
use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $training_plans;
    public $training_plan_id;
    public $training_plan;
    public $training_items;
    public $training_item_id;
    public $training_item;
    public $period;
    public $participants;
  

  

    public function mount(){
        $this->training_plans = TrainingPlan::latest()->get();
        $this->training_items = TrainingItem::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'period' => 'required',
        'training_item_id' => 'required',
        'participants' => 'required',
    ];

    private function resetInputFields(){
      
        $this->training_item_id = '';
        $this->period = '';
        $this->participants = '';
     
    }

   

    public function store(){
        try{

        $training_plan = new TrainingPlan;
        $training_plan->user_id = Auth::user()->id;
        $training_plan->period = $this->period;
        $training_plan->participants = $this->participants;
        $training_plan->training_item_id = $this->training_item_id;
        $training_plan->save();

        $this->dispatchBrowserEvent('hide-training_planModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Training Plan Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating training plan!!"
        ]);
    }
    }

    public function edit($id){
      
    $training_plan = TrainingPlan::find($id);

    $this->participants = $training_plan->participants;
    $this->period = $training_plan->period;
    $this->training_item_id = $training_plan->training_item_id;
    $this->training_plan_id = $training_plan->id;
    
    $this->dispatchBrowserEvent('show-training_planEditModal');

    }


    public function update()
    {
        if ($this->training_plan_id) {
            try{
            $training_plan = TrainingPlan::find($this->training_plan_id);
            $training_plan->period = $this->period;
            $training_plan->participants = $this->participants;
            $training_plan->training_item_id = $this->training_item_id;
            $training_plan->update();

            $this->dispatchBrowserEvent('hide-training_planEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Training Plan Updated Successfully!!"
            ]);


            // return redirect()->route('training_plans.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-training_planEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating training plan!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->training_plans = TrainingPlan::latest()->get();
        $this->training_items = TrainingItem::orderBy('name','asc')->get();
        return view('livewire.training-plans.index',[
            'training_plans' =>   $this->training_plans,
            'training_items' =>   $this->training_items,
        ]);
    }
}
