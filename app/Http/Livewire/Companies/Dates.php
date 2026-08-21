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
    public $valuation_method;
    public $default_weight;
    public $interest;
    public $rates_managed_by_finance;
    public $show_financials_to_drivers;



    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->currency_id = $company->currency_id;
        $this->interest = $company->interest;
        $this->valuation_method = $company->valuation_method;
        $this->default_rate = $company->default_rate;
        $this->default_weight = $company->default_weight;
        $this->frequency = $company->exchange_rate_frequency;
        $this->rates_managed_by_finance = $company->rates_managed_by_finance;
        $this->show_financials_to_drivers = $company->show_financials_to_drivers;

    }

    public function update(){
        $company = Company::find($this->company_id);
        $company->rates_managed_by_finance = $this->rates_managed_by_finance;
        $company->show_financials_to_drivers = $this->show_financials_to_drivers;
        $company->currency_id = $this->currency_id;
        $company->exchange_rate_frequency = $this->frequency;
        $company->valuation_method = $this->valuation_method;
        $company->interest = $this->interest;
        $company->default_rate = $this->default_rate;
        $company->default_weight = $this->default_weight;
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
