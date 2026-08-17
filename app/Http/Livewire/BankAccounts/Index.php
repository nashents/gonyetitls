<?php

namespace App\Http\Livewire\BankAccounts;

use Livewire\Component;
use App\Models\Currency;
use App\Models\BankAccount;
use App\Services\Accounting\BankAccountGlLinkService;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $name;
    public $currencies;
    public $currency_id;
    public $company_id;
    public $type;
    public $account_name;
    public $account_number;
    public $branch;
    public $branch_code;
    public $swift_code;
    public $status;
    public $bank_account_id;

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
    public function mount(){
        if (isset(Auth::user()->company)) {
            $this->company_id = Auth::user()->company->id;
          } else {
            $this->company_id = Auth::user()->employee->company->id;
          }
       
        $this->currencies = Currency::latest()->get();

    }

    private function resetInputFields(){
        $this->branch = "" ;
        $this->currency_id = "" ;
        $this->branch_code = "";
        $this->swift_code = "";
        $this->name = "";
        $this->account_number = "";
        $this->account_name = "";
        $this->status = "";
    }

    public function store(){
        try{

            if (isset($this->name)) {
                foreach ($this->name as $key => $value) {
                    $bank_account = new BankAccount;
                    $bank_account->user_id = Auth::user()->id;
                    $bank_account->company_id = $this->company_id;
                    if(isset($this->name[$key])){
                    $bank_account->name = $this->name[$key];
                    }
                    if(!empty($this->currency_id[$key])){
                    $bank_account->currency_id = $this->currency_id[$key];
                    }
                    if(isset($this->type[$key])){
                    $bank_account->type = $this->type[$key];
                    }
                    if(isset($this->account_number[$key])){
                    $bank_account->account_number = $this->account_number[$key];
                    }
                    if(isset($this->account_name[$key])){
                    $bank_account->account_name = $this->account_name[$key];
                    }
                    if(isset($this->branch[$key])){
                    $bank_account->branch = $this->branch[$key];
                    }
                    if(isset($this->branch_code[$key])){
                    $bank_account->branch_code = $this->branch_code[$key];
                    }
                    if(isset($this->swift_code[$key])){
                    $bank_account->swift_code = $this->swift_code[$key];
                    }
                    $bank_account->status = 1;
                    $bank_account->save();

                    app(BankAccountGlLinkService::class)->ensureLinkedAccount($bank_account);
                }
            }

            $this->dispatchBrowserEvent('hide-bank_accountModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bank Account(s) Uploaded Successfully!!"
            ]);
        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating bank account(s)!!"
            ]);
        }
    }

    public function edit($id){
        $bank_account = BankAccount::find($id);
        $this->name = $bank_account->name;
        $this->type = $bank_account->type;
        $this->account_number = $bank_account->account_number;
        $this->account_name = $bank_account->account_name;
        $this->branch = $bank_account->branch;
        $this->branch_code = $bank_account->branch_code;
        $this->currency_id = $bank_account->currency_id;
        $this->swift_code = $bank_account->swift_code;
        $this->status = $bank_account->status;
        $this->bank_account_id = $bank_account->id;
        $this->dispatchBrowserEvent('show-bank_accountEditModal');
    }
    public function update(){
        $bank_account = BankAccount::find($this->bank_account_id);
        $bank_account->user_id = Auth::user()->id;
        $bank_account->name = $this->name;
        $bank_account->type = $this->type;
        $bank_account->account_number = $this->account_number;
        $bank_account->account_name = $this->account_name;
        $bank_account->branch = $this->branch;
        $bank_account->currency_id = !empty($this->currency_id) ? $this->currency_id : null;
        $bank_account->branch_code = $this->branch_code;
        $bank_account->swift_code = $this->swift_code;
        $bank_account->status = $this->status;
        $bank_account->update();

        app(BankAccountGlLinkService::class)->syncLinkedAccount($bank_account);

        $this->dispatchBrowserEvent('hide-bank_accountEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bank Account Updated Successfully!!"
        ]);
    }
    public function render()
    {
      
        return view('livewire.bank-accounts.index',[
            'bank_accounts' =>   BankAccount::where('company_id',$this->company_id)->orderBy('name','asc')->get()
        ]);
    }
}
