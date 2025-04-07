<?php

namespace App\Http\Livewire\Containers;

use App\Models\Bill;
use App\Models\TopUp;
use App\Models\Vendor;
use App\Models\Expense;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Container;
use App\Models\BillExpense;
use Illuminate\Support\Facades\Auth;

class TopUps extends Component
{
    public $top_ups;
    public $vendors;
    public $vendor_id;
    public $fuel_type;
    public $currencies;
    public $currency_id;
    public $containers;
    public $container;
    public $container_id;
    public $container_number;
    public $date;
    public $quantity;
    public $rate;
    public $amount;
    public $balance;


    public function mount($id){
        $this->container_id = $id;
        $this->container = Container::find($id);
        $this->fuel_type = $this->container->fuel_type;
        $this->containers = Container::orderBy('name','asc')->get();
        $this->currencies = Currency::all();
        $value = 'Fuel';
        $this->vendors = Vendor::with(['vendor_type'])
       ->whereHas('vendor_type', function($q) use($value) {
       $q->where('name', '=', $value);
        })->get();
        $this->top_ups = $this->container->top_ups;
    }

        
    private function resetInputFields(){

        $this->balance = "";
        $this->vendor_id = "";
        $this->name = "";
        $this->email = "";
        $this->phonenumber = "";
        $this->address = "";
        $this->currency_id = "";
        $this->fuel_type = "";
        $this->quantity = "";
        $this->rate = "";
        $this->amount = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'currency_id' => 'required',
        'vendor_id' => 'required',
        'quantity' => 'required',
        'fuel_type' => 'required',
        'rate' => 'required',
        'date' => 'required',
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

    
    public function orderNumber(){
       
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

            $container = TopUp::where('container_id', $this->container_id)->orderBy('id', 'desc')->first();

        if (!$container) {
            $container_number =  $initials .'TO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $container->id + 1;
            $container_number =  $initials .'TO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $container_number;


    }

    public function showTopUpModal($id){
        $this->container_id = $id;
        $this->dispatchBrowserEvent('show-top_upModal');
    }
    public function topup(){

        try{

        $container = Container::find($this->container_id);

        $top_up = new TopUp;
        $top_up->user_id = Auth::user()->id;
        $top_up->order_number = $this->orderNumber();
        $top_up->container_id = $container->id ? $container->id : NULL;
        $top_up->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
        $top_up->date = $this->date;
        $top_up->currency_id = $this->currency_id ? $this->currency_id : NULL;
        $top_up->fuel_type = $container->fuel_type;
        $top_up->quantity = $this->quantity;
        $top_up->rate = $this->rate;
        $top_up->amount = $this->amount;
        $top_up->save();

        $container = Container::find($this->container_id);
        $container->balance = $container->balance + $this->quantity;
        $container->update();

        if (isset($top_up->amount) && $top_up->amount > 0) {
            
            $bill = new Bill;
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->container_id = $container->id;
            $bill->top_up_id = $top_up->id;
            $bill->vendor_id = $top_up->vendor_id;
            $bill->category = "Fuel Topup";
            $bill->bill_date = $top_up->date;
            $bill->currency_id =  $top_up->currency_id;
            $bill->total = $top_up->amount;
            $bill->balance = $top_up->amount;
            $bill->save();
    
    
            $expense = Expense::where('name','Fuel Topup')->get()->first();
    
            $bill_expense = new BillExpense;
            $bill_expense->user_id = Auth::user()->id;
            $bill_expense->bill_id = $bill->id;
            $bill_expense->currency_id = $bill->currency_id;
            if (isset($expense)) {
                $bill_expense->expense_id = $expense->id;
            }
            $bill_expense->qty = 1;
            $bill_expense->amount = $top_up->amount;
            $bill_expense->subtotal = $top_up->amount;
            $bill_expense->save();
            
            }
        
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Top Up Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->dispatchBrowserEvent('alert',[

            'type'=>'error',
            'message'=>"Something went wrong while creating fuel station!!"
        ]);
    }

    }



    public function render()
    {
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }
        $this->top_ups = TopUp::where('container_id',$this->container_id)->get();
        return view('livewire.containers.top-ups',[
            'top_ups' => $this->top_ups,
        ]);
    }
}
