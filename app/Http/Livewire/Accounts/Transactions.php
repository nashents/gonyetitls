<?php

namespace App\Http\Livewire\Accounts;
use App\Models\Account;
use App\Models\Payment;
use Livewire\Component;
use App\Models\CashFlow;

class Transactions extends Component
{
    public $payments;
    public $account;

    public function mount($id){

        $this->account = Account::find($id);
        $name = $this->account->name;
        $this->payments = Payment::with('account','customer','vendor')->whereYear('created_at',date('Y'))->where('account_id',$id)
        // ->orWhere('transaction_category',"Customer Deposits")
        ->orderBy('created_at','desc')->get();
    }

    public function render()
    {
        return view('livewire.accounts.transactions');
    }
}
