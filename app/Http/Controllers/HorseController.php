<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Account;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AccountType;
use App\Models\BillExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HorseController extends Controller
{
    public $selectedHorse;
    public $selected_horse;
    public $from;
    public $to;
    public $company;

  
    public $horses;
    public $trips;
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

    public $income = 0;
    public $exchange_income = 0;
    public $total_income = 0;

    public $default_currency;
    public $default_currency_id;
    public $net_profit;
    public $net_profit_percentage;
    public $gross_profit;
    public $gross_profit_percentage;
    public $total_trips;
    public $total_fuel;
    public $total_fuel_orders;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('horses.index');
    }
    public function manage()
    {
        return view('horses.manage');
    }
    public function age()
    {
        return view('horses.age');
    }
    
    public function performance()
    {
        return view('horses.performance');
    }
    public function mileage()
    {
        return view('horses.mileage');
    }
    public function archived()
    {
        return view('horses.archived');
    }
    public function incomeStatement(){
        return view('reports.income_statements.index');
    }

    public function profitLoss()
    {
        return view('horses.profit_loss.index');
    }

    public function profitLossPreview($selectedHorse = null, $from = null, $to = null){
        return view('horses.profit_loss.preview')->with([
            'selectedHorse' => $selectedHorse,
            'from' => $from,
            'to' => $to,
            ]);
    }
    public function profitLossPrint($selectedHorse = null, $from = null, $to = null){
        return view('horses.profit_loss.print_statement')->with([
            'selectedHorse' => $selectedHorse,
            'from' => $from,
            'to' => $to,
            ]);
    }

  
    public function profitLossPdf($selectedHorse = null, $from = null, $to = null){
        $this->company = Auth::user()->employee->company;
        $this->selectedHorse = $selectedHorse;
        $this->selected_horse = Horse::find($this->selectedHorse);
        $this->from = $from;
        $this->to = $to;
        $this->total_trips = Trip::where('horse_id',$selectedHorse)->where('trip_status','!=','Cancelled')->where('authorization','approved')
        ->whereDate('start_date','>=',$this->from)
        ->whereDate('start_date','<=',$this->to)->get()->count();
        $this->total_fuel_orders = Fuel::where('horse_id',$selectedHorse)->where('authorization','approved')
        ->whereDate('date','>=',$this->from)
        ->whereDate('date','<=',$this->to)->get()->count();
        $this->total_fuel = Fuel::where('horse_id',$selectedHorse)->where('authorization','approved')
        ->whereDate('date','>=',$this->from)
        ->whereDate('date','<=',$this->to)
        ->whereRaw('quantity REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('quantity');
        $this->company = Auth::user()->employee->company;

     
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
        $data = [
           'operating_expenses_accounts' => $this->operating_expenses_accounts,
            'operating_expenses_accounts_ids' => $this->operating_expenses_accounts_ids,
            'cost_of_goods_sold_accounts' => $this->cost_of_goods_sold_accounts,
            'cost_of_goods_sold_accounts_ids' => $this->cost_of_goods_sold_accounts_ids,
            'income_accounts' => $this->income_accounts,
            'income_accounts_ids' => $this->income_accounts_ids,
            'default_currency' => $this->default_currency,
            'default_currency_id' => $this->default_currency_id,
            'total_trips' => $this->total_trips,
            'total_income' => $this->total_income,
            'total_fuel_orders' => $this->total_fuel_orders,
            'total_fuel' => $this->total_fuel,
            'total_cost_of_goods_sold' => $this->total_cost_of_goods_sold,
            'total_operating_expenses' => $this->total_operating_expenses,
            'net_profit' => $this->net_profit,
            'net_profit_percentage' => $this->net_profit_percentage,
            'gross_profit' => $this->gross_profit,
            'gross_profit_percentage' => $this->gross_profit_percentage,
            'selectedHorse' => $this->selectedHorse,
            'selected_horse' => $this->selected_horse,
            'from' => $this->from,
            'to' => $this->to,
            'company' => $this->company,
        ];
      
       
        $pdf = PDF::loadView('horses.profit_loss.profit_loss_statement', $data);

        return $pdf->download('profit_loss.pdf');

    }


    public function archive($id){
        $horse = Horse::find($id);
        $horse->archive = 1;
        $horse->service = 1;
        $horse->status = 0;
        $horse->update();
        Session::flash('success','Horse Archived Successfully!!');
        return redirect(route('horses.archived'));
    }

    public function reports(){
        return view('horses.reports');
    }

    public function horsesReportPreview($selectedFilter = null, $from = null, $to = null){
        return view('horses.preview')->with([
            'selectedFilter' => $selectedFilter,
            'from' => $from,
            'to' => $to,
            ]);
    }

    public function horsesReportPrint($selectedFilter = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;

        if ( isset($selectedFilter)) {

            if (isset($from) && isset($to)) {
                $horses = Horse::whereBetween('created_at',[$from, $to] )->get();
            }else {
                $horses = Horse::all();
            }

           
          
        }
           
        return view('horses.print')->with([
            'selectedFilter' => $selectedFilter,
            'from' => $from,
            'to' => $to,
            'horses' => $horses,
            'company' => $company,
            ]);
        
   
    }

    public function horsesReportPDF($selectedFilter = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        if ( isset($selectedFilter)) {

            if (isset($from) && isset($to)) {
                $horses = Horse::whereBetween('created_at',[$from, $to] )->get();
            }else {
                $horses = Horse::all();
            }

            $data = [
                'selectedFilter' => $selectedFilter,
                'from' => $from,
                'to' => $to,
                'horses' => $horses,
                'company' => $company,
            ];
          
        }
    
       
        $pdf = PDF::loadView('horses.horses', $data);

        return $pdf->download('horses-report.pdf');

    }

    public function deactivate(Horse $horse){
        // $horse = horse::find($id);
        $horse->status = 0 ;
        $horse->update();
        Session::flash('success','Horse Deactivated Successfully!!');
        return redirect(route('horses.index'));
    }
 
    public function service(Horse $horse){
        
        $bookings = $horse->bookings->where('status', 1);

        if (isset($bookings)) {
           foreach ($bookings as $booking) {
            $booking->status = 0;
            $booking->update();

            // $ticket = $booking->ticket;
            // if (isset($ticket)) {
            //     $ticket->closed_by_id = Auth::user()->id;
            //     $ticket->status = 0;
            //     $ticket->update();
            // }

           }
        }
    
        $horse->service = 0 ;
        $horse->update();


        Session::flash('success','Horse Ticket Closed Successfully');
        return redirect(route('horses.index'));
    }

    public function activate(Horse $horse){
        // $horse = horse::find($id);
        $horse->status = 1 ;
        $horse->update();
        Session::flash('success','Horse Activated Successfully!!');
        return redirect(route('horses.index'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('horses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Horse  $horse
     * @return \Illuminate\Http\Response
     */
    public function show(Horse $horse)
    {
        return view('horses.show')->with('horse',$horse);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Horse  $horse
     * @return \Illuminate\Http\Response
     */
    public function edit(Horse $horse)
    {
        return view('horses.edit')->with('horse',$horse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Horse  $horse
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Horse $horse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Horse  $horse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Horse $horse)
    {
        $horse->delete();
        Session::flash('success','Horse deleted successfully!!');
        return redirect()->back();
    }
}
