<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;

class Dates extends Component
{
    public $company;
    public $name;
    public $period;
    public $email;
    public $second_email;
    public $third_email;
    public $noreply;
    public $role_id;
    public $offloading_details;
    public $transporter_offloading_details;
    public $invoice_serialize_by_customer;
    public $quotation_serialize_by_customer;
    public $roles;
    public $phonenumber;
    public $second_phonenumber;
    public $third_phonenumber;
    public $rates_managed_by_finance;
    public $country;
    public $allowable_loss_percentage;
    public $website;
    public $city;
    public $suburb;
    public $fiscalize;
    public $street_address;
    public $currencies;
    public $currency_id;
    public $companies;
    public $vat_number;
    public $tin_number;
    public $company_id;
    public $user_id;
    public $admin_id;
    public $quotation_memo;
    public $quotation_footer;
    public $invoice_memo;
    public $invoice_footer;
    public $receipt_footer;
    public $receipt_memo;
    public $color;
    public $vat;
    public $interest;



    public function mount($id){
        $company = Company::find($id);
        $this->company = $company;
        $this->company_id = $company->id;
        $this->name = $company->name;
        $this->email = $company->email;
        $this->second_email = $company->second_email;
        $this->third_email = $company->third_email;
        $this->noreply = $company->noreply;
        $this->phonenumber = $company->phonenumber;
        $this->second_phonenumber = $company->second_phonenumber;
        $this->third_phonenumber = $company->third_phonenumber;
        $this->rates_managed_by_finance = $company->rates_managed_by_finance;
        $this->country = $company->country;
        $this->tin_number = $company->tin_number;
        $this->vat_number = $company->vat_number;
        $this->allowable_loss_percentage = $company->allowable_loss_percentage;
        $this->city = $company->city;
        $this->suburb = $company->suburb;
        $this->period = $company->period;
        $this->street_address = $company->street_address;
        $this->color = $company->color;
        $this->fiscalize = $company->fiscalize;
        $this->website = $company->website;
        $this->vat = $company->vat;
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->currency_id = $company->currency_id;
        $this->interest = $company->interest;
        $this->quotation_memo = $company->quotation_memo;
        $this->quotation_footer = $company->quotation_footer;
        $this->transporter_offloading_details = $company->transporter_offloading_details;
        $this->offloading_details = $company->offloading_details;
        $this->quotation_serialize_by_customer = $company->quotation_serialize_by_customer;
        $this->invoice_serialize_by_customer = $company->invoice_serialize_by_customer;
        $this->invoice_memo = $company->invoice_memo;
        $this->invoice_footer = $company->invoice_footer;
        $this->receipt_memo = $company->receipt_memo;
        $this->receipt_footer = $company->receipt_footer;
    }

    public function update(){
       
        $company = Company::find($this->company_id);
        $company->name = $this->name;
        $company->email = $this->email;
        $company->second_email = $this->second_email;
        $company->third_email = $this->third_email;
        $company->noreply = $this->noreply;
        $company->phonenumber = $this->phonenumber;
        $company->second_phonenumber = $this->second_phonenumber;
        $company->third_phonenumber = $this->third_phonenumber;
        $company->rates_managed_by_finance = $this->rates_managed_by_finance;
        $company->currency_id = $this->currency_id;
        $company->website = $this->website;
        $company->fiscalize = $this->fiscalize;
        $company->vat_number = $this->vat_number;
        $company->tin_number = $this->tin_number;
        $company->allowable_loss_percentage = $this->allowable_loss_percentage;
        $company->country = $this->country;
        $company->city = $this->city;
        $company->period = $this->period;
        $company->suburb = $this->suburb;
        $company->street_address = $this->street_address;
        $company->vat = $this->vat;
        $company->interest = $this->interest;
        $company->color = $this->color;
        $company->quotation_footer = $this->quotation_footer;
        $company->quotation_memo = $this->quotation_memo;
        $company->offloading_details = $this->offloading_details;
        $company->transporter_offloading_details = $this->transporter_offloading_details;
        $company->quotation_serialize_by_customer = $this->quotation_serialize_by_customer;
        $company->invoice_serialize_by_customer = $this->invoice_serialize_by_customer;
        $company->invoice_memo = $this->invoice_memo;
        $company->invoice_footer = $this->invoice_footer;
        $company->receipt_memo = $this->receipt_memo;
        $company->receipt_footer = $this->receipt_footer;
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
