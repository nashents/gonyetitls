<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;

class Bills extends Component
{

    public $expense;
    public $expenses;
    public $expense_id;
    public $bill_expenses;
    public $bill_expense_id;
    public $bills;
    public $bill;
    public $bill_id;

    public function mount($id){
        $this->expense = Expense::find($id);
        $this->bill_expenses = $this->expense->bill_expenses;
    }
    public function render()
    {
        return view('livewire.expenses.bills');
    }
}
