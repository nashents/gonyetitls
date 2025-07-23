<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;

class Dates extends Component
{
    public $company;
    public $frequency;
    public $currencies;
    public $currency_id;
    public $companies;
    public $company_id;
    public $user_id;
    public $admin_id;
    public $default_rate;
    public $interest;
    public $rates_managed_by_finance;



    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->currency_id = $company->currency_id;
        $this->interest = $company->interest;
        $this->default_rate = $company->default_rate;
        $this->frequency = $company->exchange_rate_frequency;
        $this->rates_managed_by_finance = $company->rates_managed_by_finance;

    }

    public function update(){
        $company = Company::find($this->company_id);
        $company->rates_managed_by_finance = $this->rates_managed_by_finance;
        $company->currency_id = $this->currency_id;
        $company->exchange_rate_frequency = $this->frequency;
        $company->interest = $this->interest;
        $company->default_rate = $this->default_rate;
        $company->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Company Settings Updated Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }
    
    public function render()
    {
        return view('livewire.companies.dates');
    }
}
