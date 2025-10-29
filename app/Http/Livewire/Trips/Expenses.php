<?php

namespace App\Http\Livewire\Trips;

use App\Models\Bill;
use App\Models\Trip;
use App\Models\Account;
use App\Models\Expense;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Allowance;
use App\Models\BillExpense;
use App\Models\TripExpense;
use App\Models\PaymentMethod;
use App\Models\AllowanceDriver;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class Expenses extends Component
{

    public $trip;
    public $trip_id;
    public $user_id;
    public $expenses;
    public $trip_expense_type ;
    public $allowances;
    public $payment_methods;
    public $payment_method_id;
    public $category;
    public $selectedExpense;
    public $selectedAllowance;
    public $trip_expenses;
    public $trip_expense_id;
    public $edit;
    public $exchange_rate;
    public $exchange_amount;
    public $turnover;
    public $cost_of_sales;
    public $net_profit;
    public $trip_expense;



    public $name;
    public $amount;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;

    public $total_customer_expenses;
    public $total_transporter_expenses;
    public $total_expenses;


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

    public function addExpense()
    {
        $index = count($this->inputs); // Get new index
        $this->inputs[] = $index; // Add new input field index
        $this->trip_expense_type[$index] = "expense"; // Default each new entry to 'expense'
    }

    private function resetInputFields(){
        $this->inputs = [];
        $this->trip_expense_type = Null ;
        $this->selectedExpense = Null ;
        $this->selectedAllowance = Null ;
        $this->exchange_amount = Null ;
        $this->exchange_rate = Null ;
        $this->amount = Null;
        $this->edit = Null;
        $this->selectedCurrency = Null;
        $this->total_customer_expenses = 0;
        $this->total_transporter_expenses = 0;
        $this->total_expenses = 0;

    }

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

    public function mount($trip){
   

    foreach ($this->inputs as $index) {
        $this->trip_expense_type[$index] = $this->trip_expense_type[$index] ?? 'expense';
    }
    
    $this->trip = $trip;
    $this->trip_id = $trip->addid;
    $this->currencies = Currency::orderBy('name')->get();
    $this->allowances = Allowance::orderBy('name')->get();
    $this->payment_methods = PaymentMethod::orderBy('name')->get();
    $this->expenses = Expense::whereHas('account', function($q){
        $q->where('name', 'Trip Expense');
     })->get();
   
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $messages =[
       
        'selectedExpense.*.required' => 'Expense field is required',
        'selectedExpense.0.required' => 'Expense field is required',
    ];
    protected $rules = [
        'selectedExpense.0' => 'required',
        'selectedExpense.*' => 'required',
    ];

    public function updatedSelectedExpense($id, $key = null)
    {
        if (!is_null($id)) {
            $expense = Expense::find($id);

            if ($key !== null) {
                // Make sure $this->amount is an array
                if (!is_array($this->amount)) {
                    $this->amount = [];
                }

                $this->amount[$key] = $expense->amount ?? null;
                $this->selectedCurrency[$key] = $expense->currency_id ?? null;
                $this->payment_method_id[$key] = $expense->payment_method_id ?? null;
            } else {
                $this->amount = $expense->amount ?? null;
                $this->selectedCurrency = $expense->currency_id ?? null;
                $this->payment_method_id = $expense->payment_method_id ?? null;
            }
        }
    }

       public function refresh($category){

        if($category == "expenses"){
            $this->expenses = Expense::whereHas('account', function($q){
                $q->where('name', 'Trip Expense');
             })->orderBy('name','asc')->get();
             $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expenses Refreshed Successfully!!."
            ]);
        } elseif($category == 'allowances'){
            $this->allowances = Allowance::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowances Refreshed Successfully!!."
            ]);
        }
    }

    public function updatedSelectedCurrency($id, $key = Null){
        if(!is_null($id)){
            if ($key) {
                $this->selected_currency[$key] = Currency::find($id);
            }else{
                $this->selected_currency = Currency::find($id);
            }
          
        }
    }

    public function updatedAmount($amount, $key = null){
        
        if ($key) {
            if (!is_null($amount)) {
                if ((isset($this->exchange_rate[$key]) && $this->exchange_rate[$key] > 0)  &&  ( isset($amount) && $amount > 0 )) {
                    $this->exchange_amount[$key] = $this->exchange_rate[$key] * $amount;
                }
            }
        }else{
            if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($amount) && $amount > 0 )) {
                $this->exchange_amount = $this->exchange_rate * $amount;
            }
        }
    }

    public function store(){



            if ($this->trip_expense_type) {

                foreach ($this->trip_expense_type as $key => $value) {
                    // Skip if type is missing or not recognized
                    if (!isset($this->trip_expense_type[$key])) {
                        continue;
                    }
                
                    $type = $this->trip_expense_type[$key];
                
                    // Skip if type is 'expense' but no expense selected
                    if ($type === 'expense' && empty($this->selectedExpense[$key])) {
                        continue;
                    }
                
                    // Skip if type is 'allowance' but no allowance selected
                    if ($type === 'allowance' && empty($this->selectedAllowance[$key])) {
                        continue;
                    }
                
                    // Proceed to save only valid records
                    $trip_expense = new TripExpense;
                    $trip_expense->user_id = Auth::user()->id;
                    $trip_expense->trip_id = $this->trip->id;
                    $trip_expense->currency_id = $this->selectedCurrency[$key] ?? null;
                    $trip_expense->payment_method_id = $this->payment_method_id[$key] ?? null;
                
                    if ($type === 'expense') {
                        $trip_expense->expense_id = $this->selectedExpense[$key];
                        $trip_expense->allowance_id = null;
                    } elseif ($type === 'allowance') {
                        $trip_expense->allowance_id = $this->selectedAllowance[$key];
                        $trip_expense->expense_id = null;

                        $allowance_driver = new AllowanceDriver;
                        $allowance_driver->driver_id = $this->trip->driver_id ? $this->trip->driver_id : Null;
                        $allowance_driver->trip_id = $this->trip->id ? $this->trip->id : Null;
                        $allowance_driver->allowance_id = $this->selectedAllowance[$key] ? $this->selectedAllowance[$key] : Null;    
                        $allowance_driver->currency_id = $this->selectedCurrency[$key] ?? null;     
                        $allowance_driver->amount = $this->amount[$key] ?? Null;      
                        $allowance_driver->save();

                    }
                
                    $trip_expense->category = $this->category[$key] ?? null;
                    $trip_expense->amount = $this->amount[$key] ?? 0;
                    $trip_expense->exchange_rate = $this->exchange_rate[$key] ?? null;

                   if (isset($this->exchange_amount[$key]) && $this->exchange_amount[$key] !== null) {
                        $trip_expense->exchange_amount = $this->exchange_amount[$key];
                    } elseif (
                        isset($this->exchange_rate[$key], $this->amount[$key]) &&
                        $this->exchange_rate[$key] > 0 &&
                        $this->amount[$key] > 0
                    ) {
                        $trip_expense->exchange_amount = $this->exchange_rate[$key] * $this->amount[$key];
                    }
                                    
                   
                    $trip_expense->save();

                    $account = Account::where('name','Trip Expense')->get()->first();
    
                    $bill = new Bill;
                    $bill->user_id = Auth::user()->id;
                    $bill->bill_number = $this->billNumber();
                    $bill->trip_id = $this->trip->id;
                    $bill->trip_expense_id = $trip_expense->id;
                    $bill->horse_id = $this->trip->horse_id;
                    $bill->driver_id = $this->trip->driver_id;
                    if (isset($account)) {
                        $bill->account_id = $account->id;
                        $bill->account_type_id = $account->account_type->id;
                    }
                    $bill->bill_date = $this->trip->start_date;
                    $bill->currency_id = $trip_expense->currency_id;
                    $bill->total = $trip_expense->amount;
                    $bill->subtotal = $trip_expense->amount;
                    $bill->balance = $trip_expense->amount;
                    $bill->save();
        
                    $bill_expense = new BillExpense;
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->expense_id = $trip_expense->expense_id;
                    $bill_expense->allowance_id = $trip_expense->allowance_id;
                    if (isset($account)) {
                        $bill_expense->account_id = $account->id;
                        $bill_expense->account_type_id = $account->account_type->id;
                    }
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $trip_expense->amount;
                    $bill_expense->subtotal = $trip_expense->amount;
                    $bill_expense->subtotal_incl = $trip_expense->amount;
                    $bill_expense->save();
        
                    $this->recalculateExpenses($this->trip->id);
                }


                $this->dispatchBrowserEvent('hide-tripExpenseModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Expense(s) Added Successfully!!"
                ]);
    
                
            }
      
      
    }

    private function recalculateExpenses($trip_id){

        $trip = Trip::find($trip_id);
        $this->trip_expenses = TripExpense::where('trip_id', $trip_id)->get();
        
        $this->total_transporter_expenses = 0;
        $this->total_customer_expenses = 0;
        $this->total_expenses = 0;
        
        if ($this->trip_expenses->isNotEmpty()) {
            foreach ($this->trip_expenses as $expense) {
                $use_amount = ($expense->currency_id == Auth::user()->employee->company->currency_id) 
                    ? $expense->amount 
                    : $expense->exchange_amount;
        
                if (is_numeric($use_amount)) {
                    switch ($expense->category) {
                        case 'Transporter':
                            $this->total_transporter_expenses += $use_amount;
                            break;
                        case 'Customer':
                            $this->total_customer_expenses += $use_amount;
                            break;
                        case 'Self':
                            $this->total_expenses += $use_amount;
                            break;
                    }
                }
            }
        }
        
        $this->cost_of_sales = $this->total_expenses;
        $trip->cost_of_sales = $this->cost_of_sales;
        $this->turnover = $trip->turnover;
        
        if ($this->cost_of_sales > 0 && $this->turnover > 0) {
            $this->net_profit = $this->turnover - $this->cost_of_sales;
            $trip->net_profit = $this->net_profit;
        
            if ($this->net_profit > 0) {
                $trip->markup_percentage = ($this->net_profit / $this->cost_of_sales) * 100;
                $trip->net_profit_percentage = ($this->net_profit / $this->turnover) * 100;
            } else {
                $trip->markup_percentage = 0;
                $trip->net_profit_percentage = 0;
            }
        } else {
            $trip->net_profit_percentage = 100;
            $trip->markup_percentage = 100;
        }
        
        $trip->update();

        $this->trip = Trip::find($trip->id);
    }

    public function edit($id){

        $expense = TripExpense::find($id);
        $this->user_id = $expense->user_id;
        $this->trip_id = $expense->trip_id;
        $this->edit = True;
        $this->trip = Trip::find($this->trip_id);
        $this->selectedCurrency = $expense->currency_id;
        $this->payment_method_id = $expense->payment_method_id;
        $this->selectedExpense = $expense->expense_id;
        $this->selectedAllowance = $expense->allowance_id;
        if ($expense->expense_id) {
            $this->trip_expense_type = "expense";
        }elseif($expense->allowance_id){
            $this->trip_expense_type = "allowance";
        }
        $this->category = $expense->category;
        $this->amount = $expense->amount;
        $this->exchange_rate = $expense->exchange_rate;
        $this->exchange_amount = $expense->exchange_amount;
        $this->trip_expense_id = $expense->id;
        $this->dispatchBrowserEvent('show-tripExpenseEditModal');

        }

        public function update()
        {
            if ($this->trip_expense_id) {

                $trip_expense = TripExpense::find($this->trip_expense_id);
                $trip_expense->amount = $this->amount;
                $trip_expense->trip_id = $this->trip_id;
                $trip_expense->user_id = Auth::user()->id;
                if ($this->trip_expense_type == "expense") {
                    $trip_expense->expense_id = $this->selectedExpense;
                    $trip_expense->allowance_id = Null;
                }elseif($this->trip_expense_type == "allowance"){
                    $trip_expense->allowance_id = $this->selectedAllowance;
                    $trip_expense->expense_id = Null;
                }

                if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

                    $this->exchange_amount = $this->exchange_rate * $this->amount;
        
                }

                $trip_expense->category = $this->category;
                $trip_expense->exchange_rate = $this->exchange_rate;
                $trip_expense->exchange_amount = $this->exchange_amount;
                $trip_expense->currency_id = $this->selectedCurrency;
                $trip_expense->payment_method_id = $this->payment_method_id;
                $trip_expense->update();
                

                $bill = $trip_expense->bill;
               
                if (isset($bill)) {   
                
                    $bill->trip_id = $this->trip->id;
                    $bill->horse_id = $this->trip->horse_id;
                    $bill->driver_id = $this->trip->driver_id;
                    $bill->bill_date = $this->trip->start_date;
                    $bill->currency_id = $this->trip->currency_id;
                    $bill->subtotal = $trip_expense->amount;
                    $bill->total = $trip_expense->amount;
                    $bill->balance = $trip_expense->amount;
                    $bill->update();
        
                    $bill_expense = BillExpense::where('bill_id',$bill->id)
                                                ->where('expense_id',$this->selectedExpense)->orWhere('allowance_id',$this->selectedAllowance)->get()->first();
                    $bill_expense->user_id = Auth::user()->id;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $bill_expense->expense_id = $trip_expense->expense_id;
                    $bill_expense->qty = 1;
                    $bill_expense->amount = $trip_expense->amount;
                    $bill_expense->subtotal = $trip_expense->amount;
                    $bill_expense->subtotal_incl = $trip_expense->amount;
                    $bill_expense->update();

                }

                $this->recalculateExpenses($this->trip->id);

                $this->dispatchBrowserEvent('hide-tripExpenseEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Expense Updated Successfully!!"
                ]);
           

            }
        }


        public function showDelete($id){
            $this->trip_expense_id = $id;
            $this->trip_expense = TripExpense::find($id);
            $this->dispatchBrowserEvent('show-expenseDeleteModal');
        }

        public function deleteExpense(){
            $bill = $this->trip_expense->bill;
            if (isset($bill)) {
                $bill_expenses = $bill->bill_expenses;
                if (isset($bill_expenses)) {
                    foreach ($bill_expenses as $bill_expense) {
                        $bill_expense->delete();
                    }
                }
                $bill->delete();
            }
            

           $this->trip_expense->delete();

           $this->recalculateExpenses($this->trip->id);

           $this->dispatchBrowserEvent('hide-expenseDeleteModal');
           $this->resetInputFields();
           $this->dispatchBrowserEvent('alert',[
               'type'=>'success',
               'message'=>"Expense Deleted Successfully!!"
           ]);
        }



    public function render()
    {
    
        $this->expenses = Expense::whereHas('account', function($q){
            $q->where('name', 'Trip Expense');
            })->get();
             
        $this->trip_expenses = TripExpense::where('trip_id',$this->trip->id)->get();

        return view('livewire.trips.expenses');
    }
}
