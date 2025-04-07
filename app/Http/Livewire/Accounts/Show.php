<?php

namespace App\Http\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;

class Show extends Component
{
    public $account;

    public function mount($id){
        $this->account = Account::find($id);
    }
    public function render()
    {
        return view('livewire.accounts.show');
    }
}
