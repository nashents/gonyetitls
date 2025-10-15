<?php

namespace App\Http\Livewire\Salaries;

use App\Models\Loan;
use App\Models\Salary;
use App\Models\Earning;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Recovery;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\SalaryItem;
use App\Models\TaxBracket;
use App\Models\SalaryDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Create extends Component
{


    public $employees;
    public $currencies;
    public $loans;
    public $earning_recoveries;
    public $deduction_recoveries;
    public $loan_amount;
    public $deductions;
    public $allowances;
    public $earnings;
    public $salary_items;
    public $salary_item_id;
    public $net;
    public $payment_per_month;
    public $gross;
    public $salary;
    public $driver;


    public $salary_id;
    public $salary_number;
    public $selectedEmployee;
    public $selectedEarningCurrency;
    public $selectedAllowanceCurrency;
    public $selectedDeductionCurrency;
    public $selectedCurrency;
    public $selected_currency;
    public $basic;
    public $selectedEarning = [];
    public $selectedAllowance = [];
    public $allowance_amount = [];
    public $earning_amount = [];
    public $selectedDeduction = [];
    public $deduction_amount = [];
    public $selectedLoan = [];
    public $selectedRecovery = [];
    public $paye = false;
    public $aids_levy = false;
    public $total_allowances = 0;
    public $total_deductions = 0;
    public $frequency;
    public $exchange_amount;
    public $exchange_rate;

    public $earnings_inputs = [];
    public $e = 1;
    public $f = 1;
    
    public function earningsAdd($e)
    {
        $e = $e + 1;
        $this->e = $e;
        array_push($this->earnings_inputs ,$e);
    }
    
    public function earningsRemove($e)
    {
        unset($this->earnings_inputs[$e]);
    }
 
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

    public $recoveries_inputs = [];
    public $r = 1;
    public $s = 1;
    
    public function recoveriesAdd($r)
    {
        $r = $r + 1;
        $this->r = $r;
        array_push($this->recoveries_inputs ,$r);
    }
    
    public function recoveriesRemove($r)
    {
        unset($this->recoveries_inputs[$r]);
    }
  
    public $earnings_recoveries_inputs = [];
    public $er = 1;
    public $es = 1;
    
    public function earningsRecoveriesAdd($er)
    {
        $er = $er + 1;
        $this->er = $er;
        array_push($this->earnings_recoveries_inputs ,$er);
    }
    
    public function earningsRecoveriesRemove($er)
    {
        unset($this->earnings_recoveries_inputs[$er]);
    }
    

    public function updated($value){
        $this->validateOnly($value);
    }


    protected $messages =[
        'selectedEmployee.required' => 'Please select an employee.',
        'selectedEmployee.unique:employees,id' => 'This employee already has a salary.',
    ];
    protected $rules = [
        'selectedEmployee' => 'required|unique:salaries,id,NULL,id,deleted_at,NULL',
        'selectedCurrency' => 'required',
        'frequency' => 'required',
        
    ];

    public function salaryNumber(){

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
 
        $salary = Salary::orderBy('id','desc')->first();

        if (!$salary) {
            $salary_number =  $initials .'S'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $salary->id + 1;
            $salary_number =  $initials .'S'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $salary_number;

    }

    public function mount(){
        $this->employees = Employee::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->allowances = Allowance::where('status','1')->orderBy('name','asc')->get();
        $this->earnings = Earning::where('status','1')->orderBy('name','asc')->get();
        $this->deductions = Deduction::where('status','1')->where('name','!=','PAYE')->where('name','!=','AIDS Levy')->orderBy('name','asc')->get();
        $this->loans = collect();
       
        $this->salary_number = $this->salaryNumber();
    }

    public function updatedSelectedEmployee($id){
        if (!is_null($id)) {
            $this->loans =   Loan::where('employee_id',$id)->where('balance','>','0')->where('authorization','approved')->get();
            
            $employee = Employee::find($id);
            $this->driver = $employee?->driver;

            if ($this->driver) {
                $this->earning_recoveries =   Recovery::where('driver_id',$this->driver->id)->where('type','Gain')->where('balance','>','0')->where('authorization','approved')->get();
                $this->deduction_recoveries =   Recovery::where('driver_id',$this->driver->id)->where('type','Loss')->where('balance','>','0')->where('authorization','approved')->get();
            }
           
        }
    }

    public function updatedSelectedAllowance($id, $key){
            if (!is_null($id)) {
              $allowance = Allowance::find($id);
              if (isset($allowance)) {
                 $this->selectedAllowanceCurrency[$key] = $allowance->currency_id;
                    if ($allowance->calculate_by == "currency") {
                        $this->allowance_amount[$key] = $allowance->amount;
                    }elseif($allowance->calculate_by == "percentage"){
                        $this->allowance_amount[$key] = $allowance->percentage;
                    }
              }
            }
    }
  
    public function updatedSelectedEarning($id, $key){
            if (!is_null($id)) {
              $earning = Earning::find($id);
              if (isset($earning)) {
                    $this->selectedEarningCurrency[$key] = $earning->currency_id;
              }
            }
    }


    public function updatedSelectedDeduction($id, $key){
            if (!is_null($id)) {
                $deduction = Deduction::find($id);
                if (isset($deduction)) {
                     $this->selectedDeductionCurrency[$key] = $deduction->currency_id;
                      if ($deduction->calculate_by == "currency") {
                          $this->deduction_amount[$key] = $deduction->amount;
                      }elseif($deduction->calculate_by == "percentage"){
                          $this->deduction_amount[$key] = $deduction->percentage;
                      }
                }
            }
    }
  
    public function updatedSelectedCurrency($id){
            if (!is_null($id)) {
                $currency = Currency::find($id);
                if (isset($currency)) {
                     $this->selected_currency = $currency;
                }
            }
    }

    
    public function refresh($category){

        if($category == "earnings"){
            $this->earnings = Earning::where('status',1)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Earnings Refreshed Successfully!!."
            ]);
        }
        elseif($category == "allowances"){
            $this->allowances = Allowance::where('status',1)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowances Refreshed Successfully!!."
            ]);
        }
        elseif($category == "deductions"){
            $this->deductions = Deduction::where('status',1)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Deductions Refreshed Successfully!!."
            ]);
        }
    }

    
    public function store(){

     
        try {
            // Create Salary Record

            $existingSalary = Salary::where('employee_id', $this->selectedEmployee)->first();
            if ($existingSalary) {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Salary record already exists for this employee."
                ]);
               
            }else{

                // DB::beginTransaction();
                DB::transaction(function () {
                    
                $salary = new Salary;
                $salary->user_id = Auth::id();
                $salary->salary_number = $this->salary_number;
                $salary->employee_id = $this->selectedEmployee;
                $salary->currency_id = $this->selectedCurrency;
                $salary->basic = $this->basic;
                $salary->paye = $this->paye;
                $salary->aids_levy = $this->aids_levy;
                $salary->frequency = $this->frequency;
                $salary->exchange_rate = $this->exchange_rate;
                $salary->save();
            
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
                            $exchange_amount = $this->exchange_rate * $loan->payment_per_month;
                            SalaryItem::create([
                                'salary_id' => $this->salary_id,
                                'loan_id' => $loanId,
                                'amount' => $loan->payment_per_month,
                                'exchange_rate' => $this->exchange_rate,
                                'exchange_amount' => $exchange_amount,
                            ]);
                            $this->total_deductions += $loan->payment_per_month;
                        }elseif($loan && ($loan->balance > 0 && $loan->balance < $loan->payment_per_month)){
                            $exchange_amount = $this->exchange_rate * $loan->balance;
                            SalaryItem::create([
                                'salary_id' => $this->salary_id,
                                'loan_id' => $loanId,
                                'amount' => $loan->balance,
                                'exchange_rate' => $this->exchange_rate,
                                'exchange_amount' => $exchange_amount,
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


                 });
                
                Session::flash('success','Salary Created Successfully!!');
                return redirect()->route('salaries.index');

        

            }

        } catch (\Exception $e) {
            // DB::rollBack();
            Log::error("Salary Processing Error: " . $e->getMessage());
            throw $e;
        }
        

    }

        // Helper function for processing Salary Items
        private function processSalaryItems($items, $column, $amounts) {
            $total = 0;
            if (!empty($items)) {
                foreach ($items as $key => $item) {
                    if (!empty($item) && isset($amounts[$key])) {
                        $exchange_amount = $this->exchange_rate * $amounts[$key];
                        SalaryItem::create([
                            'salary_id' => $this->salary_id,
                            $column => $item,
                            'amount' => $amounts[$key],
                            'exchange_rate' => $this->exchange_rate,
                            'exchange_amount' =>  $exchange_amount,
                        ]);
                        $total += $amounts[$key];
                    }
                }
            }
            return $total;
        }
    
        // Helper function to process PAYE and AIDS Levy
        private function processPayeAndAidsLevy($gross) {
         
            $tax_bracket = TaxBracket::where('currency_id', $this->selectedCurrency)
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
                  
                    $payeDeduction = Deduction::firstOrCreate(
                        ['name' => 'PAYE'], 
                        ['status' => 1]
                    );
                    $exchange_amount = $this->exchange_rate * $paye_var;
                    SalaryItem::create([
                        'salary_id' => $this->salary_id,
                        'deduction_id' => $payeDeduction->id,
                        'amount' => $paye_var,
                        'exchange_rate' => $this->exchange_rate,
                        'exchange_amount' =>  $exchange_amount,
                    ]);
                    $this->total_deductions += $paye_var;
                    
                    if ($this->aids_levy) {

                        $aidsLevyDeduction = Deduction::firstOrCreate(
                            ['name' => 'AIDS Levy'], 
                            ['status' => 1]
                        );

                        $aids_levy_var = $paye_var * 0.03;
                        $exchange_amount = $this->exchange_rate *  $aids_levy_var;
                        SalaryItem::create([
                            'salary_id' => $this->salary_id,
                            'deduction_id' => $aidsLevyDeduction->id,
                            'amount' => $aids_levy_var,
                            'exchange_rate' => $this->exchange_rate,
                            'exchange_amount' =>  $exchange_amount,
                        ]);
                        $this->total_deductions += $aids_levy_var;
                    }
                }
            }
        }

    public function render()
    {
        $this->allowances = Allowance::where('status','1')->orderBy('name','asc')->get();
        $this->deductions = Deduction::where('status','1')->where('name','!=','PAYE')->where('name','!=','AIDS Levy')->orderBy('name','asc')->get();
        return view('livewire.salaries.create',[
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
        ]);
    }
}
