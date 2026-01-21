<?php

namespace App\Http\Livewire\Requisitions;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\User;
use App\Models\Account;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\Purchase;
use App\Models\Allowance;
use App\Models\Department;
use App\Models\Requisition;
use App\Models\ExchangeRate;
use App\Models\Notification;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\PaymentMethod;
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
    public $allowances;
    public $currencies;
    private $requisitions;
    public $requisition;
    public $requisition_number;
    public $subject;
    public $requisition_id;
    public $employees;
    public $employee;
    public $payment_methods;
    public $employee_id;
    public $departments;
    public $department;
    public $department_id;
    public $description;
    public $department_ids;
    public $products;
    public $date;
    public $total;
    public $subtotal;
    public $requisition_items;
    public $completed_date;
    public $completed_comments;
    public $paid_comments;
    public $paid_date;
   
    public $company;
    public $item_totals = 0;

    public $selectedProduct = [];
    public $selectedCurrency = [];
    public $selected_currency = [];
    public $payment_method_id = [];
    public $exchange_rate = [];
    public $exchange_amount = [];
    public $expense_id = [];
    public $allowance_id = [];
    public $qty = [];
    public $amount = [];
    
    public $current_selectedProduct = [];
    public $current_selectedCurrency = [];
    public $current_selected_currency = [];
    public $current_payment_method_id = [];
    public $current_exchange_rate = [];
    public $current_exchange_amount = [];
    public $current_expense_id = [];
    public $current_allowance_id = [];
    public $current_qty = [];
    public $current_amount = [];

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
     
        unset($this->requisition_items[$i]);
        unset($this->inputs[$i]);
        unset($this->selectedCurrency[$i]);
        unset($this->selected_currency[$i]);
        unset($this->expense_id[$i]);
        unset($this->allowance_id[$i]);
        unset($this->selectedProduct[$i]);
        unset($this->amount[$i]);
        unset($this->qty[$i]);
        unset($this->exchange_rate[$i]);
        unset($this->exchange_amount[$i]);
    }

    private function resetInputFields(){
        $this->requisition = Null;
        $this->selectedTrip = '';
        $this->employee_id = '';
        $this->department_id = '';
        $this->date = '';
        $this->selectedCurrency = '';
        $this->expense_id = '';
        $this->allowance_id = '';
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

        $this->selectedProduct = [];
        $this->selectedCurrency = [];
        $this->selected_currency = [];
        $this->exchange_rate = [];
        $this->exchange_amount = [];
        $this->expense_id = [];
        $this->allowance_id = [];
        $this->qty = [];
        $this->amount = [];
        
        $this->current_selectedProduct = [];
        $this->current_selectedCurrency = [];
        $this->current_selected_currency = [];
        $this->current_exchange_rate = [];
        $this->current_exchange_amount = [];
        $this->current_expense_id = [];
        $this->current_allowance_id = [];
        $this->current_qty = [];
        $this->current_amount = [];
        
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

    public function findUser($id){
        $user = User::find($id);
        $name = $user?->name;
        $surname = $user?->surname;
        return $name ." ". $surname;
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
                    $this->allowance_id[$index] = $trip_expense->allowance_id;
                    $this->selectedCurrency[$index] = $trip_expense->currency_id;
                    $this->payment_method_id[$index] = $trip_expense->payment_method_id;
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

    public function updatedSelectedPurchase($id)
    {   
        if (!is_null($id)) {
        
            $purchase = Purchase::with('purchase_products')->find($id);
           
            if ($purchase && $purchase->purchase_products) {
                $this->reset(['inputs', 'selectedCurrency', 'selected_currency', 'amount', 'qty', 'exchange_rate', 'exchange_amount']);

                $index = 0;

                foreach ($purchase->purchase_products as $purchase_product) {
                    
                    $this->inputs[] = $index;

                    $this->selectedProduct[$index] = $purchase_product->product_id;
                    $this->selectedCurrency[$index] = $purchase->currency_id;
                    $this->selected_currency[$index] = $purchase->currency;
                    $this->payment_method_id[$index] = $purchase_product->payment_method_id;
                    $this->amount[$index] = $purchase_product->subtotal_incl ?? $purchase_product->subtotal;;
                    $this->qty[$index] = 1;
                    $this->exchange_rate[$index] = $purchase_product->exchange_rate;
                    $this->exchange_amount[$index] = $purchase_product->exchange_amount;

                    $index++;
                }

                $this->i = $index - 1;
            }
        }
    }

    public function updatedSelectedBooking($id)
    {   
        if (!is_null($id)) {
        
            $booking = Booking::with('ticket')->find($id);
            $ticket = $booking?->ticket;
         
            if ($ticket && $ticket->ticket_expenses) {

                $this->reset(['inputs', 'selectedCurrency', 'selected_currency', 'payment_method_id', 'amount', 'qty', 'exchange_rate', 'exchange_amount']);

                $index = 0;

                foreach ($ticket->ticket_expenses as $ticket_expense) {
                    
                    $this->inputs[] = $index;

                    $this->selectedProduct[$index] = $ticket_expense->product_id;
                    $this->selectedCurrency[$index] = $ticket_expense->currency_id;
                    $this->payment_method_id[$index] = $ticket_expense->payment_method_id;
                    $this->selected_currency[$index] = $ticket_expense->currency;
                    $this->amount[$index] = $ticket_expense->subtotal_incl ?? $ticket_expense->subtotal;
                    $this->qty[$index] = 1;
                    $this->exchange_rate[$index] = $ticket_expense->exchange_rate;
                    $this->exchange_amount[$index] = $ticket_expense->exchange_amount;

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
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
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
        'allowance_id.0' => 'required',
        'selectedProduct.0' => 'required',
        'selectedAccount.0' => 'required',
        'employee_id.0' => 'required',
        'qty.0' => 'required',
        'amount.0' => 'required',
        'selectedProduct.*' => 'required',
        'expense_id.*' => 'required',
        'allowance_id.*' => 'required',
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
        }elseif($category == "allowances"){
            $this->allowances = Allowance::orderBy('name','asc')->where('status',1)->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowances Refreshed Successfully!!."
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
        $requisition->subject = $this->subject;
        $requisition->status = "Unpaid";
        $requisition->save();

        $items = [];
        $type = null;
        $requisition_total = 0;
       

        if ($this->amount) {

            foreach ($this->amount as $key => $value) {
              
               
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;

                // Assign either expense_id or product_id
    
                // Handle quantity and amount
             
               
                $product_id = $this->selectedProduct[$key] ?? Null;
                $expense_id = $this->expense_id[$key] ?? Null;
                $allowance_id = $this->allowance_id[$key] ?? Null;
                $payment_method_id = $this->payment_method_id[$key] ?? Null;
                $qty = $this->qty[$key] ?? 0;
                $amount = $this->amount[$key] ?? 0;
                $currency_id = $this->selectedCurrency[$key] ?? 0;
                $exchange_rate = $this->exchange_rate[$key] ?? 0;
                $exchange_amount = $this->exchange_amount[$key] ?? 0;

                $requisition_item->allowance_id = $allowance_id;
                $requisition_item->product_id = $product_id;
                $requisition_item->expense_id = $expense_id;
                $requisition_item->payment_method_id = $payment_method_id;
                $requisition_item->qty = $qty;
                $requisition_item->amount = $amount;
                $requisition_item->currency_id = $currency_id;
                $requisition_item->exchange_rate = $exchange_rate;
                $requisition_item->exchange_amount = $exchange_amount;

                // Calculate subtotal based on currency
                if (is_numeric($amount) && is_numeric($qty)) {
                    $subtotal = ($currency_id != $this->company->currency_id) 
                    ? $exchange_amount * $qty 
                    : $amount * $qty;
                }
                

                // Assign and save the subtotal
                $requisition_item->subtotal = $subtotal;
                $requisition_item->save();

                // Add to the cumulative total
                $requisition_total += $subtotal;

            }
        }

        $requisition = Requisition::find($requisition->id);

        if ($this->selectedPurchase && $purchase_order = Purchase::find($this->selectedPurchase)) {
            $requisition->total = $purchase_order->total;
            $requisition->exchange_rate = $purchase_order->exchange_rate;
            $requisition->exchange_amount = $purchase_order->exchange_amount;
        } else {
            $requisition->total = $requisition_total;
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
        $this->reset(['searchTrip', 'searchBooking', 'searchPurchase']);
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
        $requisition->paid_by_id = Auth::user()->id;
        $requisition->paid_on = $this->paid_date;
        $requisition->paid_comments = $this->paid_comments;
        $requisition->status = "Paid";
        $requisition->update();
        $this->dispatchBrowserEvent('hide-requisitionPaymentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Marked As Paid Successfully!!"
        ]);
    }
    public function showStatus($id){
        $requisition = Requisition::find($id);
        $this->requisition_id = $id;
        $this->requisition_number = $requisition->requisition_number;
        $this->dispatchBrowserEvent('show-requisitionStatusModal');
    }

    public function updateStatus(){
        $requisition =  Requisition::find($this->requisition_id);
        $requisition->is_completed = True;
        $requisition->completed_by_id = Auth::user()->id;
        $requisition->completed_on = $this->completed_date;
        $requisition->completed_comments = $this->completed_comments;
        $requisition->update();
        $this->dispatchBrowserEvent('hide-requisitionStatusModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Requisition Marked As Completed Successfully!!"
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
        $this->selectedAccount = $requisition->account_id;
        $this->date = $requisition->date;
        $this->description = $requisition->description;
        $this->subject = $requisition->subject;
        $this->requisition_id = $requisition->id;
        $this->requisition_items = $requisition->requisition_items;
        if($this->requisition_items){

                 foreach ($this->requisition_items as $key => $requisition_item) {
                    
                    $this->current_expense_id[$key] = $requisition_item->expense_id;
                    $this->current_allowance_id[$key] = $requisition_item->allowance_id;
                    $this->current_selectedProduct[$key] = $requisition_item->product_id;
                    $this->current_payment_method_id[$key] = $requisition_item->payment_method_id;
                    $this->current_selectedCurrency[$key] = $requisition_item->currency_id;
                    $this->current_selected_currency[$key] = $requisition_item->currency;
                    $this->current_amount[$key] = $requisition_item->amount;
                    $this->current_qty[$key] = $requisition_item->qty;
                    $this->current_exchange_rate[$key] = $requisition_item->exchange_rate;
                    $this->current_exchange_amount[$key] = $requisition_item->exchange_amount;

                   
                }
        }
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
        $requisition->account_id = $this->selectedAccount;
        $requisition->date = $this->date;
        $requisition->description = $this->description;
        $requisition->subject = $this->subject;
        $requisition->update();

        $items = [];
        $current_items = [];
        $type = null;
        $requisition_total = 0;

        foreach($this->requisition_items as $key => $requisition_item){
               
                $expense_id = $this->current_expense_id[$key] ?? Null;
                $allowance_id = $this->current_allowance_id[$key] ?? Null;
                $product_id = $this->current_selectedProduct[$key] ?? Null;
                $payment_method_id = $this->current_payment_method_id[$key] ?? Null;
                $qty = $this->current_qty[$key] ?? 0;
                $amount = $this->current_amount[$key] ?? 0;
                $currency_id = $this->current_selectedCurrency[$key] ?? 0;
                $exchange_rate = $this->current_exchange_rate[$key] ?? 0;
                $exchange_amount = $this->current_exchange_amount[$key] ?? 0;
                
                $requisition_item->expense_id = $expense_id;
                $requisition_item->allowance_id = $allowance_id;
                $requisition_item->product_id = $product_id;
                $requisition_item->payment_method_id = $payment_method_id;
                $requisition_item->qty = $qty;
                $requisition_item->amount = $amount;
                $requisition_item->currency_id = $currency_id;
                $requisition_item->exchange_rate = $exchange_rate;
                $requisition_item->exchange_amount = $exchange_amount;

                // Calculate subtotal based on currency
                 if (is_numeric($amount) && is_numeric($qty)) {
                    $subtotal = ($currency_id != $this->company->currency_id) 
                    ? $exchange_amount * $qty 
                    : $amount * $qty;
                }

                // Assign and save the subtotal
                $requisition_item->subtotal = $subtotal;
                $requisition_item->update();

                // Add to the cumulative total
                $requisition_total += $subtotal;
        }

       

   
         if ($this->amount) {

            foreach ($this->amount as $key => $value) {
              
               
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;

                // Assign either expense_id or product_id
    
                // Handle quantity and amount
               

                $product_id = $this->selectedProduct[$key] ?? Null;
                $expense_id = $this->expense_id[$key] ?? Null;
                $allowance_id = $this->allowance_id[$key] ?? Null;
                $payment_method_id = $this->payment_method_id[$key] ?? Null;
                $qty = $this->qty[$key] ?? 0;
                $amount = $this->amount[$key] ?? 0;
                $currency_id = $this->selectedCurrency[$key] ?? 0;
                $exchange_rate = $this->exchange_rate[$key] ?? 0;
                $exchange_amount = $this->exchange_amount[$key] ?? 0;

                $requisition_item->allowance_id = $allowance_id;
                $requisition_item->payment_method_id = $payment_method_id;
                $requisition_item->product_id = $product_id;
                $requisition_item->expense_id = $expense_id;
                $requisition_item->qty = $qty;
                $requisition_item->amount = $amount;
                $requisition_item->currency_id = $currency_id;
                $requisition_item->exchange_rate = $exchange_rate;
                $requisition_item->exchange_amount = $exchange_amount;

                // Calculate subtotal based on currency
                 if (is_numeric($amount) && is_numeric($qty)) {
                    $subtotal = ($currency_id != $this->company->currency_id) 
                    ? $exchange_amount * $qty 
                    : $amount * $qty;
                }

                // Assign and save the subtotal
                $requisition_item->subtotal = $subtotal;
                $requisition_item->save();

                // Add to the cumulative total
                $requisition_total += $subtotal;

            }
        }

        

        $requisition = Requisition::find($requisition->id);

        if ($this->selectedPurchase && $purchase_order = Purchase::find($this->selectedPurchase)) {
            $requisition->total = $purchase_order->total;
            $requisition->exchange_rate = $purchase_order->exchange_rate;
            $requisition->exchange_amount = $purchase_order->exchange_amount;
        } else {
          
            $requisition->total = $requisition_total;
        }

        $requisition->update();
        
        $this->reset(['searchTrip', 'searchBooking','selectedPurchase']);
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

            $tripQuery = Trip::query()
                    ->select('id', 'trip_number', 'trip_ref','start_date', 'customer_id', 'driver_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'driver',
                        'horse:id,registration_number,fleet_number',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date', date('Y'))
                    ->where('authorization', 'approved')
                    ->where('trip_status', '!=', 'Cancelled');

            if (filled($this->searchTrip)) {
                $term = '%'.$this->searchTrip.'%';

                $tripQuery->where(function ($q) use ($term) {
                    $q->where('trip_number', 'like', $term)
                    ->orWhere('trip_ref', 'like', $term)
                    ->orWhereHas('horse', function ($qq) use ($term) {
                        $qq->where('registration_number', 'like', $term);
                    });
                });
            }

            $this->trips = $tripQuery
                ->orderBy('id', 'desc')
                ->get();

        }elseif($this->requisition_for == 'Booking'){
           
            $bookingQuery = Booking::query()
            ->with([
                'ticket',
                'horse:id,registration_number,fleet_number',
                'trailer:id,registration_number,fleet_number',
                'vehicle:id,registration_number,fleet_number',
                'employees:id,name,surname',
                'employee:id,name,surname',
            ])
            ->whereYear('in_date', date('Y'))
            ->where('authorization', 'approved')
            ->where('status', true);

        if (filled($this->searchBooking)) {

            $term = '%'.$this->searchBooking.'%';

            $bookingQuery->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', $term)
                ->orWhereHas('ticket', function ($qq) use ($term) {
                    $qq->where('ticket_number', 'like', $term);
                })
                ->orWhereHas('service_type', function ($qq) use ($term) {
                    $qq->where('name', 'like', $term);
                })
                ->orWhereHas('horse', function ($qq) use ($term) {
                    $qq->where('registration_number', 'like', $term);
                })
                ->orWhereHas('trailer', function ($qq) use ($term) {
                    $qq->where('registration_number', 'like', $term);
                })
                ->orWhereHas('vehicle', function ($qq) use ($term) {
                    $qq->where('registration_number', 'like', $term);
                })
                ->orWhereHas('employee', function ($qq) use ($term) {
                    $qq->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                });
            });
        }

        $this->bookings = $bookingQuery
            ->orderBy('id', 'desc')
            ->get(); 

        }elseif($this->requisition_for == 'Purchase'){
           
               $purchaseQuery = Purchase::query()
                    ->with(['vendor', 'currency'])
                    ->whereYear('date', date('Y'))
                    ->where('authorization', 'approved')
                    ->where('status', true);

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
                    ->orderBy('id', 'desc')
                    ->get();
            
        }


        $user = Auth::user();
        $employee = $user->employee;
      
        $this->expenses = Expense::orderBy('name','asc')->where('status',1)->get();
        $this->allowances = Allowance::orderBy('name','asc')->where('status',1)->get();
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
