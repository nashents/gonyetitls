<?php

namespace App\Http\Livewire\Attendances;

use App\Models\Driver;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use Livewire\WithPagination;
use App\Models\AttendanceEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
     use WithPagination;

    protected $paginationTheme = 'bootstrap';

 
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    protected $attendances;
    public $attendance;
    public $attendance_id;
    public $drivers;
    public $selectedDriver;
    public $date;
    public $time;
    public $notes;
    public $departments;
    public $selectedDepartment;
    public $selected_department;
    public $employees;
    public $status = [];
    public $shift = [];
    public $employee_id = [];
    public $checkin = [];
    public $checkout = [];
    public $is_drivers = False;

    public function mount(){
        $this->departments = Department::orderBy('name','asc')->get();
        $this->drivers = collect();
        $this->employees = collect();
    }


   public function updatedSelectedDepartment($id)
    {
        if (blank($id)) {
            $this->selected_department = null;
            $this->employees = collect();
            return;
        }

        $this->selected_department = Department::find($id);

        $this->loadEmployees();
    }

    public function updatedIsDrivers()
    {
        // If no department selected yet, nothing to filter
        if (!$this->selected_department) {
            $this->employees = collect();
            return;
        }

        $this->loadEmployees();
    }

    private function loadEmployees(): void
    {
        if (!$this->selected_department) {
            $this->employees = collect();
            return;
        }

        $query = $this->selected_department->employees()
            ->where('archive', 0);

        if ($this->selected_department->name === 'Transport & Logistics') {
            // In T&L: default show non-drivers, checked show drivers
            if ($this->is_drivers === true) {
                $query->whereHas('driver');
            } else {
                $query->whereDoesntHave('driver');
            }
        } else {
            // Other departments: checkbox means "drivers only"
            if ($this->is_drivers === true) {
                $query->whereHas('driver');
            }
        }

        $this->employees = $query->get();
    }


     public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){
        $this->date = "";
        $this->time = "";
        $this->notes = "";
        $this->selectedDepartment = Null;
        $this->selected_department = Null;
        $this->is_drivers = False;
    }
    protected $rules = [
        'date' => 'required',
        'time' => 'required',
        'selectedDepartment' => 'required',
        
    ];

       public function attendanceNumber(){
       
        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $attendance = Attendance::orderBy('id', 'desc')->first();

        if (!$attendance) {
            $attendance_number =  $initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $attendance->id + 1;
            $attendance_number =  $initials .'I'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $attendance_number;


    }

    public function store(){

        DB::transaction(function () {
        
        $this->validate();

        $attendance = new Attendance();
        $attendance->attendance_number = $this->attendanceNumber();
        $attendance->user_id = Auth::user()->id;
        $attendance->date = $this->date;
        $attendance->time = $this->time;
        $attendance->department_id = $this->selectedDepartment;
        $attendance->save();

        if ($this->status) {

            foreach ($this->status as $key => $status) {
                $attendance_entry = new AttendanceEntry;
                $attendance_entry->attendance_id = $attendance->id;
                $attendance_entry->driver_id = Null;
                $attendance_entry->employee_id = $key;
                $attendance_entry->status = $status ?? Null;
                $attendance_entry->shift = $this->shift[$key] ?? Null;
                $attendance_entry->start_time = $this->checkin[$key] ?? Null;
                $attendance_entry->end_time = $this->checkout[$key] ?? Null;
                $attendance_entry->notes = $this->notes[$key] ?? Null;
                $attendance_entry->save();
            }

        }

        $this->dispatchBrowserEvent('hide-attendanceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Attendance Register Marked Successfully!!"
        ]);
        
        });
    }


    public function render()
    {
        $attendances = Attendance::query()->orderBy('created_at','desc')->paginate(10);
        return view('livewire.attendances.index',[
            'attendances' => $attendances
        ]);
    }
}
