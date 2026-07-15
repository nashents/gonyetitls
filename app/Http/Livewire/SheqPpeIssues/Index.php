<?php

namespace App\Http\Livewire\SheqPpeIssues;

use App\Models\Employee;
use App\Models\SheqPpeIssue;
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
    public $due_filter = '';

    public $employees;

    public $sheq_ppe_issue_id;
    public $employee_id;
    public $ppe_type;
    public $size;
    public $quantity = 1;
    public $issue_date;
    public $next_replacement_date;
    public $acknowledged = 0;

    protected $rules = [
        'employee_id' => 'required',
        'ppe_type' => 'required',
        'issue_date' => 'required',
    ];

    public function mount(){
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->employee_id = "";
        $this->ppe_type = "";
        $this->size = "";
        $this->quantity = 1;
        $this->issue_date = "";
        $this->next_replacement_date = "";
        $this->acknowledged = 0;
    }

    public function store(){
        $this->validate();

        $issue = new SheqPpeIssue;
        $issue->user_id = Auth::user()->id;
        $issue->issued_by_id = Auth::user()->id;
        $this->fill_fields($issue);
        $issue->save();

        $this->dispatchBrowserEvent('hide-sheq_ppe_issueModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"PPE Issue Recorded Successfully!!"
        ]);
    }

    private function fill_fields($issue){
        $issue->employee_id = $this->employee_id;
        $issue->ppe_type = $this->ppe_type;
        $issue->size = $this->size;
        $issue->quantity = $this->quantity ?: 1;
        $issue->issue_date = $this->issue_date;
        $issue->next_replacement_date = $this->next_replacement_date ?: Null;
        $issue->acknowledged = $this->acknowledged ? 1 : 0;
    }

    public function edit($id){
        $issue = SheqPpeIssue::find($id);
        $this->sheq_ppe_issue_id = $issue->id;
        $this->employee_id = $issue->employee_id;
        $this->ppe_type = $issue->ppe_type;
        $this->size = $issue->size;
        $this->quantity = $issue->quantity;
        $this->issue_date = $issue->issue_date ? Carbon::parse($issue->issue_date)->format('Y-m-d') : Null;
        $this->next_replacement_date = $issue->next_replacement_date ? Carbon::parse($issue->next_replacement_date)->format('Y-m-d') : Null;
        $this->acknowledged = $issue->acknowledged;
        $this->dispatchBrowserEvent('show-sheq_ppe_issueEditModal');
    }

    public function update(){
        $this->validate();

        $issue = SheqPpeIssue::find($this->sheq_ppe_issue_id);
        $this->fill_fields($issue);
        $issue->update();

        $this->dispatchBrowserEvent('hide-sheq_ppe_issueEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"PPE Issue Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_ppe_issue_id = $id;
        $this->dispatchBrowserEvent('show-sheq_ppe_issueDeleteModal');
    }

    public function destroy(){
        $issue = SheqPpeIssue::find($this->sheq_ppe_issue_id);
        if ($issue) {
            $issue->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_ppe_issueDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"PPE Issue Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqPpeIssue::query()->with(['employee','issued_by']);

        if ($this->due_filter == 'due') {
            $query->whereDate('next_replacement_date','<', Carbon::today());
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where('ppe_type','like',$search)
                ->orWhereHas('employee', function($q) use ($search){
                    $q->where('name','like',$search)->orWhere('surname','like',$search);
                });
        }

        $sheq_ppe_issues = $query->orderBy('issue_date','desc')->paginate(10);

        return view('livewire.sheq-ppe-issues.index',[
            'sheq_ppe_issues' => $sheq_ppe_issues
        ]);
    }
}
