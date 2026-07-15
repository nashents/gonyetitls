<?php

namespace App\Http\Livewire\SheqEmergencies;

use App\Models\Department;
use App\Models\SheqEmergency;
use App\Models\SheqRisk;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $department_filter = '';

    public $departments;
    public $risks;

    public $sheq_emergency_id;
    public $department_id;
    public $scenario;
    public $location;
    public $sheq_risk_id;
    public $response_plan;
    public $drill_frequency;
    public $status = 'active';

    protected $rules = [
        'department_id' => 'required',
        'scenario' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->risks = SheqRisk::orderBy('rating','desc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->scenario = "";
        $this->location = "";
        $this->sheq_risk_id = "";
        $this->response_plan = "";
        $this->drill_frequency = "";
        $this->status = "active";
    }

    public function store(){
        $this->validate();

        $emergency = new SheqEmergency;
        $emergency->user_id = Auth::user()->id;
        $emergency->department_id = $this->department_id;
        $emergency->scenario = $this->scenario;
        $emergency->location = $this->location;
        $emergency->sheq_risk_id = $this->sheq_risk_id ?: Null;
        $emergency->response_plan = $this->response_plan;
        $emergency->drill_frequency = $this->drill_frequency;
        $emergency->status = $this->status;
        $emergency->save();

        $this->dispatchBrowserEvent('hide-sheq_emergencyModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Emergency Scenario Created Successfully!!"
        ]);
    }

    public function edit($id){
        $emergency = SheqEmergency::find($id);
        $this->sheq_emergency_id = $emergency->id;
        $this->department_id = $emergency->department_id;
        $this->scenario = $emergency->scenario;
        $this->location = $emergency->location;
        $this->sheq_risk_id = $emergency->sheq_risk_id;
        $this->response_plan = $emergency->response_plan;
        $this->drill_frequency = $emergency->drill_frequency;
        $this->status = $emergency->status;
        $this->dispatchBrowserEvent('show-sheq_emergencyEditModal');
    }

    public function update(){
        $this->validate();

        $emergency = SheqEmergency::find($this->sheq_emergency_id);
        $emergency->department_id = $this->department_id;
        $emergency->scenario = $this->scenario;
        $emergency->location = $this->location;
        $emergency->sheq_risk_id = $this->sheq_risk_id ?: Null;
        $emergency->response_plan = $this->response_plan;
        $emergency->drill_frequency = $this->drill_frequency;
        $emergency->status = $this->status;
        $emergency->update();

        $this->dispatchBrowserEvent('hide-sheq_emergencyEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Emergency Scenario Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_emergency_id = $id;
        $this->dispatchBrowserEvent('show-sheq_emergencyDeleteModal');
    }

    public function destroy(){
        $emergency = SheqEmergency::find($this->sheq_emergency_id);
        if ($emergency) {
            $emergency->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_emergencyDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Emergency Scenario Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqEmergency::query()->with(['department','risk','drills']);

        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('scenario','like',$search)
                  ->orWhere('location','like',$search);
            });
        }

        $sheq_emergencies = $query->orderBy('scenario','asc')->paginate(10);

        return view('livewire.sheq-emergencies.index',[
            'sheq_emergencies' => $sheq_emergencies
        ]);
    }
}
