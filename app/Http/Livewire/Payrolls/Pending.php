<?php

namespace App\Http\Livewire\Payrolls;

use Carbon\Carbon;
use App\Models\Salary;
use App\Models\Payroll;
use Livewire\Component;
use App\Models\PayrollSalary;
use App\Models\PayrollSalaryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{
    public $payrolls;
    public $payroll;
    public $payroll_id;
    public $salaries;
    public $authorize;
    public $comments;
    
    public function mount(){
        $this->payrolls = Payroll::where('authorization','pending')->latest()->get();
    }

    
    public function authorize($id){
        $payroll = Payroll::find($id);
        $this->payroll_id = $payroll->id;
        $this->payroll = $payroll;
        $this->salaries = Salary::with('employee')->where('status',1)->get();
        $this->dispatchBrowserEvent('show-authorizationModal');
    }

    public function update(){

      DB::transaction(function () {

            $payroll = Payroll::find($this->payroll_id);

            if (!$payroll) {
                throw new \Exception("Payroll not found.");
            }

            $payroll->authorized_by_id = Auth::id();
            $payroll->authorization = $this->authorize;
            $payroll->authorization_date = Carbon::today();
            $payroll->comments = $this->comments;
            $payroll->save();

            if ($this->authorize == "Approved") {
                
                if (isset($this->salaries)) {

                    if ($this->salaries->count()>0) {

                        foreach ($this->salaries as $salary) {

                            $payroll_salary = new PayrollSalary;
                            $payroll_salary->payroll_id = $payroll->id;
                            $payroll_salary->salary_id = $salary->id;
                            $payroll_salary->currency_id = $salary->currency_id;
                            $payroll_salary->employee_id = $salary->employee_id;
                            $payroll_salary->basic = $salary->basic;
                            $payroll_salary->gross = $salary->gross;
                            $payroll_salary->net = $salary->net;
                            $payroll_salary->total_deductions = $salary->total_deductions;
                            $payroll_salary->total_allowances = $salary->total_allowances;
                            $payroll_salary->save();
                            
                            foreach ($salary->salary_items as $salary_item) {

                                $payroll_salary_item = new PayrollSalaryItem;
                                $payroll_salary_item->payroll_salary_id = $payroll_salary->id;
                                $payroll_salary_item->salary_item_id = $salary_item->id;
                                $payroll_salary_item->loan_id = $salary_item->loan_id;
                                $payroll_salary_item->deduction_id = $salary_item->deduction_id;
                                $payroll_salary_item->allowance_id = $salary_item->allowance_id;
                                $payroll_salary_item->amount = $salary_item->amount;
                                $payroll_salary_item->save();

                            }

                        }
                    }

                }

                  $this->dispatchBrowserEvent('hide-authorizationModal');
                    $this->dispatchBrowserEvent('alert', [
                        'type' => 'success',
                        'message' => "Payroll Processed Successfully"
                    ]);
                    return redirect()->route('payrolls.approved');
            }

            
            // If rejected
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => "Payroll Rejected Successfully"
            ]);
              return redirect()->route('payrolls.rejected');
      });
    }
    public function render()
    {
        return view('livewire.payrolls.pending');
    }
}
