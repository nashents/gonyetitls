<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
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

    }

    public function update(){

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
        $company->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Operational Settings Updated Successfully!!"
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
