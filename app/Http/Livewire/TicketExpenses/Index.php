<?php

namespace App\Http\Livewire\TicketExpenses;

use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\BillExpense;
use App\Models\TicketExpense;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    
    public $ticket;
    public $ticket_id;
    public $accounts;
    public $selectedAccount;
    public $products;
    public $selectedProduct;
    public $currencies;
    public $currency_id;
    public $expenses;
    public $bill_expense;
    public $selectedExpense;
    public $ticket_expenses;
    public $ticket_expense_id;
    public $qty;
    public $amount;
    public $expense_qty;
    public $weight;
    public $measurement;
    public $vendors;
    public $vendor_id;
    public $subtotal;
    public $usage;

    public $bill;
    public $bill_id;
    public $bill_number;
    public $exchange_rate;
    public $exchange_amount;


    public $bill_subtotal;
    public $bill_tax_amount;

    public $transporters;
    public $transporter_id;
    public $bill_date;
    public $due_date;
    public $notes;
    public $description;
    public $total;
    public $bill_total;
    public $tax_rate;
    public $tax_amount;
    public $total_tax_amount;
    public $user_id;
    public $selected_currency;
    public $company;


    public $item_name;
    public $item_description;
    public $item_price;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $selectedTax;
    public $expense_account_id;
    public $sell = False;
    public $buy = True;

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

   
  
    public function mount($ticket){
        $this->company = Auth::user()->employee->company;
        $this->ticket = $ticket;
        $this->currencies = Currency::all();
        $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();

        $this->ticket_expenses = TicketExpense::where('ticket_id', $this->ticket->id)->latest()->get();
    }

    public function billDate(){
        if ($this->due_date == "") {
            $this->due_date  = $this->bill_date;
        }
    }

    public function updatedSelectedTax($id){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate = $tax->rate;
            }
           
        }
    }

    public function storeItem(){
       
       
            $product = new Product;
            $product->user_id = Auth::user()->id;
            $product->name = $this->item_name;
            $product->description = $this->item_description;
            $product->price = $this->item_price;
            $product->sell = $this->sell;
            $product->buy = $this->buy;
            $product->expense_account_id = $this->expense_account_id;
            $product->tax_id = $this->tax_id;
            $product->save(); 
    
            $this->dispatchBrowserEvent('hide-product_serviceModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Created Successfully!!"
            ]);
    
           
        }
       

    public function updatedSelectedProduct($id){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount = $product->price;
                }
                if ($product->description) {
                    $this->description = $product->description;
                }
                if ($product->expense_account_id) {
                    $this->selectedAccount = $product->expense_account_id;
                }
                if ($product->tax_id) {
                    $this->selectedTax = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedExpense' => 'required',
        'qty' => 'required',
        'amount' => 'required',
    ];

    public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }

    private function resetInputFields(){
        $this->selectedExpense = '';
        $this->selectedAccount = '';
        $this->qty = '';
        $this->currency_id = '';
        $this->amount = '';
        $this->item_name = '';
        $this->item_description = '';
        $this->item_price = '';
        $this->tax_id = '';
        $this->sell = True;
        $this->buy = False;
    }

    

    public function updatedSelectedAccount($id){
        if (!is_null($id)) {
            $this->expenses = Expense::where('account_id',$id)->orderBy('name','asc')->get();
        }
    }

    public function store(){
        // try{

        if (isset($this->selectedProduct)) {
          

                $ticket_expense = new  TicketExpense;
                $ticket_expense->ticket_id = $this->ticket->id;
                if (isset($this->selectedAccount)) {
                    $ticket_expense->account_id =  $this->selectedAccount;
                }
                $ticket_expense->currency_id =  $this->currency_id;
                $ticket_expense->vendor_id =  $this->vendor_id;
                if (isset($this->selectedProduct)) {
                    $ticket_expense->product_id =  $this->selectedProduct;
                }
                if (isset($this->qty)) {
                    $ticket_expense->qty =  $this->qty;
                }
                if (isset($this->amount)) {
                    $ticket_expense->amount =  $this->amount;
                }
              
                if (isset($this->tax_rate)) {
                    $ticket_expense->tax_rate = $this->tax_rate;
                }
                if (isset($this->selectedTax)) {
                    $ticket_expense->tax_id = $this->selectedTax;
                }
    
                 if ((isset($this->amount) && is_numeric($this->amount)) && ( isset($this->qty) && is_numeric($this->qty) ) ) {
    
                    $item_subtotal = $this->amount*$this->qty;
                    $ticket_expense->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
    
                if (isset($this->tax_rate) && is_numeric($this->tax_rate)) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate / 100 ));
                    $ticket_expense->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $ticket_expense->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $ticket_expense->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $item_subtotal_incl;
                }
    
                $ticket_expense->exchange_rate = $this->exchange_rate;
                $ticket_expense->exchange_amount = $this->exchange_amount;
                $ticket_expense->save();

                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->vendor_id = $this->vendor_id;
                $bill->ticket_id = $this->ticket->id;
                $bill->ticket_expense_id = $ticket_expense->id;
                $bill->horse_id = $ticket_expense->ticket->booking ? $ticket_expense->ticket->booking->horse_id : null;
                $bill->trailer_id = $ticket_expense->ticket->booking ? $ticket_expense->ticket->booking->trailer_id : null;
                $bill->vehicle_id = $ticket_expense->ticket->booking ? $ticket_expense->ticket->booking->vehicle_id : null;
                $bill->currency_id = $this->currency_id;
                $bill->category = "Vendor";
                $bill->bill_number = $this->billNumber();
                $bill->bill_date = $this->bill_date;
                $bill->due_date = $this->due_date;
                $bill->notes = $this->notes;
                $bill->to_be_paid = True;
                // $bill->authorization = 'approved';
                $bill->save();

                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;

                if (isset($this->selectedProduct)) {
                    $bill_expense->product_id = $this->selectedProduct;
                }

                if (isset($this->selectedAccount)) {
                    $account = Account::find($this->selectedAccount);
                    $bill_expense->account_id = $this->selectedAccount;
                    $bill_expense->account_type_id = $account->account_type->id;
                }


                if (isset($this->description)) {
                    $bill_expense->description = $this->description;
                }

                if (isset($this->qty)) {
                    $bill_expense->qty = $this->qty;
                }

                if (isset($this->amount)) {
                    $bill_expense->amount = $this->amount;
                }

                if (isset($this->selectedTax)) {
                    $bill_expense->tax_id = $this->selectedTax;
                }

                if (isset($this->amount) && isset($this->qty)) {
                    $bill_subtotal = ($this->amount * $this->qty);
                    $bill_expense->subtotal = $bill_subtotal;
                }
              
                if (isset($this->tax_rate)) {
                    $bill_tax_amount = ($this->subtotal * ($this->tax_rate / 100 ));
                    $bill_expense->tax_amount =  $bill_tax_amount;
                    $bill_subtotal_incl = $bill_tax_amount + $bill_subtotal ;
                    $bill_expense->subtotal_incl = $bill_subtotal_incl;
                }else{
                    $bill_subtotal_incl = $bill_subtotal;
                    $bill_expense->subtotal_incl = $bill_subtotal_incl;
                }
                
                $bill_expense->save();
 

                $bill = Bill::find($bill->id);
                if (isset($bill_tax_amount)) {
                    $bill->tax_amount = $bill_tax_amount;
                }
                if(isset($bill_subtotal)){
                    $bill->subtotal = $bill_subtotal;
                }
                if(isset($bill_subtotal_incl)){
                    $bill->total = $bill_subtotal_incl;
                    $bill->balance = $bill_subtotal_incl;
                }
               
                $bill->exchange_rate = $this->exchange_rate;
                $bill->exchange_amount = $this->exchange_amount;
               
               
                $bill->update();


          
        }

        

        $this->dispatchBrowserEvent('hide-ticket_expenseModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense(s) Added Successfully!!"
        ]);

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating cargo!!"
    //     ]);
    // }
    }

    public function updatedCurrencyId($id){
        if (!is_null($id)) {
            $this->selected_currency = Currency::find($id);
        }
    }

    public function edit($id){
        $ticket_expense = TicketExpense::find($id);
        $this->bill = $ticket_expense->bill;
        $this->bill_expense = $this->bill->bill_expenses->first();
        if (isset($this->bill)) {
           $this->bill_date = $this->bill->bill_date;
           $this->due_date = $this->bill->due_date;
        }
        if (isset($this->bill_expense)) {
           $this->selectedTax = $this->bill->tax_id;
        }
        
        $this->vendor_id = $ticket_expense->vendor_id;
        $this->selectedProduct = $ticket_expense->product_id;
        $this->selectedAccount = $ticket_expense->account_id;
        $this->qty = $ticket_expense->qty;
        $this->amount = $ticket_expense->amount;
        $this->subtotal = $ticket_expense->subtotal;
        $this->currency_id = $ticket_expense->currency_id;
        $this->ticket_expense_id = $ticket_expense->id;

        $product = Product::find($this->selectedProduct);
        if ($product->tax_id) {
            $this->selectedTax = $product->tax_id;
            $tax = Account::find($product->tax_id);
            if (isset($tax)) {
                $this->tax_rate = $tax->rate;
            }
            
        }  

        $this->dispatchBrowserEvent('show-ticket_expenseEditModal');
    }

    public function update(){
        // try{

                $ticket_expense = TicketExpense::find($this->ticket_expense_id);
                $ticket_expense->ticket_id = $this->ticket->id;
                $ticket_expense->account_id =  $this->selectedAccount;
                $ticket_expense->currency_id =  $this->currency_id;
                $ticket_expense->vendor_id =  $this->vendor_id;
                $ticket_expense->product_id =  $this->selectedProduct;
                $ticket_expense->qty =  $this->qty;
                $ticket_expense->amount =  $this->amount;
              
                if ((isset($this->qty) && $this->qty > 0) && (isset($this->amount) && $this->amount > 0)) {
                    $ticket_expense->subtotal = $this->amount * $this->qty;
                }
                $ticket_expense->update();

                $bill = $this->bill;
                $bill->user_id = Auth::user()->id;
                $bill->vendor_id = $this->vendor_id;
                $bill->ticket_id = $this->ticket->id;
                $bill->ticket_expense_id = $ticket_expense->id;
                $bill->currency_id = $this->currency_id;
                $bill->category = "Vendor";
                $bill->bill_date = $this->bill_date;
                $bill->due_date = $this->due_date;
                $bill->notes = $this->notes;
                $bill->authorization = 'approved';
                $bill->update();

                $bill_expense = $this->bill_expense;
                $bill_expense->bill_id = $bill->id;
                $bill_expense->currency_id = $bill->currency_id;

                $bill_expense->product_id = $this->selectedProduct;
                $bill_expense->account_id = $this->selectedAccount;
                $bill_expense->description = $this->description;
                $bill_expense->qty = $this->qty;
                $bill_expense->amount = $this->amount;
                $bill_expense->tax_id = $this->selectedTax;

                if (isset($this->amount) && isset($this->qty)) {
                $this->subtotal = ($this->amount * $this->qty);
                $bill_expense->subtotal = $this->subtotal;
                }
              
                if (isset($this->tax_rate)) {
                    $this->tax_amount = ($this->subtotal * ($this->tax_rate / 100 ));
                    $bill_expense->tax_amount =  $this->tax_amount;
                    $this->subtotal_incl =  $this->tax_amount + $this->subtotal ;
                }else{
                    $this->subtotal_incl = $this->subtotal;
                }
                $bill_expense->subtotal_incl = $this->subtotal_incl;
                $bill_expense->update();

                $this->total_tax_amount =  $this->total_tax_amount + $this->tax_amount;
                $this->total = $this->total +  $this->subtotal_incl;
       

        $bill = $this->bill;
        $bill->tax_amount = $this->total_tax_amount;
        $bill->subtotal = $this->subtotal;
        $bill->total = $this->total;
        $bill->balance = $this->total;
        $bill->exchange_rate = $this->exchange_rate;
        $bill->exchange_amount = $this->exchange_amount;
        $bill->update();

        $this->dispatchBrowserEvent('hide-ticket_expenseEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Expense Updated Successfully!!"
        ]);

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating cargo!!"
    //     ]);
    // }
    }





    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {
            $this->exchange_amount = $this->total * $this->exchange_rate;
            }
            $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();
            $this->vendors = Vendor::orderBy('name','asc')->get();

        $this->ticket_expenses = TicketExpense::where('ticket_id', $this->ticket->id)->latest()->get();
        return view('livewire.ticket-expenses.index',[
            'ticket_expenses' => $this->ticket_expenses,
            'accounts' => $this->accounts,
            'currencies' => $this->currencies,
            'vendors' => $this->vendors,
            'products' => $this->products,
        ]);
    }
}
