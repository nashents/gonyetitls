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




    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->enable_requisition_two_step_authorization = $company->enable_requisition_two_step_authorization;
     
    }

    public function update(){
     
        $company = Company::find($this->company_id);
        $company->enable_requisition_two_step_authorization = $this->enable_requisition_two_step_authorization;
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
