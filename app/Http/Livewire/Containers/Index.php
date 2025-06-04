<?php

namespace App\Http\Livewire\Containers;

use App\Models\Bill;
use App\Models\TopUp;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Expense;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Container;
use App\Models\BillExpense;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\ContainerCount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    private $containers;
    public $container_filter;
    public $container_number;
    public $container_id;
    public $container_currency_id;
    public $currency_id;
    public $currencies;
    public $vendor_id;
    public $name;
    public $expense_id;
    public $date;
    public $email;
    public $phonenumber;
    public $purchase_type;
    public $address;
    public $total_fuel;
    public $total_amount;
    public $vendors;
    public $capacity;
    public $quantity;
    public $rate;
    public $fuel_type;
    public $amount;
    public $balance;
    public $account_balance;
    public $selected_currency;
    public $company;
    public $exchange_rate;
    public $exchange_amount;



    public $user_id;

    public function mount(){
        $this->resetPage();
        $this->company = Auth::user()->employee->company;
        $this->currencies = Currency::all();
        $this->vendors = Vendor::orderBy('name','asc')->get();
    }
    public function containerNumber(){
       
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

            $container = Container::orderBy('id', 'desc')->first();

        if (!$container) {
            $container_number =  $initials .'FS'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $container->id + 1;
            $container_number =  $initials .'FS'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $container_number;


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

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        // 'capacity' => 'required',
        'name' => 'required|unique:cargos,name,NULL,id,deleted_at,NULL|string|min:2',
        'email' => 'nullable|email',
        'phonenumber' => 'nullable',
        'container_currency_id' => 'required',
        'address' => 'nullable',
        'fuel_type' => 'required',
        'purchase_type' => 'required',
    ];
    private function resetInputFields(){

        $this->balance = "";
        $this->account_balance = "";
        $this->vendor_id = "";
        $this->name = "";
        $this->email = "";
        $this->container_currency_id = "";
        $this->phonenumber = "";
        $this->address = "";
        $this->purchase_type = "";
        $this->currency_id = "";
        $this->fuel_type = "";
        $this->capacity = "";
        $this->quantity = "";
        $this->rate = "";
        $this->amount = "";
        $this->selected_currency = Null;
    }
    public function showTopUpModal($id){
        $this->container_id = $id;
        $container = Container::find($id);
        $this->fuel_type = $container->fuel_type;
        $this->capacity = $container->capacity;
        $this->balance = $container->balance;
        $this->currency_id = $container->currency_id;
        $this->account_balance = $container->account_balance;
        $this->dispatchBrowserEvent('show-top_upModal');
    }

    public function updatedCurrencyId($id){
        if (!is_null($id)) {
            $this->selected_currency = Currency::find($id);
        }
    }

    public function topup(){

    
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
        $top_up->exchange_amount = $this->exchange_amount;
        $top_up->exchange_rate = $this->exchange_rate;
        $top_up->save();

    
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->resetInputFields();
        Session::flash('success','Fuel Top Up Created Successfully!!');
        return redirect(route('top_ups.manage',$this->container_id));
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


    public function store(){
    
        $existing_container = Container::where('name',$this->name)->get()->first();
        if (!$existing_container) {

        $container = new Container;
        $container->user_id = Auth::user()->id;
        $container->container_number = $this->containerNumber();
        $container->name = $this->name;
        $container->email = $this->email;
        $container->currency_id = $this->container_currency_id;
        $container->phonenumber = $this->phonenumber;
        $container->purchase_type = $this->purchase_type;
        $container->address = $this->address;
        $container->fuel_type = $this->fuel_type;
        $container->capacity = $this->capacity;
        $container->balance = $this->quantity;
        $container->account_balance = $this->amount;
        $container->save();

        $this->container_id = $container->id;
        
        if(isset($this->quantity) && $this->quantity > 0){
            $top_up = new TopUp;
            $top_up->user_id = Auth::user()->id;
            $top_up->order_number = $this->orderNumber();
            $top_up->container_id = $container->id ? $container->id : NULL;
            $top_up->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
            $top_up->date = date('Y-m-d');
            $top_up->currency_id = $this->container_currency_id;
            $top_up->fuel_type = $container->fuel_type;
            $top_up->quantity = $this->quantity;
            $top_up->rate = $this->rate;
            $top_up->amount = $this->amount;
            $top_up->exchange_amount = $this->exchange_amount;
            $top_up->exchange_rate = $this->exchange_rate;
            $top_up->save();
        }
       

        $this->dispatchBrowserEvent('hide-containerModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fueling Station Created Successfully!!"
        ]);

              # code...
            }else {
                $this->dispatchBrowserEvent('hide-containerModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Fueling Station Name Exists!!"
                ]);
            }

       

    }

    public function edit($id){
    $container = Container::find($id);
    $this->name = $container->name;
    $this->email = $container->email;
    $this->phonenumber = $container->phonenumber;
    $this->address = $container->address;
    $this->user_id = $container->user_id;
    $this->purchase_type = $container->purchase_type;
    $this->fuel_type = $container->fuel_type;
    $this->container_currency_id = $container->currency_id;
    $this->capacity = $container->capacity;
    $this->balance = $container->balance;
    $this->account_balance = $container->account_balance;
    $this->container_id = $container->id;
    $this->dispatchBrowserEvent('show-containerEditModal');

    }


    public function update()
    {
        if ($this->container_id) {
       
            $container = container::find($this->container_id);
            $container->fuel_type = $this->fuel_type;
            $container->name = $this->name;
            $container->email = $this->email;
            $container->purchase_type = $this->purchase_type;
            $container->currency_id = $this->container_currency_id;
            $container->phonenumber = $this->phonenumber;
            $container->address = $this->address;
            $container->capacity = $this->capacity;
            $container->balance = $this->balance;
            $container->account_balance = $this->account_balance;
            $container->update();

            $this->dispatchBrowserEvent('hide-containerEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fueling Station Updated Successfully!!"
            ]);
           
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {
       
 
        if ($this->quantity != null && $this->rate != null) {
            $this->amount = $this->quantity * $this->rate;
        }

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        if ((isset($this->quantity) && $this->quantity != null)  && (isset($this->balance) && $this->balance != null)) {
            $this->total_fuel = $this->quantity + $this->balance;
        }
        if ((isset($this->amount) && $this->amount != null)  && (isset($this->account_balance) && $this->account_balance != null)) {
            $this->total_amount = $this->amount + $this->account_balance;
        }

        $this->vendors = Vendor::orderBy('name','asc')->get();
   
        if (isset($this->search)) {
            return view('livewire.containers.index',[
                'containers' => Container::query()->with('vendor','currency')
                ->where('container_number','like', '%'.$this->search.'%')
                ->orwhere('name','like', '%'.$this->search.'%')
                ->orWhere('fuel_type','like', '%'.$this->search.'%')
                ->orWhere('purchase_type','like', '%'.$this->search.'%')
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10),
                'amount' => $this->amount,
                'total_fuel' => $this->total_fuel,
                'total_amount' => $this->total_amount,
                'quantity' => $this->quantity,
                'vendors' => $this->vendors,
            ]);
        }
        else {
           
            return view('livewire.containers.index',[
                'containers' => Container::query()->with('vendor','currency')->orderBy('name','asc')->paginate(10),
                'amount' => $this->amount,
                'total_fuel' => $this->total_fuel,
                'total_amount' => $this->total_amount,
                'vendors' => $this->vendors,
            ]);
          
        }

   
    }
}
