<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;

class Show extends Component
{
    public $expense;

    public function mount($id){
        $this->expense = Expense::find($id);
    }
    public function render()
    {
        return view('livewire.expenses.show');
    }
}
