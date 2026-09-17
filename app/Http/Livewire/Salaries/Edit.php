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
    
    public function remove($i, $rowIndex = null)
    {
        unset($this->inputs[$i]);
        if ($rowIndex !== null) {
            unset($this->selectedAllowance[$rowIndex], $this->allowance_amount[$rowIndex]);
        }
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
    
    public function deductionsRemove($l, $rowIndex = null)
    {
        unset($this->deductions_inputs[$l]);
        if ($rowIndex !== null) {
            unset($this->selectedDeduction[$rowIndex], $this->deduction_amount[$rowIndex]);
        }
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
    
    public function loansRemove($j, $rowIndex = null)
    {
        unset($this->loans_inputs[$j]);
        if ($rowIndex !== null) {
            unset($this->selectedLoan[$rowIndex]);
        }
    }
    


    public function mount($id){
        $salary = Salary::find($id);
        $this->salary_id = $salary->id;
        $this->basic = $salary->basic;
        $this->net = $salary->net;
        $this->gross = $salary->gross;
        $this->salary_number = $salary->salary_number;
        $this->currency_id = $salary->currency_id;
        $this->paye = $salary->paye;
        $this->aids_levy = $salary->aids_levy;
        $this->frequency = $salary->frequency;
        $this->selectedEmployee = $salary->employee_id;
        $this->salary_items = $salary->salary_items;

        // Pre-load existing allowances/deductions/loans directly into the same
        // selectedAllowance/selectedDeduction/selectedLoan arrays the "add new"
        // rows use (row 0, then the dynamic $inputs/$deductions_inputs/
        // $loans_inputs rows) — the blade already renders those correctly, it
        // was just never being handed the existing data (the old
        // existing_selectedAllowance/existing_selectedDeduction arrays were
        // computed but never referenced anywhere in the view; the loan version
        // of this was rendered but bound every row to selectedLoan.0, so
        // multiple existing loans collapsed onto the same slot).
        //
        // PAYE / AIDS Levy are excluded here — those stay driven purely by the
        // paye/aids_levy checkboxes and are recomputed automatically on save.
        $payeDeductionId = Deduction::where('name', 'PAYE')->value('id');
        $aidsLevyDeductionId = Deduction::where('name', 'AIDS Levy')->value('id');
        $autoDeductionIds = array_filter([$payeDeductionId, $aidsLevyDeductionId]);

        $existingAllowances = SalaryItem::where('salary_id', $id)->whereNotNull('allowance_id')->get()->values();
        foreach ($existingAllowances as $idx => $item) {
            $this->selectedAllowance[$idx] = $item->allowance_id;
            $this->allowance_amount[$idx] = $item->amount;
            if ($idx > 0) {
                $this->inputs[] = $idx;
                $this->i = $idx + 1;
            }
        }

        $existingDeductions = SalaryItem::where('salary_id', $id)
            ->whereNotNull('deduction_id')
            ->whereNotIn('deduction_id', $autoDeductionIds)
            ->get()->values();
        foreach ($existingDeductions as $idx => $item) {
            $this->selectedDeduction[$idx] = $item->deduction_id;
            $this->deduction_amount[$idx] = $item->amount;
            if ($idx > 0) {
                $this->deductions_inputs[] = $idx;
                $this->l = $idx + 1;
            }
        }

        $existingLoans = SalaryItem::where('salary_id', $id)->whereNotNull('loan_id')->get()->values();
        foreach ($existingLoans as $idx => $item) {
            $this->selectedLoan[$idx] = $item->loan_id;
            if ($idx > 0) {
                $this->loans_inputs[] = $idx;
                $this->j = $idx + 1;
            }
        }


        $this->employees = Employee::with('user')
        ->whereHas('user', function ($query) {
            $query->where('category', '!=', 'admin');
        })
        ->orderBy('name', 'asc')
        ->orderBy('surname', 'asc')
        ->get();
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

                // The form now shows the full current set of allowances/
                // deductions/loans (pre-loaded in mount(), editable, and
                // removable), so it represents the complete intended state —
                // replace the existing rows with it rather than appending, or
                // every save would duplicate everything already there.
                SalaryItem::where('salary_id', $this->salary_id)->whereNotNull('allowance_id')->delete();
                SalaryItem::where('salary_id', $this->salary_id)->whereNotNull('loan_id')->delete();
                // Deductions include PAYE/AIDS Levy alongside manual ones —
                // clearing all of them here (not just the manual selections)
                // means the fresh PAYE/AIDS computation below never stacks on
                // top of last save's amounts.
                SalaryItem::where('salary_id', $this->salary_id)->whereNotNull('deduction_id')->delete();

                $this->processSalaryItems($this->selectedAllowance, 'allowance_id', $this->allowance_amount);
                $this->processSalaryItems($this->selectedDeduction, 'deduction_id', $this->deduction_amount);

                if (!empty($this->selectedLoan)) {
                    foreach ($this->selectedLoan as $key => $loanId) {
                        if (empty($loanId)) continue;
                        $loan = Loan::find($loanId);
                        if ($loan && $loan->balance > 0) {
                            SalaryItem::create([
                                'salary_id' => $this->salary_id,
                                'loan_id' => $loanId,
                                'amount' => min($loan->balance, $loan->payment_per_month),
                            ]);
                        }
                    }
                }

                // Recompute totals from the full current set of salary items
                // (not just what changed this session) so gross/net/PAYE stay
                // correct even when this edit didn't re-touch every existing
                // allowance/deduction.
                $this->total_allowances = (float) SalaryItem::where('salary_id', $this->salary_id)
                    ->whereNotNull('allowance_id')->sum('amount');
                $this->total_deductions = (float) SalaryItem::where('salary_id', $this->salary_id)
                    ->where(function ($q) {
                        $q->whereNotNull('deduction_id')->orWhereNotNull('loan_id');
                    })->sum('amount');

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

                DB::commit();

                Session::flash('success','Salary Updated Successfully!!');
                return redirect()->route('salaries.index');
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

            // tax_brackets.lower_band/upper_band are stored as strings. Binding
            // $gross as a PHP float (rather than an int) against those VARCHAR
            // columns makes MySQL's implicit cast pick the wrong row — it was
            // matching the unbounded top bracket (upper_band IS NULL) instead
            // of the correct one, producing a negative amount that the
            // `$paye_var > 0` guard below then silently skipped. Casting both
            // sides explicitly to DECIMAL avoids that regardless of $gross's
            // PHP type.
            $tax_bracket = TaxBracket::where('currency_id', $this->currency_id)
                ->where('frequency', $this->frequency)
                ->where(function ($query) use ($gross) {
                    $query->whereRaw('CAST(lower_band AS DECIMAL(18,2)) <= ?', [$gross])->orWhereNull('lower_band');
                })
                ->where(function ($query) use ($gross) {
                    $query->whereRaw('CAST(upper_band AS DECIMAL(18,2)) >= ?', [$gross])->orWhereNull('upper_band');
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
