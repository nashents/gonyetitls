<?php

namespace App\Http\Livewire\Deductions;

use Livewire\Component;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Deduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $deductions;
    public $calculate_by = "currency";
    public $calculate_on = 'gross';
    public $description;
    public $name;
    public $amount;
    public $percentage;
    public $status;
    public $deduction_id;
    public $user_id;
    public $currencies;
    public $currency_id;
    public $is_system = false;

    // Shared premium (Medical Aid, Funeral Cover ...): company pays a % of a fixed premium.
    public $contribution_type = Deduction::CONTRIBUTION_EMPLOYEE;
    public $premium;
    public $employer_percentage;
    public $payable_account_id;
    public $expense_account_id;
    public $expense_account_drivers_id;
    public $employer_share_taxable = 1;
    public $paye_credit_percentage;
    public $payableAccounts;
    public $expenseAccounts;

    public function mount(){
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->payableAccounts = Account::whereHas('account_type', fn ($q) => $q->whereIn('name', ['Due for Payroll', 'Other Short-Term Liability']))
            ->orderBy('name')->get(['id', 'name']);
        $this->expenseAccounts = Account::whereHas('account_type', fn ($q) => $q->whereIn('name', ['Payroll Expense', 'Operating Expense', 'Cost Of Goods Sold']))
            ->orderBy('name')->get(['id', 'name']);
    }

    /** Extra rules when the deduction is a shared premium. */
    private function validateShared(): void
    {
        $this->validate([
            'contribution_type'      => 'required|in:employee,shared',
            'premium'                => 'required_if:contribution_type,shared|nullable|numeric|min:0.01',
            'employer_percentage'    => 'required_if:contribution_type,shared|nullable|numeric|min:0|max:100',
            'payable_account_id'     => 'required_if:contribution_type,shared|nullable|exists:accounts,id',
            'expense_account_id'     => 'required_if:contribution_type,shared|nullable|exists:accounts,id',
            'expense_account_drivers_id' => 'nullable|exists:accounts,id',
            'employer_share_taxable' => 'required|in:0,1',
            'paye_credit_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'premium.required_if'             => 'Enter the full premium per pay period.',
            'employer_percentage.required_if' => 'Enter the % the company pays.',
            'payable_account_id.required_if'  => 'Pick the Payable account (owed to the provider).',
            'expense_account_id.required_if'  => 'Pick the Expense account for the company share.',
        ]);
    }

    private function applySharedFields(Deduction $deduction): void
    {
        $deduction->contribution_type = $this->contribution_type ?: Deduction::CONTRIBUTION_EMPLOYEE;
        $deduction->payable_account_id = $this->payable_account_id ?: null;
        $deduction->paye_credit_percentage = $this->paye_credit_percentage !== '' ? $this->paye_credit_percentage : null;

        if ($deduction->isShared()) {
            $deduction->premium = $this->premium;
            $deduction->employer_percentage = $this->employer_percentage;
            $deduction->expense_account_id = $this->expense_account_id ?: null;
            $deduction->expense_account_drivers_id = $this->expense_account_drivers_id ?: null;
            $deduction->employer_share_taxable = (bool) $this->employer_share_taxable;
            // A salary is pre-filled with the EMPLOYEE share of the premium.
            $deduction->calculate_by = 'currency';
            $deduction->amount = $deduction->defaultEmployeeShare();
            $deduction->percentage = null;
        } else {
            $deduction->premium = null;
            $deduction->employer_percentage = null;
            $deduction->expense_account_id = null;
            $deduction->expense_account_drivers_id = null;
            $deduction->employer_share_taxable = true;
        }
    }

    private function loadSharedFields(Deduction $deduction): void
    {
        $this->contribution_type = $deduction->contribution_type ?: Deduction::CONTRIBUTION_EMPLOYEE;
        $this->premium = $deduction->premium;
        $this->employer_percentage = $deduction->employer_percentage !== null ? (float) $deduction->employer_percentage : null;
        $this->payable_account_id = $deduction->payable_account_id;
        $this->expense_account_id = $deduction->expense_account_id;
        $this->expense_account_drivers_id = $deduction->expense_account_drivers_id;
        $this->employer_share_taxable = $deduction->employer_share_taxable ? 1 : 0;
        $this->paye_credit_percentage = $deduction->paye_credit_percentage !== null ? (float) $deduction->paye_credit_percentage : null;
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'calculate_by' => 'required',
        'calculate_on' => 'required|in:basic,gross',
        'name' => 'required|unique:deductions,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->calculate_by = "currency";
        $this->calculate_on = 'gross';
        $this->name = '';
        $this->currency_id = '';
        $this->amount = '';
        $this->percentage = '';
        $this->description = '';
        $this->contribution_type = Deduction::CONTRIBUTION_EMPLOYEE;
        $this->premium = '';
        $this->employer_percentage = '';
        $this->payable_account_id = '';
        $this->expense_account_id = '';
        $this->expense_account_drivers_id = '';
        $this->employer_share_taxable = 1;
        $this->paye_credit_percentage = '';
        $this->is_system = false;
        $this->deduction_id = null;
    }

    public function store(){
        $this->validateShared();
        try{
        $deduction = new Deduction;
        $deduction->user_id = Auth::user()->id;
        $deduction->name = $this->name;
        $deduction->currency_id = $this->currency_id;
        $deduction->calculate_by = $this->calculate_by;
        $deduction->calculate_on = $this->calculate_on;
        if ($this->calculate_by == "currency") {
            $deduction->amount = $this->amount;
            $deduction->percentage = Null;
        }elseif ($this->calculate_by == "percentage") {
            $deduction->amount = Null;
            $deduction->percentage =  $this->percentage;
        }
       
        $deduction->description = $this->description;
        $this->applySharedFields($deduction);
        $deduction->status =1;
        $deduction->save();

        $this->dispatchBrowserEvent('hide-deductionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Deduction Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating deduction!!"
        ]);
    }
    }

    public function edit($id){
    $deduction = Deduction::find($id);
    $this->user_id = $deduction->user_id;
    $this->name = $deduction->name;
    $this->currency_id = $deduction->currency_id;
    $this->calculate_on = $deduction->calculate_on ?: 'gross';
    $this->calculate_by = $deduction->calculate_by;
    $this->amount = $deduction->amount;
    $this->percentage = $deduction->percentage;
    $this->description = $deduction->description;
    $this->deduction_id = $deduction->id;
    $this->status = $deduction->status;
    $this->is_system = $deduction->isSystem();
    $this->loadSharedFields($deduction);
    $this->resetValidation();
    $this->dispatchBrowserEvent('show-deductionEditModal');

    }


    public function update()
    {
        if ($this->deduction_id) {
            $this->validateShared();
            try{
            $deduction = Deduction::find($this->deduction_id);
            if ($deduction->isSystem() && $this->name !== $deduction->name) {
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"This deduction is a core system deduction - its name cannot be changed."
                ]);
                return;
            }
            $deduction->user_id = Auth::user()->id;
            $deduction->name = $this->name;
            $deduction->calculate_by = $this->calculate_by;
            $deduction->calculate_on = $this->calculate_on;
            $deduction->currency_id = $this->currency_id;
            if ($this->calculate_by == "currency") {
                $deduction->amount = $this->amount;
                $deduction->percentage = Null;
            }elseif ($this->calculate_by == "percentage") {
                $deduction->amount = Null;
                $deduction->percentage =  $this->percentage;
            }
            $deduction->description = $this->description;
            // PAYE / NSSA / ... are worked out by payroll - they can't be shared premiums.
            if (!$deduction->isSystem()) {
                $this->applySharedFields($deduction);
            }
            // System deductions must stay active — payroll depends on them.
            $deduction->status = $deduction->isSystem() ? 1 : $this->status;
            $deduction->update();

            $this->dispatchBrowserEvent('hide-deductionEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Deduction Updated Successfully!!"
            ]);


            // return redirect()->route('deductions.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-deductionEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating deduction!!"
            ]);
          }
        }
    }


    public function toggleStatus($id)
    {
        $deduction = Deduction::find($id);
        if (!$deduction) {
            return;
        }

        if ($deduction->isSystem()) {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"{$deduction->name} is a core system deduction used by payroll and cannot be deactivated."
            ]);
            return;
        }

        $deduction->status = $deduction->status == 1 ? 0 : 1;
        $deduction->save();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Deduction ".($deduction->status == 1 ? 'Activated' : 'Deactivated')." Successfully!!"
        ]);
    }

    public function render()
    {
        $this->deductions = Deduction::orderBy('name','asc')->get();
        return view('livewire.deductions.index',[
            'deductions'=>   $this->deductions
        ]);
    }
}
