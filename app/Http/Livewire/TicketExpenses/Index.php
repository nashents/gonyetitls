<?php

namespace App\Http\Livewire\TicketExpenses;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\TicketExpense;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $ticket;
    public $ticket_id;
    public $accounts;
    public $selectedAccount;
    public $products;
    public $selectedProduct;
    public $currencies;
    public $currency_id;
    public $payment_methods;
    public $payment_method_id;
    public $expenses;
    public $bill_expense;
    public $selectedExpense;
    protected $ticket_expenses;
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
    public $subtotal_incl;
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

    public $employee_ids = [];
    public $employees;
    public $user;
    public $employee;

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
         $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->employees = $this->ticket->booking->employees;
        foreach ($this->employees as $employee) {
            $this->employee_ids[] = $employee->id;
        }
    
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
                $ticket_expense->account_id =  $this->selectedAccount;
                $ticket_expense->currency_id =  $this->currency_id;
                $ticket_expense->payment_method_id =  $this->payment_method_id;
                $ticket_expense->vendor_id =  $this->vendor_id;
                $ticket_expense->product_id =  $this->selectedProduct;
                $ticket_expense->qty =  $this->qty;
                $ticket_expense->amount =  $this->amount;
                $ticket_expense->tax_rate = $this->tax_rate;
                $ticket_expense->tax_id = $this->selectedTax;
    
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
                    $bill_expense->product_id = $this->selectedProduct;

                    $account = Account::find($this->selectedAccount);
                    $bill_expense->account_id = $this->selectedAccount;
                    $bill_expense->account_type_id = $account ? $account->account_type->id : null;
                    $bill_expense->description = $this->description;
                    $bill_expense->qty = $this->qty;
                    $bill_expense->amount = $this->amount;
                    $bill_expense->tax_id = $this->selectedTax;

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
                    $bill->tax_amount = $bill_tax_amount ?? null;
                    $bill->subtotal = $bill_subtotal ?? null;
                    $bill->total = $bill_subtotal_incl ?? null;
                    $bill->balance = $bill_subtotal_incl ?? null;
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
        $this->payment_method_id = $ticket_expense->payment_method_id;
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
                $ticket_expense->payment_method_id =  $this->payment_method_id;
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

        $this->ticket_expenses = TicketExpense::where('ticket_id', $this->ticket->id)->latest()->paginate(10);
        return view('livewire.ticket-expenses.index',[
            'ticket_expenses' => $this->ticket_expenses,
          
        ]);
    }
}
