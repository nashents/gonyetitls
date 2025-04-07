<?php

namespace App\Http\Livewire\JobTypes;

use App\Models\JobType;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $job_types;
    public $job_type;
    public $name;

    public $job_type_id;
    public $user_id;

    public function mount(){
        $this->job_types = JobType::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:job_types,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->name = '';
    }

    public function store(){
        try{
        $job_type = new JobType;
        $job_type->user_id = Auth::user()->id;
        $job_type->name = $this->name;
        $job_type->save();

        $this->dispatchBrowserEvent('hide-job_typeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Type Created Successfully!!"
        ]);

        // return redirect()->route('job_types.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating job_type!!"
        ]);
    }
    }

    public function edit($id){
    $job_type = JobType::find($id);
    $this->user_id = $job_type->user_id;
    $this->name = $job_type->name;
    $this->job_type_id = $job_type->id;
    $this->dispatchBrowserEvent('show-job_typeEditModal');

    }


    public function update()
    {
        if ($this->job_type_id) {
            try{
            $job_type = JobType::find($this->job_type_id);
            $job_type->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-job_typeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Job Type Updated Successfully!!"
            ]);


            // return redirect()->route('job_types.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-job_typeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating Job Type!!"
            ]);
          }
        }
    }
    public function render()
    {
        return view('livewire.job-types.index');
    }
}
