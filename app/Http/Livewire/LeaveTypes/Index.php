<?php

namespace App\Http\Livewire\LeaveTypes;

use Livewire\Component;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Index extends Component
{
    public $leave_type_id;
    public $leave_types;

    public $name;
    public $code;
    public $entitlement;

    public $is_paid = true;
    public $is_accruable = false;
    public $requires_medical_report = false;
    public $requires_attachment = false;
    public $carry_forward_allowed = false;

    public $monthly_accrual_rate = 0;
    public $max_carry_forward_days = 0;
    public $max_consecutive_days;
    public $maximum_leave_days = 0;

    public $requires_hod_approval = true;
    public $requires_management_approval = true;

    public $active = true;
    public $description;

    public function mount()
    {
        $this->leave_types = LeaveType::orderBy('name')->get();
    }

    private function resetInputFields()
    {
        $this->leave_type_id = null;
        $this->name = '';
        $this->code = '';
        $this->entitlement = '';
        $this->is_paid = true;
        $this->is_accruable = false;
        $this->requires_medical_report = false;
        $this->requires_attachment = false;
        $this->carry_forward_allowed = false;
        $this->monthly_accrual_rate = 0;
        $this->max_carry_forward_days = 0;
        $this->max_consecutive_days = '';
        $this->maximum_leave_days = 0;
        $this->requires_hod_approval = true;
        $this->requires_management_approval = true;
        $this->active = true;
        $this->description = '';
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                Rule::unique('leave_types', 'name')
                    ->ignore($this->leave_type_id)
                    ->whereNull('deleted_at'),
            ],
            'code' => 'nullable|string|max:20',
            'entitlement' => 'nullable|numeric|min:0',
            'monthly_accrual_rate' => 'nullable|numeric|min:0',
            'max_carry_forward_days' => 'nullable|numeric|min:0',
            'max_consecutive_days' => 'nullable|numeric|min:0',
            'maximum_leave_days' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'is_paid' => 'boolean',
            'is_accruable' => 'boolean',
            'requires_medical_report' => 'boolean',
            'requires_attachment' => 'boolean',
            'carry_forward_allowed' => 'boolean',
            'requires_hod_approval' => 'boolean',
            'requires_management_approval' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function store()
    {
        $this->validate();

        try {
            LeaveType::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'code' => $this->code,
                'entitlement' => $this->entitlement,
                'is_paid' => $this->is_paid,
                'is_accruable' => $this->is_accruable,
                'requires_medical_report' => $this->requires_medical_report,
                'requires_attachment' => $this->requires_attachment,
                'carry_forward_allowed' => $this->carry_forward_allowed,
                'monthly_accrual_rate' => $this->monthly_accrual_rate ?: 0,
                'max_carry_forward_days' => $this->max_carry_forward_days ?: 0,
                'max_consecutive_days' => $this->max_consecutive_days ?: null,
                'maximum_leave_days' => $this->maximum_leave_days ?: 0,
                'requires_hod_approval' => $this->requires_hod_approval,
                'requires_management_approval' => $this->requires_management_approval,
                'active' => $this->active,
                'description' => $this->description,
            ]);

            $this->dispatchBrowserEvent('hide-leaveTypeModal');
            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Leave Type Created Successfully!!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Something went wrong while creating Leave Type!!'
            ]);
        }
    }

    public function edit($id)
    {
        $leave_type = LeaveType::findOrFail($id);

        $this->leave_type_id = $leave_type->id;
        $this->name = $leave_type->name;
        $this->code = $leave_type->code;
        $this->entitlement = $leave_type->entitlement;

        $this->is_paid = (bool) $leave_type->is_paid;
        $this->is_accruable = (bool) $leave_type->is_accruable;
        $this->requires_medical_report = (bool) $leave_type->requires_medical_report;
        $this->requires_attachment = (bool) $leave_type->requires_attachment;
        $this->carry_forward_allowed = (bool) $leave_type->carry_forward_allowed;

        $this->monthly_accrual_rate = $leave_type->monthly_accrual_rate;
        $this->max_carry_forward_days = $leave_type->max_carry_forward_days;
        $this->max_consecutive_days = $leave_type->max_consecutive_days;
        $this->maximum_leave_days = $leave_type->maximum_leave_days;

        $this->requires_hod_approval = (bool) $leave_type->requires_hod_approval;
        $this->requires_management_approval = (bool) $leave_type->requires_management_approval;

        $this->active = (bool) $leave_type->active;
        $this->description = $leave_type->description;

        $this->dispatchBrowserEvent('show-leaveTypeEditModal');
    }

    public function update()
    {
        $this->validate();

        try {
            $leave_type = LeaveType::findOrFail($this->leave_type_id);

            if ($leave_type->is_locked && $this->name !== $leave_type->name) {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'This leave type is a core system leave type - its name cannot be changed.'
                ]);
                return;
            }

            $leave_type->update([
                'name' => $this->name,
                'code' => $this->code,
                'entitlement' => $this->entitlement,
                'is_paid' => $this->is_paid,
                'is_accruable' => $this->is_accruable,
                'requires_medical_report' => $this->requires_medical_report,
                'requires_attachment' => $this->requires_attachment,
                'carry_forward_allowed' => $this->carry_forward_allowed,
                'monthly_accrual_rate' => $this->monthly_accrual_rate ?: 0,
                'max_carry_forward_days' => $this->max_carry_forward_days ?: 0,
                'max_consecutive_days' => $this->max_consecutive_days ?: null,
                'maximum_leave_days' => $this->maximum_leave_days ?: 0,
                'requires_hod_approval' => $this->requires_hod_approval,
                'requires_management_approval' => $this->requires_management_approval,
                'active' => $this->active,
                'description' => $this->description,
            ]);

            $this->dispatchBrowserEvent('hide-leaveTypeEditModal');
            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Leave Type Updated Successfully!!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Something went wrong while updating Leave Type!!'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $leave_type = LeaveType::findOrFail($id);

            if ($leave_type->is_locked) {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'This leave type is a core system leave type and cannot be deleted.'
                ]);
                return;
            }

            $leave_type->delete();

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Leave Type Deleted Successfully!!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Something went wrong while deleting Leave Type!!'
            ]);
        }
    }

    public function render()
    {
        $this->leave_types = LeaveType::orderBy('name')->get();

        return view('livewire.leave-types.index', [
            'leave_types' => $this->leave_types
        ]);
    }
}