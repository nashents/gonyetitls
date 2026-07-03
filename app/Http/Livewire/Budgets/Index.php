<?php

namespace App\Http\Livewire\Budgets;

use App\Models\Budget;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    public $company_id;
    public $currencies;

    public $name = [];
    public $module = [];
    public $period = [];
    public $value = [];
    public $currency_id = [];
    public $status = [];

    public $edit_name;
    public $edit_module;
    public $edit_period;
    public $edit_value;
    public $edit_currency_id;
    public $edit_status;
    public $budget_id;

    public $inputs = [];
    public $i = 1;

    public function mount()
    {
        $this->company_id = Auth::user()->employee->company->id ?? Auth::user()->company_id ?? null;

        $this->currencies = Currency::orderBy('name', 'asc')->get();

        $this->name[0] = '';
        $this->module[0] = '';
        $this->period[0] = '';
        $this->value[0] = '';
        $this->currency_id[0] = '';
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;

        array_push($this->inputs, $i);

        $this->name[$i] = '';
        $this->module[$i] = '';
        $this->period[$i] = '';
        $this->value[$i] = '';
        $this->currency_id[$i] = '';
    }

    public function remove($i)
    {
        $index = $this->inputs[$i] ?? null;

        if ($index !== null) {
            unset($this->name[$index]);
            unset($this->module[$index]);
            unset($this->period[$index]);
            unset($this->value[$index]);
            unset($this->currency_id[$index]);
        }

        unset($this->inputs[$i]);
        $this->inputs = array_values($this->inputs);
    }

    private function resetCreateInputFields()
    {
        $this->name = [];
        $this->module = [];
        $this->period = [];
        $this->value = [];
        $this->currency_id = [];

        $this->inputs = [];
        $this->i = 1;

        $this->name[0] = '';
        $this->module[0] = '';
        $this->period[0] = '';
        $this->value[0] = '';
        $this->currency_id[0] = '';
    }

    private function resetEditInputFields()
    {
        $this->edit_name = '';
        $this->edit_module = '';
        $this->edit_period = '';
        $this->edit_value = '';
        $this->edit_currency_id = '';
        $this->edit_status = '';
        $this->budget_id = null;
    }

    public function store()
    {
        $this->validate([
            'module.*' => 'required|string',
            'name.*' => 'required|string|max:255',
            'period.*' => 'required|string|max:255',
            'value.*' => 'required|numeric|min:0',
            'currency_id.*' => 'nullable',
        ]);

        foreach ($this->name as $key => $budgetName) {

            Budget::create([
                'user_id' => Auth::user()->id,
                'company_id' => $this->company_id,
                'name' => $budgetName,
                'module' => $this->module[$key] ?? null,
                'period' => $this->period[$key] ?? null,
                'value' => $this->value[$key] ?? 0,
                'currency_id' => !empty($this->currency_id[$key]) ? $this->currency_id[$key] : null,
                'status' => 1,
            ]);
        }

        $this->dispatchBrowserEvent('hide-addModal');

        $this->resetCreateInputFields();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Budget Created Successfully!!'
        ]);
    }
    public function edit($id)
    {

        $this->name = Null;
        $this->module = Null;
        $this->period = Null;
        $this->value = Null;
        $this->currency_id = Null;
        $this->status = Null;

        $budget = Budget::findOrFail($id);
    
        $this->budget_id = $budget->id;
        $this->name = $budget->name;
        $this->module = $budget->module;
        $this->period = $budget->period;
        $this->value = $budget->value;
        $this->currency_id = $budget->currency_id;
        $this->status = $budget->status;

        $this->dispatchBrowserEvent('show-updateModal');
    }

    public function update()
    {
        $this->validate([
            'module' => 'required|string',
            'name' => 'required|string|max:255',
            'period' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'status' => 'required',
        ]);

        $budget = Budget::findOrFail($this->budget_id);

        $budget->update([
            'user_id' => Auth::user()->id,
            'name' => $this->name,
            'module' => $this->module,
            'period' => $this->period,
            'value' => $this->value,
            'currency_id' => !empty($this->currency_id) ? $this->currency_id : null,
            'status' => $this->status,
        ]);

        $this->dispatchBrowserEvent('hide-updateModal');

        $this->resetEditInputFields();

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Budget Updated Successfully!!'
        ]);
    }

    public function render()
    {
        $query = Budget::with('currency')
        ->where('company_id', $this->company_id);

        if (isset($this->search) && $this->search != "") {
            $query->where(function ($q) {
                $q->where('module', 'like', '%' . $this->search . '%')
                    ->orWhere('name', 'like', '%' . $this->search . '%')
                    ->orWhere('period', 'like', '%' . $this->search . '%')
                    ->orWhere('value', 'like', '%' . $this->search . '%')
                    ->orWhereHas('currency', function ($currency) {
                        $currency->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('symbol', 'like', '%' . $this->search . '%');
                    });
            });
        }

        $budgets = $query->orderBy('name', 'asc')->paginate(10);

        return view('livewire.budgets.index', [
            'budgets' => $budgets,
        ]);
    }
}