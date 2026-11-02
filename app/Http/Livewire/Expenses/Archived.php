<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore an expense back to the active list. */
    public function restore($id)
    {
        $expense = Expense::find($id);
        if ($expense) {
            $expense->archive = 0;
            $expense->status  = 1;
            $expense->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Expense restored.']);
        }
    }

    public function render()
    {
        $query = Expense::with('account')->where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return view('livewire.expenses.archived', [
            'expenses' => $query->paginate(10),
        ]);
    }
}
