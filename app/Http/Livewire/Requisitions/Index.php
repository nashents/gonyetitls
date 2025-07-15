<?php

namespace App\Http\Livewire\Requisitions;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Account;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\ExchangeRate;
use App\Models\Notification;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\RequisitionItem;
use App\Exports\RequisitionExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $searchTrip;
    public $searchBooking;
    public $searchPurchase;
    protected $queryString = ['search','searchTrip','searchBooking' ,'searchPurchase'];
    public $from;
    public $to;

    public $requisition_filter;
    public $accounts;
    public $expense_accounts;
    public $account;
    public $selectedAccount;
    public $requisition_for;
    public $trips;
    public $selectedTrip;
    public $purchases;
    public $selectedPurchase;
    public $bookings;
    public $selectedBooking;
    public $expenses;
    public $currencies;
    private $requisitions;
    public $requisition;
    public $requisition_number;
    public $subject;
    public $requisition_id;
    public $items = False;
    public $employees;
    public $employee;
    public $employee_id;
    public $departments;
    public $department;
    public $department_id;
    public $description;
    public $department_ids;
    public $products;
    public $selectedProduct;
    public $date;
    public $total;
    public $subtotal;
   
    public $company;
    public $item_totals = 0;

    public $selectedCurrency = [];
    public $selected_currency = [];
    public $exchange_rate = [];
    public $exchange_amount = [];
    public $expense_id = [];
    public $qty = [];
    public $amount = [];

    public $item_name;
    public $item_description;
    public $buy_price;
    public $sell_price;
    public $tax_id;
    public $tax;
    public $tax_accounts;
    public $selectedTax;
    public $tax_rate;
    public $expense_account_id;
    public $sell = False;
    public $buy = True;
    public $item_key;

  

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
        unset($this->selectedCurrency[$key]);
        unset($this->selected_currency[$key]);
        unset($this->amount[$key]);
        unset($this->exchange_rate[$key]);
        unset($this->exchange_amount[$key]);
    }

    private function resetInputFields(){
        $this->requisition = Null;
        $this->selectedTrip = '';
        $this->employee_id = '';
        $this->department_id = '';
        $this->date = '';
        $this->selectedCurrency = '';
        $this->expense_id = '';
        $this->selectedAccount = '';
        $this->qty = '';
        $this->amount = '';
        $this->description = '';
        $this->subject = '';
        $this->total = Null;
        $this->subtotal = Null;
        $this->requisition_for = Null;
        $this->inputs = [];

        $this->item_name = '';
        $this->item_description = '';
        $this->buy_price = '';
        $this->sell_price = '';
        $this->tax_id = '';
        $this->sell = False;
        $this->buy = True;
        $this->item_key = Null;
        
    }
    
    
    public function exportRequisitionCSV(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,   $this->search), 'requisitions_' .time().'.csv', Excel::CSV);
    }
    public function exportRequisitionPDF(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,  $this->search), 'requisitions_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportRequisitionExcel(Excel $excel){
        return $excel->download(new RequisitionExport($this->from, $this->to, $this->requisition_filter,  $this->search), 'requisitions_' .time().'.xlsx');
    }

    public function calculateTotals(){
        $requisitions = Requisition::all();
        foreach($requisitions as $requisition){
           $items_total = $requisition->requisition_items->where('subtotal','!=',"")->where('subtotal','!=',Null)->sum('subtotal');
           $requisition->total = $items_total;
           $requisition->update();
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"All Totals Updated Successfully!!"
        ]);
    }

    public function updatedSelectedTrip($id)
    {   
        if (!is_null($id)) {

            $trip = Trip::with('trip_expenses')->find($id);
           
            if ($trip && $trip->trip_expenses) {
                $this->reset(['inputs', 'selectedCurrency', 'selected_currency', 'amount', 'qty', 'exchange_rate', 'exchange_amount']);

                $index = 0;

                foreach ($trip->trip_expenses as $trip_expense) {
                    
                    $this->inputs[] = $index;

                    $this->expense_id[$index] = $trip_expense->expense_id;
                    $this->selectedCurrency[$index] = $trip_expense->currency_id;
                    $this->selected_currency[$index] = $trip_expense->currency;
                    $this->amount[$index] = $trip_expense->amount;
                    $this->qty[$index] = 1;
                    $this->exchange_rate[$index] = $trip_expense->exchange_rate;
                    $this->exchange_amount[$index] = $trip_expense->exchange_amount;

                    $index++;
                }

                $this->i = $index - 1;
            }
        }
    }

        public function updatedSelectedCurrency($id, $key){

            if(!$id && !$key){
                return ;
            }

             $this->selected_currency[$key] = Currency::find($id);
            if($id != $this->company->currency_id){
                $predefined_exchange_rate = ExchangeRate::where('currency_id', $id)
                    ->where('status', 1)
                    ->where('expiry', '>', Carbon::today())
                    ->first();
                if ($predefined_exchange_rate) {   
                    $this->exchange_rate[$key] = $predefined_exchange_rate->exchange_rate;
                }
            }
        }

        public function updatedSelectedProduct($id, $key)
        {
            if (is_null($id) || is_null($key)) {
                return;
            }

            // Make sure $key is a valid array key (e.g., numeric or string)
            if (!is_scalar($key)) {
                return;
            }

            $this->qty[$key] = 1;

            $product = Product::find($id);

            if (!$product) {
                return;
            }

            $this->amount[$key] = $product->price;
            $this->selectedTax[$key] = $product->tax_id;

            if ($product->tax_id) {
                $tax = Account::find($product->tax_id);
                $this->tax_rate[$key] = $tax?->rate ?? 0;
            } else {
                $this->tax_rate[$key] = 0;
            }
        }

        public function updatedSelectedTax($id, $key){
            if (is_null($id) || is_null($key)) {
                return;
            }
            
             // Make sure $key is a valid array key (e.g., numeric or string)
            if (!is_scalar($key)) {
                return;
            }
            
            $tax = Account::find($id);
            if ($tax) {
                $this->tax_rate[$key] = $tax->rate;
            }
    }


    public function mount(){
        $this->resetPage();
        $this->reset(['search', 'searchTrip', 'searchBooking', 'searchPurchase']);
        $employee_departments = Auth::user()->employee->departments;
        foreach ($employee_departments as $department) {
            $this->department_ids[] = $department->id;
        }
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = "created_at";
        $this->employees = Employee::where('archive', 0)->where('status',1)->orderBy('surname','asc')->get()->sortBy('name');
        $this->departments = Department::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();

        $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'selectedCurrency' => 'required',
        'expense_id.0' => 'required',
        'selectedAccount.0' => 'required',
        'employee_id.0' => 'required',
        'qty.0' => 'required',
        'amount.0' => 'required',
        'expense_id.*' => 'required',
        'selectedAccount.*' => 'required',
        'qty.*' => 'required',
        'amount.*' => 'required',
    ];
    

    public function requisitionNumber(){

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

        $requisition = Requisition::latest()->orderBy('id','desc')->first();

        if (!$requisition) {
            $requisition_number =  $initials .'RQ'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $requisition->id + 1;
            $requisition_number =  $initials .'RQ'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $requisition_number;


    }

      public function refresh($category){

        if($category == "products"){
           $this->products = Product::where('buy',True)->where('status',True)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Products Refreshed Successfully!!."
            ]);
        }elseif ($category == "expenses") {
            $this->expenses = Expense::orderBy('name','asc')->where('status',1)->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expenses Refreshed Successfully!!."
            ]);
        }
    }
   
    
    public function showItem($key){
        $this->item_key = $key;
        $this->dispatchBrowserEvent('show-product_serviceModal');
    }

    public function storeItem(){
        // try{
       
            $product = new Product;
            $product->user_id = Auth::user()->id;
            $product->name = $this->item_name;
            $product->description = $this->item_description;
            $product->price = $this->buy_price;
            $product->sell_price = $this->sell_price;
            $product->sell = $this->sell;
            $product->buy = $this->buy;
            $product->expense_account_id = $this->expense_account_id;
            $product->tax_id = $this->tax_id;
            $product->save(); 


            $this->selectedProduct[$this->item_key] = $product->id;
            $this->qty[$this->item_key] = 1;
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$this->item_key] = $product->price;
                }
                if ($product->tax_id) {
                    $this->selectedTax[$this->item_key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$this->item_key] = $tax->rate;
                    }
                }  
            }
    
            $this->dispatchBrowserEvent('hide-product_serviceModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Item Created Successfully!!"
            ]);
    
            // }
            //     catch(\Exception $e){
            //     // Set Flash Message
            //     $this->dispatchBrowserEvent('alert',[
            //         'type'=>'error',
            //         'message'=>"Something went wrong while creating item!!"
            //     ]);
            //  }
        }


       

    public function store(){

        // try{
          DB::transaction(function () {

        $requisition = new Requisition;
        $requisition->requisition_number = $this->requisitionNumber();
        $requisition->user_id = Auth::user()->id;
        $requisition->department_id = $this->department_id;
        $requisition->trip_id = $this->selectedTrip ? $this->selectedTrip : Null;
        $requisition->booking_id = $this->selectedBooking ? $this->selectedBooking : Null;
        $requisition->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : Null;
        $requisition->employee_id = $this->employee_id;
        $requisition->account_id = $this->selectedAccount;
        $requisition->date = $this->date;
        $requisition->description = $this->description;
        $requisition->items = $this->items;
        $requisition->subject = $this->subject;
        $requisition->status = "Unpaid";
        $requisition->save();

        $items = [];
        $type = null;

        if (!empty($this->expense_id)) {
            $items = $this->expense_id;
            $type = 'expense';
        } elseif (!empty($this->selectedProduct)) {
            $items = $this->selectedProduct;
            $type = 'product';
        }

        if ($type && !empty($items)) {
            foreach ($items as $key => $value) {
               
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;

                // Assign either expense_id or product_id
                if ($type === 'expense') {
                    $requisition_item->expense_id = $value;
                } else {
                    $requisition_item->product_id = $value;
                }

                // Handle quantity and amount
                $qty = $this->qty[$key] ?? 0;
                $amount = $this->amount[$key] ?? 0;
                $currency_id = $this->selectedCurrency[$key] ?? 0;
                $exchange_rate = $this->exchange_rate[$key] ?? 0;
                $exchange_amount = $this->exchange_amount[$key] ?? 0;

                $requisition_item->qty = $qty;
                $requisition_item->amount = $amount;
                $requisition_item->currency_id = $currency_id;
                $requisition_item->exchange_rate = $exchange_rate;
                $requisition_item->exchange_amount = $exchange_amount;

                // Calculate subtotal based on currency
                $subtotal = ($currency_id !== $this->company->currency_id) 
                    ? $exchange_amount * $qty 
                    : $amount * $qty;

                // Assign and save the subtotal
                $requisition_item->subtotal = $subtotal;
                $requisition_item->save();

                // Add to the cumulative total
                $this->total += $subtotal;
            }
        }

        $requisition = Requisition::find($requisition->id);

        if ($this->selectedPurchase && $purchase_order = Purchase::find($this->selectedPurchase)) {
            $requisition->total = $purchase_order->total;
            $requisition->exchange_rate = $purchase_order->exchange_rate;
            $requisition->exchange_amount = $purchase_order->exchange_amount;
        } else {
            $requisition->total = $this->total;
        }

        $requisition->save();

        $notifications = Notification::where('when','before')->where('category','Requisition Authorization')->where('status',1)->get();
        $company =  $this->company;
                
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($company, $notification, $requisition));
                }
                }
            }
        }
        $this->reset(['searchTrip', 'searchBooking']);
        $this->dispatchBrowserEvent('hide-requisitionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Created Successfully!!"
        ]);

