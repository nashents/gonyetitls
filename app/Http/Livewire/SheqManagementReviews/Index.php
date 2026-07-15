<?php

namespace App\Http\Livewire\SheqManagementReviews;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqManagementReview;
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
    public $department_filter = '';
    public $status_filter = '';

    public $departments;
    public $employees;

    public $sheq_management_review_id;
    public $department_id;
    public $chairperson_id;
    public $review_date;
    public $attendees;
    public $audit_results;
    public $customer_feedback;
    public $process_performance;
    public $incident_nc_status;
    public $action_status;
    public $objective_progress;
    public $compliance_status;
    public $risks_opportunities;
    public $resource_adequacy;
    public $improvement_opportunities;
    public $decisions;
    public $status = 'scheduled';

    protected $rules = [
        'review_date' => 'required',
        'chairperson_id' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->department_id = "";
        $this->chairperson_id = "";
        $this->review_date = "";
        $this->attendees = "";
        $this->audit_results = "";
        $this->customer_feedback = "";
        $this->process_performance = "";
        $this->incident_nc_status = "";
        $this->action_status = "";
        $this->objective_progress = "";
        $this->compliance_status = "";
        $this->risks_opportunities = "";
        $this->resource_adequacy = "";
        $this->improvement_opportunities = "";
        $this->decisions = "";
        $this->status = "scheduled";
    }

    public function reviewNumber(){
        $last_id = SheqManagementReview::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'MR'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $review = new SheqManagementReview;
        $review->user_id = Auth::user()->id;
        $review->review_number = $this->reviewNumber();
        $this->fill_fields($review);
        $review->save();

        $this->dispatchBrowserEvent('hide-sheq_management_reviewModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Management Review Created Successfully!!"
        ]);
    }

    private function fill_fields($review){
        $review->department_id = $this->department_id ?: Null;
        $review->chairperson_id = $this->chairperson_id;
        $review->review_date = $this->review_date;
        $review->attendees = $this->attendees;
        $review->audit_results = $this->audit_results;
        $review->customer_feedback = $this->customer_feedback;
        $review->process_performance = $this->process_performance;
        $review->incident_nc_status = $this->incident_nc_status;
        $review->action_status = $this->action_status;
        $review->objective_progress = $this->objective_progress;
        $review->compliance_status = $this->compliance_status;
        $review->risks_opportunities = $this->risks_opportunities;
        $review->resource_adequacy = $this->resource_adequacy;
        $review->improvement_opportunities = $this->improvement_opportunities;
        $review->decisions = $this->decisions;
        $review->status = $this->status;
    }

    public function edit($id){
        $review = SheqManagementReview::find($id);
        $this->sheq_management_review_id = $review->id;
        $this->department_id = $review->department_id;
        $this->chairperson_id = $review->chairperson_id;
        $this->review_date = $review->review_date ? Carbon::parse($review->review_date)->format('Y-m-d') : Null;
        $this->attendees = $review->attendees;
        $this->audit_results = $review->audit_results;
        $this->customer_feedback = $review->customer_feedback;
        $this->process_performance = $review->process_performance;
        $this->incident_nc_status = $review->incident_nc_status;
        $this->action_status = $review->action_status;
        $this->objective_progress = $review->objective_progress;
        $this->compliance_status = $review->compliance_status;
        $this->risks_opportunities = $review->risks_opportunities;
        $this->resource_adequacy = $review->resource_adequacy;
        $this->improvement_opportunities = $review->improvement_opportunities;
        $this->decisions = $review->decisions;
        $this->status = $review->status;
        $this->dispatchBrowserEvent('show-sheq_management_reviewEditModal');
    }

    public function update(){
        $this->validate();

        $review = SheqManagementReview::find($this->sheq_management_review_id);
        $this->fill_fields($review);
        $review->update();

        $this->dispatchBrowserEvent('hide-sheq_management_reviewEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Management Review Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_management_review_id = $id;
        $this->dispatchBrowserEvent('show-sheq_management_reviewDeleteModal');
    }

    public function destroy(){
        $review = SheqManagementReview::find($this->sheq_management_review_id);
        if ($review) {
            $review->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_management_reviewDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Management Review Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqManagementReview::query()->with(['department','chairperson','actions']);

        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('review_number','like',$search)
                  ->orWhere('decisions','like',$search);
            });
        }

        $sheq_management_reviews = $query->orderBy('review_date','desc')->paginate(10);

        return view('livewire.sheq-management-reviews.index',[
            'sheq_management_reviews' => $sheq_management_reviews
        ]);
    }
}
