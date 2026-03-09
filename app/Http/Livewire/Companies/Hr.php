<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;

class Hr extends Component
{
    public $company;
    public $company_id;
    public $maximum_leave_days;
    public $accrual_rate;
    public $days_calculation;




    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->maximum_leave_days = $company->maximum_leave_days;
        $this->accrual_rate = $company->accrual_rate;
        $this->days_calculation = $company->days_calculation;
    }

    public function update(){
        $employees = Employee::where('status',1)->where('archive',0)->get();
        $company = Company::find($this->company_id);
        $company->maximum_leave_days = $this->maximum_leave_days;
        $company->accrual_rate = $this->accrual_rate;
        $company->days_calculation = $this->days_calculation;
        $company->update();
        
        if (isset($employees)) {
      
            foreach ($employees as $employee) {
                $employee->maximum_leave_days = $this->maximum_leave_days;
                $employee->accrual_rate = $this->accrual_rate;
                $employee->update();
            }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Leave Details Updated & Effected To All Employees Successfully!!"
        ]);
     
    }
    
    public function render()
    {
        $this->company = Company::find($this->company_id);
        return view('livewire.companies.hr',[
            'company' => $this->company
        ]);
    }
}
