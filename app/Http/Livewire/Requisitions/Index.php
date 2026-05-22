<?php

namespace App\Http\Livewire\Requisitions;

use App\Exports\RequisitionExport;
use App\Mail\PendingNotificationEmails;
use App\Models\Account;
use App\Models\Allowance;
use App\Models\Booking;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\ExchangeRate;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Tax;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class Index extends Component
{

    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $searchTrip;
    public $searchBooking;
    public $searchPurchase;
    public $filter = "created_at";
    public $search_from, $search_to;
    
    protected $queryString = ['search','searchTrip','searchBooking' ,'searchPurchase'];
    public $from;
    public $to;

    public $requisition_filter;
    public $accounts;
    public $expense_accounts;
    public $account;
    public $selectedAccount;
    public $requisition_for;
    public $requisition_type;
   
    
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

    public array $included = [];   
    public $inputs = [];
    public $i = 1;
    public $n = 1;
    
    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public $title;
    public $file;
    public $expires_at;

    public $documentInputs = [];
    public $m = 1;
    public $o = 1;
    
    public function documentsAdd($m)
    {
        $m = $m + 1;
        $this->m = $m;
        array_push($this->documentInputs ,$m);
    }

    public function documentsRemove($m)
    {
        unset($this->documentInputs[$m]);
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
        unset($this->payment_method_id[$i]);
        unset($this->exchange_rate[$i]);
        unset($this->exchange_amount[$i]);
    }

   

    private function resetInputFields(){

        $this->requisition = Null;
        $this->requisition_type = Null;
        $this->selectedTrip = '';
        $this->employee_id = '';
        $this->department_id = '';
        $this->date = '';
        $this->expense_id = '';
        $this->allowance_id = '';
        $this->selectedAccount = '';
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
        $this->payment_method_id = [];
        
        $this->current_selectedProduct = [];
        $this->current_selectedCurrency = [];
        $this->current_selected_currency = [];
        $this->current_exchange_rate = [];
        $this->current_exchange_amount = [];
        $this->current_expense_id = [];
        $this->current_allowance_id = [];
        $this->current_qty = [];
        $this->current_amount = [];
        $this->current_payment_method_id = [];
        
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

     public function activeRowKeys(): array
    {
        return array_values(array_filter($this->inputs, function ($value) {
            return (bool)($this->included[$value] ?? true);
        }));
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

                      // NEW: default row is ON
                     $this->included[$index] = true;

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

    public function updatedAmount($id, $key){
      
        if (is_null($id) || is_null($key)) {
            return;
        }
        if((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && (isset($this->exchange_rate[$key]) && is_numeric($this->exchange_rate[$key]))){
            $this->exchange_amount[$key] = $this->amount[$key] * $this->exchange_rate[$key];
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
                    $this->included[$index] = true;
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
                    $this->included[$index] = true;
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
            
            $tax = Tax::find($id);
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

        $this->expenses = Expense::orderBy('name','asc')->where('status',1)->get();
        $this->allowances = Allowance::orderBy('name','asc')->where('status',1)->get();
        $this->products = Product::where('buy',True)->where('status',True)->orderBy('name','asc')->get();

        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type.account_type_group', function ($query) {
            return $query->where('name','Expenses');
        })->orderBy('name','asc')->get();

    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
      
        'selectedProduct.0' => 'required',
        'qty.0' => 'required',
        'selectedProduct.*' => 'required',
        'qty.*' => 'required',
       
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
        $requisition->type = $this->requisition_type;
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
       
        if (in_array($this->requisition_for,['Trip','Purchase','Booking'])) {
        
           foreach ($this->activeRowKeys() as $value) {
              
       
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;

                // Assign either expense_id or product_id
    
                // Handle quantity and amount
             
               
                $product_id = $this->selectedProduct[$value] ?? Null;
                $expense_id = $this->expense_id[$value] ?? Null;
                $allowance_id = $this->allowance_id[$value] ?? Null;
                $payment_method_id = $this->payment_method_id[$value] ?? Null;
                $qty = $this->qty[$value] ?? Null;
                $amount = $this->amount[$value] ?? 0;
                $currency_id = $this->selectedCurrency[$value] ?? 0;
                $exchange_rate = $this->exchange_rate[$value] ?? 0;
                $exchange_amount = $this->exchange_amount[$value] ?? 0;

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
      
        }else{

            if ($this->qty) {

                foreach ($this->qty as $key => $value) {
                
                
                    $requisition_item = new RequisitionItem;
                    $requisition_item->requisition_id = $requisition->id;

                    // Assign either expense_id or product_id
        
                    // Handle quantity and amount
                
                
                    $product_id = $this->selectedProduct[$key] ?? Null;
                    $expense_id = $this->expense_id[$key] ?? Null;
                    $allowance_id = $this->allowance_id[$key] ?? Null;
                    $payment_method_id = $this->payment_method_id[$key] ?? Null;
                    $qty = $value ?? 0;
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
        }

        

        $requisition = Requisition::find($requisition->id);

        $requisition->total = $requisition_total;

        $requisition->save();

        $notifications = Notification::where('when','before')->where('category','Requisition Authorization')->where('status',1)->get();
        $company =  $this->company;



         if ( isset($this->title) && ( isset($this->file) && !empty($this->file) )) {
    
            foreach ($this->file as $key => $value) {

                if(isset($this->file[$key])){
                    $file = $this->file[$key];
                    // get file with ext
                    $fileNameWithExt = $file->getClientOriginalName();
                    //get filename
                    $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    //get extention
                    $extention = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore= $filename.'_'.time().'.'.$extention;
                    $file->storeAs('/documents', $fileNameToStore, 'my_files');
                }

                $document = new Document;
                $document->user_id = Auth::user()->id;
                $document->requisition_id = $requisition->id;
                $document->category = 'requisition';
                if(isset($this->title[$key])){
                    $document->title = $this->title[$key];
                }
                if (isset($fileNameToStore)) {
                    $document->filename = $fileNameToStore;
                }
              
                if(isset($this->expires_at[$key])){
                    $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
                    $today = now()->toDateTimeString();
                    $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
                    if ($today <=  $expire) {
                        $document->status = 1;
                    }else{
                        $document->status = 0;
                    }
                }else {
                    $document->status = 1;
                }
                $document->save();
            }
    
        }

                
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

    public function updatedRequisitionType($value){
        if ($value == "po_requisition") {
           $this->requisition_for = "Other";
        }
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
        $this->requisition_type = $requisition->type ?: "payment_requisition";
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
        $requisition->type = $this->requisition_type;
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

       

   
         if ($this->qty) {

            foreach ($this->qty as $key => $value) {
              
               
                $requisition_item = new RequisitionItem;
                $requisition_item->requisition_id = $requisition->id;

                // Assign either expense_id or product_id
    
                // Handle quantity and amount
               

                $product_id = $this->selectedProduct[$key] ?? Null;
                $expense_id = $this->expense_id[$key] ?? Null;
                $allowance_id = $this->allowance_id[$key] ?? Null;
                $payment_method_id = $this->payment_method_id[$key] ?? Null;
                $qty = $value ?? 0;
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

        $requisition->total = $requisition_total;

        // if ($this->selectedPurchase && $purchase_order = Purchase::find($this->selectedPurchase)) {
        //     $requisition->total = $purchase_order->total;
        //     $requisition->exchange_rate = $purchase_order->exchange_rate;
        //     $requisition->exchange_amount = $purchase_order->exchange_amount;
        // } else {
          
        //     $requisition->total = $requisition_total;
        // }

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

    public function updatedRequisitionFor($value){

          if($value == 'Trip'){

           $tripQuery = Trip::query()
                        ->select('id', 'trip_number', 'trip_ref', 'start_date', 'customer_id',
                         'driver_id', 'horse_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                        ->with([
                            'customer:id,name',
                            'driver',
                            'horse:id,registration_number,fleet_number',
                            'loading_point:id,name',
                            'offloading_point:id,name'
                        ])
                        ->where('authorization', 'approved')
                        ->where('trip_status', '!=', 'Cancelled');

                    // Date range filter (replaces whereYear)
                    if (filled($this->search_from) && filled($this->search_to)) {
                        $tripQuery->whereBetween($this->filter, [$this->search_from, $this->search_to]);
                    } elseif (filled($this->search_from)) {
                        $tripQuery->whereDate($this->filter, '>=', $this->search_from);
                    } elseif (filled($this->search_to)) {
                        $tripQuery->whereDate($this->filter, '<=', $this->search_to);
                    } else {
                        // Fallback: current year if both are cleared
                        $tripQuery->whereYear($this->filter, date('Y'))->whereMonth($this->filter, date('m'));
                    }

                    if (filled($this->searchTrip)) {
                        $term = '%' . $this->searchTrip . '%';

                        $tripQuery->where(function ($q) use ($term) {
                            $q->where('trip_number', 'like', $term)
                                ->orWhere('trip_ref', 'like', $term)
                                ->orWhereHas('horse', function ($qq) use ($term) {
                                    $qq->where('registration_number', 'like', $term);
                                });
                        });
                    }

                    $this->trips = $tripQuery
                        ->orderBy($this->filter, 'desc')
                        ->get();

        }elseif($value == 'Booking'){
           
            $bookingQuery = Booking::query()
            ->with([
                'ticket',
                'horse:id,registration_number,fleet_number',
                'trailer:id,registration_number,fleet_number',
                'vehicle:id,registration_number,fleet_number',
                'employees:id,name,surname',
                'employee:id,name,surname',
            ])
           
            ->where('authorization', 'approved')
            ->where('status', true);
        // Date range filter (replaces whereYear)
            if (filled($this->search_from) && filled($this->search_to)) {
                $bookingQuery->whereBetween($this->filter, [$this->search_from, $this->search_to]);
            } elseif (filled($this->search_from)) {
                $bookingQuery->whereDate($this->filter, '>=', $this->search_from);
            } elseif (filled($this->search_to)) {
                $bookingQuery->whereDate($this->filter, '<=', $this->search_to);
            } else {
                // Fallback: current year if both are cleared
                $bookingQuery->whereYear($this->filter, date('Y'))->whereMonth($this->filter, date('m'));
            }

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
                ->orderBy($this->filter, 'desc')
                ->get(); 

        }elseif($value == 'Purchase'){
           
               $purchaseQuery = Purchase::query()
                    ->with(['vendor', 'currency'])
                    ->where('authorization', 'approved')
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

    public function updatingSearch()
    {
        $this->resetPage();
        
    }

    public function updatingSearchTrip()
    {
        $this->resetPage();
        
    }

    public function updatedFilter()
    {
       $this->updatedRequisitionFor($this->requisition_for);
        
    }
    public function updatedSearchTrip()
    {
       $this->updatedRequisitionFor($this->requisition_for);
        
    }

    public function updatingSearchBooking()
    {
        $this->resetPage();
        
    }

    public function updatedSearchBooking()
    {
         $this->updatedRequisitionFor($this->requisition_for);
        
    }
    public function updatedSearchFrom()
    {
         $this->updatedRequisitionFor($this->requisition_for);
        
    }
    public function updatedSearchTo()
    {
         $this->updatedRequisitionFor($this->requisition_for);
        
    }

    public function updatingSearchPurchase()
    {
        $this->resetPage();
        
    }
    public function updatedSearchPurchase()
    {
         $this->updatedRequisitionFor($this->requisition_for);
    }



    public function render()
    {
      

     // ✅ Force an Eloquent user model instance
    $user = User::query()
        ->with(['employee.departments', 'roles', 'employee.ranks'])
        ->findOrFail(Auth::id());

    $employee        = $user->employee;

    $departmentNames = $employee?->departments?->pluck('name')->all() ?? [];
    $roleNames       = $user->roles->pluck('name')->all();

    $isFinanceOrSuper = in_array('Finance', $departmentNames, true)
        || in_array('Super Admin', $roleNames, true);

    $base = Requisition::query()
        ->with(['employee', 'department', 'trip', 'currency', 'payments'])
        ->orderBy($this->requisition_filter, 'desc');

    // Non-finance/non-super: restrict to their departments
    if (! $isFinanceOrSuper) {
        $base->whereIn('department_id', (array) $this->department_ids);
    }

  
     // Date filter: range OR default current month/year
        if (filled($this->from) && filled($this->to)) {
            $base->whereBetween($this->requisition_filter, [$this->from, $this->to]);
        } else {
            $base->whereMonth($this->requisition_filter, now()->month)
                ->whereYear($this->requisition_filter, now()->year);
        }

    // Search (GROUPED OR CONDITIONS) — critical fix
    if (filled($this->search)) {
        $term = trim($this->search);

        $base->where(function ($q) use ($term) {
            $like = "%{$term}%";

            $q->where('requisition_number', 'like', $like)
              ->orWhere('subject', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhere('status', 'like', $like)
              ->orWhere('date', 'like', $like)
              ->orWhere('total', 'like', $like)

              ->orWhereHas('requisition_items.expense', function ($qq) use ($like) {
                  $qq->where('name', 'like', $like);
              })

              ->orWhereHas('trip', function ($qq) use ($like) {
                  $qq->where('trip_number', 'like', $like)
                     ->orWhereHas('horse', function ($hhh) use ($like) {
                         $hhh->where('registration_number', 'like', $like);
                     });
              })

              ->orWhereHas('employee', function ($qq) use ($term) {
                  $qq->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%{$term}%");
              })

              ->orWhereHas('currency', function ($qq) use ($like) {
                  $qq->where('name', 'like', $like);
              });
        });
    }

    return view('livewire.requisitions.index', [
        'requisitions' => $base->paginate(10),
    ]);
      
  
   

    }
}
