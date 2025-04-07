<?php

namespace App\Http\Livewire\JobTitles;

use Livewire\Component;
use App\Models\JobTitle;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $departments;
    public $department_id;
    public $title;

    public $job_titles;
    public $job_title_id;
    public $user_id;

    public function mount(){
        $this->departments = Department::all();
        $this->job_titles = JobTitle::latest()->get();
    }
    private function resetInputFields(){
        $this->title = '';
        $this->department_id = '';

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages =[
        'department_id.required' => "Department field is required"
    ];
    protected $rules = [
        'department_id' => 'required',
        'title' => 'required|unique:job_titles,title,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
        $job_title = new JobTitle;
        $job_title->user_id = Auth::user()->id;
        $job_title->title = $this->title;
        $job_title->department_id = $this->department_id;
        $job_title->save();

        $this->dispatchBrowserEvent('hide-job_titleModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Job Title Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating job title!!"
        ]);
    }
    }

    public function edit($id){
    $job_title = JobTitle::find($id);
    $this->user_id = $job_title->user_id;
    $this->title = $job_title->title;
    $this->department_id = $job_title->department_id;
    $this->job_title_id = $job_title->id;
    $this->dispatchBrowserEvent('show-job_titleEditModal');

    }


    public function update()
    {
        if ($this->job_title_id) {
            try{
            $job_title = JobTitle::find($this->job_title_id);
            $job_title->update([
                'user_id' => Auth::user()->id,
                'title' => $this->title,
                'department_id' => $this->department_id,
            ]);

            $this->dispatchBrowserEvent('hide-job_titleEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Job Title Updated Successfully!!"
            ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating job title!!"
        ]);
    }
        }
    }


    public function render()
    {
        $this->job_titles = JobTitle::latest()->get();
        return view('livewire.job-titles.index',[
            'job_titles' => $this->job_titles
        ]);
    }
}
