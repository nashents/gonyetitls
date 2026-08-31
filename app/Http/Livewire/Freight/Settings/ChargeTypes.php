<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\Account;
use App\Models\ChargeType;
use Livewire\Component;

class ChargeTypes extends Component
{
    public $charge_type_id;
    public $name;
    public $description;
    public $is_locked = false;
    public $revenue_account_id;
    public $expense_account_id;

    public $revenueAccounts = [];
    public $expenseAccounts = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'revenue_account_id' => 'nullable|exists:accounts,id',
            'expense_account_id' => 'nullable|exists:accounts,id',
        ];
    }

    public function mount()
    {
        $this->revenueAccounts = Account::with('account_type')->whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name', 'Income');
        })->orderBy('name', 'asc')->get();

        $this->expenseAccounts = Account::with('account_type')->whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name', 'Expenses');
        })->orderBy('name', 'asc')->get();
    }

    public function edit($id)
    {
        $chargeType = ChargeType::findOrFail($id);
        $this->charge_type_id = $chargeType->id;
        $this->name = $chargeType->name;
        $this->description = $chargeType->description;
        $this->is_locked = $chargeType->is_locked;
        $this->revenue_account_id = $chargeType->revenue_account_id;
        $this->expense_account_id = $chargeType->expense_account_id;
    }

    public function save()
    {
        $this->validate();

        if ($this->charge_type_id) {
            $existing = ChargeType::findOrFail($this->charge_type_id);

            if ($existing->is_locked) {
                $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be edited.']);
                return;
            }
        }

        ChargeType::updateOrCreate(
            ['id' => $this->charge_type_id],
            [
                'name' => $this->name,
                'description' => $this->description,
                'is_locked' => $this->is_locked,
                'revenue_account_id' => $this->revenue_account_id ?: null,
                'expense_account_id' => $this->expense_account_id ?: null,
            ]
        );

        $this->reset(['charge_type_id', 'name', 'description', 'is_locked', 'revenue_account_id', 'expense_account_id']);
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type saved.']);
    }

    public function delete($id)
    {
        $chargeType = ChargeType::findOrFail($id);

        if ($chargeType->is_locked) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be deleted.']);
            return;
        }

        $chargeType->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type removed.']);
    }

    public function render()
    {
        return view('livewire.freight.settings.charge-types', [
            'chargeTypes' => ChargeType::with(['revenue_account', 'expense_account'])->orderBy('name', 'asc')->get(),
        ]);
    }
}