//     }
//     catch(\Exception $e){
//     // Set Flash Message
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating requisition!!"
//     ]);
// }

    });

    }
    
    public function showPayment($id){
        $requisition = Requisition::find($id);
        $this->requisition_id = $id;
        $this->requisition_number = $requisition->requisition_number;
        $this->dispatchBrowserEvent('show-requisitionPaymentModal');
    }

    public function recordPayment(){
        $requisition =  Requisition::find($this->requisition_id);
        $requisition->status = "Paid";
        $requisition->update();
        $this->dispatchBrowserEvent('hide-requisitionPaymentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Marked As Paid Successfully!!"
        ]);
    }

    public function edit($id){
        $requisition = Requisition::find($id);
        $this->selectedCurrency = $requisition->currency_id;
        $this->selectedTrip = $requisition->trip_id;
        $this->selectedBooking = $requisition->booking_id;
        if (isset($this->selectedTrip)) {
           $this->requisition_for = "Trip";
        }elseif(isset($this->selectedBooking)){
            $this->requisition_for = "Booking";
        }elseif(isset($this->selectedPurchase)){
            $this->requisition_for = "Purchase";
        }
        $this->employee_id = $requisition->employee_id;
        $this->department_id = $requisition->department_id;
        $this->date = $requisition->date;
        $this->description = $requisition->description;
        $this->subject = $requisition->subject;
        $this->requisition_id = $requisition->id;
        $this->dispatchBrowserEvent('show-requisitionEditModal');
    }


        public function update(){

        // try{

          DB::transaction(function () {

        $requisition =  Requisition::find($this->requisition_id);
        $requisition->user_id = Auth::user()->id;
        $requisition->department_id = $this->department_id;
        $requisition->trip_id = $this->selectedTrip ? $this->selectedTrip : Null;
        $requisition->booking_id = $this->selectedBooking ? $this->selectedBooking : Null;
        $requisition->purchase_id = $this->selectedPurchase ? $this->selectedPurchase : Null;
        $requisition->employee_id = $this->employee_id;
        $requisition->currency_id = $this->selectedCurrency;
        $requisition->date = $this->date;
        $requisition->description = $this->description;
        $requisition->subject = $this->subject;
        $requisition->update();

        $this->reset(['searchTrip', 'searchBooking']);
        $this->dispatchBrowserEvent('hide-requisitionEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Updated Successfully!!"
        ]);

//     }
//     catch(\Exception $e){
//     // Set Flash Message
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something goes wrong while creating requisition!!"
//     ]);
// }

    });

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if($this->requisition_for == 'Trip'){
            $this->items = True;
            if (filled($this->searchTrip)) {
                $this->trips = Trip::query()->with(['customer:id,name',
                'horse:id,registration_number,fleet_number',
                'loading_point:id,name',
                'offloading_point:id,name'])
                ->whereYear('start_date',date('Y'))
                ->where('authorization','approved')
                ->where('trip_status','!=','Cancelled')
                ->where('trip_number', 'like', '%'.$this->searchTrip.'%')
                ->orWhere('trip_ref', 'like', '%'.$this->searchTrip.'%')
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->searchTrip.'%');
                })
                ->orderBy('id','desc')->get();
            }else{
                $this->trips =  Trip::select('id', 'trip_number', 'trip_ref','start_date', 'customer_id', 'driver_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                ->with([
                    'customer:id,name',
                    'driver',
                    'horse:id,registration_number,fleet_number',
                    'loading_point:id,name',
                    'offloading_point:id,name'
                ])
                ->whereYear('start_date',date('Y'))
                ->where('authorization','approved')
                ->where('trip_status','!=','Cancelled')
                ->orderBy('id', 'desc')
                ->get();
            }
        }elseif($this->requisition_for == 'Booking'){
                $this->items = True;
                if (filled($this->searchBooking)) {
                    $this->bookings = Booking::query()->with([
                    'horse:id,registration_number',
                    'trailer:id,registration_number',
                    'vehicle:id,registration_number',
                    'employee:id,name,surname'])
                    ->whereYear('in_date',date('Y'))
                    ->where('authorization','approved')
                    ->where('status',True)
                    ->where('booking_number', 'like', '%'.$this->searchBooking.'%')
                    ->orWhereHas('service_type', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchBooking.'%');
                    })
                    ->orWhereHas('horse', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->searchBooking.'%');
                    })
                    ->orWhereHas('trailer', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->searchBooking.'%');
                    })
                    ->orWhereHas('vehicle', function ($query) {
                        return $query->where('registration_number', 'like', '%'.$this->searchBooking.'%');
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'like', '%'.$this->searchBooking.'%');
                    })
                    ->orderBy('id','desc')->get();
                }else{
                    $this->bookings = Booking::whereYear('in_date',date('Y'))->where('authorization','approved')->where('status',True)->orderBy('id','desc')->get();
                }
            
        }elseif($this->requisition_for == 'Purchase'){
              if (filled($this->searchPurchase)) {
                    $this->purchases = Purchase::query()->with(['vendor','currency'])
                    ->whereYear('date',date('Y'))
                    ->where('authorization','approved')
                    ->where('status',True)
                    ->where('purchase_number', 'like', '%'.$this->searchPurchase.'%')
                    ->orWhere('date', 'like', '%'.$this->searchPurchase.'%')
                    ->orWhere('total', 'like', '%'.$this->searchPurchase.'%')
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchPurchase.'%');
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->searchPurchase.'%');
                    })
                    ->orderBy('id','desc')->get();
            }else{
                $this->purchases = Purchase::whereYear('date',date('Y'))->where('authorization','approved')->where('status',True)->orderBy('id','desc')->get();
            }
            
        }


        $user = Auth::user();
        $employee = $user->employee;
      
        $this->expenses = Expense::orderBy('name','asc')->where('status',1)->get();
        $this->products = Product::where('buy',True)->where('status',True)->orderBy('name','asc')->get();
        $employee_departments = $employee->departments;
        foreach($employee_departments as $department){
            $department_names[] = $department->name;
        }
        $roles = $user->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = $employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Finance', $department_names) || in_array('Super Admin', $role_names)){
            if (isset($this->from) && isset($this->to)) {
                if (filled($this->search)) {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                        ->where('requisition_number','like', '%'.$this->search.'%')
                        ->orWhere('subject','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('total','like', '%'.$this->search.'%')
                        ->orWhereHas('requisition_items', function ($query) {
                            $query->whereHas('expense', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%');
                            });
                        })
                        ->orWhereHas('trip', function ($query) {
                            $query->where('trip_number', 'like', '%' . $this->search . '%')
                                  ->orWhereHas('horse', function ($q) {
                                      $q->where('registration_number', 'like', '%' . $this->search . '%');
                                  });
                        })
                        ->orWhereHas('employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->requisition_filter,'desc')->paginate(10),
                      
                    ]);
                }else {
                    return view('livewire.requisitions.index',[
                        'requisitions' => requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc')->paginate(10),
                      
                    ]);
                }
               
            }
            elseif (filled($this->search)) {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('subject','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('total','like', '%'.$this->search.'%')
                    ->orWhereHas('requisition_items', function ($query) {
                        $query->whereHas('expense', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orWhereHas('trip', function ($query) {
                        $query->where('trip_number', 'like', '%' . $this->search . '%')
                              ->orWhereHas('horse', function ($q) {
                                  $q->where('registration_number', 'like', '%' . $this->search . '%');
                              });
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc')->paginate(10),
                  
                   
                ]);
            }
            else {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc')->paginate(10),
                ]);
              
            }
           
        }else{

            //not super admin
            if (isset($this->from) && isset($this->to)) {
                if (filled($this->search)) {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereBetween($this->requisition_filter,[$this->from, $this->to] )
                        ->whereIn('department_id', $this->department_ids)
                        ->where('requisition_number','like', '%'.$this->search.'%')
                        ->orWhere('subject','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('total','like', '%'.$this->search.'%')
                        ->orWhereHas('requisition_items', function ($query) {
                            $query->whereHas('expense', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%');
                            });
                        })
                        ->orWhereHas('trip', function ($query) {
                            $query->where('trip_number', 'like', '%' . $this->search . '%')
                                  ->orWhereHas('horse', function ($q) {
                                      $q->where('registration_number', 'like', '%' . $this->search . '%');
                                  });
                        })
                        ->orWhereHas('employee', function ($query) {
                            return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->requisition_filter,'desc')->paginate(10),
                       
                       
                    ]);
                }else {
                    return view('livewire.requisitions.index',[
                        'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')
                        ->whereIn('department_id', $this->department_ids)
                        ->whereBetween($this->requisition_filter,[$this->from, $this->to] )->orderBy($this->requisition_filter,'desc')->paginate(10),
                       
                     
                    ]);
                }
               
            }
            elseif (filled($this->search)) {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))
                    ->whereIn('department_id', $this->department_ids)
                    ->where('requisition_number','like', '%'.$this->search.'%')
                    ->orWhere('subject','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhere('status','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('total','like', '%'.$this->search.'%')
                    ->orWhereHas('requisition_items', function ($query) {
                        $query->whereHas('expense', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%');
                        });
                    })
                    ->orWhereHas('trip', function ($query) {
                        $query->where('trip_number', 'like', '%' . $this->search . '%')
                              ->orWhereHas('horse', function ($q) {
                                  $q->where('registration_number', 'like', '%' . $this->search . '%');
                              });
                    })
                    ->orWhereHas('employee', function ($query) {
                        return $query->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                    })
                    ->orWhereHas('currency', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orderBy($this->requisition_filter,'desc')->paginate(10),
                   
                ]);
            }
            else {
               
                return view('livewire.requisitions.index',[
                    'requisitions' => Requisition::query()->with('employee','department','trip','currency','payments')
                    ->whereIn('department_id', $this->department_ids)
                    ->whereMonth($this->requisition_filter, date('m'))
                    ->whereYear($this->requisition_filter, date('Y'))->orderBy($this->requisition_filter,'desc')->paginate(10),
                ]);
              
            }
           
        }
      
  
   

    }
}
