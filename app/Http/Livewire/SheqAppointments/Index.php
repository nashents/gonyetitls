<?php

namespace App\Http\Livewire\SheqAppointments;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SheqAppointment;
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
    public $status_filter = '';

    public $departments;
    public $employees;

    public $sheq_appointment_id;
    public $employee_id;
    public $title;
    public $type = 'functional';
    public $department_id;
    public $appointed_by_id;
    public $start_date;
    public $expiry_date;
    public $notes;
    public $status = 'active';

    protected $rules = [
        'employee_id' => 'required',
        'title' => 'required',
        'type' => 'required',
        'start_date' => 'required',
    ];

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
    }

    private function resetInputFields(){
        $this->employee_id = "";
        $this->title = "";
        $this->type = "functional";
        $this->department_id = "";
        $this->appointed_by_id = "";
        $this->start_date = "";
        $this->expiry_date = "";
        $this->notes = "";
        $this->status = "active";
    }

    public function store(){
        $this->validate();

        $appointment = new SheqAppointment;
        $appointment->user_id = Auth::user()->id;
        $appointment->employee_id = $this->employee_id;
        $appointment->title = $this->title;
        $appointment->type = $this->type;
        $appointment->department_id = $this->department_id ?: Null;
        $appointment->appointed_by_id = $this->appointed_by_id ?: Null;
        $appointment->start_date = $this->start_date;
        $appointment->expiry_date = $this->expiry_date ?: Null;
        $appointment->notes = $this->notes;
        $appointment->status = $this->status;
        $appointment->save();

        $this->dispatchBrowserEvent('hide-sheq_appointmentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Appointment Created Successfully!!"
        ]);
    }

    public function edit($id){
        $appointment = SheqAppointment::find($id);
        $this->sheq_appointment_id = $appointment->id;
        $this->employee_id = $appointment->employee_id;
        $this->title = $appointment->title;
        $this->type = $appointment->type;
        $this->department_id = $appointment->department_id;
        $this->appointed_by_id = $appointment->appointed_by_id;
        $this->start_date = $appointment->start_date ? Carbon::parse($appointment->start_date)->format('Y-m-d') : Null;
        $this->expiry_date = $appointment->expiry_date ? Carbon::parse($appointment->expiry_date)->format('Y-m-d') : Null;
        $this->notes = $appointment->notes;
        $this->status = $appointment->status;
        $this->dispatchBrowserEvent('show-sheq_appointmentEditModal');
    }

    public function update(){
        $this->validate();

        $appointment = SheqAppointment::find($this->sheq_appointment_id);
        $appointment->employee_id = $this->employee_id;
        $appointment->title = $this->title;
        $appointment->type = $this->type;
        $appointment->department_id = $this->department_id ?: Null;
        $appointment->appointed_by_id = $this->appointed_by_id ?: Null;
        $appointment->start_date = $this->start_date;
        $appointment->expiry_date = $this->expiry_date ?: Null;
        $appointment->notes = $this->notes;
        $appointment->status = $this->status;
        $appointment->update();

        $this->dispatchBrowserEvent('hide-sheq_appointmentEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Appointment Updated Successfully!!"
        ]);
    }

    public function delete($id){
        $this->sheq_appointment_id = $id;
        $this->dispatchBrowserEvent('show-sheq_appointmentDeleteModal');
    }

    public function destroy(){
        $appointment = SheqAppointment::find($this->sheq_appointment_id);
        if ($appointment) {
            $appointment->delete();
        }
        $this->dispatchBrowserEvent('hide-sheq_appointmentDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Appointment Deleted Successfully!!"
        ]);
    }

    public function render()
    {
        $query = SheqAppointment::query()->with(['department','employee','appointed_by']);

        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        if ($this->department_filter) {
            $query->where('department_id', $this->department_filter);
        }
        if ($this->status_filter == 'expired') {
            $query->whereDate('expiry_date','<', Carbon::today());
        } elseif ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        if ($this->search) {
            $search = '%'.$this->search.'%';
            $query->where('title','like',$search)
                ->orWhereHas('employee', function($q) use ($search){
                    $q->where('name','like',$search)->orWhere('surname','like',$search);
                });
        }

        $sheq_appointments = $query->orderBy('expiry_date','asc')->paginate(10);

        return view('livewire.sheq-appointments.index',[
            'sheq_appointments' => $sheq_appointments
        ]);
    }
}
