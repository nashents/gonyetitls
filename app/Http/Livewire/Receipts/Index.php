<?php

namespace App\Http\Livewire\Receipts;

use App\Models\Cargo;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $receipts;
    public $receipt_id;
    public $invoices;
    public $invoice;
    public $invoice_id;
    public $customers;
    public $currencies;
    public $currency_id;
    public $receipt_number;
    public $invoice_number;
    public $date;
    public $amount;
    public $receipt;
    public $balance;
    public $old_receipt;

    public function mount(){

         $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->receipts = Receipt::latest()->get();
        } else {
            $this->receipts = Receipt::where('user_id',Auth::user()->id)->latest()->get();
        }
        $this->customers = Customer::orderBy('name','asc')->get();;
        $this->invoices = Invoice::orderBy('invoice_number','asc')->get();
        $this->currencies = Currency::all();

    }


    private function resetInputFields(){
        $this->invoice_number = '';
        $this->receipt_number = '';
        $this->amount = '';
        $this->currency_id = '';
        $this->receipt = '';
    }

    public function edit($id){
        $receipt = Receipt::find($id);
        $this->invoice_number = $receipt->invoice_number;
        $this->receipt_number = $receipt->receipt_number;
        $this->date = $receipt->date;
        $this->amount = $receipt->amount;
        $this->balance = $receipt->balance;
        $this->currency_id = $receipt->currency_id;
        $this->old_receipt = $receipt->filename;
        $this->receipt_id = $receipt->id;
        $this->invoice_id = $receipt->invoice_id;
        $this->dispatchBrowserEvent('show-receiptEditModal');
    }
    public function updateReceipt(){
        if(isset($this->receipt)){
            $file = $this->receipt;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/receipts', $fileNameToStore, 'my_files');

        }

        $this->receipt = Receipt::find($this->receipt_id);
        $this->receipt->user_id = Auth::user()->id;
        $this->receipt->invoice_number = $this->invoice_number;
        $this->receipt->receipt_number = $this->receipt_number;
        $this->receipt->currency_id = $this->currency_id;
        $this->receipt->date = $this->date;
        $this->receipt->save();

        $this->dispatchBrowserEvent('hide-receiptEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Receipt Updates Successfully!!"
        ]);


    }

    public function render()
    {
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->receipts = Receipt::latest()->get();
        } else {
            $this->receipts = Receipt::where('user_id',Auth::user()->id)->latest()->get();
        }
        return view('livewire.receipts.index',[
            'receipts' => $this->receipts
        ]);
    }
}
