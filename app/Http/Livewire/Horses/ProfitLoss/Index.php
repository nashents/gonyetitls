<?php

namespace App\Http\Livewire\Horses\ProfitLoss;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Account;
use Livewire\Component;
use App\Models\AccountType;
use App\Models\BillExpense;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $from;
    public $to;
    public $selectedHorse;
    public $horses;
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


    public $operating_expenses = 0;
    public $exchange_operating_expenses = 0;
    public $total_operating_expenses = 0;

    public $cost_of_goods_sold = 0;
    public $exchange_cost_of_goods_sold = 0;
    public $total_cost_of_goods_sold = 0;

    public $selected_horse;
    public $trips;
    public $income = 0;
    public $exchange_income = 0;
    public $total_income = 0;

    public $default_currency;
    public $default_currency_id;
    public $net_profit;
    public $net_profit_percentage;
    public $gross_profit;
    public $gross_profit_percentage;
   

    public function mount(){
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->horses = Horse::orderBy('registration_number','asc')->get();
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

    public function updatedSelectedHorse($id){
        if (is_null($id)) {
            $this->selected_horse = Horse::find($id);
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
            if ($this->selectedHorse) {
            $this->income = Trip::whereDate('start_date','>=',$this->from)
            ->whereDate('start_date','<=',$this->to)
            ->where('horse_id',$this->selectedHorse)
            ->where('authorization','approved')
            ->where('trip_status','!=','Cancelled')
            ->where('currency_id', $this->default_currency_id)
            ->whereRaw('freight REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('freight');

            $this->exchange_income = Trip::whereDate('start_date','>=',$this->from)
            ->whereDate('start_date','<=',$this->to)
            ->where('horse_id',$this->selectedHorse)
            ->where('authorization','approved')
            ->where('trip_status','!=','Cancelled')
            ->where('currency_id','!=', $this->default_currency_id)
            ->whereRaw('exchange_customer_freight REGEXP "^-?[0-9]+(\.[0-9]+)?$"')
            ->get()->sum('exchange_customer_freight');
            
            $this->total_income = $this->income +  $this->exchange_income;
            $selected_horse = $this->selectedHorse;
            
            $this->cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
            ->whereNull('allowance_id')
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','>=',$this->from);
            })
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','<=',$this->to);
            })
            ->whereHas('bill', function($q){
                $q->where('authorization', 'approved');
            })
            ->whereHas('bill', function ($query) {
                $query->whereNotNull('trip_id');
            })
            ->whereHas('bill', function($q){
                $q->where('currency_id', $this->default_currency_id);
            })
            ->whereHas('bill', function($q){
                $q->where('horse_id', $this->selectedHorse);
            })
            ->orWhereHas('bill', function ($billQuery) use ($selected_horse) {
                $billQuery->whereHas('trip', function ($tripQuery) use ($selected_horse) {
                    $tripQuery->where('horse_id', $selected_horse);
                });
            })
            ->whereRaw('subtotal_incl REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('subtotal_incl');

            $this->exchange_cost_of_goods_sold = BillExpense::whereIn('account_id',$this->cost_of_goods_sold_accounts_ids)
            ->whereNull('allowance_id')
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','>=',$this->from);
            })
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','<=',$this->to);
            })
            ->whereHas('bill', function ($query) {
                $query->whereNotNull('trip_id');
            })
            ->whereHas('bill', function($q){
                $q->where('authorization', 'approved');
            })
            ->whereHas('bill', function($q){
                $q->where('currency_id','!=', $this->default_currency_id);
            })
            ->whereHas('bill', function($q){
                $q->where('horse_id', $this->selectedHorse);
            })
            ->orWhereHas('bill', function ($billQuery) use ($selected_horse) {
                $billQuery->whereHas('trip', function ($tripQuery) use ($selected_horse) {
                    $tripQuery->where('horse_id', $selected_horse);
                });
                })
            ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

            $this->total_cost_of_goods_sold = $this->cost_of_goods_sold + $this->exchange_cost_of_goods_sold;


            $this->operating_expenses = BillExpense::whereIn('account_id',$this->operating_expenses_accounts_ids)
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','>=',$this->from);
            })
            ->whereHas('bill', function($q){
                $q->whereDate('bill_date','<=',$this->to);
            })
            ->whereHas('bill', function($q){
                $q->where('authorization', 'approved');
            })
            ->whereHas('bill', function($q){
                $q->where('currency_id', $this->default_currency_id);
            })
            ->whereHas('bill', function($q){
                $q->where('horse_id', $this->selectedHorse);
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
                $q->where('authorization', 'approved');
            })
            ->whereHas('bill', function($q){
                $q->where('currency_id','!=', $this->default_currency_id);
            })
            ->whereHas('bill', function($q){
                $q->where('horse_id', $this->selectedHorse);
            })
            ->whereRaw('exchange_amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('exchange_amount');

            $this->total_operating_expenses = $this->operating_expenses + $this->exchange_operating_expenses;


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

       

        

        return view('livewire.horses.profit-loss.index');
    }
}
