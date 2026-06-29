<?php

namespace App\Http\Livewire\Payrolls;

use App\Models\User;
use App\Models\Salary;
use App\Models\Payroll;
use App\Models\Payslip;
use Livewire\Component;
use App\Models\Currency;
use App\Models\AccountType;
use Livewire\WithPagination;
use App\Models\PayrollSalary;
use App\Models\PayrollSalaryItem;
use App\Models\PayrollSalaryDetail;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    
    public $payroll;
    protected $payrolls;
    public $payroll_id;
    public $payroll_number;
    public $month;
    public $year;
    public $selectedCurrency;
    public $currencies;
    public $salaries;
    public $selected_payroll;

    public function mount(){
        $this->salaries = Salary::with('employee')->where('status',1)->get();
    }


    public function payrollNumber(){
       
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

            $payroll = Payroll::orderBy('id', 'desc')->first();

        if (!$payroll) {
            $payroll_number =  $initials .'L'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $payroll->id + 1;
            $payroll_number =  $initials .'L'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $payroll_number;


    }
    private function resetInputFields(){
        $this->payroll_number = '';
        $this->month = '';
        $this->year = '';
    }

    public function store(){

        $payroll = new Payroll;
        $payroll->user_id = Auth::user()->id;
        $payroll->payroll_number = $this->payrollNumber();
        $payroll->month = $this->month;
        $payroll->year = $this->year;
        $payroll->save();

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
                                $payroll_salary_item->recovery_id = $salary_item->recovery_id;
                                $payroll_salary_item->movement = $salary_item->movement;
                                $payroll_salary_item->allowance_id = $salary_item->allowance_id;
                                $payroll_salary_item->currency_id = $salary_item->currency_id;
                                $payroll_salary_item->amount = $salary_item->amount;
                                $payroll_salary_item->exchange_amount = $salary_item->exchange_amount;
                                $payroll_salary_item->exchange_rate = $salary_item->exchange_rate;
                                $payroll_salary_item->save();
                            }

                        }
                    }

                }

        $this->dispatchBrowserEvent('hide-payrollModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Created Successfully!!"
        ]);


    }

     public function getAuthorizer($id){
        if(is_null($id)){
            return ;
        }
        $user = User::find($id);
        return $user?->name." ".$user?->surname;
    }

    public function edit($id){
        $payroll = Payroll::find($id);
        $this->payroll_id = $id;
        $this->month = $payroll->month;
        $this->year = $payroll->year;
       
        $this->dispatchBrowserEvent('show-payrollEditModal');
    }

    public function update(){
        $payroll = Payroll::find($this->payroll_id);

        // Lock check: approved payrolls are immutable
        if ($payroll && $payroll->authorization === 'approved') {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'This payroll has been approved and cannot be edited.',
            ]);
            $this->dispatchBrowserEvent('hide-payrollEditModal');
            return;
        }

        $this->authorize('update', $payroll);

        $payroll->month = $this->month;
        $payroll->year = $this->year;
        $payroll->update();

        $payroll_salaries = $payroll->payroll_salaries;
        foreach ($payroll_salaries as $payroll_salary) {
            $payroll_salary->delete();
        }

        if (isset($this->salaries)) {
            if ($this->salaries->count()>0) {
                foreach ($this->salaries as $salary) {
                    $payroll_salary = new PayrollSalary;
                    $payroll_salary->payroll_id = $payroll->id;
                    $payroll_salary->salary_id = $salary->id;
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
    
        $this->dispatchBrowserEvent('hide-payrollEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Updated Successfully!!"
        ]);


    }

    public function delete($id){
        $payroll = Payroll::find($id);
        $this->selected_payroll = $payroll;
        $this->payroll_id = $id;
        $this->dispatchBrowserEvent('show-payrollDeleteModal');
    }

    public function destroy(){
        $payroll = Payroll::find($this->payroll_id);

        // Lock check: approved payrolls cannot be deleted
        if ($payroll && $payroll->authorization === 'approved') {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Approved payrolls cannot be deleted. Reverse the payroll first.',
            ]);
            $this->dispatchBrowserEvent('hide-payrollDeleteModal');
            return;
        }

        $this->authorize('delete', $payroll);

        $payroll_salaries = $payroll->payroll_salaries;

        if ($payroll_salaries) {
            foreach ($payroll_salaries as $payroll_salary) {

                $payroll_salary_items = $payroll_salary->payroll_salary_items;

                if ($payroll_salary_items) {
                    foreach ($payroll_salary_items as $item) {
                       $item->delete();
                    }
                }

                $payroll_salary->delete();
            }
        }
        $payroll->delete();

        $this->dispatchBrowserEvent('hide-payrollDeleteModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payroll Deleted Successfully!!"
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function render()
    {

        $base = Payroll::query()
        ->when($this->search, function ($q) {
            $term  = trim($this->search);
            $lower = strtolower($term);

            // Maps
            $numToFull = [
                1  => 'January',
                2  => 'February',
                3  => 'March',
                4  => 'April',
                5  => 'May',
                6  => 'June',
                7  => 'July',
                8  => 'August',
                9  => 'September',
                10 => 'October',
                11 => 'November',
                12 => 'December',
            ];

            $abbrToFull = [
                'jan'       => 'January',
                'january'   => 'January',
                'feb'       => 'February',
                'february'  => 'February',
                'mar'       => 'March',
                'march'     => 'March',
                'apr'       => 'April',
                'april'     => 'April',
                'may'       => 'May',
                'jun'       => 'June',
                'june'      => 'June',
                'jul'       => 'July',
                'july'      => 'July',
                'aug'       => 'August',
                'august'    => 'August',
                'sep'       => 'September',
                'sept'      => 'September',
                'september' => 'September',
                'oct'       => 'October',
                'october'   => 'October',
                'nov'       => 'November',
                'november'  => 'November',
                'dec'       => 'December',
                'december'  => 'December',
            ];

            $monthName = null;

            // Numeric month: 1, 01, 12, etc.
            if (preg_match('/^(0?[1-9]|1[0-2])$/', $term)) {
                $num = (int) $term;
                $monthName = $numToFull[$num] ?? null;
            }

            // Text month: Jan, January, etc.
            if (!$monthName && isset($abbrToFull[$lower])) {
                $monthName = $abbrToFull[$lower];
            }

            $q->where(function ($sub) use ($term, $monthName) {
                // 1) Match user name/surname
                $sub->whereHas('user', function ($uq) use ($term) {
                    $uq->where('name', 'like', '%'.$term.'%')
                    ->orWhere('surname', 'like', '%'.$term.'%');
                });

                // 2) If it's a 4-digit year, match year column
                if (preg_match('/^\d{4}$/', $term)) {
                    $sub->orWhere('year', $term);
                }

                // 3) Month name handling on string month column
                if ($monthName) {
                    // Column holds full month string, e.g. "January"
                    $sub->orWhere('month', 'like', '%'.$monthName.'%');
                } else {
                    // Fallback: if DB has "January" and user types "Jan" or similar
                    $sub->orWhere('month', 'like', '%'.$term.'%');
                }
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);
       
        return view('livewire.payrolls.index',[
            'payrolls' => $base
        ]);
    }
}
