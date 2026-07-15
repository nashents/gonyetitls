<?php

namespace App\Http\Livewire\SheqMonitoringReadings;

use App\Models\Department;
use App\Models\SheqMonitoringParameter;
use App\Models\SheqMonitoringReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $parameter_filter = '';
    public $department_filter = '';
    public $breach_filter = '';

    public $departments;
    public $parameters;

    public $sheq_monitoring_reading_id;
    public $sheq_monitoring_parameter_id;
    public $department_id;
    public $reading_date;
    public $value;
    public $comments;

    protected $rules = [
        'sheq_monitoring_parameter_id' => 'required',
        'reading_date' => 'required',
        'value' => 'required|numeric',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->parameters = SheqMonitoringParameter::where('is_active',1)->orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->sheq_monitoring_parameter_id = "";
        $this->department_id = "";
        $this->reading_date = "";
        $this->value = "";
        $this->comments = "";
    }

    public function store(){
        $this->validate();

        $parameter = SheqMonitoringParameter::find($this->sheq_monitoring_parameter_id);

        $reading = new SheqMonitoringReading;
        $reading->user_id = Auth::user()->id;
        $reading->sheq_monitoring_parameter_id = $this->sheq_monitoring_parameter_id;
        $reading->department_id = $this->department_id ?: Null;
        $reading->reading_date = $this->reading_date;
        $reading->value = $this->value;
        $reading->breach = $parameter && $parameter->isBreach($this->value) ? 1 : 0;
        $reading->comments = $this->comments;
        $reading->save();

        $this->dispatchBrowserEvent('hide-sheq_monitoring_readingModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reading Recorded Successfully!!"
        ]);
    }

    public function edit($id){
        $reading = SheqMonitoringReading::find($id);
        $this->sheq_monitoring_reading_id = $reading->id;
        $this->sheq_monitoring_parameter_id = $reading->sheq_monitoring_parameter_id;
        $this->department_id = $reading->department_id;
        $this->reading_date = $reading->reading_date ? Carbon::parse($reading->reading_date)->format('Y-m-d') : Null;
        $this->value = $reading->value;
        $this->comments = $reading->comments;
        $this->dispatchBrowserEvent('show-sheq_monitoring_readingEditModal');
    }

    public function update(){
        $this->validate();

        $parameter = SheqMonitoringParameter::find($this->sheq_monitoring_parameter_id);

        $reading = SheqMonitoringReading::find($this->sheq_monitoring_reading_id);
        $reading->sheq_monitoring_parameter_id = $this->sheq_monitoring_parameter_id;
        $reading->department_id = $this->department_id ?: Null;
        $reading->reading_date = $this->reading_date;
        $reading->value = $this->value;
        $reading->breach = $parameter && $parameter->isBreach($this->value) ? 1 : 0;
        $reading->comments = $this->comments;
        $reading->update();

        $this->dispatchBrowserEvent('hide-sheq_monitoring_readingEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reading Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_monitoring_reading_id = $id;
        $this->dispatchBrowserEvent('show-sheq_monitoring_readingDeleteModal');
    }

    public function destroy(){
        $reading = SheqMonitoringReading::find($this->sheq_monitoring_reading_id);
        if ($reading) {
            $reading->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_monitoring_readingDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Reading Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqMonitoringReading::query()->with(['parameter','department']);

        if ($this->parameter_filter) {
            $query->where('sheq_monitoring_parameter_id', $this->parameter_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->breach_filter !== '' && $this->breach_filter !== null) {
            $query->where('breach', $this->breach_filter);
        }

        $sheq_monitoring_readings = $query->orderBy('reading_date','desc')->paginate(10);

        return view('livewire.sheq-monitoring-readings.index',[
            'sheq_monitoring_readings' => $sheq_monitoring_readings
        ]);
    }
}
