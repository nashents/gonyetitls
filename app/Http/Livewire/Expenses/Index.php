<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Account;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{



    public $accounts;
    public $account_id;
    public $expenses;
    public $status;
    public $name;
    public $amount;
    public $currencies;
    public $currency_id;
    public $frequency;
    public $description;
    public $type;

    public $expense_id;
    public $user_id;

    public function mount(){
        $this->expenses = Expense::latest()->get();
        $this->currencies = Currency::latest()->get();
        $this->accounts = Account::latest()->get();
    }
    private function resetInputFields(){
        $this->account_id = '';
        $this->name = '';
        $this->amount = '';
        $this->frequency = '';
        $this->currency_id = '';
        $this->description = '';
        $this->type = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:expenses,name,NULL,id,deleted_at,NULL|string|min:2',
        'type' => 'required',
        'account_id' => 'required',
    ];

    public function store(){
        try{
        $expense = new Expense;
        $expense->user_id = Auth::user()->id;
        $expense->account_id = $this->account_id;
        $expense->name = $this->name;
        if (isset($this->currency_id) && $this->currency_id !="") {
            $expense->currency_id = $this->currency_id;
        }
      
        $expense->amount = $this->amount;
        $expense->frequency = $this->frequency;
        $expense->description = $this->description;
        $expense->type = $this->type;
        $expense->save();
        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-expenseModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating expenses!!"
        ]);
    }

    }

    public function edit($id){
    $expense = Expense::find($id);
    $this->user_id = $expense->user_id;
    $this->name = $expense->name;
    $this->type = $expense->type;
    $this->amount = $expense->amount;
    $this->currency_id = $expense->currency_id;
    $this->frequency = $expense->frequency;
    $this->description = $expense->description;
    $this->account_id = $expense->account_id;
    $this->status = $expense->status;
    $this->expense_id = $expense->id;
    $this->dispatchBrowserEvent('show-expenseEditModal');

    }

    public function update()
    {
        if ($this->expense_id) {
            try{
            $expense = Expense::find($this->expense_id);
            $expense->user_id = Auth::user()->id;
            $expense->account_id = $this->account_id;
            $expense->amount = $this->amount;
            if (isset($this->currency_id) && $this->currency_id  != "" ) {
                $expense->currency_id = $this->currency_id;
            }
           
            $expense->name = $this->name;
            $expense->type = $this->type;
            $expense->frequency = $this->frequency;
            $expense->description = $this->description;
            $expense->status = $this->status;
            $expense->update();

            $this->dispatchBrowserEvent('hide-expenseEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expense Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-expenseEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating expenses!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->expenses = Expense::latest()->get();
        return view('livewire.expenses.index',[
            'expenses' => $this->expenses
        ]);
    }
}
