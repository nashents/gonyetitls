<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use App\Models\Account;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\BillExpense;

class Expenses extends Component
{

    public $expenses;
    public $expense_id;
    public $qty;
    public $description;
    public $amount;
    public $subtotal;
    public $subtotal_incl;
    public $total;
    public $bills;
    public $bill;
    public $bill_id;
    public $bill_expenses;
    public $bill_expense_id;
    public $tax_amount;
    
    public $bill_total;
    public $bill_tax_amount;
    public $bill_balance;
    public $bill_subtotal;
    public $total_tax_amount;

    public $products;
    public $selectedProduct;
    public $accounts;
    public $selectedAccount;
    public $tax_accounts;
    public $selectedTax;

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

    public function mount($id){
        $this->bill_id = $id;
        $this->bill = Bill::find($id);
        $this->bill_expenses = BillExpense::where('bill_id', $this->bill_id)->get();
        $this->products = Product::where('buy',True)->orderBy('name','asc')->get();
        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();

        $this->bill_tax_amount =  $this->bill->tax_amount;
        $this->bill_subtotal =  $this->bill->subtotal;
        $this->bill_total =  $this->bill->total;
        $this->bill_balance =  $this->bill->balance;
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

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate = $tax->rate;
            }
           
        }
    }

    public function store(){     

                $bill_expense = new BillExpense;
                $bill_expense->bill_id = $this->bill->id;
                $bill_expense->currency_id = $this->bill->currency_id;

                $bill_expense->product_id = $this->selectedProduct;

                $account = Account::find($this->selectedAccount);
                $bill_expense->account_id = $this->selectedAccount;
                $bill_expense->account_type_id = $account->account_type->id;

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
                    $this->subtotal_incl =  $this->tax_amount + $this->subtotal;
                }else{
                    $this->subtotal_incl = $this->subtotal;
                }
                $bill_expense->subtotal_incl = $this->subtotal_incl;
                $bill_expense->save();
           
        $bill = Bill::find($this->bill->id);
        $bill->tax_amount = $this->bill_tax_amount  + $this->tax_amount;
        $bill->subtotal = $this->bill_subtotal + $this->subtotal;
        $bill->total = $this->bill_total + $this->subtotal_incl;
        $bill->balance = $this->bill_balance + $this->subtotal_incl;
        $bill->exchange_rate = $this->exchange_rate;
        $bill->exchange_amount = $this->exchange_amount;
        $bill->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bill Expense Added Successfully!!"
        ]);
      
    }

    public function showDelete($id){
        $this->bill_expense_id = $id;
        $this->bill_expense = BillExpense::find($id);
        $this->bill = $this->bill_expense->bill;
        $this->dispatchBrowserEvent('show-bill_expenseDeleteModal');
    }

    public function delete(){


        $bill_expense = BillExpense::find($this->bill_expense_id);
        $bill = $bill_expense->bill;
        if (is_numeric($bill->tax_amount) && is_numeric($bill_expense->tax_amount)) {
            $bill->total = $bill->tax_amount - $bill_expense->tax_amount;
        }
        if (is_numeric($bill->subtotal) && is_numeric($bill_expense->subtotal)) {
            $bill->total = $bill->subtotal - $bill_expense->subtotal;
        }
        if (is_numeric($bill->total) && is_numeric($bill_expense->subtotal_incl)) {
            $bill->total = $bill->total - $bill_expense->subtotal_incl;
        }
      
       
       
        $bill_expense->delete();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Bill Expense Deleted Successfully!!"
        ]);
    }

    public function edit($id){
        $bill_expense = BillExpense::find($id);
        $this->selectedProduct = $bill_expense->product_id;
        $this->selectedAccount = $bill_expense->account_id;
        $this->selectedTax = $bill_expense->tax_id;
        $this->qty = $bill_expense->qty;
        $this->description = $bill_expense->description;
        $this->bill_id = $bill_expense->bill_id;
        $this->amount = $bill_expense->amount;
        $this->bill_expense_id = $bill_expense->id;
        $this->dispatchBrowserEvent('show-bill_expenseEditModal');
    }

    public function update(){
   
                $bill_expense = BillExpense::find($this->bill_expense_id);
                $bill_expense->product_id = $this->selectedProduct;
                $bill_expense->account_id = $this->selectedAccount;
                $bill_expense->tax_id = $this->selectedTax;
                $bill_expense->qty = $this->qty;
                $bill_expense->description = $this->description;
                $bill_expense->amount = $this->amount;

                if (isset( $this->amount) && isset($this->qty)) {
                    $bill_expense->subtotal = $this->amount * $this->qty;
                }
               
              $bill_expense->update();

              $this->total = BillExpense::where('bill_id',$this->bill_id)->sum('subtotal');
              $bill = Bill::find($this->bill_id);
              $bill->total = $this->total;
              $bill->update();
            
              
              $this->dispatchBrowserEvent('hide-bill_expenseEditModal');
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Expense(s) Updated Successfully!!"
              ]);
      

      
    }

   
    public function render()
    {

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->total;

        }

        $this->bill_expenses = BillExpense::where('bill_id', $this->bill_id)->get();
        return view('livewire.bills.expenses',[
            'bill_expenses' => $this->bill_expenses,
            'expenses' => $this->expenses,
        ]);
    }
}
