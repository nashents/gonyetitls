<?php

namespace App\Http\Livewire\Purchases;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Account;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Purchase;
use App\Models\VendorType;
use App\Models\AccountType;
use App\Models\ExchangeRate;
use App\Models\Notification;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\CategoryValue;
use Livewire\WithFileUploads;
use App\Models\PurchaseProduct;
use App\Exports\PurchasesExport;
use App\Models\PurchaseDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PendingNotificationEmails;

class Index extends Component
{
    use WithFileUploads;

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $purchase_filter;

    
    public $purchase_number;
    private $purchases;
    public $purchase_id;
    public $purchase;
    public $currencies;
    public $selectedCurrency;
    public $selected_currency;
    public $currency_id;
    public $department;
    public $value = 0;
    public $description;
    public $date;
    public $purchase_order_products = [];

    public $exchange_rate;
    public $exchange_amount;

    
    public $expense_accounts;
    public $selectedAccount;
    public $expenses;
    public $expense_id;

    public $vendor_id;
    public $vendors;
    public $vendor_types;
    public $selectedVendorType;

    public $booking_id;
    public $bookings;

    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $name;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $country;
    public $city;
    public $website;
    public $suburb;
    public $street_address;
    public $company;
   


    public $title;
    public $vendor;
    public $file;
    public $expires_at;


    public $tax_rate = [];
    public $current_tax_rate = [];
    public $tax_amount;

    public $products;
    public $selectedProduct = [];
    public $selectedCurrentProduct = [];
    public $tax_accounts;
    public $selectedTax = [];
    public $selectedCurrentTax = [];
    public $qty = [];
    public $current_qty = [];
    public $amount  = [];
    public $current_amount = [];
    public $total;
    public $subtotal;
    public $subtotal_incl;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->title = '';
        $this->file = '';
        $this->date = '';
        $this->selectedCurrency = '';
        $this->vendor_id = '';
        $this->selectedAccount = '';
        $this->inputs = [];
        $this->current_amount = [];
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    
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

