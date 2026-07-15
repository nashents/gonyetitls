<?php

namespace App\Http\Livewire\SheqMonitoringParameters;

use App\Models\SheqMonitoringParameter;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $sheq_monitoring_parameter_id;
    public $name;
    public $category;
    public $unit;
    public $limit_value;
    public $limit_type = 'max';
    public $frequency;
    public $is_active = 1;

    protected $rules = [
        'name' => 'required',
        'category' => 'required',
    ];

    private function resetInputFields(){
        $this->name = "";
        $this->category = "";
        $this->unit = "";
        $this->limit_value = "";
        $this->limit_type = "max";
        $this->frequency = "";
        $this->is_active = 1;
    }

    public function store(){
        $this->validate();

        $parameter = new SheqMonitoringParameter;
        $parameter->user_id = Auth::user()->id;
        $parameter->name = $this->name;
        $parameter->category = $this->category;
        $parameter->unit = $this->unit;
        $parameter->limit_value = $this->limit_value;
        $parameter->limit_type = $this->limit_type;
        $parameter->frequency = $this->frequency;
        $parameter->is_active = $this->is_active ? 1 : 0;
        $parameter->save();

        $this->dispatchBrowserEvent('hide-sheq_monitoring_parameterModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Monitoring Parameter Created Successfully!!"
        ]);
    }

    public function edit($id){
        $parameter = SheqMonitoringParameter::find($id);
        $this->sheq_monitoring_parameter_id = $parameter->id;
        $this->name = $parameter->name;
        $this->category = $parameter->category;
        $this->unit = $parameter->unit;
        $this->limit_value = $parameter->limit_value;
        $this->limit_type = $parameter->limit_type;
        $this->frequency = $parameter->frequency;
        $this->is_active = $parameter->is_active;
        $this->dispatchBrowserEvent('show-sheq_monitoring_parameterEditModal');
    }

    public function update(){
        $this->validate();

        $parameter = SheqMonitoringParameter::find($this->sheq_monitoring_parameter_id);
        $parameter->name = $this->name;
        $parameter->category = $this->category;
        $parameter->unit = $this->unit;
        $parameter->limit_value = $this->limit_value;
        $parameter->limit_type = $this->limit_type;
        $parameter->frequency = $this->frequency;
        $parameter->is_active = $this->is_active ? 1 : 0;
        $parameter->update();

        $this->dispatchBrowserEvent('hide-sheq_monitoring_parameterEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Monitoring Parameter Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_monitoring_parameter_id = $id;
        $this->dispatchBrowserEvent('show-sheq_monitoring_parameterDeleteModal');
    }

    public function destroy(){
        $parameter = SheqMonitoringParameter::find($this->sheq_monitoring_parameter_id);
        if ($parameter) {
            $parameter->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_monitoring_parameterDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Monitoring Parameter Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqMonitoringParameter::query()->withCount('readings');
        if ($this->search) {
            $query->where('name','like','%'.$this->search.'%');
        }
        $sheq_monitoring_parameters = $query->orderBy('name','asc')->paginate(10);

        return view('livewire.sheq-monitoring-parameters.index',[
            'sheq_monitoring_parameters' => $sheq_monitoring_parameters
        ]);
    }
}
