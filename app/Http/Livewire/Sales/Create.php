<?php

namespace App\Http\Livewire\Sales;

use App\Models\Sale;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\BankAccount;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\InventoryDispatch;
use App\Models\InventoryRequisition;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    public $reason;

    public $inventories;
    public $invoice_number;
    public $products;
    public $selectedInventory = [];
    public $selectedProduct = [];
    public $description;
    public $qty = [];
    public $amount = [];
    public $sales;
    public $sale_number;
    public $number;
    public $sale_id;
    public $initials;
    public $customers;
    public $exchange_rate;
    public $exchange_amount;
    public $selectedCustomer;
    public $bank_accounts;
    public $bank_account_id;
    public $company_id;
    public $currencies;
    public $destinations;
    public $selectedCurrency;
    public $tax_rate = [];
    public $tax_amount;
    public $total_tax_amount;
    public $sale_amount = 0;
    public $sale_sub_amount = 0;
    public $turnover = 0;
    public $sale_total_amount = 0;
    public $date;
    public $expiry;
    public $subheading;
    public $memo;
    public $footer;
    

    public $item_name;
    public $item_description;
    public $buy_price;
    public $sell_price;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $selectedTax = [];
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $sell = True;
    public $buy = False;

    // payment vars

    public $accounts;
    public $account_id;
    public $notes;
    public $invoice_balance;
    public $pop;
    public $reference_code;
    public $invoice_currency;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $customer_id;
    public $current_balance;
    public $mode_of_payment;
    public $payment_amount;
   

    
    public $item_subtotal = 0;
    public $subtotal = 0;
    public $subtotal_incl = 0;
    public $total = 0;
    public $user_id;
  
    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    private function resetInputFields(){
        $this->item_name = '';
        $this->item_description = '';
        $this->item_price = '';
        $this->tax_id = '';
        $this->sell = True;
        $this->buy = False;
    }

    public function updatedSelectedCustomer($id){

        $this->customer = Customer::find($id);
        $this->initials = $this->customer->initials;
        $this->sale_number = $this->saleNumber();
       
    
    }

    public function paymentNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
    
        $payment = Payment::orderBy('id','desc')->first();
    
        if (!$payment) {
            $payment_number =  $initials .'P'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $payment->id + 1;
            $payment_number =  $initials .'P'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $payment_number;
    
    
    }
 

    public function saleNumber(){
        if (Auth::user()->employee->company->sale_serialize_by_customer == TRUE) {
            if (isset($this->initials) &&  $this->initials != NULL) {
            }else {
                $str = Auth::user()->employee->company->name;
                $words = explode(' ', $str);
                if (isset($words[1][0])) {
                    $this->initials = $words[0][0].$words[1][0];
                }else {
                    $this->initials = $words[0][0];
                }
            }
            $sale = Sale::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();
    
            if (!$sale) {
                $this->number = 1;
                $sale_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                if ($sale->number) {
                    $this->number = $sale->number + 1;
                }else{
                    $this->number = $sale->id + 1;
                }
               
                $sale_number = $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $sale_number;
        }else {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $sale = Sale::orderBy('id','desc')->first();
            if (!$sale) {
                $this->number = 1;
                $sale_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $sale->id + 1;
                $sale_number =  $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $sale_number;
        }
    
    }

    public function mount(){

        $this->accounts = Account::where('account_type_id',1)->latest()->get();
        $this->sale_number = $this->saleNumber();
        $this->invoice_number = $this->invoiceNumber();
        $this->inventories = collect();

        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->income_account_id = Account::where('name','Sales')->first()->id;
        $this->bank_accounts = BankAccount::latest()->get();

        $this->products = Product::where('sell',true)->where('department','inventory')->whereHas('inventories', function ($query) {
            return $query->where('status',true);
        })->get()->sortBy('name')->sortBy('product.brand.name');
        
        $this->currencies = Currency::orderBy('name','asc')->get();
      
        $this->customers = Customer::orderBy('name','asc')->get();
        if (Auth::user()->employee->company) {
            $this->memo = Auth::user()->employee->company->sale_memo;
            $this->footer = Auth::user()->employee->company->sale_footer;
            $this->company_id = Auth::user()->employee->company->id;
        }elseif (Auth::user()->company) {
            $this->memo = Auth::user()->company->sale_memo;
            $this->footer = Auth::user()->company->sale_footer;
            $this->company_id = Auth::user()->company->id;
        }
    }

   
   
    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $this->inventories = Inventory::where('product_id',$id)->where('status',1)->get();
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->sale_price) {
                    $this->amount[$key] = $product->sale_price;
                }
                if ($product->tax_id) {
                    $this->selectedTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedInventory($id, $key){
        if (!is_null($id)) {
            $inventory = Inventory::find($id);
            if (isset($inventory)) {
                if ($inventory->rate) {
                    $this->amount[$key] = $inventory->rate;
                    $this->qty[$key] = 1;
                }
            }
           
        }
    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
            }
           
        }
    }



    public function updated($value){
        $this->validateOnly($value);
    }
    protected $messages = [
        'bank_account_id.required' => 'Bank Account field is required',

    ];

    protected $rules = [
        'sale_number' => 'required',
        'memo' => 'required',
        'subheading' => 'required',
        'expiry' => 'required',
        'date' => 'required',
        'footer' => 'required',
        'item_name' => 'required',
    ];

    public function storeItem(){
    // try{
    
        $product = new Product;
        $product->user_id = Auth::user()->id;
        $product->name = $this->item_name;
        $product->description = $this->item_description;
        $product->price = $this->buy_price;
        $product->sell_price = $this->sell_price;
        $product->department = "inventory";
        $product->sell = $this->sell;
        $product->buy = $this->buy;
        $product->account_id = $this->income_account_id;
        $product->expense_account_id = $this->expense_account_id;
        $product->tax_id = $this->tax_id;
        $product->save(); 

        $this->dispatchBrowserEvent('hide-product_serviceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Created Successfully!!"
        ]);

        // }
        //     catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while creating item!!"
        //     ]);
        //  }
    }


    public function receiptNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }
    
        $receipt = Receipt::orderBy('id','desc')->first();
    
        if (!$receipt) {
            $receipt_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $receipt->id + 1;
            $receipt_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $receipt_number;
    
    
    }


    public function invoiceNumber(){
        if (Auth::user()->employee->company->invoice_serialize_by_customer == TRUE) {
            if (isset($this->initials) &&  $this->initials != NULL) {
            }else {
                $str = Auth::user()->employee->company->name;
                $words = explode(' ', $str);
                if (isset($words[1][0])) {
                    $this->initials = $words[0][0].$words[1][0];
                }else {
                    $this->initials = $words[0][0];
                }
            }
            $invoice = Invoice::where('customer_id', $this->selectedCustomer)->orderBy('id', 'desc')->get()->first();
    
            if (!$invoice) {
                $this->number = 1;
                $invoice_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                if ($invoice->number) {
                    $this->number = $invoice->number + 1;
                }else{
                    $this->number = $invoice->id + 1;
                }
               
                $invoice_number = $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $invoice_number;
        }else {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $invoice = Invoice::orderBy('id','desc')->first();
            if (!$invoice) {
                $this->number = 1;
                $invoice_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $invoice->id + 1;
                $invoice_number =  $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $invoice_number;
        }
    
    }
 
  

    public function store(){
     
        $sale = new Sale;
        $sale->user_id = Auth::user()->id;
        $sale->company_id = $this->company_id;
        $sale->currency_id = $this->selectedCurrency;
        $sale->customer_id = $this->selectedCustomer;
        $sale->sale_number = $this->sale_number;
        $sale->account_id = $this->income_account_id;
        $sale->date = $this->date;
        $sale->save();

        $invoice = new Invoice;
        $invoice->user_id = Auth::user()->id;
        $invoice->company_id = $this->company_id;
        $invoice->sale_id = $sale->id;
        $invoice->bank_account_id = $this->bank_account_id;
        $invoice->currency_id = $this->selectedCurrency;
        $invoice->customer_id = $this->selectedCustomer;
        $invoice->invoice_number = $this->invoice_number;
        $invoice->account_id = $this->income_account_id;
        $invoice->date = $this->date;
        $invoice->expiry = $this->date;
        $invoice->reason = "General Invoice";
        $invoice->save();

      
        if (isset($this->selectedInventory)) {

            foreach($this->selectedInventory as $key => $value){

                $sale_item = new SaleItem;
                $sale_item->sale_id = $sale->id;
                
                if (isset($this->selectedProduct[$key])) {
                    $sale_item->product_id = $this->selectedProduct[$key];
                }
                if (isset($this->selectedInventory[$key])) {
                    $sale_item->inventory_id = $this->selectedInventory[$key];
                }
                if (isset($this->qty[$key])) {
                    $sale_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $sale_item->amount = $this->amount[$key];
                }
                if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]))) {
                    $sale_item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $sale_item->subtotal = $sale_item_subtotal;
                    $this->subtotal = $this->subtotal + $sale_item_subtotal;
                }
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
                    $this->tax_amount = ($sale_item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $sale_item->tax_amount =  $this->tax_amount;
                    $this->subtotal_incl =  $this->tax_amount + $sale_item_subtotal ;
                }else{
                    $this->subtotal_incl = $sale_item_subtotal;
                }
               
                $sale_item->subtotal_incl = $this->subtotal_incl;
                $sale_item->save();
               
               
                // recording invoice

                $invoice_item = new InvoiceItem;
                $invoice_item->invoice_id = $invoice->id;

                if (isset($this->selectedProduct[$key])) {
                    $sale_item->product_id = $this->selectedProduct[$key];
                }
                if (isset($this->selectedInventory[$key])) {
                    $sale_item->inventory_id = $this->selectedInventory[$key];
                }
                if (isset($this->qty[$key])) {
                    $invoice_item->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $invoice_item->amount = $this->amount[$key];
                }
                 if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]))) {
                    $invoice_item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $invoice_item->subtotal = $invoice_item_subtotal;
                }
                
                $invoice_item->tax_amount =  $this->tax_amount;
                $invoice_item->subtotal_incl = $this->subtotal_incl;
                $invoice_item->save();

                $this->total_tax_amount =  $this->total_tax_amount + $this->tax_amount;
                $this->total = $this->total +  $this->subtotal_incl;



                $requisition = new InventoryRequisition;
                $requisition->user_id = Auth::user()->id;
                $requisition->invoice_id = $invoice->id;
                $requisition->sale_id = $sale->id;
                $requisition->inventory_id = $this->selectedInventory[$key];
                $requisition->qty = 1;
                $requisition->save();
    
             
                $dispatch = new InventoryDispatch;
                $dispatch->user_id = Auth::user()->id;
                $dispatch->inventory_requisition_id = $requisition->id;
                $dispatch->inventory_id = $this->selectedInventory[$key];
                $dispatch->invoice_id = $invoice->id;
                $dispatch->sale_id = $sale->id;
                $dispatch->save();
                
           
                $inventory = Inventory::find($this->selectedInventory[$key]);
                if(isset($inventory)){
                    $inventory->status = 0;
                    $inventory->update();
                }
               

                        

            }

            $sale = Sale::find($sale->id);
      
            $sale->tax_amount =  $this->total_tax_amount;
            $sale->subtotal = $this->subtotal;
            $sale->total = $this->total;
            $sale->exchange_rate = $this->exchange_rate;
            $sale->exchange_amount = $this->exchange_amount;
            $sale->balance = $this->total;
            $sale->update();


            $invoice = Invoice::find($invoice->id);
      
            $invoice->tax_amount =  $this->total_tax_amount;
            $invoice->subtotal = $this->subtotal;
            $invoice->total = $this->total;
            $invoice->exchange_rate = $this->exchange_rate;
            $invoice->exchange_amount = $this->exchange_amount;
            $invoice->balance = $this->total;
            $invoice->update();

        }
        

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->customer_id = $invoice->customer_id;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $invoice->currency_id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->notes = $this->notes;
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->category = "sale";
        $payment->invoice_id = $invoice->id;
        $payment->sale_id = $sale->id;
        $payment->account_id = $this->account_id;
        $payment->amount = $this->payment_amount;
        if (isset($sale)) {
            $this->current_balance = $sale->balance - $this->payment_amount;
            $payment->balance = $this->current_balance;
        }
       
        $payment->date = $this->date;
        $payment->save();

        $invoice_payment = new InvoicePayment;
        $invoice_payment->customer_id = $invoice->customer_id;
        $invoice_payment->invoice_id = $invoice->id;
        $invoice_payment->payment_id = $payment->id;
        $invoice_payment->currency_id = $invoice->currency_id;
        $invoice_payment->amount = $this->payment_amount;
        $invoice_payment->save();  

        $sale = Sale::find($sale->id);
        $sale->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $sale->status = "Paid";
        }else {
            $sale->status = "Partial";
        }
        $sale->update();

        $invoice = Invoice::find($invoice->id);
        $invoice->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $invoice->status = "Paid";
        }else {
            $invoice->status = "Partial";
        }
        $invoice->update();

        if ($this->denomination) {
            foreach ($this->denomination as $key => $value) {
                $denomination = new Denomination;
                $denomination->payment_id = $payment->id;
                if (isset($this->denomination[$key])) {
                    $denomination->denomination = $this->denomination[$key];
                }
              if (isset($this->denomination_qty[$key])) {
                $denomination->quantity =  $this->denomination_qty[$key];
              }
               
                $denomination->save();
            }
        }

        if (isset($this->account_id)) {
            $account = Account::find($this->account_id);
            $current_balance = $account->balance;
            $account->balance = $current_balance + $this->payment_amount;
            $account->update();
        }
     

        
        $cashflow = new CashFlow;
        $cashflow->user_id = Auth::user()->id;
        $cashflow->customer_id = $invoice->customer_id;
        $cashflow->currency_id =$invoice->currency_id;
        $cashflow->invoice_id = $invoice->id;
        $cashflow->sale_id = $sale->id;
        $cashflow->payment_id = $payment->id;
        $cashflow->account_id = $this->account_id;
        $cashflow->type = 'Direct';
        $cashflow->sub_type = 'Income';
        $cashflow->category = 'Sale';
        $cashflow->transaction_type = 'Deposit';
        $cashflow->date = $payment->date;
        $cashflow->amount = $payment->amount;
        $cashflow->save();        

      
        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->sale_id = $payment->sale_id;
        $receipt->vendor_id = $payment->vendor_id;
        $receipt->transporter_id = $payment->transporter_id;
        $receipt->currency_id = $payment->currency_id;
        $receipt->receipt_number = $this->receiptNumber(); ;
        $receipt->user_id = Auth::user()->id;
        $receipt->amount = $payment->amount;
        $receipt->balance = $this->current_balance;
        $receipt->date = $this->date;
        $receipt->save();
  

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Sale Created Successfully!!"
        ]);

        return redirect()->route('sales.index');

        
        //     }
        //     catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while creating sale!!"
        //     ]);
        // }
    }



    public function render()
    {

        $this->products = Product::where('sell',true)->where('department','inventory')->whereHas('inventories', function ($query) {
            return $query->where('status',true);
        })->get()->sortBy('name')->sortBy('product.brand.name');

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

           $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();


       
            return view('livewire.sales.create',[
                'sale_amount' =>$this->sale_amount,
            ]);



    }
}
