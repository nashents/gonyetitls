<?php

namespace App\Http\Livewire\Loans;

use App\Models\Loan;
use App\Models\Vendor;
use App\Models\Account;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\LoanType;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    
    public $employees;
    public $employee_id;
    public $amount;
    public $name;
    public $surname;
    public $total;
    public $balance;
    public $currencies;
    public $currency_id;
    public $vendors;
    public $vendor_id;
    public $asset_accounts;
    public $liability_accounts;
    public $account_id;
    public $movement;
    public $loan_types;
    public $loan_number;
    private $loans;
    public $loan_id;
    public $loan_type_id;
    public $period;
    public $purpose;
    public $date;
    public $interest;
    public $payment_per_month;

    public function mount(){
        $this->employees = Employee::orderBy('name')->get();
        $this->vendors = Vendor::orderBy('name')->get();
        $this->liability_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Liabilities & Credit Cards');
        })->orderBy('name','asc')->get();
        $this->asset_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Assets');
        })->orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name')->get();
        $this->loan_types = LoanType::orderBy('name')->get();
        $this->name = Auth::user()->employee->name;
        $this->loan_number = $this->loanNumber();
        $this->surname = Auth::user()->employee->surname;
        $this->interest = Auth::user()->employee->company->interest;
    }

    public function loanNumber(){
       
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

            $loan = Loan::orderBy('id', 'desc')->first();

        if (!$loan) {
            $loan_number =  $initials .'L'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $loan->id + 1;
            $loan_number =  $initials .'L'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $loan_number;


    }
    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'amount' => 'required',
        'period' => 'required',
        'date' => 'required',
        'purpose' => 'required',
        'loan_type_id' => 'required',
    ];

    private function resetInputFields(){
        $this->movement = Null;
        $this->loan_type = '';
        $this->currency_id = '';
        $this->employee_id = '';
        $this->account_id = '';
        $this->vendor_id = '';
        $this->amount = '';
        $this->balance = '';
        $this->interest = '';
        $this->period = '';
        $this->date = '';
        $this->purpose = '';
        $this->loan_type_id = '';
    }


    public function store(){
        // try{

        $loan = new Loan;
        $loan->user_id = Auth::user()->id;
        $loan->employee_id = $this->employee_id;
        $loan->vendor_id = $this->vendor_id;
        $loan->account_id = $this->account_id;
        $loan->movement = $this->movement;
        $loan->amount = $this->amount;
        $loan->loan_type_id = $this->loan_type_id;
        $loan->currency_id = $this->currency_id;
        $loan->interest = $this->interest;
        $loan->period = $this->period;
        $loan->loan_number = $this->loan_number;
        $loan->start_date = $this->date;
        $loan->purpose = $this->purpose;
        $loan->payment_per_month = $this->payment_per_month;
        $loan->amount = $this->amount;
        $loan->total = $this->total;
        $loan->balance = $this->total;
        $loan->authorization = 'pending';
        $loan->save();

        $this->dispatchBrowserEvent('hide-loanModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loan Application Submitted Successfully!!"
        ]);
//     }
//     catch(\Exception $e){
//     // Set Flash Message
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating loan!!"
//     ]);
// }
    }

    public function edit($id){
        $loan = Loan::find($id);
        $this->loan_id = $id;
        $this->loan_type_id = $loan->loan_type_id;
        $this->amount = $loan->amount;
        $this->currency_id = $loan->currency_id;
        $this->interest = $loan->interest;
        $this->movement = $loan->movement;
        $this->employee_id = $loan->employee_id;
        $this->vendor_id = $loan->vendor_id;
        $this->account_id = $loan->account_id;
        $this->period = $loan->period;
        $this->date = $loan->start_date;
        $this->purpose = $loan->purpose;
        $this->payment_per_month = $loan->payment_per_month;
        $this->total = $loan->total;
        $this->balance = $loan->balance;
        $this->dispatchBrowserEvent('show-loanEditModal');
    }

    public function update(){
        try{
        $loan =  Loan::find($this->loan_id);
        $loan->loan_type_id = $this->loan_type_id;
        $loan->currency_id = $this->currency_id;
        $loan->interest = $this->interest;
        $loan->vendor_id = $this->vendor_id;
        $loan->employee_id = $this->employee_id;
        $loan->account_id = $this->account_id;
        $loan->movement = $this->movement;
        $loan->period = $this->period;
        $loan->loan_number = $this->loan_number;
        $loan->start_date = $this->date;
        $loan->purpose = $this->purpose;
        $loan->payment_per_month = $this->payment_per_month;
        $loan->amount = $this->amount;
        $loan->total = $this->total;
        $loan->balance = $this->total;
        $loan->authorization = 'pending';
        $loan->update();

        $this->dispatchBrowserEvent('hide-loanEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Loan Application Updated Successfully!!"
        ]);
    }
    catch(\Exception $e){
    // Set Flash Message
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something goes wrong while updating loan!!"
    ]);
}
    }
    public function render()
    {
        if ((is_numeric($this->amount) && $this->amount > 0)) {
            if (is_numeric($this->interest) && $this->interest > 0) {
                $interest_amount = $this->amount * ($this->interest/100);
                if (isset( $interest_amount)) {
                 $this->total =  $interest_amount + $this->amount;
                 if (filled($this->period)) {
                     $this->payment_per_month = $this->total / $this->period;
                    } 
                }
            }else{
                $this->total = $this->amount;
                if (filled($this->period)) {
                    $this->payment_per_month = $this->total / $this->period;
                   } 
            }
           
         
          
        }
        $this->vendors = Vendor::orderBy('name')->get();
          $this->employees = Employee::orderBy('name')->get();
        $this->liability_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Liabilities & Credit Cards');
        })->orderBy('name','asc')->get();
        $this->asset_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Assets');
        })->orderBy('name','asc')->get();
        $this->loan_types = LoanType::orderBy('name')->get();
        return view('livewire.loans.index',[
            'loans' => Loan::orderBy('created_at','desc')->paginate(10),
            'total' => $this->total,
            'payment_per_month' => $this->payment_per_month,
            'vendors' => $this->vendors,
            'employees' => $this->employees,
            'loan_types' => $this->loan_types,
            'liability_accounts' => $this->liability_accounts,
            'asset_accounts' => $this->asset_accounts,
        ]);
    }
}
