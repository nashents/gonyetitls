<?php

namespace App\Http\Livewire\SheqAudits;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqAudit;
use App\Models\SheqAuditTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search','status_filter'];
    public $status_filter = '';
    public $department_filter = '';

    public $templates;
    public $departments;
    public $employees;

    public $sheq_audit_id;
    public $sheq_audit_template_id;
    public $department_id;
    public $lead_auditor_id;
    public $auditee_id;
    public $audit_type = 'internal';
    public $scheduled_date;

    protected $rules = [
        'sheq_audit_template_id' => 'required',
        'department_id' => 'required',
        'lead_auditor_id' => 'required',
        'scheduled_date' => 'required',
    ];

    public function mount(){
        $this->templates = SheqAuditTemplate::where('is_active',1)->orderBy('name','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->sheq_audit_template_id = "";
        $this->department_id = "";
        $this->lead_auditor_id = "";
        $this->auditee_id = "";
        $this->audit_type = "internal";
        $this->scheduled_date = "";
    }

    public function auditNumber(){
        $last_id = SheqAudit::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'AUD'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $sheq_audit = new SheqAudit;
        $sheq_audit->user_id = Auth::user()->id;
        $sheq_audit->audit_number = $this->auditNumber();
        $sheq_audit->sheq_audit_template_id = $this->sheq_audit_template_id;
        $sheq_audit->department_id = $this->department_id;
        $sheq_audit->lead_auditor_id = $this->lead_auditor_id;
        $sheq_audit->auditee_id = $this->auditee_id ?: Null;
        $sheq_audit->audit_type = $this->audit_type;
        $sheq_audit->scheduled_date = $this->scheduled_date;
        $sheq_audit->status = 'planned';
        $sheq_audit->save();

        $this->dispatchBrowserEvent('hide-sheq_auditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Scheduled Successfully!!"
        ]);
    }

    public function edit($id){
        $sheq_audit = SheqAudit::find($id);
        $this->sheq_audit_id = $sheq_audit->id;
        $this->sheq_audit_template_id = $sheq_audit->sheq_audit_template_id;
        $this->department_id = $sheq_audit->department_id;
        $this->lead_auditor_id = $sheq_audit->lead_auditor_id;
        $this->auditee_id = $sheq_audit->auditee_id;
        $this->audit_type = $sheq_audit->audit_type;
        $this->scheduled_date = $sheq_audit->scheduled_date ? Carbon::parse($sheq_audit->scheduled_date)->format('Y-m-d') : Null;
        $this->dispatchBrowserEvent('show-sheq_auditEditModal');
    }

    public function update(){
        $this->validate();

        $sheq_audit = SheqAudit::find($this->sheq_audit_id);
        $sheq_audit->sheq_audit_template_id = $this->sheq_audit_template_id;
        $sheq_audit->department_id = $this->department_id;
        $sheq_audit->lead_auditor_id = $this->lead_auditor_id;
        $sheq_audit->auditee_id = $this->auditee_id ?: Null;
        $sheq_audit->audit_type = $this->audit_type;
        $sheq_audit->scheduled_date = $this->scheduled_date;
        $sheq_audit->update();

        $this->dispatchBrowserEvent('hide-sheq_auditEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_audit_id = $id;
        $this->dispatchBrowserEvent('show-sheq_auditDeleteModal');
    }

    public function destroy(){
        $sheq_audit = SheqAudit::find($this->sheq_audit_id);
        if ($sheq_audit) {
            $sheq_audit->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_auditDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqAudit::query()->with(['template','department','lead_auditor','responses']);

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('audit_number','like',$search);
            });
        }

        $sheq_audits = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-audits.index',[
            'sheq_audits' => $sheq_audits
        ]);
    }
}
