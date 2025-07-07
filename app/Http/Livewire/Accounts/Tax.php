<?php

namespace App\Http\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;
use App\Models\AccountType;
use Illuminate\Support\Facades\Auth;

class Tax extends Component
{
    public $hs_code;
    public $name;
    public $abbreviation;
    public $rate;
    public $account_id;
    public $account_type_id;
    public $accounts;
    public $description;

    public function mount(){
         $account_type = AccountType::where('name','Sales Taxes')->first();
         $this->accounts = $account_type->accounts;
         $this->account_type_id = $account_type->id;
    }


        public function accountNumber(){
       
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

            $account = Account::orderBy('id', 'desc')->first();

        if (!$account) {
            $account_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $account->id + 1;
            $account_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $account_number;


    }

      protected $rules = [
        'name' => 'required|unique:accounts,name,NULL,id,deleted_at,NULL',
        'hs_code' => 'required',
        'rate' => 'required',
        'abbreviation' => 'required|unique:accounts,abbreviation,NULL,id,deleted_at,NULL',
       
    ];

      private function resetInputFields(){
        $this->hs_code = "";
        $this->name = "";
        $this->abbreviation = "";
        $this->description = "";
        $this->rate = "";
    }


        public function store(){
          
        $account = new Account;
        $account->user_id = Auth::id();
        $account->account_type_id = $this->account_type_id;
        $account->hs_code = $this->hs_code;
        $account->abbreviation = $this->abbreviation;
        $account->name = $this->name;
        $account->description = $this->description;
        $account->rate = $this->rate;
        $account->save();

        $this->dispatchBrowserEvent('hide-accountModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Account Created Successfully!!"
        ]);

    }

    public function edit($id){
       
        $tax_account = Account::find($id);
      
        $this->hs_code = $tax_account->hs_code;
        $this->rate = $tax_account->rate;
        $this->description = $tax_account->description;
        $this->name = $tax_account->name;
        $this->abbreviation = $tax_account->abbreviation;
        $this->account_id = $tax_account->id;

        $this->dispatchBrowserEvent('show-accountEditModal');
    }

    public function update(){

        $account = Account::find($this->account_id);
        $account->hs_code = $this->hs_code;
        $account->rate = $this->rate;
        $account->description = $this->description;
        $account->abbreviation = $this->abbreviation;
        $account->name = $this->name;
        $account->save();

        $this->dispatchBrowserEvent('hide-accountEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Account Updated Successfully!!"
        ]);

    }
    

    public function render()
    {
        return view('livewire.accounts.tax');
    }
}
