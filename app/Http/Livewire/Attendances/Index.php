<?php

namespace App\Http\Livewire\Attendances;

use App\Exports\AttendanceRegisterExport;
use App\Models\Attendance;
use App\Models\AttendanceRegister;
use App\Models\Department;
use App\Models\Driver;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

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
    public $attendance_filter; 
    public $attendance_id;
    public $drivers;
    public $selectedDriver;
    public $date;
    public $time;
    public $user_id;
    public $notes;
    public $departments;
    public $selectedDepartment;
    public $department_id;
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
        $this->attendance_filter = "created_at";
    }

    public function exportAttendanceRegisterCSV(Excel $excel){
        return $excel->download(new AttendanceRegisterExport($this->from, $this->to, $this->department_id, $this->search, $this->attendance_filter), 'attendance_register_' .time().'.csv', Excel::CSV);
    }
    public function exportAttendanceRegisterPDF(Excel $excel){
        return $excel->download(new AttendanceRegisterExport($this->from, $this->to, $this->department_id, $this->search, $this->attendance_filter), 'attendance_register_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportAttendanceRegisterExcel(Excel $excel){
        return $excel->download(new AttendanceRegisterExport($this->from, $this->to, $this->department_id, $this->search, $this->attendance_filter), 'attendance_register_' .time().'.xlsx');
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
        $attendance->is_drivers = $this->is_drivers;
        $attendance->save();

        if ($this->status) {

            foreach ($this->status as $key => $status) {
                $attendance_register = new AttendanceRegister;
                $attendance_register->attendance_id = $attendance->id;
                $attendance_register->employee_id = $key;
                $attendance_register->status = $status ?? Null;
                $attendance_register->shift = $this->shift[$key] ?? Null;
                $attendance_register->checkin = $this->checkin[$key] ?? Null;
                $attendance_register->checkout = $this->checkout[$key] ?? Null;
                $attendance_register->notes = $this->notes[$key] ?? Null;
                $attendance_register->save();
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
    
    public function edit($id){
    
        $attendance = Attendance::find($id);
        $this->selectedDepartment = $attendance->department_id;
        $this->selected_department = Department::find($attendance->department_id);
        $this->loadEmployees();
        $this->date = $attendance->date;
        $this->time = $attendance->time;
        $this->user_id = $attendance->user_id;
        $this->is_drivers = $attendance->is_drivers ?? False;
        $this->attendance_id = $attendance->id;
        $attendance_registers = $attendance->attendance_registers;
        if($attendance_registers){
            foreach ($attendance_registers as $attendance_register) {
                $id = $attendance_register->employee_id;
                $this->shift[$id] = $attendance_register->shift ?? '';
                $this->status[$id] = $attendance_register->status ?? '';
                $this->checkin[$id] = $attendance_register->checkin ?? '';
                $this->checkout[$id] = $attendance_register->checkout ?? '';
                $this->notes[$id] = $attendance_register->notes ?? '';
            }
        }

          $this->dispatchBrowserEvent('show-attendanceEditModal');
    }

    public function update(){

        DB::transaction(function () {
        
        $this->validate();

        $attendance = Attendance::find($this->attendance_id);
        $attendance->date = $this->date;
        $attendance->time = $this->time;
        $attendance->department_id = $this->selectedDepartment;
        $attendance->is_drivers = $this->is_drivers;
        $attendance->update();

        if ($this->status) {
            foreach ($this->status as $employeeId => $status) {

                AttendanceRegister::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'employee_id'   => $employeeId,
                    ],
                    [
                        'status'   => $status ?? null,
                        'shift'    => $this->shift[$employeeId] ?? null,
                        'checkin'  => $this->checkin[$employeeId] ?? null,
                        'checkout' => $this->checkout[$employeeId] ?? null,
                        'notes'    => $this->notes[$employeeId] ?? null,
                    ]
                );
            }
        }

        $this->dispatchBrowserEvent('hide-attendanceEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Attendance Register Update Successfully!!"
        ]);
        
        });
    }


    public function render()
    {
       $search = trim($this->search);

        $attendances = Attendance::query()
           
            // ✅ date filter on date when from/to provided
            ->when($this->from || $this->to, function ($q) {
                $from = $this->from ?: null;
                $to   = $this->to ?: null;

            if ($from && $to) {
                $q->whereBetween('date', [$from, $to]);
                } elseif ($from) {
                    $q->where('date', '>=', $from);
                } else {
                    $q->where('date', '<=', $to);
                }
            })
            ->when($this->department_id, function($q){
                 $q->where('department_id', $this->department_id);
            })
            ->when($search !== '', function ($q) use ($search) {

                $q->where(function ($qq) use ($search) {

                    // department name
                    $qq->whereHas('department', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%");
                    })

                    // user name / surname / full name
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(name,' ',surname) LIKE ?", ["%{$search}%"]);
                    })

                    // date/time on created_at (works for "2026-02-05", "14:30", "2026-02-05 14")
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("DATE_FORMAT(created_at, '%H:%i') LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy($this->attendance_filter, 'desc')
            ->paginate(10);

        return view('livewire.attendances.index', [
            'attendances' => $attendances,
        ]);
    }
}
