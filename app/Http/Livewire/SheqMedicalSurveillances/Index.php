<?php

namespace App\Http\Livewire\SheqMedicalSurveillances;

use App\Models\Employee;
use App\Models\SheqMedicalSurveillance;
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
    public $exam_filter = '';
    public $due_filter = '';

    public $employees;

    public $sheq_medical_surveillance_id;
    public $employee_id;
    public $exam_type;
    public $exam_date;
    public $next_due_date;
    public $provider;
    public $outcome;
    public $restrictions;
    public $remarks;

    protected $rules = [
        'employee_id' => 'required',
        'exam_type' => 'required',
        'exam_date' => 'required',
    ];

    public function mount(){
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->employee_id = "";
        $this->exam_type = "";
        $this->exam_date = "";
        $this->next_due_date = "";
        $this->provider = "";
        $this->outcome = "";
        $this->restrictions = "";
        $this->remarks = "";
    }

    public function store(){
        $this->validate();

        $surveillance = new SheqMedicalSurveillance;
        $surveillance->user_id = Auth::user()->id;
        $this->fill_fields($surveillance);
        $surveillance->save();

        $this->dispatchBrowserEvent('hide-sheq_medical_surveillanceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Medical Surveillance Record Created Successfully!!"
        ]);
    }

    private function fill_fields($surveillance){
        $surveillance->employee_id = $this->employee_id;
        $surveillance->exam_type = $this->exam_type;
        $surveillance->exam_date = $this->exam_date;
        $surveillance->next_due_date = $this->next_due_date ?: Null;
        $surveillance->provider = $this->provider;
        $surveillance->outcome = $this->outcome;
        $surveillance->restrictions = $this->restrictions;
        $surveillance->remarks = $this->remarks;
    }

    public function edit($id){
        $surveillance = SheqMedicalSurveillance::find($id);
        $this->sheq_medical_surveillance_id = $surveillance->id;
        $this->employee_id = $surveillance->employee_id;
        $this->exam_type = $surveillance->exam_type;
        $this->exam_date = $surveillance->exam_date ? Carbon::parse($surveillance->exam_date)->format('Y-m-d') : Null;
        $this->next_due_date = $surveillance->next_due_date ? Carbon::parse($surveillance->next_due_date)->format('Y-m-d') : Null;
        $this->provider = $surveillance->provider;
        $this->outcome = $surveillance->outcome;
        $this->restrictions = $surveillance->restrictions;
        $this->remarks = $surveillance->remarks;
        $this->dispatchBrowserEvent('show-sheq_medical_surveillanceEditModal');
    }

    public function update(){
        $this->validate();

        $surveillance = SheqMedicalSurveillance::find($this->sheq_medical_surveillance_id);
        $this->fill_fields($surveillance);
        $surveillance->update();

        $this->dispatchBrowserEvent('hide-sheq_medical_surveillanceEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Medical Surveillance Record Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_medical_surveillance_id = $id;
        $this->dispatchBrowserEvent('show-sheq_medical_surveillanceDeleteModal');
    }

    public function destroy(){
        $surveillance = SheqMedicalSurveillance::find($this->sheq_medical_surveillance_id);
        if ($surveillance) {
            $surveillance->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_medical_surveillanceDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Medical Surveillance Record Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqMedicalSurveillance::query()->with(['employee']);

        if ($this->exam_filter) {
            $query->where('exam_type', $this->exam_filter);
        }
        if ($this->due_filter == 'overdue') {
            $query->whereDate('next_due_date','<', Carbon::today());
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->whereHas('employee', function($q) use ($search){
                $q->where('name','like',$search)->orWhere('surname','like',$search);
            });
        }

        $sheq_medical_surveillances = $query->orderBy('next_due_date','asc')->paginate(10);

        return view('livewire.sheq-medical-surveillances.index',[
            'sheq_medical_surveillances' => $sheq_medical_surveillances
        ]);
    }
}