    public function exportPurchasesCSV(Excel $excel){
        return $excel->download(new PurchasesExport($this->from, $this->to, $this->purchase_filter, $this->department,  $this->search), 'purchase_orders' .time().'.csv', Excel::CSV);
    }
    public function exportPurchasesPDF(Excel $excel){
        return $excel->download(new PurchasesExport($this->from, $this->to, $this->purchase_filter,$this->department,  $this->search), 'purchase_orders' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportPurchasesExcel(Excel $excel){
        return $excel->download(new PurchasesExport($this->from, $this->to, $this->purchase_filter, $this->department, $this->search), 'purchase_orders' .time().'.xlsx');
    }

    public function vendorNumber(){

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

            $last_vendor_id = Vendor::latest()->pluck('id')->first();

        if (!$last_vendor_id) {
            $vendor_number =  $initials .'V'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $vendor_number = $last_vendor_id + 1;
            $vendor_number =  $initials .'V'. str_pad($vendor_number, 5, "0", STR_PAD_LEFT);
        }

        return  $vendor_number;


    }

    public function storeVendor(){

        $vendor = new Vendor;
        $vendor->user_id = Auth::user()->id;
        $vendor->vendor_number = $this->vendorNumber();
        $vendor->name = $this->name;
        $vendor->email = $this->email;
        $vendor->phonenumber = $this->phonenumber;
        $vendor->currency_id = $this->currency_id ? $this->currency_id : NULL;
        $vendor->worknumber = $this->worknumber;
        $vendor->website = $this->website;
        $vendor->country = $this->country;
        $vendor->city = $this->city;
        $vendor->suburb = $this->suburb;
        $vendor->street_address = $this->street_address;
        $vendor->status = 1;
        $vendor->save();
        $this->vendor_id = $vendor->id;

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->vendor_id = $vendor->id;
               $contact->category = 'vendor';
               if (isset($this->contact_name[$key])) {
                $contact->name = $this->contact_name[$key];
               }
               if (isset($this->contact_surname[$key])) {
                $contact->surname = $this->contact_surname[$key];
               }
                if (isset($this->contact_phonenumber[$key])) {
                    $contact->phonenumber = $this->contact_phonenumber[$key];
                }
                if (isset($this->contact_email[$key])) {
                    $contact->email = $this->contact_email[$key];
                }
              
               $contact->save();
            }
        }
    
        if (isset($this->file) && isset($this->title) && $this->file != "") {
           
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
              $document->vendor_id = $vendor->id;
              $document->category = 'vendor';
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
                   # code...
          
        }

        $this->dispatchBrowserEvent('hide-vendorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vendor Created Successfully!!"
        ]);

      
    }


    public function purchaseNumber(){

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

            $purchase = Purchase::orderBy('id', 'desc')->first();

        if (!$purchase) {
            $purchase_number =  $initials .'PO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $purchase->id + 1;
            $purchase_number =  $initials .'PO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $purchase_number;


    }

    public function mount($category){
            $this->resetPage();
            $this->company = Auth::user()->employee->company;
            $this->purchase_filter = "created_at";
            $this->bookings = Booking::where('authorization','approved')->where('status',1)->latest()->get();
            $this->department = $category;
            $this->products = Product::orderBy('name','asc')->where('department', $this->department)->where('status',True)->where('buy',True)->get();
            $this->vendor_types = VendorType::latest()->get();
            $this->account_types = AccountType::orderBy('name','asc')->get();
            $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();
            $this->income_accounts = Account::whereHas('account_type', function($q){
                $q->where('name', 'Income');
             })->orderBy('name','asc')->get();
             $this->tax_accounts = Account::whereHas('account_type', function ($query) {
                return $query->where('name','Sales Taxes');
            })->orderBy('name','asc')->get();
            $this->vendors = Vendor::orderBy('name','asc')->get();
            $this->currencies = Currency::orderBy('name','asc')->get();
            $this->purchase_number = $this->purchaseNumber();
    }

   
    public function updatedSelectedAccount($id)
    {
        if (!is_null($id) ) {
        $this->expenses = Expense::where('account_id', $id)->orderBy('name','asc')->get();
        }
    }
    public function updatedSelectedProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->amount[$key] = $product->price;
                }
                $this->qty[$key] = 1;

                if ($product->tax_id) {
                    $this->selectedTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->tax_rate[$key] = $tax->rate;
            }else{
                $this->tax_rate[$key] = "";
            }
           
        }
    }

    public function updatedSelectedCurrentProduct($id, $key){
        if (!is_null($id)) {
            $product = Product::find($id);
            if (isset($product)) {
                if ($product->price) {
                    $this->current_amount[$key] = $product->price;
                }
                $this->current_qty[$key] = 1;

                if ($product->tax_id) {
                    $this->selectedCurrentTax[$key] = $product->tax_id;
                    $tax = Account::find($product->tax_id);
                    if (isset($tax)) {
                        $this->current_tax_rate[$key] = $tax->rate;
                    }
                    
                }  
            }
           
        }
    }

    public function updatedSelectedCurrentTax($id, $key){
        if(!is_null($id)){
            $tax = Account::find($id);
            if (isset($tax)) {
                $this->current_tax_rate[$key] = $tax->rate;
            }
           
        }
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'selectedCurrency' => 'required',
        'selectedVendorType' => 'required',
        'vendor_id' => 'required',
        'purchase_number' => 'required',
        'value' => 'required',
        'selectedProduct.0' => 'required',
        'amount.0' => 'required',
        'qty.0' => 'required',
        'selectedProduct.*' => 'required',
        'amount.*' => 'required',
        'qty.*' => 'required',
    ];
  
    public function markSent($id){
        $purchase = Purchase::find($id);
        $purchase->is_sent = True;
        $purchase->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Purchase Order Marked As Sent"
        ]);
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

    public function store(){
        $purchase = new Purchase;
        $purchase->user_id = Auth::user()->id;
        $purchase->purchase_number = $this->purchase_number;
        $purchase->date = $this->date;
        $purchase->department = $this->department;
        $purchase->booking_id = $this->booking_id ? $this->booking_id : Null;
        $purchase->description = $this->description;
        $purchase->account_id = $this->selectedAccount;
        $purchase->vendor_id = $this->vendor_id;
        $purchase->currency_id = $this->selectedCurrency;
        $purchase->status = '1';
        $purchase->expiry = Carbon::parse($this->date)->addMonth()->format('Y-m-d');
        $purchase->save();
        $this->purchase_id = $purchase->id;

        foreach($this->selectedProduct as $key => $value){
               
            $purchase_product = new PurchaseProduct;
            $purchase_product->purchase_id = $purchase->id;

            if (isset($this->selectedProduct[$key])) {
                $purchase_product->product_id = $this->selectedProduct[$key];
            }
            if (isset($this->qty[$key])) {
                $purchase_product->qty = $this->qty[$key];
            }
            if (isset($this->amount[$key])) {
                $purchase_product->amount = $this->amount[$key];
            }
            if (isset($this->tax_rate[$key])) {
                $purchase_product->tax_rate = $this->tax_rate[$key];
            }
            if (isset($this->selectedTax[$key])) {
                $purchase_product->tax_id = $this->selectedTax[$key];
            }
             if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {

                $item_subtotal = $this->amount[$key]*$this->qty[$key];
                $purchase_product->subtotal = $item_subtotal;
                $this->subtotal = $this->subtotal + $item_subtotal;

            }
            if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {

                $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                $purchase_product->tax_amount =  $item_tax_amount;
                $this->tax_amount = $this->tax_amount + $item_tax_amount;
                $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                $purchase_product->subtotal_incl =  $item_subtotal_incl;
                $this->total =  $this->total + $item_subtotal_incl;

            }else{
                $item_subtotal_incl = $item_subtotal;
                $purchase_product->subtotal_incl = $item_subtotal_incl;
                $this->total =  $this->total + $item_subtotal_incl;
            }

            if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                $purchase_product->exchange_rate = $this->exchange_rate;
                $purchase_product->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
             }
            
            $purchase_product->save();
        }

            $purchase = Purchase::find($purchase->id);
            $purchase->tax_amount = $this->tax_amount;
            $purchase->subtotal = $this->subtotal;
            $purchase->total = $this->total;
            $purchase->exchange_rate = $this->exchange_rate;
            $purchase->exchange_amount = $this->exchange_amount;
            $purchase->update();


          
        if (isset($this->file) && isset($this->title) && $this->file != "") {
    
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
              $document->purchase_id = $purchase->id;
              $document->category = 'purchase';
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

         $notifications = Notification::where('category','Purchase Order Authorization')->where('status',1)->get();
       
                
        if ($notifications->isNotEmpty()) {
            foreach ($notifications as $notification) {
                if($notification && isset($notification->category)){
                $email = $notification->email ?? $notification->employee->email ?? null;
                if($email){
                    Mail::to($email)->send(new PendingNotificationEmails($this->company, $notification, $purchase));
                }
                }
            }
        }

         
            $this->inputs = [];
            $this->subtotal = Null;
            $this->total = Null;
            $this->tax_amount = Null;

            redirect(request()->header('Referer'));

          $this->dispatchBrowserEvent('hide-purchaseModal');
          
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Purchase Order Created Successfully!!"
          ]);
    }

    public function quotation($id){
        $purchase = Purchase::find($id);
        $this->purchase_id = $purchase->id;
        $this->dispatchBrowserEvent('show-purchaseQuotationsModal');
    }
    

    public function edit($id){
        $this->purchase = Purchase::find($id);
        $this->purchase_number = $this->purchase->purchase_number;
        $this->date = $this->purchase->date;
        $this->selectedCurrency = $this->purchase->currency_id;
        $this->booking_id = $this->purchase->booking_id;
        $this->purchase_order_products = $this->purchase->purchase_products;
        if(isset($this->purchase_order_products)){

            foreach($this->purchase_order_products as $purchase_product){
                $this->selectedCurrentProduct[] = $purchase_product->product_id; 
                $this->current_qty[] = $purchase_product->qty; 
                $this->current_amount[] = $purchase_product->amount; 
                $this->selectedCurrentTax[] = $purchase_product->tax_id; 
                $this->current_tax_rate[] = $purchase_product->tax_rate; 
            }

        }
        $this->vendor_id = $this->purchase->vendor_id;
        $this->selectedVendorType = $this->purchase->vendor_type_id;
        $this->selectedAccount = $this->purchase->account_id;
        $this->expense_id = $this->purchase->expense_id;
        $this->status = $this->purchase->status;
        $this->description = $this->purchase->description;
        $this->purchase_id = $this->purchase->id;
        $this->purchase = $this->purchase;
        $this->vendors = Vendor::where('vendor_type_id',$this->selectedVendorType)->orderBy('name','asc')->get();
        $this->dispatchBrowserEvent('show-purchaseEditModal');
    }
    public function update(){
        if ($this->purchase_id) {

        $purchase = Purchase::find($this->purchase_id);
        $purchase->user_id = Auth::user()->id;
        $purchase->purchase_number = $this->purchase_number;
        $purchase->date = $this->date;
        $purchase->description = $this->description;
        $purchase->booking_id = $this->booking_id ? $this->booking_id : Null;
        $purchase->account_id = $this->selectedAccount;
        $purchase->expense_id = $this->expense_id;
        $purchase->vendor_id = $this->vendor_id;
        $purchase->vendor_type_id = $this->selectedVendorType;
        $purchase->currency_id = $this->selectedCurrency;
        $purchase->expiry = Carbon::parse($this->date)->addDays(7)->format('Y-m-d');
        $purchase->status = '1';
        $purchase->update();
        
        // dd($this->purchase_order_products->count());

        foreach($this->purchase_order_products as $key => $purchase_product){
               
            $purchase_product = PurchaseProduct::find($purchase_product->id);
            $purchase_product->purchase_id = $purchase->id;
            if (isset($this->selectedCurrentProduct[$key])) {
                $purchase_product->product_id = $this->selectedCurrentProduct[$key];
            }
            if (isset($this->current_qty[$key])) {
                $purchase_product->qty = $this->current_qty[$key];
            }
            
            if (isset($this->current_amount[$key])) {
                $purchase_product->amount = $this->current_amount[$key];
            }
            if (isset($this->current_tax_rate[$key])) {
                $purchase_product->tax_rate = $this->current_tax_rate[$key];
            }
            if (isset($this->selectedCurrentTax[$key])) {
                $purchase_product->tax_id = $this->selectedCurrentTax[$key];
            }

             if ((isset($this->current_amount[$key]) && is_numeric($this->current_amount[$key])) && ( isset($this->current_qty[$key]) && is_numeric($this->current_qty[$key]) ) ) {

                $item_subtotal = $this->current_amount[$key]*$this->current_qty[$key];
                $purchase_product->subtotal = $item_subtotal;
                $this->subtotal = $this->subtotal + $item_subtotal;

            }

            if (isset($this->current_tax_rate[$key]) && is_numeric($this->current_tax_rate[$key])) {

                $item_tax_amount = ($item_subtotal * ($this->current_tax_rate[$key] / 100 ));
                $purchase_product->tax_amount =  $item_tax_amount;
                $this->tax_amount = $this->tax_amount + $item_tax_amount;
                $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                $purchase_product->subtotal_incl =  $item_subtotal_incl;
                $this->total =  $this->total + $item_subtotal_incl;

            }else{
                $item_subtotal_incl = $item_subtotal;
                $purchase_product->subtotal_incl = $item_subtotal_incl;
                $this->total =  $this->total + $item_subtotal_incl;
            }

            if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                $purchase_product->exchange_rate = $this->exchange_rate;
                $purchase_product->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
             }
            
            $purchase_product->update();


        }

        $purchase = Purchase::find($purchase->id);
        $purchase->tax_amount = $this->tax_amount;
        $purchase->subtotal = $this->subtotal;
        $purchase->total = $this->total;
        $purchase->exchange_rate = $this->exchange_rate;
        $purchase->exchange_amount = $this->exchange_amount;
        $purchase->update();

        if (isset($this->selectedProduct)) {
            foreach($this->selectedProduct as $key => $value){
               
                $purchase_product = new PurchaseProduct;
                $purchase_product->purchase_id = $purchase->id;
    
                if (isset($this->selectedProduct[$key])) {
                    $purchase_product->product_id = $this->selectedProduct[$key];
                }
                if (isset($this->qty[$key])) {
                    $purchase_product->qty = $this->qty[$key];
                }
                if (isset($this->amount[$key])) {
                    $purchase_product->amount = $this->amount[$key];
                }
                if (isset($this->tax_rate[$key])) {
                    $purchase_product->tax_rate = $this->tax_rate[$key];
                }
                if (isset($this->selectedTax[$key])) {
                    $purchase_product->tax_id = $this->selectedTax[$key];
                }
                 if ((isset($this->amount[$key]) && is_numeric($this->amount[$key])) && ( isset($this->qty[$key]) && is_numeric($this->qty[$key]) ) ) {
    
                    $item_subtotal = $this->amount[$key]*$this->qty[$key];
                    $purchase_product->subtotal = $item_subtotal;
                    $this->subtotal = $this->subtotal + $item_subtotal;
    
                }
                if (isset($this->tax_rate[$key]) && is_numeric($this->tax_rate[$key])) {
    
                    $item_tax_amount = ($item_subtotal * ($this->tax_rate[$key] / 100 ));
                    $purchase_product->tax_amount =  $item_tax_amount;
                    $this->tax_amount = $this->tax_amount + $item_tax_amount;
                    $item_subtotal_incl = $item_tax_amount + $item_subtotal;
                    $purchase_product->subtotal_incl =  $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
    
                }else{
                    $item_subtotal_incl = $item_subtotal;
                    $purchase_product->subtotal_incl = $item_subtotal_incl;
                    $this->total =  $this->total + $item_subtotal_incl;
                }

                if ((isset($this->exchange_rate) && is_numeric($this->exchange_rate))) {
                    $purchase_product->exchange_rate = $this->exchange_rate;
                    $purchase_product->exchange_amount = $this->exchange_rate * $item_subtotal_incl ;
                 }
                
                $purchase_product->save();
            }

            $purchase = Purchase::find($purchase->id);
            $purchase->tax_amount = $this->tax_amount;
            $purchase->subtotal = $this->subtotal;
            $purchase->total = $this->total;
            $purchase->exchange_rate = $this->exchange_rate;
            $purchase->exchange_amount = $this->exchange_amount;
            $purchase->update();
        }

          $this->inputs = [];
          $this->subtotal = Null;
          $this->total = Null;
          $this->tax_amount = Null;

          redirect(request()->header('Referer'));
          $this->dispatchBrowserEvent('hide-purchaseEditModal');
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Purchase Order Updated Successfully!!"
          ]);
        
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function removeShow($id){

        $this->purchase_product = PurchaseProduct::find($id);
        $this->tax_amount = $this->purchase->tax_amount;
        $this->total = $this->purchase->total;
        $this->subtotal = $this->purchase->subtotal;
        $this->dispatchBrowserEvent('show-removeModal');
    }


    public function removePurchaseItem(){ 

        if (is_numeric($this->subtotal) && $this->purchase_product->subtotal) {
            $this->subtotal = $this->subtotal - $this->purchase_product->subtotal;
        }
        if (is_numeric($this->total) && $this->purchase_product->subtotal_incl) {
            $this->total = $this->total - $this->purchase_product->subtotal_incl;
        }
        if (is_numeric($this->tax_amount) && $this->purchase_product->tax_amount) {
            $this->tax_amount = $this->tax_amount - $this->purchase_product->tax_amount;

        }
      
        $purchase =  Purchase::find($this->purchase->id);
        $purchase->total = $this->total;
        $purchase->subtotal = $this->subtotal;
        $purchase->tax_amount = $this->tax_amount;
        $purchase->update();

        $this->purchase_product->delete();

        $this->purchase_order_products = PurchaseProduct::where('purchase_id',$this->purchase->id)->get();
        
        $this->total = Null;
        $this->subtotal = Null;
        $this->tax_amount = Null;

        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Item Deleted Successfully!!"
        ]);
      

    }

    public function render()
    {

        if($this->purchase){
            $this->purchase_order_products = PurchaseProduct::where('purchase_id',$this->purchase->id)->get();
        }
      
        $this->vendors = Vendor::orderBy('name','asc')->get();

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->total) && $this->total > 0 )) {
            $this->exchange_amount = $this->exchange_rate * $this->total;
        }
        $this->products = Product::orderBy('name','asc')->where('department', $this->department)->where('status',True)->where('buy',True)->get();
        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                if ($this->department == "asset") {
                    return view('livewire.purchases.index',[
                        'purchases' => Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereBetween($this->purchase_filter,[$this->from, $this->to] )
                        ->where('purchase_number','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhereHas('vendor', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('booking', function ($query) {
                            return $query->where('booking_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('purchase_products.product', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                       
                        ->orderBy($this->purchase_filter,'desc')->paginate(10),
                      
                    ]);
                }else{
                    return view('livewire.purchases.index',[
                        'purchases' => Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereBetween($this->purchase_filter,[$this->from, $this->to] )
                        ->where('department',$this->department)
                        ->where('purchase_number','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('description','like', '%'.$this->search.'%')
                        ->orWhereHas('vendor', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('booking', function ($query) {
                            return $query->where('booking_number', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('purchase_products.product', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                       
                        ->orderBy($this->purchase_filter,'desc')->paginate(10),
                      
                    ]);
                }
              
            }else {
                if ($this->department == "asset") {
                    return view('livewire.purchases.index',[
                        'purchases' => Purchase::query()->with('vendor','purchase_products','purchase_products.product')->whereBetween($this->purchase_filter,[$this->from, $this->to] )->orderBy($this->purchase_filter,'desc')->paginate(10),
                    ]);
                }else{
                    return view('livewire.purchases.index',[
                        'purchases' => Purchase::query()->with('vendor','purchase_products','purchase_products.product')->where('department',$this->department)->whereBetween($this->purchase_filter,[$this->from, $this->to] )->orderBy($this->purchase_filter,'desc')->paginate(10),
                    ]);
                }
                
            }
           
        }
        elseif (isset($this->search)) {
            if ($this->department == "asset") {
                return view('livewire.purchases.index',[
                    'purchases' => Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereMonth($this->purchase_filter, date('m'))
                    ->whereYear($this->purchase_filter, date('Y'))
                    ->where('purchase_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhereHas('booking', function ($query) {
                        return $query->where('booking_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase_products.product', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                   
                    ->orderBy($this->purchase_filter,'desc')->paginate(10),
                ]);
            }else{
                return view('livewire.purchases.index',[
                    'purchases' => Purchase::query()->with('vendor','booking','purchase_products','purchase_products.product')->where('department',$this->department)->whereMonth($this->purchase_filter, date('m'))
                    ->whereYear($this->purchase_filter, date('Y'))
                    ->where('department',$this->department)
                    ->where('purchase_number','like', '%'.$this->search.'%')
                    ->orWhere('date','like', '%'.$this->search.'%')
                    ->orWhere('description','like', '%'.$this->search.'%')
                    ->orWhereHas('booking', function ($query) {
                        return $query->where('booking_number', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('vendor', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('purchase_products.product', function ($query) {
                        return $query->where('name', 'like', '%'.$this->search.'%');
                    })
                   
                    ->orderBy($this->purchase_filter,'desc')->paginate(10),
                ]);
            }
           
           
        }
        else {
            if ($this->department == "asset") {
                return view('livewire.purchases.index',[
                    'purchases' => Purchase::query()->with('vendor','purchase_products','purchase_products.product')->whereMonth($this->purchase_filter, date('m'))
                    ->whereYear($this->purchase_filter, date('Y'))->orderBy($this->purchase_filter,'desc')->paginate(10),
                ]);
            }else{
                return view('livewire.purchases.index',[
                    'purchases' => Purchase::query()->with('vendor','purchase_products','purchase_products.product')->where('department',$this->department)->whereMonth($this->purchase_filter, date('m'))
                    ->whereYear($this->purchase_filter, date('Y'))->orderBy($this->purchase_filter,'desc')->paginate(10),
                ]);
            }
           
          
        }

    

       
    }
}
