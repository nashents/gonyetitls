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
