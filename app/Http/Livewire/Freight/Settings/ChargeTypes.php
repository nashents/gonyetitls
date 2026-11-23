<?php

namespace App\Http\Livewire\Freight\Settings;

use App\Models\Account;
use App\Models\ChargeType;
use App\Models\ContainerStage;
use App\Services\Freight\PortExposureService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ChargeTypes extends Component
{
    public $charge_type_id;
    public $name;
    public $description;
    public $is_locked = false;
    public $revenue_account_id;
    public $expense_account_id;

    // Container free time (Charge Config free days + rate tiers)
    public $tracks_free_time = false;
    public $start_milestone;
    public $stop_milestone;
    public $is_line_specific = true;
    public $editing_built_in = false;

    public $revenueAccounts = [];
    public $expenseAccounts = [];

    protected function rules()
    {
        $milestones = array_keys(ContainerStage::options(true));
        $needsMilestones = $this->tracks_free_time && !$this->editing_built_in;

        return [
            'name' => 'required|string|max:255',
            'revenue_account_id' => 'nullable|exists:accounts,id',
            'expense_account_id' => 'nullable|exists:accounts,id',
            'start_milestone' => [Rule::requiredIf($needsMilestones), 'nullable', Rule::in($milestones)],
            'stop_milestone' => [Rule::requiredIf($needsMilestones), 'nullable', Rule::in($milestones), function ($attribute, $value, $fail) use ($milestones, $needsMilestones) {
                if ($needsMilestones && $value && $this->start_milestone
                    && array_search($value, $milestones, true) <= array_search($this->start_milestone, $milestones, true)) {
                    $fail('The stop milestone must come after the start milestone.');
                }
            }],
        ];
    }

    protected $validationAttributes = [
        'start_milestone' => 'start milestone',
        'stop_milestone' => 'stop milestone',
    ];

    public function mount()
    {
        $this->revenueAccounts = Account::active()->with('account_type')->whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name', 'Income');
        })->orderBy('name', 'asc')->get();

        $this->expenseAccounts = Account::active()->with('account_type')->whereHas('account_type.account_type_group', function ($query) {
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
        $this->tracks_free_time = $chargeType->hasFreeTime();
        $this->start_milestone = $chargeType->start_milestone;
        $this->stop_milestone = $chargeType->stop_milestone;
        $this->is_line_specific = $chargeType->is_line_specific;
        $this->editing_built_in = $chargeType->isBuiltInFreeTime();
        $this->resetValidation();

        // Keep this charge type's already-assigned accounts visible even if since deactivated.
        $this->revenueAccounts = Account::selectable($this->revenue_account_id)->with('account_type')->whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name', 'Income');
        })->orderBy('name', 'asc')->get();
        $this->expenseAccounts = Account::selectable($this->expense_account_id)->with('account_type')->whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name', 'Expenses');
        })->orderBy('name', 'asc')->get();
    }

    public function save()
    {
        $this->validate();

        $existing = $this->charge_type_id ? ChargeType::findOrFail($this->charge_type_id) : null;

        if ($existing && $existing->is_locked) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be edited.']);
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'is_locked' => $this->is_locked,
            'revenue_account_id' => $this->revenue_account_id ?: null,
            'expense_account_id' => $this->expense_account_id ?: null,
        ];

        if ($existing && $existing->isBuiltInFreeTime()) {
            // Port Storage / Demurrage / Detention: free-time setup is fixed by the system.
        } elseif ($this->tracks_free_time) {
            $data['start_milestone'] = $this->start_milestone;
            $data['stop_milestone'] = $this->stop_milestone;
            $data['is_line_specific'] = (bool) $this->is_line_specific;
            if (!$existing || !$existing->hasFreeTime()) {
                $data['exposure_code'] = ChargeType::makeExposureCode($this->name);
            }
        } elseif ($existing && $existing->hasFreeTime()) {
            if ($existing->freeTimeInUse()) {
                $this->addError('tracks_free_time', 'Free day policies, rate tiers or containers already use this charge type, so free-time tracking cannot be switched off.');
                return;
            }
            $data += ['exposure_code' => null, 'start_milestone' => null, 'stop_milestone' => null, 'is_line_specific' => true];
        }

        ChargeType::updateOrCreate(['id' => $this->charge_type_id], $data);

        $this->resetForm();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type saved.']);
    }

    public function resetForm()
    {
        $this->reset(['charge_type_id', 'name', 'description', 'is_locked', 'revenue_account_id', 'expense_account_id',
            'tracks_free_time', 'start_milestone', 'stop_milestone', 'is_line_specific', 'editing_built_in']);
        $this->resetValidation();
    }

    public function delete($id)
    {
        $chargeType = ChargeType::findOrFail($id);

        if ($chargeType->is_locked) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is locked and cannot be deleted.']);
            return;
        }

        if ($chargeType->isBuiltInFreeTime() || $chargeType->freeTimeInUse()) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'This charge type is used for container free time (Charge Config) and cannot be deleted.']);
            return;
        }

        $chargeType->delete();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Charge type removed.']);
    }

    public function render()
    {
        return view('livewire.freight.settings.charge-types', [
            'chargeTypes' => ChargeType::with(['revenue_account', 'expense_account'])->orderBy('name', 'asc')->get(),
            // Selectable = active stages; labels include deactivated custom stages still referenced.
            'milestones' => ContainerStage::options(),
            'stageLabels' => ContainerStage::options(true),
            'builtInClock' => $this->editing_built_in && $this->charge_type_id
                ? PortExposureService::builtInClock((string) ChargeType::whereKey($this->charge_type_id)->value('exposure_code'))
                : null,
        ]);
    }
}
