<?php

namespace App\Http\Livewire\Containers;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\CashFlow;
use App\Models\Container;
use App\Models\ContainerCount;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\TopUp;
use App\Models\Transfer;
use App\Models\Vendor;
use App\Models\VendorType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    use WithPagination;
    public $searchPurchase;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search','searchPurchase'];
    public $from;
    public $to;

    private $containers;
    public $container_filter;
    public $container_number;
    public $container_id;
    public $container_currency_id;
    public $selectedCurrency;
    public $currencies;
    public $vendor_id;
    // The fuelling station's OWN Sage vendor (container.vendor_id) - kept
    // separate from $vendor_id (which belongs to the top-up/purchase form) so
    // the two modals don't overwrite each other. This is what the Sage PR -
    // Diesel sync resolves the station to.
    public $station_vendor_id;
    public $name;
    public $expense_id;
    public $date;
    public $email;
    public $phonenumber;
    public $purchase_type;
    public $address;
    public $total_fuel;
    public $vendors;
    public $capacity;
    public $quantity;
    public $rate;
    public $fuel_type;
    public $amount;
    public $balance;
    public $account_balance;
    public $account_amount;
    public $selected_currency;
    public $company;
    public $exchange_rate;
    public $exchange_amount;
    public $top_up_to;
    public $selected_container;
    public $purchases;
    public $selectedPurchase;
    public $attach_po = False;

    public $reason;
    public $from_station;
    public $to_station;
    public $transfer_date;
    public $transfer_quantity;
    public $acknowledgment;



    public $user_id;

    public function mount(){

        $this->top_up_to = "quantity";
        $this->resetPage();
        $this->company = Auth::user()->employee->company;
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
         $this->purchases = collect();
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

    public function updatedAttachPo($value){
        if($value == False){
            $this->selectedPurchase = Null;
        }else{
            $purchaseQuery = Purchase::query()
                    ->with(['vendor', 'currency'])
                    ->where('authorization', 'approved')
                    ->where('department', 'inventory')
                    ->where('status', true);

                     // Date range filter (replaces whereYear)
                    if (filled($this->search_from) && filled($this->search_to)) {
                        $purchaseQuery->whereBetween($this->filter, [$this->search_from, $this->search_to]);
                    } elseif (filled($this->search_from)) {
                        $purchaseQuery->whereDate($this->filter, '>=', $this->search_from);
                    } elseif (filled($this->search_to)) {
                        $purchaseQuery->whereDate($this->filter, '<=', $this->search_to);
                    } else {
                        // Fallback: current year if both are cleared
                        $purchaseQuery->whereYear($this->filter, date('Y'))->whereMonth($this->filter, date('m'));
                    }


                if (filled($this->searchPurchase)) {
                    $term = '%'.$this->searchPurchase.'%';

                    $purchaseQuery->where(function ($q) use ($term) {
                        $q->where('purchase_number', 'like', $term)
                        ->orWhere('date', 'like', $term)
                        ->orWhere('total', 'like', $term)
                        ->orWhereHas('vendor', function ($qq) use ($term) {
                            $qq->where('name', 'like', $term);
                        })
                        ->orWhereHas('currency', function ($qq) use ($term) {
                            $qq->where('name', 'like', $term);
                        });
                    });
                }

                $this->purchases = $purchaseQuery
                    ->orderBy($this->filter, 'desc')
                    ->get();
        }
    }

    public function updatedSelectedPurchaseOrder($id){
        if(is_null($id)){
            return ;
        }

        $purchase = Purchase::find($id);
        $this->vendor_id = $purchase?->vendor_id;
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

    public function updatedTopUpTo($value){
        if($value == "quantity"){
            $this->account_amount = Null;
        }else{
            $this->quantity = Null;
            $this->rate = Null;
            $this->amount = Null;
        }
    }

    public function transferFuel(){

     DB::transaction(function () {

        $this->validate([
            'from_station' => 'required',
            'to_station' => 'required|different:from_station',
            'transfer_date' => 'required|date',
            'transfer_quantity' => 'required|numeric|min:0.01',
            'acknowledgment' => 'nullable',
            'reason' => 'required|string',
        ]);
        

        $from_container = Container::find($this->from_station);
        $to_container = Container::find($this->to_station);

        if($from_container && $to_container){

            if($from_container->balance >= $this->transfer_quantity){

                // Deduct from sending station
                $from_container->balance -= $this->transfer_quantity;
                $from_container->save();

                // Add to receiving station
                $to_container->balance += $this->transfer_quantity;
                $to_container->save();

                $transfer = new Transfer;
                $transfer->user_id = Auth::user()->id;
                $transfer->from = $from_container->id;
                $transfer->to = $to_container->id;
                $transfer->date = $this->transfer_date;
                $transfer->quantity = $this->transfer_quantity;
                $transfer->comments = $this->reason;
                $transfer->category = "fuel";
                $transfer->save();

                $this->resetTransferInputFields();
                $this->dispatchBrowserEvent('hide-transferModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Fuel Transfered Successfully!!"
                ]);

            }
        }
     });
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
        'station_vendor_id' => 'required',
    ];

    protected $messages = [
        'station_vendor_id.required' => 'Please link this station to a vendor.',
    ];

       public function refresh($category){

        if($category == "vendors"){
            $this->vendors = Vendor::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vendors Refreshed Successfully!!."
            ]);
        }
    }

    private function resetTransferInputFields(){

        $this->transfer_quantity = Null;
        $this->transfer_date = Null;
        $this->from_station = Null;
        $this->to_station = Null;
        $this->reason = Null;
        $this->acknowledgment = Null;
      
    }
 
    private function resetInputFields(){
        
        $this->account_amount = Null;
        $this->balance = Null;
        $this->account_balance = Null;
        $this->vendor_id = Null;
        $this->station_vendor_id = Null;
        $this->name = Null;
        $this->email = Null;
        $this->container_currency_id = Null;
        $this->phonenumber = Null;
        $this->address = Null;
        $this->purchase_type = Null;
        $this->selectedCurrency = Null;
        $this->fuel_type = Null;
        $this->capacity = Null;
        $this->quantity = Null;
        $this->top_up_to = "quantity";
        $this->rate = Null;
        $this->amount = Null;
    }
    public function showTopUpModal($id){
        $this->container_id = $id;
        $container = Container::find($id);
        $this->selected_container = $container;
        $this->fuel_type = $container->fuel_type;
        $this->capacity = $container->capacity;
        $this->balance = $container->balance;
        $this->selectedCurrency = $container->currency_id;
        $this->account_balance = $container->account_balance;
        $this->dispatchBrowserEvent('show-top_upModal');
    }

        public function updatedSelectedCurrency($id){
        if(!is_null($id)){
            $this->selected_currency = Currency::find($id);
            if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                if ($predefined_exchange_rate) {   
                    $this->exchange_rate = $predefined_exchange_rate->exchange_rate;
                }
            }
        }
    }

    public function topup(){

        DB::transaction(function () {

        $container = Container::find($this->container_id);
        $top_up = new TopUp;
        $top_up->user_id = Auth::user()->id;
        $top_up->order_number = $this->orderNumber();
        $top_up->purchase_id = $this->selectedPurchase ?: NULL;
        $top_up->container_id = $container->id ? $container->id : NULL;
        $top_up->vendor_id = $this->vendor_id ? $this->vendor_id : NULL;
        $top_up->date = $this->date;
        $top_up->currency_id = $this->selectedCurrency ? $this->selectedCurrency : NULL;
        $top_up->fuel_type = $container->fuel_type;
        $top_up->quantity = $this->quantity;
        $top_up->account_amount = $this->account_amount;
        $top_up->rate = $this->rate;
        $top_up->amount = $this->amount;
        $top_up->exchange_amount = $this->exchange_amount;
        $top_up->exchange_rate = $this->exchange_rate;
        $top_up->save();

    
        $this->dispatchBrowserEvent('hide-top_upModal');
        $this->resetInputFields();
        Session::flash('success','Fuel Top Up Created Successfully!!');
        return redirect(route('top_ups.manage',$this->container_id));

        });
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

    /**
     * Create the vendor record backing a fueling station so it can be used
     * anywhere vendors are (bills, purchases, payments, PR - Diesel sync, etc.).
     */
    private function createStationVendor($name, $email, $phonenumber, $address, $currency_id): Vendor
    {
        $company = Auth::user()->company ?? Auth::user()->employee->company ?? null;

        $vendor = new Vendor;
        $vendor->creator_id = Auth::user()->id;
        $vendor->company_id = $company->id ?? null;
        $vendor->vendor_type_id = optional(VendorType::where('name', 'Fuel')->first())->id;
        $vendor->vendor_number = Vendor::nextVendorNumber();
        $vendor->name = $name;
        $vendor->email = $email;
        $vendor->phonenumber = $phonenumber;
        $vendor->street_address = $address;
        $vendor->currency_id = $currency_id ?: null;
        $vendor->status = 1;
        $vendor->save();

        return $vendor;
    }

    /**
     * Manually sync a pre-existing fueling station (created before this feature,
     * or with no manually selected vendor) to a new vendor record.
     */
    public function syncToVendor($id)
    {
        $container = Container::find($id);

        if (! $container || $container->vendor_id) {
            return;
        }

        DB::transaction(function () use ($container) {
            $container->vendor_id = $this->createStationVendor(
                $container->name, $container->email, $container->phonenumber, $container->address, $container->currency_id
            )->id;
            $container->save();
        });

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fueling Station Synced to Vendor Successfully!!"
        ]);
    }


    public function store(){

     $this->validate(['station_vendor_id' => 'required']);

     DB::transaction(function () {
    
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
        $container->account_balance = $this->account_balance;
        // The Sage vendor this station's PR - Diesel purchases post to. Use the
        // manually selected vendor if one was chosen, otherwise auto-create a
        // vendor for the station so every fueling station is synced to a vendor.
        $container->vendor_id = $this->station_vendor_id
            ? $this->station_vendor_id
            : $this->createStationVendor($container->name, $container->email, $container->phonenumber, $container->address, $container->currency_id)->id;
        $container->save();

        $this->container_id = $container->id;
        
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

     });

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
    $this->quantity = $container->balance;
    $this->account_balance = $container->account_balance;
    $this->station_vendor_id = $container->vendor_id;
    $this->container_id = $container->id;
    $this->dispatchBrowserEvent('show-containerEditModal');

    }


    public function update()
    {
         $this->validate(['station_vendor_id' => 'required']);

         DB::transaction(function () {
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
            $container->balance = $this->quantity;
            $container->account_balance = $this->account_balance;
            $container->vendor_id = $this->station_vendor_id ? $this->station_vendor_id : NULL;
            $container->update();

            $this->dispatchBrowserEvent('hide-containerEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fueling Station Updated Successfully!!"
            ]);
           
        }
         });
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

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0  || isset($this->account_amount) && $this->account_amount > 0 ) ) {

            $this->exchange_amount = $this->exchange_rate * $this->amount ? $this->amount : $this->account_amount;

        }

        if ((isset($this->quantity) && $this->quantity != null)  && (isset($this->balance) && $this->balance != null)) {
            $this->total_fuel = $this->quantity + $this->balance;
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
                'quantity' => $this->quantity,
                'vendors' => $this->vendors,
            ]);
        }
        else {
           
            return view('livewire.containers.index',[
                'containers' => Container::query()->with('vendor','currency')->orderBy('name','asc')->paginate(10),
                'amount' => $this->amount,
                'total_fuel' => $this->total_fuel,
                'vendors' => $this->vendors,
            ]);
          
        }

   
    }
}
