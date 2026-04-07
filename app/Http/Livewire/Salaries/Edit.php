<?php

namespace App\Http\Livewire\Salaries;

use App\Models\Loan;
use App\Models\Salary;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\SalaryItem;
use App\Models\TaxBracket;
use App\Models\SalaryDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{
    public $employees;
    public $currencies;
    public $loans;
    public $loan_amount;
    public $deductions;
    public $allowances;
    public $salary_items;
    public $salary_item_id;
    public $net;
    public $payment_per_month;
    public $gross;
    public $salary;


    public $salary_id;
    public $salary_number;
    public $selectedEmployee;
    public $currency_id;
    public $basic;

    public $existing_selectedAllowance = [];
    public $existing_allowance_amount = [];
    public $existing_selectedDeduction = [];
    public $existing_deduction_amount = [];
    public $existing_selectedLoan = [];

    public $selectedAllowance = [];
    public $allowance_amount = [];
    public $selectedDeduction = [];
    public $deduction_amount = [];
    public $selectedLoan = [];
    public $paye;
    public $aids_levy;
    public $total_allowances = 0;
    public $total_deductions = 0;
    public $frequency;

    public $inputs = [];
    public $i = 1;
    public $n = 1;
    
    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    
    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public $deductions_inputs = [];
    public $l = 1;
    public $m = 1;
    
    public function deductionsAdd($l)
    {
        $l = $l + 1;
        $this->l = $l;
        array_push($this->deductions_inputs ,$l);
    }
    
    public function deductionsRemove($l)
    {
        unset($this->deductions_inputs[$l]);
    }
    
    public $loans_inputs = [];
    public $j = 1;
    public $k = 1;
    
    public function loansAdd($j)
    {
        $j = $j + 1;
        $this->j = $j;
        array_push($this->loans_inputs ,$j);
    }
    
    public function loansRemove($j)
    {
        unset($this->loans_inputs[$j]);
    }
    


    public function mount($id){
        $salary = Salary::find($id);
        $this->salary_id = $salary->id;
        $this->basic = $salary->basic;
        $this->net = $salary->net;
        $this->gross = $salary->gross;
        $this->salary_number = $salary->salary_number;
        $this->currency_id = $salary->currency_id;
        $this->currency_id = $salary->paye;
        $this->aids_levy = $salary->aids_levy;
        $this->frequency = $salary->frequency;
        $this->selectedEmployee = $salary->employee_id;
        $this->salary_items = $salary->salary_items;
        $salary_items_loans = SalaryItem::where('salary_id',$id)->whereNotNull('loan_id')->get();
        $salary_items_deductions = SalaryItem::where('salary_id',$id)->whereNotNull('deduction_id')->get();
        $salary_items_allowances = SalaryItem::where('salary_id',$id)->whereNotNull('allowance_id')->get();
        if ($salary_items_loans) {
           foreach ($salary_items_loans as $item) {
                $this->existing_selectedLoan[] = $item->loan_id;
           }
        }
        if ($salary_items_allowances) {
           foreach ($salary_items_allowances as $item) {
            $this->existing_selectedAllowance[] = $item->allowance_id;
            $this->existing_allowance_amount[] = $item->amount;
           }
        }
        if ($salary_items_deductions) {
           foreach ($salary_items_deductions as $item) {
            $this->existing_selectedDeduction[] = $item->deduction_id;
            $this->existing_deduction_amount[] = $item->amount;
           }
        }
       

        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->allowances = Allowance::orderBy('name','asc')->get();
        $this->loans =   Loan::where('employee_id',$salary->employee_id)->where('balance','>','0')->where('authorization','approved')->get();
        $this->deductions = Deduction::orderBy('name','asc')->get();
      
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {

            $this->loans =   Loan::where('employee_id',$id)->where('balance','>','0')->where('authorization','approved')->get();
        }
    }

    public function update(){
        DB::beginTransaction();
        try {
            // Create Salary Record

                $salary = Salary::find($this->salary_id);
                $salary->employee_id = $this->selectedEmployee;
                $salary->currency_id = $this->currency_id;
                $salary->basic = $this->basic;
                $salary->frequency = $this->frequency;
                $salary->paye = $this->paye;
                $salary->aids_levy = $this->aids_levy;
                $salary->update();
                $this->salary_id = $salary->id;
                
                // Process Allowances
                $this->total_allowances = $this->processSalaryItems($this->selectedAllowance, 'allowance_id', $this->allowance_amount);
                
                // Process Deductions
                $this->total_deductions = $this->processSalaryItems($this->selectedDeduction, 'deduction_id', $this->deduction_amount);
                
                // Process Loans
                if (!empty($this->selectedLoan)) {
                    foreach ($this->selectedLoan as $key => $loanId) {
                        $loan = Loan::find($loanId);
                        if ($loan && $loan->balance >= $loan->payment_per_month) {
                            SalaryItem::create([
                                'salary_id' => $this->salary_id,
                                'selectedLoan' => $loanId,
                                'amount' => $loan->payment_per_month,
                            ]);
                            $this->total_deductions += $loan->payment_per_month;
                        }elseif($loan && ($loan->balance > 0 && $loan->balance < $loan->payment_per_month)){
                            SalaryItem::create([
                                'salary_id' => $this->salary_id,
                                'selectedLoan' => $loanId,
                                'amount' => $loan->balance,
                            ]);
                            $this->total_deductions += $loan->balance;
                        }
                    }
                }
                
                // Calculate Gross Salary
                $gross = $this->basic + $this->total_allowances;
                
                // Process PAYE & AIDS Levy
                if ($this->paye) {
                    $this->processPayeAndAidsLevy($gross);
                }
                
                // Final Salary Calculation
                $salary->gross = $gross;
                $salary->net = $gross - $this->total_deductions;
                $salary->total_allowances = $this->total_allowances;
                $salary->total_deductions = $this->total_deductions;
                $salary->update();
                
                Session::flash('success','Salary Created Successfully!!');
                return redirect()->route('salaries.index');


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
    }


          // Helper function for processing Salary Items
          private function processSalaryItems($items, $column, $amounts) {
            $total = 0;
            if (!empty($items)) {
                foreach ($items as $key => $item) {
                    if (!empty($item) && isset($amounts[$key])) {
                        SalaryItem::create([
                            'salary_id' => $this->salary_id,
                            $column => $item,
                            'amount' => $amounts[$key],
                        ]);
                        $total += $amounts[$key];
                    }
                }
            }
            return $total;
        }
    
        // Helper function to process PAYE and AIDS Levy
        private function processPayeAndAidsLevy($gross) {
         
            $tax_bracket = TaxBracket::where('currency_id', $this->currency_id)
                ->where('frequency', $this->frequency)
                ->where(function ($query) use ($gross) {
                    $query->where('lower_band', '<=', $gross)->orWhereNull('lower_band');
                })
                ->where(function ($query) use ($gross) {
                    $query->where('upper_band', '>=', $gross)->orWhereNull('upper_band');
                })
                ->first();
             
            
            if ($tax_bracket && is_numeric($tax_bracket->percentage)) {
                $gross_percentage = $gross * ($tax_bracket->percentage / 100);
                $paye_var = $gross_percentage - ($tax_bracket->rate ?? 0);
              
                if ($paye_var > 0) {
                  
                    $payeDeduction = Deduction::where('name', 'PAYE')->first();
                    SalaryItem::create([
                        'salary_id' => $this->salary_id,
                        'deduction_id' => $payeDeduction->id,
                        'amount' => $paye_var,
                    ]);
                    $this->total_deductions += $paye_var;
                    
                    if ($this->aids_levy) {
                        $aidsLevyDeduction = Deduction::where('name', 'AIDS Levy')->first();
                        $aids_levy_var = $paye_var * 0.03;
                        SalaryItem::create([
                            'salary_id' => $this->salary_id,
                            'deduction_id' => $aidsLevyDeduction->id,
                            'amount' => $aids_levy_var,
                        ]);
                        $this->total_deductions += $aids_levy_var;
                    }
                }
            }
        }

    public function render()
    {
        return view('livewire.salaries.edit');
    }
}
