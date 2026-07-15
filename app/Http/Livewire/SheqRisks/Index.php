<?php

namespace App\Http\Livewire\SheqRisks;

use App\Models\Department;
use App\Models\SheqRisk;
use App\Models\SheqRiskAssessment;
use App\Models\SheqRiskControl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search','category_filter'];
    public $category_filter = '';
    public $department_filter = '';
    public $top_filter = '';

    public $departments;
    public $assessments;

    public $sheq_risk_id;
    public $sheq_risk_assessment_id;
    public $department_id;
    public $category;
    public $hazard;
    public $risk;
    public $likelihood;
    public $severity;
    public $residual_likelihood;
    public $residual_severity;
    public $is_top_risk = 0;
    public $status = 'open';

    public $control_risk_id;
    public $control_description;
    public $control_hierarchy = 'administrative';
    public $control_is_critical = 0;
    public $control_effectiveness;
    public $control_last_evaluated;
    public $control_notes;
    public $sheq_risk_control_id;

    protected $rules = [
        'department_id' => 'required',
        'category' => 'required',
        'hazard' => 'required',
        'risk' => 'required',
        'likelihood' => 'required|integer|min:1|max:5',
        'severity' => 'required|integer|min:1|max:5',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->assessments = SheqRiskAssessment::orderBy('created_at','desc')->get();
    }

    private function resetInputFields(){
        $this->sheq_risk_assessment_id = "";
        $this->department_id = "";
        $this->category = "";
        $this->hazard = "";
        $this->risk = "";
        $this->likelihood = "";
        $this->severity = "";
        $this->residual_likelihood = "";
        $this->residual_severity = "";
        $this->is_top_risk = 0;
        $this->status = "open";
    }

    private function resetControlFields(){
        $this->sheq_risk_control_id = "";
        $this->control_description = "";
        $this->control_hierarchy = "administrative";
        $this->control_is_critical = 0;
        $this->control_effectiveness = "";
        $this->control_last_evaluated = "";
        $this->control_notes = "";
    }

    public function store(){
        $this->validate();

        $sheq_risk = new SheqRisk;
        $sheq_risk->user_id = Auth::user()->id;
        $sheq_risk->sheq_risk_assessment_id = $this->sheq_risk_assessment_id ?: Null;
        $sheq_risk->department_id = $this->department_id;
        $sheq_risk->category = $this->category;
        $sheq_risk->hazard = $this->hazard;
        $sheq_risk->risk = $this->risk;
        $sheq_risk->likelihood = $this->likelihood;
        $sheq_risk->severity = $this->severity;
        $sheq_risk->rating = (int)$this->likelihood * (int)$this->severity;
        if ($this->residual_likelihood && $this->residual_severity) {
            $sheq_risk->residual_likelihood = $this->residual_likelihood;
            $sheq_risk->residual_severity = $this->residual_severity;
            $sheq_risk->residual_rating = (int)$this->residual_likelihood * (int)$this->residual_severity;
        }
        $sheq_risk->is_top_risk = $this->is_top_risk ? 1 : 0;
        $sheq_risk->status = $this->status;
        $sheq_risk->save();

        $this->dispatchBrowserEvent('hide-sheq_riskModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Created Successfully!!"
        ]);
    }

    public function edit($id){
        $sheq_risk = SheqRisk::find($id);
        $this->sheq_risk_id = $sheq_risk->id;
        $this->sheq_risk_assessment_id = $sheq_risk->sheq_risk_assessment_id;
        $this->department_id = $sheq_risk->department_id;
        $this->category = $sheq_risk->category;
        $this->hazard = $sheq_risk->hazard;
        $this->risk = $sheq_risk->risk;
        $this->likelihood = $sheq_risk->likelihood;
        $this->severity = $sheq_risk->severity;
        $this->residual_likelihood = $sheq_risk->residual_likelihood;
        $this->residual_severity = $sheq_risk->residual_severity;
        $this->is_top_risk = $sheq_risk->is_top_risk;
        $this->status = $sheq_risk->status;
        $this->dispatchBrowserEvent('show-sheq_riskEditModal');
    }

    public function update(){
        $this->validate();

        $sheq_risk = SheqRisk::find($this->sheq_risk_id);
        $sheq_risk->sheq_risk_assessment_id = $this->sheq_risk_assessment_id ?: Null;
        $sheq_risk->department_id = $this->department_id;
        $sheq_risk->category = $this->category;
        $sheq_risk->hazard = $this->hazard;
        $sheq_risk->risk = $this->risk;
        $sheq_risk->likelihood = $this->likelihood;
        $sheq_risk->severity = $this->severity;
        $sheq_risk->rating = (int)$this->likelihood * (int)$this->severity;
        if ($this->residual_likelihood && $this->residual_severity) {
            $sheq_risk->residual_likelihood = $this->residual_likelihood;
            $sheq_risk->residual_severity = $this->residual_severity;
            $sheq_risk->residual_rating = (int)$this->residual_likelihood * (int)$this->residual_severity;
        }
        $sheq_risk->is_top_risk = $this->is_top_risk ? 1 : 0;
        $sheq_risk->status = $this->status;
        $sheq_risk->update();

        $this->dispatchBrowserEvent('hide-sheq_riskEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Updated Successfully!!"
        ]);
    }

    public function controls($id){
        $this->control_risk_id = $id;
        $this->resetControlFields();
        $this->dispatchBrowserEvent('show-sheq_riskControlsModal');
    }

    public function storeControl(){
        $this->validate([
            'control_description' => 'required',
        ]);

        $control = new SheqRiskControl;
        $control->sheq_risk_id = $this->control_risk_id;
        $control->description = $this->control_description;
        $control->hierarchy = $this->control_hierarchy;
        $control->is_critical = $this->control_is_critical ? 1 : 0;
        $control->effectiveness = $this->control_effectiveness ?: Null;
        $control->last_evaluated = $this->control_last_evaluated ?: Null;
        $control->notes = $this->control_notes;
        $control->save();

        $this->resetControlFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Control Added Successfully!!"
        ]);
    }

    public function deleteControl($id){
        $control = SheqRiskControl::find($id);
        if ($control) {
            $control->delete();
        }
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Control Deleted Successfully!!"
        ]);
    }

    public function evaluateControl($id, $effectiveness){
        $control = SheqRiskControl::find($id);
        if ($control) {
            $control->effectiveness = $effectiveness;
            $control->last_evaluated = Carbon::today()->format('Y-m-d');
            $control->update();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Control Evaluated Successfully!!"
            ]);
        }
    }

    public function delete($id){
        $this->sheq_risk_id = $id;
        $this->dispatchBrowserEvent('show-sheq_riskDeleteModal');
    }

    public function destroy(){
        $sheq_risk = SheqRisk::find($this->sheq_risk_id);
        if ($sheq_risk) {
            foreach ($sheq_risk->controls as $control) {
                $control->delete();
            }
            $sheq_risk->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_riskDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Risk Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqRisk::query()->with(['department','controls','assessment']);

        if ($this->category_filter) {
            $query->where('category', $this->category_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->top_filter !== '' && $this->top_filter !== null) {
            $query->where('is_top_risk', $this->top_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('hazard','like',$search)
                  ->orWhere('risk','like',$search);
            });
        }

        $sheq_risks = $query->orderBy('rating','desc')->paginate(10);

        $current_controls = $this->control_risk_id
            ? SheqRiskControl::where('sheq_risk_id',$this->control_risk_id)->get()
            : collect();

        return view('livewire.sheq-risks.index',[
            'sheq_risks' => $sheq_risks,
            'current_controls' => $current_controls,
        ]);
    }
}
