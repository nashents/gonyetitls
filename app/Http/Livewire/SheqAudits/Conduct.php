<?php

namespace App\Http\Livewire\SheqAudits;

use App\Models\Employee;
use App\Models\SheqAction;
use App\Models\SheqAudit;
use App\Models\SheqAuditResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Conduct extends Component
{
    public $sheq_audit_id;

    public $grading = [];
    public $actual_mark = [];
    public $findings = [];
    public $evidence = [];

    public $employees;

    public $action_item_id;
    public $action_title;
    public $action_description;
    public $action_employee_id;
    public $action_due_date;
    public $action_priority = 'medium';

    public $summary;
    public $recommendations;

    public function mount($sheq_audit_id){
        $this->sheq_audit_id = $sheq_audit_id;
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();

        $sheq_audit = SheqAudit::with('responses')->find($sheq_audit_id);
        $this->summary = $sheq_audit->summary;
        $this->recommendations = $sheq_audit->recommendations;

        foreach ($sheq_audit->responses as $response) {
            $this->grading[$response->sheq_audit_item_id] = $response->grading;
            $this->actual_mark[$response->sheq_audit_item_id] = $response->actual_mark;
            $this->findings[$response->sheq_audit_item_id] = $response->findings;
            $this->evidence[$response->sheq_audit_item_id] = $response->evidence;
        }
    }

    public function start(){
        $sheq_audit = SheqAudit::find($this->sheq_audit_id);
        $sheq_audit->status = 'in_progress';
        $sheq_audit->started_date = Carbon::today()->format('Y-m-d');
        $sheq_audit->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Started!!"
        ]);
    }

    public function saveItem($itemId){
        $sheq_audit = SheqAudit::find($this->sheq_audit_id);

        $mark = isset($this->actual_mark[$itemId]) && $this->actual_mark[$itemId] !== ''
            ? (int) $this->actual_mark[$itemId]
            : Null;

        $possible = \App\Models\SheqAuditItem::find($itemId)->possible_mark ?? 0;
        if (!is_null($mark) && $mark > $possible) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Actual mark cannot exceed possible mark of {$possible}!!"
            ]);
            return;
        }

        SheqAuditResponse::updateOrCreate(
            [
                'sheq_audit_id' => $sheq_audit->id,
                'sheq_audit_item_id' => $itemId,
            ],
            [
                'grading' => $this->grading[$itemId] ?? Null,
                'actual_mark' => $mark,
                'findings' => $this->findings[$itemId] ?? Null,
                'evidence' => $this->evidence[$itemId] ?? Null,
            ]
        );

        if ($sheq_audit->status == 'planned') {
            $sheq_audit->status = 'in_progress';
            $sheq_audit->started_date = Carbon::today()->format('Y-m-d');
            $sheq_audit->update();
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Response Saved Successfully!!"
        ]);
    }

    public function raiseAction($itemId){
        $this->action_item_id = $itemId;
        $item = \App\Models\SheqAuditItem::find($itemId);
        $this->action_title = $item ? 'Address audit finding: '.($item->code ?: 'requirement '.$item->id) : '';
        $this->action_description = $this->findings[$itemId] ?? '';
        $this->dispatchBrowserEvent('show-auditActionModal');
    }

    public function storeAction(){
        $this->validate([
            'action_title' => 'required',
            'action_employee_id' => 'required',
            'action_due_date' => 'required',
        ]);

        $sheq_audit = SheqAudit::find($this->sheq_audit_id);

        $last_id = SheqAction::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;

        $sheq_action = new SheqAction;
        $sheq_action->user_id = Auth::user()->id;
        $sheq_action->action_number = 'ACT'. str_pad($next, 5, "0", STR_PAD_LEFT);
        $sheq_action->department_id = $sheq_audit->department_id;
        $sheq_action->employee_id = $this->action_employee_id;
        $sheq_action->actionable_type = SheqAudit::class;
        $sheq_action->actionable_id = $sheq_audit->id;
        $sheq_action->source = 'audit';
        $sheq_action->reference = $sheq_audit->audit_number;
        $sheq_action->title = $this->action_title;
        $sheq_action->description = $this->action_description;
        $sheq_action->priority = $this->action_priority;
        $sheq_action->due_date = $this->action_due_date;
        $sheq_action->status = 'open';
        $sheq_action->save();

        $this->action_item_id = "";
        $this->action_title = "";
        $this->action_description = "";
        $this->action_employee_id = "";
        $this->action_due_date = "";
        $this->action_priority = "medium";

        $this->dispatchBrowserEvent('hide-auditActionModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Action Raised Successfully!!"
        ]);
    }

    public function completeAudit(){
        $sheq_audit = SheqAudit::find($this->sheq_audit_id);
        $sheq_audit->summary = $this->summary;
        $sheq_audit->recommendations = $this->recommendations;
        $sheq_audit->completed_date = Carbon::today()->format('Y-m-d');
        $sheq_audit->status = 'completed';
        $sheq_audit->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Audit Completed Successfully!!"
        ]);

        return redirect()->route('sheq_audits.show', $sheq_audit->id);
    }

    public function render()
    {
        $sheq_audit = SheqAudit::with(['template.sections.items','responses','department','lead_auditor'])->find($this->sheq_audit_id);

        return view('livewire.sheq-audits.conduct',[
            'sheq_audit' => $sheq_audit
        ]);
    }
}
