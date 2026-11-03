<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use App\Models\Customer;
use Livewire\Component;
use App\Models\Employee;

class Settings extends Component
{
    public $company;
    public $company_id;
    public $enable_requisition_two_step_authorization;
    public $fleet_identifier_preference;
    public $default_booking_transaction_type;
    public $transporter_expense_recovery;
    public $transporter_fuel_recovery;
    public $transporter_fuel_markup;
    public $require_tyre_serial_on_dispatch;
    public $allowable_loss_percentage;
    public $chargeable_loss_rate;




    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->enable_requisition_two_step_authorization = $company->enable_requisition_two_step_authorization;
        $this->fleet_identifier_preference = $company->fleet_identifier_preference;
        $this->default_booking_transaction_type = $company->default_booking_transaction_type ?: 'expense';
        $this->transporter_expense_recovery = $company->transporter_expense_recovery ?: 'deduct';
        $this->transporter_fuel_recovery = $company->transporter_fuel_recovery ?: 'cost';
        $this->transporter_fuel_markup = $company->transporter_fuel_markup ?? 0;
        $this->require_tyre_serial_on_dispatch = (bool) ($company->require_tyre_serial_on_dispatch ?? true);
        $this->allowable_loss_percentage = $company->allowable_loss_percentage;
        $this->chargeable_loss_rate = $company->chargeable_loss_rate;

    }

    public function update(){
        $this->validate([
            'allowable_loss_percentage' => 'nullable|numeric|min:0|max:100',
            'chargeable_loss_rate' => 'nullable|numeric|min:0',
        ]);

        $company = Company::find($this->company_id);
        $company->enable_requisition_two_step_authorization = $this->enable_requisition_two_step_authorization;
        $company->fleet_identifier_preference = $this->fleet_identifier_preference;
        $company->default_booking_transaction_type = in_array($this->default_booking_transaction_type, ['expense', 'income'])
            ? $this->default_booking_transaction_type
            : 'expense';
        $company->transporter_expense_recovery = in_array($this->transporter_expense_recovery, ['deduct', 'invoice'])
            ? $this->transporter_expense_recovery
            : 'deduct';
        $company->transporter_fuel_recovery = $this->transporter_fuel_recovery === 'markup' ? 'markup' : 'cost';
        $company->transporter_fuel_markup = is_numeric($this->transporter_fuel_markup) ? max(0, (float) $this->transporter_fuel_markup) : 0;
        $company->require_tyre_serial_on_dispatch = (bool) $this->require_tyre_serial_on_dispatch;
        $company->allowable_loss_percentage = is_numeric($this->allowable_loss_percentage) ? $this->allowable_loss_percentage : null;
        $company->chargeable_loss_rate = is_numeric($this->chargeable_loss_rate) ? $this->chargeable_loss_rate : null;
        $company->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Operational Settings Updated Successfully!!"
        ]);
     
    }
    
    /**
     * Saves one loss default (allowable % or chargeable rate) and copies it
     * onto every customer. Customers can still be adjusted one by one
     * afterwards (Customers > Edit).
     */
    public function applyToCustomers($field){
        $rules = [
            'allowable_loss_percentage' => 'required|numeric|min:0|max:100',
            'chargeable_loss_rate'      => 'required|numeric|min:0',
        ];
        if (! isset($rules[$field])) {
            return;
        }
        $this->validate([$field => $rules[$field]]);

        $company = Company::find($this->company_id);
        $company->$field = $this->$field;
        $company->update();

        $count = Customer::where(function ($q) {
                $q->where('company_id', $this->company_id)->orWhereNull('company_id');
            })
            ->update([$field => $this->$field]);

        $label = $field === 'allowable_loss_percentage'
            ? "Allowable loss of {$this->$field}%"
            : "Chargeable loss rate of {$this->$field}";

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"{$label} applied to {$count} customer(s)!!"
        ]);
    }

    public function render()
    {
        $this->company = Company::find($this->company_id);
        return view('livewire.companies.settings',[
            'company' => $this->company
        ]);
    }
}
