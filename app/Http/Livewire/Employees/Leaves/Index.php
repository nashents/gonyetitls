<?php

namespace App\Http\Livewire\Employees\Leaves;

use Livewire\Component;
use App\Models\Employee;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\EmployeesLeaveExport;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $employees;
    public $employee;
    public $employee_id;
    public $leave_days;
    public $accrual_rate;
    public $maximum_leave_days;

    public function mount(){
        $this->resetPage();
    }

    public function exportEmployeesLeaveCSV(Excel $excel){

        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.csv', Excel::CSV);
    }
    public function exportEmployeesLeavePDF(Excel $excel){

        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.pdf', Excel::DOMPDF);
    }
    public function exportEmployeesLeaveExcel(Excel $excel){
        return $excel->download(new EmployeesLeaveExport, 'employees_leave_data.xlsx');
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'leave_days' => 'required',
        'accrual_rate' => 'required',
        'maximum_leave_days' => 'required',
    ];

    private function resetInputFields(){
        $this->leave_days = '';
        $this->accrual_rate= '';
        $this->maximum_leave_days= '';
    }


    public function showLeave($id){
        $this->employee_id = $id;
        $this->employee = Employee::find($id);
        $this->accrual_rate = $this->employee->accrual_rate;
        $this->leave_days = $this->employee->leave_days;
        $this->maximum_leave_days = $this->employee->maximum_leave_days;
        $this->dispatchBrowserEvent('show-leaveDaysModal');
    }

    public function update(){
        dd($this->accrual_rate);
        if (!is_null($this->employee_id)) {

            $employee = Employee::find($this->employee_id);
            $employee->leave_days = $this->leave_days;
            $employee->accrual_rate = $this->accrual_rate;
            $employee->maximum_leave_days = $this->maximum_leave_days;
            $employee->update();
            $this->dispatchBrowserEvent('hide-leaveDaysModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Leave Details Updated Successfully!!"
            ]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (isset($this->search)) {
               
            return view('livewire.employees.leaves.index',[
                'employees' => Employee::query()->where('employee_number','like', '%'.$this->search.'%')
                ->orWhere(DB::raw("concat(name, ' ', surname)"),'like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10),
            ]);
        }{
            return view('livewire.employees.leaves.index',[
                'employees' => Employee::where('status',1)->orderBy('name','asc')->paginate(10)
            ]);
        }
     
    }
}
