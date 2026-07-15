<?php

namespace App\Http\Livewire\SheqContextIssues;

use App\Models\Department;
use App\Models\SheqContextIssue;
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
    public $type_filter = '';
    public $department_filter = '';

    public $departments;

    public $sheq_context_issue_id;
    public $department_id;
    public $type = 'internal';
    public $framework = 'swot';
    public $category;
    public $description;
    public $impact;
    public $review_date;
    public $status = 'open';

    protected $rules = [
        'department_id' => 'required',
        'type' => 'required',
        'category' => 'required',
        'description' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->type = "internal";
        $this->framework = "swot";
        $this->category = "";
        $this->description = "";
        $this->impact = "";
        $this->review_date = "";
        $this->status = "open";
    }

    public function store(){
        $this->validate();

        $issue = new SheqContextIssue;
        $issue->user_id = Auth::user()->id;
        $issue->department_id = $this->department_id;
        $issue->type = $this->type;
        $issue->framework = $this->framework;
        $issue->category = $this->category;
        $issue->description = $this->description;
        $issue->impact = $this->impact;
        $issue->review_date = $this->review_date ?: Null;
        $issue->status = $this->status;
        $issue->save();

        $this->dispatchBrowserEvent('hide-sheq_context_issueModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Context Issue Created Successfully!!"
        ]);
    }

    public function edit($id){
        $issue = SheqContextIssue::find($id);
        $this->sheq_context_issue_id = $issue->id;
        $this->department_id = $issue->department_id;
        $this->type = $issue->type;
        $this->framework = $issue->framework;
        $this->category = $issue->category;
        $this->description = $issue->description;
        $this->impact = $issue->impact;
        $this->review_date = $issue->review_date ? Carbon::parse($issue->review_date)->format('Y-m-d') : Null;
        $this->status = $issue->status;
        $this->dispatchBrowserEvent('show-sheq_context_issueEditModal');
    }

    public function update(){
        $this->validate();

        $issue = SheqContextIssue::find($this->sheq_context_issue_id);
        $issue->department_id = $this->department_id;
        $issue->type = $this->type;
        $issue->framework = $this->framework;
        $issue->category = $this->category;
        $issue->description = $this->description;
        $issue->impact = $this->impact;
        $issue->review_date = $this->review_date ?: Null;
        $issue->status = $this->status;
        $issue->update();

        $this->dispatchBrowserEvent('hide-sheq_context_issueEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Context Issue Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_context_issue_id = $id;
        $this->dispatchBrowserEvent('show-sheq_context_issueDeleteModal');
    }

    public function destroy(){
        $issue = SheqContextIssue::find($this->sheq_context_issue_id);
        if ($issue) {
            $issue->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_context_issueDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Context Issue Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqContextIssue::query()->with(['department']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('description','like',$search)
                  ->orWhere('impact','like',$search);
            });
        }

        $sheq_context_issues = $query->orderBy('created_at','desc')->paginate(10);

        return view('livewire.sheq-context-issues.index',[
            'sheq_context_issues' => $sheq_context_issues
        ]);
    }
}
