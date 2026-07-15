<?php

namespace App\Http\Livewire\SheqMeetings;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqMeeting;
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
    public $employees;

    public $sheq_meeting_id;
    public $type;
    public $department_id;
    public $chairperson_id;
    public $meeting_date;
    public $attendees_count;
    public $agenda;
    public $minutes;
    public $status = 'scheduled';

    protected $rules = [
        'type' => 'required',
        'department_id' => 'required',
        'meeting_date' => 'required',
        'chairperson_id' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->type = "";
        $this->department_id = "";
        $this->chairperson_id = "";
        $this->meeting_date = "";
        $this->attendees_count = "";
        $this->agenda = "";
        $this->minutes = "";
        $this->status = "scheduled";
    }

    public function meetingNumber(){
        $last_id = SheqMeeting::withTrashed()->latest('id')->pluck('id')->first();
        $next = $last_id ? $last_id + 1 : 1;
        return 'MTG'. str_pad($next, 5, "0", STR_PAD_LEFT);
    }

    public function store(){
        $this->validate();

        $meeting = new SheqMeeting;
        $meeting->user_id = Auth::user()->id;
        $meeting->meeting_number = $this->meetingNumber();
        $meeting->type = $this->type;
        $meeting->department_id = $this->department_id;
        $meeting->chairperson_id = $this->chairperson_id;
        $meeting->meeting_date = $this->meeting_date;
        $meeting->attendees_count = $this->attendees_count ?: Null;
        $meeting->agenda = $this->agenda;
        $meeting->minutes = $this->minutes;
        $meeting->status = $this->status;
        $meeting->save();

        $this->dispatchBrowserEvent('hide-sheq_meetingModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Meeting Created Successfully!!"
        ]);
    }

    public function edit($id){
        $meeting = SheqMeeting::find($id);
        $this->sheq_meeting_id = $meeting->id;
        $this->type = $meeting->type;
        $this->department_id = $meeting->department_id;
        $this->chairperson_id = $meeting->chairperson_id;
        $this->meeting_date = $meeting->meeting_date ? Carbon::parse($meeting->meeting_date)->format('Y-m-d') : Null;
        $this->attendees_count = $meeting->attendees_count;
        $this->agenda = $meeting->agenda;
        $this->minutes = $meeting->minutes;
        $this->status = $meeting->status;
        $this->dispatchBrowserEvent('show-sheq_meetingEditModal');
    }

    public function update(){
        $this->validate();

        $meeting = SheqMeeting::find($this->sheq_meeting_id);
        $meeting->type = $this->type;
        $meeting->department_id = $this->department_id;
        $meeting->chairperson_id = $this->chairperson_id;
        $meeting->meeting_date = $this->meeting_date;
        $meeting->attendees_count = $this->attendees_count ?: Null;
        $meeting->agenda = $this->agenda;
        $meeting->minutes = $this->minutes;
        $meeting->status = $this->status;
        $meeting->update();

        $this->dispatchBrowserEvent('hide-sheq_meetingEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Meeting Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_meeting_id = $id;
        $this->dispatchBrowserEvent('show-sheq_meetingDeleteModal');
    }

    public function destroy(){
        $meeting = SheqMeeting::find($this->sheq_meeting_id);
        if ($meeting) {
            $meeting->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_meetingDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Meeting Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqMeeting::query()->with(['department','chairperson']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where(function($q) use ($search){
                $q->where('meeting_number','like',$search)
                  ->orWhere('agenda','like',$search);
            });
        }

        $sheq_meetings = $query->orderBy('meeting_date','desc')->paginate(10);

        return view('livewire.sheq-meetings.index',[
            'sheq_meetings' => $sheq_meetings
        ]);
    }
}
