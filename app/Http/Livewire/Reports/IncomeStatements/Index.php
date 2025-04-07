<?php

namespace App\Http\Livewire\Reports\IncomeStatements;

use Carbon\Carbon;
use App\Models\Fuel;
use App\Models\Account;
use App\Models\Invoice;
use Livewire\Component;
use App\Models\AccountType;
use App\Models\BillExpense;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $from;
    public $to;
    public $selectedType = "Acrual (Paid & Unpaid)";
    public $details;
    public $summary = "summary";
    
    public $income_account_type;
    public $income_accounts;
    public $income_accounts_ids = [];
   
    public $cost_of_goods_sold_account_type;
    public $cost_of_goods_sold_accounts;
    public $cost_of_goods_sold_accounts_ids = [];

    public $operating_expenses_account_type;
    public $operating_expenses_accounts;
    public $operating_expenses_accounts_ids = [];


    public $operating_expenses;
    public $exchange_operating_expenses;
    public $total_operating_expenses;

    public $cost_of_goods_sold;
    public $exchange_cost_of_goods_sold;
    public $total_cost_of_goods_sold;

    public $income;
    public $exchange_income;
    public $total_income;

    public $default_currency;
    public $default_currency_id;
    public $net_profit;
    public $net_profit_percentage;
    public $gross_profit;
    public $gross_profit_percentage;
   

    public function mount(){
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');
        $this->accounts = Account::orderBy('name','asc')->get();
        $this->account_types = AccountType::orderBy('name','asc')->get();
        $this->income_account_type = AccountType::where('name','Income')->first();
        $this->income_accounts = $this->income_account_type->accounts;
        $this->cost_of_goods_sold_account_type = AccountType::where('name','Cost Of Goods Sold')->first();
        $this->cost_of_goods_sold_accounts = $this->cost_of_goods_sold_account_type->accounts;
        $this->operating_expenses_account_type = AccountType::where('name','Operating Expense')->first();
        $this->operating_expenses_accounts = $this->operating_expenses_account_type->accounts;

        if (isset($this->operating_expenses_accounts)) {
            foreach ($this->operating_expenses_accounts as $account) {
                $this->operating_expenses_accounts_ids[] = $account->id;
              }
        }
        if (isset($this->cost_of_goods_sold_accounts)) {
            foreach ($this->cost_of_goods_sold_accounts as $account) {
                $this->cost_of_goods_sold_accounts_ids[] = $account->id;
              }
        }
        if (isset($this->income_accounts)) {
            foreach ($this->income_accounts as $account) {
                $this->income_accounts_ids[] = $account->id;
              }
        }
       

    }

   

    public function set_report($value){
      
        $this->summary = Null;
        $this->details = Null;
        if ($value == "details") {
            $this->details = 'details';
            
        }elseif ($value == "summary") {
            $this->summary = 'summary';
        }
       
    }

    public function render()
    {
        $this->default_currency = Auth::user()->employee->company->currency;
        $this->default_currency_id = Auth::user()->employee->company->currency_id;
       
        if (isset($this->from) && isset($this->to)) {

            if ($this->selectedType == "Acrual (Paid & Unpaid)") {

                    $this->income = Invoice::whereDate('date','>=',$this->from)
                    ->whereDate('date','<=',$this->to)
                    ->where('currency_id', $this->default_currency_id)
                    ->where('authorization', 'approved')
                    ->whereRaw('total REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('total');
                    $this->exchange_income = Invoice::whereDate('date','>=',$this->from)
                    ->whereDate('date','<=',$this->to)
                    ->where('currency_id','!=', $this->default_currency_id)
                    ->where('authorization', 'approved')
                    ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');
                    $this->total_income = $this->income +  $this->exchange_income;

                    if (isset($this->cost_of_goods_sold_accounts_ids)) {

                        $this->cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');
                    
                        $this->exchange_cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');
            
                        $this->total_cost_of_goods_sold = $this->cost_of_goods_sold + $this->exchange_cost_of_goods_sold;
                    }

                    if (isset($this->operating_expenses_accounts_ids)) {      
                        $this->operating_expenses = BillExpense::whereIn('account_id',$this->operating_expenses_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');

                        $this->exchange_operating_expenses = BillExpense::whereIn('account_id',$this->operating_expenses_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id','!=', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

                        $this->total_operating_expenses = $this->operating_expenses + $this->exchange_operating_expenses;
                    }

            }elseif ($this->selectedType == "Cash Basis") {
          
                    $this->income = Invoice::whereDate('date','>=',$this->from)
                    ->whereDate('date','<=',$this->to)
                    ->where('currency_id', $this->default_currency_id)
                    ->where('status','Paid')
                    ->where('authorization', 'approved')
                    ->whereRaw('total REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('total');
                    $this->exchange_income = Invoice::whereDate('date','>=',$this->from)
                    ->whereDate('date','<=',$this->to)
                    ->where('currency_id','!=', $this->default_currency_id)
                    ->where('status','Paid')
                    ->where('authorization', 'approved')
                    ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');
                    $this->total_income = $this->income +  $this->exchange_income;

                    if (isset($this->cost_of_goods_sold_accounts_ids)) {

                        $this->cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('status', 'Paid');
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');

                        $this->exchange_cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('status', 'Paid');
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id','!=', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

                        $this->total_cost_of_goods_sold = $this->cost_of_goods_sold + $this->exchange_cost_of_goods_sold;
                    }

                    if (isset($this->operating_expenses_accounts_ids)) {      
                        $this->operating_expenses = BillExpense::whereIn('account_id',$this->operating_expenses_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('status', 'Paid');
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');

                        $this->exchange_operating_expenses = BillExpense::whereIn('account_id',$this->operating_expenses_accounts_ids)
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','>=',$this->from);
                        })
                        ->whereHas('bill', function($q){
                            $q->whereDate('bill_date','<=',$this->to);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('status', 'Paid');
                        })
                        ->whereHas('bill', function($q){
                            $q->where('currency_id','!=', $this->default_currency_id);
                        })
                        ->whereHas('bill', function($q){
                            $q->where('authorization','approved');
                        })
                        ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');
                    
                        $this->total_operating_expenses = $this->operating_expenses + $this->exchange_operating_expenses;
                    }
            }

        }

        if ((isset($this->total_income) && is_numeric($this->total_income)) && (isset($this->total_cost_of_goods_sold) && is_numeric($this->total_cost_of_goods_sold))) {
            $this->gross_profit = $this->total_income -  $this->total_cost_of_goods_sold;
            if ((is_numeric($this->gross_profit) && $this->gross_profit > 0) && (is_numeric($this->total_income) && $this->total_income > 0)) {
                $this->gross_profit_percentage =  ($this->gross_profit / $this->total_income) * 100 ;
            }
        }else {
            $this->gross_profit_percentage = 0;
        }
     
        if ((isset($this->total_income) && is_numeric($this->total_income)) && (isset($this->total_cost_of_goods_sold) && is_numeric($this->total_cost_of_goods_sold)) && (isset($this->total_operating_expenses) && is_numeric($this->total_operating_expenses))) {
            $this->net_profit = $this->total_income -  $this->total_cost_of_goods_sold - $this->total_operating_expenses;
            if ((is_numeric($this->net_profit) && $this->net_profit > 0) && (is_numeric($this->total_income) && $this->total_income > 0) ) {
                $this->net_profit_percentage =  ($this->net_profit / $this->total_income) * 100 ;
            }
        } else {
            $this->net_profit_percentage = 0;
        }

        

        

        return view('livewire.reports.income-statements.index');
    }
}
