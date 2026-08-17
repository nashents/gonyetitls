<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;
use App\Models\Currency;

class Invoices extends Component
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
    public $invoice_template;
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
    //columns
    public $total_column;
    public $price_column;
    public $units_column;
    public $items_column;
    public $hide_items;
    public $hide_description;
    public $hide_price;
    public $hide_quantity;
    public $hide_amount;
    public $invoice_due_when;
    public $quote_valid_until;
    public $invoice_title;
    public $invoice_subheading;
    public $quotation_title;
    public $quotation_subheading;
    public $show_branding;



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
        $this->invoice_template = $company->invoice_template;
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
        $this->hide_amount = $company->hide_amount;
        $this->hide_description = $company->hide_description;
        $this->hide_items = $company->hide_items;
        $this->hide_quantity = $company->hide_quantity;
        $this->hide_price = $company->hide_price;
        $this->items_column = $company->items_column;
        $this->price_column = $company->price_column;
        $this->units_column = $company->units_column;
        $this->total_column = $company->total_column;
        $this->quotation_subheading = $company->quotation_subheading;
        $this->quotation_title = $company->quotation_title;
        $this->invoice_subheading = $company->invoice_subheading;
        $this->invoice_title = $company->invoice_title;
        $this->invoice_due_when = $company->invoice_due_when;
        $this->quote_valid_until = $company->quote_valid_until;
        $this->show_branding = $company->show_branding;
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
        $company->invoice_template = $this->invoice_template;
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
        $company->receipt_footer = $this->receipt_footer;
        $company->receipt_footer = $this->receipt_footer;
        $company->total_column = $this->total_column;
        $company->items_column = $this->items_column;
        $company->units_column = $this->units_column;
        $company->price_column = $this->price_column;
        $company->hide_amount = $this->hide_amount;
        $company->hide_description = $this->hide_description;
        $company->hide_price = $this->hide_price;
        $company->hide_items = $this->hide_items;
        $company->hide_quantity = $this->hide_quantity;
        $company->invoice_due_when = $this->invoice_due_when;
        $company->quote_valid_until = $this->quote_valid_until;
        $company->quotation_title = $this->quotation_title;
        $company->quotation_subheading = $this->quotation_subheading;
        $company->invoice_title = $this->invoice_title;
        $company->invoice_subheading = $this->invoice_subheading;
        $company->show_branding = $this->show_branding;
        $company->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Company Settings Updated Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function render()
    {
        return view('livewire.companies.invoices');
    }
}
