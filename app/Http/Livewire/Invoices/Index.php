<?php

namespace App\Http\Livewire\Invoices;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\Trip;
use App\Models\Cargo;
use App\Models\Account;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\Destination;
use App\Models\InvoiceItem;
use App\Exports\SalesExport;
use App\Models\Denomination;
use App\Models\TripDocument;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Models\InvoicePayment;
use App\Models\TransactionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $invoice_filter;
    public $tax_status = "all";
    public $trip_filter;
    private $invoices;
    public $invoice_products;
    public $invoice;
    public $invoice_id;
    public $customers;
    public $currencies;
    public $currency_id;
    public $selectedCurrency;
    public $receipt_number;
    public $invoice_number;
    public $date;
    public $amount;
    public $receipt;
    public $balance;   
    public $accounts;
    public $account_id;
    public $bank_accounts;
    public $bank_account_id;
    public $trips;
    public $trip;
    public $trip_id;
    public $notes;
    public $loans;
    public $loan;
    public $selectedLoan;
    public $invoice_balance;
    public $pop;
    public $reference_code;
    public $invoice_currency;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $user_id;
    public $customer_id;
    public $current_balance;
    public $mode_of_payment;
    public $selectedCustomer ;
    public $customer_accounts;
    public $selectedCustomerAccount;
    public $unpaid_invoices;
    public $selectedInvoice;
    public $account_payments;
    public $selected_currency;
    public $selected_customer;
    public $last_payment;
    public $payment_id;
    public $drawdown_invoice_balance;
    public $drawdown_amount;
    public $invoice_drawdown_current_balance;
    public $invoice_drawdown_balance;
    public $payment_drawdown_balance;
    public $amount_paid;
    public $accrual_balance;
    public $uninvoiced_trips;
    public $item_subtotal = 0;
    public $subtotal = 0;
    public $subtotal_incl = 0;
    public $total = 0;

    public $exchange_amount;
    public $exchange_rate;

    public $expires_at;
    public $title;
    public $file;


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
    }
   
    
    

    public function mount(){
        $this->resetPage();
        $this->invoice_filter = "created_at";
        $this->tax_status = "all";
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get()->sortBy('account_name');
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->accounts = Account::where('account_type_id',1)->orderBy('name','asc')->get();
        $this->transaction_type_id = TransactionType::where('name','Deposit')->first()->id;
        $this->transaction_category = "Sales";
    }



    
    public function exportSalesCSV(Excel $excel){
        return $excel->download(new SalesExport($this->from, $this->to, $this->invoice_filter, $this->tax_status, $this->search), 'sales_' .time().'.csv', Excel::CSV);
    }
    public function exportSalesPDF(Excel $excel){
        return $excel->download(new SalesExport($this->from, $this->to, $this->invoice_filter, $this->tax_status, $this->search), 'sales_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportSalesExcel(Excel $excel){
        return $excel->download(new SalesExport($this->from, $this->to, $this->invoice_filter, $this->tax_status, $this->search), 'sales_' .time().'.xlsx');
    }



    public function receiptNumber(){

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
    
        $receipt = Receipt::orderBy('id','desc')->first();
    
        if (!$receipt) {
            $receipt_number =  $initials .'R'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $receipt->id + 1;
            $receipt_number =  $initials .'R'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $receipt_number;
    
    
    }
    public function paymentNumber(){

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
    
        $payment = Payment::orderBy('id','desc')->first();
    
        if (!$payment) {
            $payment_number =  $initials .'P'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $payment->id + 1;
            $payment_number =  $initials .'P'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
    
        return  $payment_number;
    
    
    }


    public function updatedSelectedCustomer($id){
        if (!is_null($id)) {
            $this->selectedCustomer = $id;
            $this->selected_customer = Customer::find($id);
        } 
    }
    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
         
            $this->selected_currency = Currency::find($id);
        } 
    }

    public function updatedSelectedCustomerAccount($id){
        if (!is_null($id)) {
            $this->selectedCustomerAccount = $id;
            $account = Account::find($id);
            $currency_id = $account->currency_id;
           

            $this->account_payments = Payment::where('customer_account_id',$id)
            ->where('drawdown_balance','>',0)
            ->orderBy('created_at','desc')->get();
        } 
    }

 

    public function updatedSelectedInvoice($id){
        if (!is_null($id)) {
        $this->selectedInvoice = $id;
        $this->invoice = Invoice::find($id);
        $this->invoice_drawdown_balance = $this->invoice->balance;
        }
      
    }

    public function updatedSelectedLoan($id){
        if(!is_null($id)){
            $this->loan = Loan::find($id);
            $this->amount = $this->loan->payment_per_month;
        }
    }

    public function drawdownPayments(){

        if (isset($this->drawdown_amount) && isset($this->invoice_drawdown_balance) && ($this->drawdown_amount >= $this->invoice_drawdown_balance)) {
            $this->payment_drawdown_balance = $this->drawdown_amount - $this->invoice_drawdown_balance;
            $this->invoice_drawdown_balance = 0;
            $this->amount_paid = $this->invoice_drawdown_balance;
        }else{
            $this->invoice_drawdown_balance = $this->invoice_drawdown_balance - $this->drawdown_amount;
            $this->payment_drawdown_balance = 0;
            $this->amount_paid = $this->drawdown_amount;
        }

        $payment = Payment::find($this->last_payment->id);
        $payment->drawdown_balance = $this->payment_drawdown_balance;
        $payment->update();
        
        $invoice = Invoice::find($this->selectedInvoice); 

        $invoice_payment = new InvoicePayment;
        $invoice_payment->invoice_id = $this->selectedInvoice;
        $invoice_payment->customer_id = $invoice->customer_id;
        $invoice_payment->payment_id =$this->last_payment->id;
        $invoice_payment->currency_id = $invoice->currency_id;
        $invoice_payment->amount = $this->amount_paid;
        $invoice_payment->save();
      
    

        $invoice = Invoice::find($this->selectedInvoice);
        $invoice->balance = $this->invoice_drawdown_balance;
        if ($this->invoice_drawdown_balance <= 0) {
            $invoice->status = "Paid";
        }else {
            $invoice->status = "Partial";
        }
        $invoice->update();
 
        $this->dispatchBrowserEvent('hide-paymentDrawdownModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Drawdown Effected Successfully!!"
        ]);
       
    }

    public function invoiceNumber(){
  
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $this->initials = $words[0][0].$words[1][0];
            }else {
                $this->initials = $words[0][0];
            }
            $invoice = Invoice::orderBy('id','desc')->first();
            if (!$invoice) {
                $this->number = 1;
                $invoice_number =  $this->initials .'I'. str_pad(1, 5, "0", STR_PAD_LEFT);
            }else {
                $this->number = $invoice->id + 1;
                $invoice_number =  $this->initials .'I'. str_pad($this->number, 5, "0", STR_PAD_LEFT);
            }
        
            return  $invoice_number;
      
    
    }

    public function showBulkInvoices(){
            $this->trip_filter = "created_at";
            $this->dispatchBrowserEvent('show-bulkInvoicesModal');
    }

    public function setDescription($id){

        $trip = Trip::find($id);
                
        $cargo = $trip->cargo;
        $weight = $trip->weight.'tons' ;
        $cargo_name = $cargo ? $cargo->name : "";

        if (isset($cargo)) {
            if ($trip->cargo->type == "Solid") {
                $cargo_measurement = $trip->quantity.' '. $trip->measurement;
            }else {
                $cargo_measurement =  $trip->litreage_at_20.' '. $trip->measurement;
            }
            
        }else{
            $cargo_measurement = "";
        }
      
      

        if ($trip->horse) {
            $regnumber = $trip->horse ? $trip->horse->registration_number : "";
        }elseif ($trip->vehicle) {
            $regnumber = $trip->vehicle ? $trip->vehicle->registration_number : "";
        }else{
            $regnumber = "";
        }
        
        $origin = Destination::find($trip->from);
        $origin_city = $origin ? $origin->city : "";
        $destination = Destination::find($trip->to);
        $destination_city = $destination ? $destination->city : "";
        $symbol =  $trip->currency ? $trip->currency->symbol : "";

     

        if ($trip->freight_calculation) {
            
            if ($trip->freight_calculation == "flat_rate") {
               $formula = "R";
               $variables =  $symbol.$trip->rate;
            }
            elseif($trip->freight_calculation == "rate_weight"){
               
                if ($trip->cargo) {
                    if ($trip->cargo->type == "Solid") {
                        $formula = "R*W";
                        $variables = $symbol.$trip->rate.'*'.$trip->weight;
                    }elseif ($trip->cargo->type == "Liquid") {
                        $formula = "R*L";
                        $variables = $symbol.$trip->rate.'*'.$trip->litreage_at_20;
                    }
                }else{
                    $formula = "";
                    $variables = "";
                }
              
            }
            elseif($trip->freight_calculation == "rate_distance"){
                $formula = "R*D";
                $variables = $symbol.$trip->rate.'*'.$trip->distance;
            }
            elseif($trip->freight_calculation == "rate_weight_distance"){
                if ($trip->cargo) {
                    if ($trip->cargo->type == "Solid") {
                        $formula = "R*W*D";
                        $variables = $symbol.$trip->rate.'*'.$trip->weight.'*'.$trip->distance;
                    }elseif ($trip->cargo->type == "Liquid") {
                        $formula = "R*L*D";
                        $variables = $symbol.$trip->rate.'*'.$trip->litreage_at_20.'*'.$trip->distance;
                    }
                }else{
                    $formula = "";
                    $variables = "";
                }
            }else {
                $formula = "";
                $variables = "";
            }
        }else{
            $formula = "";
            $variables = "";
        }
        
        $lp = $trip->loading_point ? $trip->loading_point->name : "";
        $op = $trip->offloading_point ? $trip->offloading_point->name : "";
        $from = $origin_city.' '.$lp ;
        $to = $destination_city.' '.$op ;
        $rate = $trip->rate;
        $quantity = $trip->quantity.' '.$trip->measurement;
        $litreage = $trip->litreage_at_20.' '.$trip->measurement;
        $trip_number = $trip->trip_number;
        $document = TripDocument::where('trip_id',$trip->id)->where('title','POD')->get()->first();
        $pod_number = $document ? $document->document_number : "";
      

        if (isset($cargo) && $cargo->type == "Solid") {
            $cargo_description = $cargo_name .' '.$weight.' '. $quantity;
        }elseif (isset($cargo) && $cargo->type == "Liquid") {
            $cargo_description =  $cargo_name.' '.$weight .' '. $litreage;
        }else {
            $cargo_description = "";
        }
       
        $load_details = $cargo_description .' '.$formula .' '.$variables;

       $trip_details = $load_details .' '.  $from .' to '.  $to .' '.  $regnumber.' '.$pod_number;

        return  $trip_details;
    }

    public function createInvoices(){

        $this->income_account_id = Account::where('name','Sales')->first()->id;

        if (isset($this->uninvoiced_trips)) {
          
          foreach ($this->uninvoiced_trips as $trip) {
           
            $invoice = new Invoice;
            $invoice->user_id = Auth::user()->id;
            $invoice->company_id = Auth::user()->employee->company_id;
            $invoice->currency_id = $trip->currency_id;
            $invoice->customer_id = $trip->customer_id;
            $invoice->invoice_number = $this->invoiceNumber();
            $invoice->account_id = $this->income_account_id;
            $invoice->date = $trip->start_date;
            $invoice->expiry = $trip->start_date;
            $invoice->reason = 'Trip';
            $invoice->authorization = 'approved';
            $invoice->save();                   
                    
            $invoice_item = new InvoiceItem;
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->trip_id = $trip->id;
            $invoice_item->description = $this->setDescription($trip->id);
            $invoice_item->qty = 1;
            $invoice_item->amount = $trip->freight;
            $subtotal = $trip->freight;
            $invoice_item->subtotal = $subtotal;
            $invoice_item->subtotal_incl = $subtotal;
            $invoice_item->save();

            $invoice = Invoice::find($invoice->id);
            $invoice->subtotal = $subtotal;
            $invoice->total = $subtotal;
            $invoice->balance = $subtotal;
            $invoice->update();


          }

          $this->dispatchBrowserEvent('hide-bulkInvoicesModal');
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Invoices Created Successfully!!"
          ]);

          return redirect(request()->header('Referer'));
      
        }
    }

    public function showInvoiceUpdate(){
        $this->dispatchBrowserEvent('show-updateInvoicesModal');
    }
    public function updateAllInvoices(){

        $invoices = Invoice::whereNotNull('accrual_balance')->orderBy('id','asc')->orderBy($this->invoice_filter,'asc')->get();
        if($invoices){
            foreach($invoices as $invoice){
                if((isset($invoice->customer_id) && isset($invoice->currency_id))){
                    if (!is_null($invoice->accrual_balance)) {
                     $last_payment = Payment::where('created_at', '<', $invoice->created_at)
                                            ->where('customer_id', $invoice->customer_id)
                                            ->where('currency_id', $invoice->currency_id)
                                            ->whereNotNull('invoice_id') // Ensure payment is linked to an invoice
                                            ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                                            ->orderByDesc('date') // Prioritize latest transaction date
                                            ->orderByDesc('created_at') // If same date, get most recently recorded
                                            ->orderByDesc('id') // If same creation time, get latest ID
                                            ->first();
    
                                        // If no valid payment exists, retrieve the last invoice with the highest accrual balance
                    $last_invoice = null;
                    if (!$last_payment) {
                        $last_invoice = Invoice::where('authorization', 'approved')
                            ->where('customer_id', $invoice->customer_id)
                            ->where('currency_id', $invoice->currency_id)
                            ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                            ->orderByDesc('accrual_balance') // Prioritize highest balance
                            ->orderByDesc('date') // If tie, use latest invoice date
                            ->orderByDesc('id') // If tie, use latest ID
                            ->first();
                    }
    
                  
                    // Determine the last accrual balance, prioritizing payments over invoices
                    $previous_balance = $last_payment && is_numeric($last_payment->accrual_balance) 
                        ? $last_payment->accrual_balance 
                        : ($last_invoice && is_numeric($last_invoice->accrual_balance) ? $last_invoice->accrual_balance : 0);
    
                    // Compute and set the new accrual balance
                    $invoice->accrual_balance = $previous_balance + $invoice->total;
                    $invoice->update(); // Save the updated invoice
                       
                    }
                }
            }
        }

        $this->dispatchBrowserEvent('hide-updateInvoicesModal');
    }
    
    public function showPayment($id){
        $this->invoice_id = $id ;
        $this->invoice = Invoice::find($id);
        $this->invoice_currency = $this->invoice->currency;
        $this->selectedCurrency = $this->invoice->currency_id;
        $this->customer_id = $this->invoice->customer_id;
        $this->loans = Loan::where('authorization','approved')->where('currency_id',$this->invoice_currency->id)->where('movement','In')->where('balance','>',0)->where('status','Unpaid')->orWhere('status','Partial')->get();
        $this->invoice_balance = $this->invoice->balance;
        $this->current_balance = $this->invoice_balance - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function recordPayment(){

        $payment = new Payment;
        $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
        $payment->customer_id = $this->invoice->customer_id;
        $payment->user_id = Auth::user()->id;
        $payment->currency_id = $this->invoice->currency_id;
        $payment->payment_number = $this->paymentNumber();   
        $payment->transaction_type_id = $this->transaction_type_id;
        $payment->transaction_category = $this->transaction_category;
        $payment->movement = "Crt";
        $payment->notes = $this->notes;
        $payment->movement = "Crt";
        $payment->mode_of_payment = $this->mode_of_payment;
        $payment->category = "invoice";
        $payment->reference_code = $this->reference_code;
        $payment->bank_account_id = $this->bank_account_id;
        $payment->loan_id = $this->selectedLoan;
        if ($this->loan) {
            $loan_balance = $this->loan->balance - $this->amount;
            $this->loan->balance = $this->loan->balance - $this->amount;
            if ($loan_balance <= 0) {
                $this->loan->status = "Paid";
            }else {
                $this->loan->status = "Partial";
            }
            $this->loan->update();
            $payment->account_id = $this->loan->account_id;
           
        }else{
            $payment->account_id = $this->account_id;
        }
        $payment->invoice_id = $this->invoice->id;
        
        $payment->amount = $this->amount;
        $payment->exchange_rate = $this->exchange_rate;
        $payment->exchange_amount = $this->exchange_amount;
        if (is_numeric($this->invoice->balance) && is_numeric($this->amount)) {
            $this->current_balance = $this->invoice->balance - $this->amount;
        }
        
        $payment->balance = $this->current_balance;

        $payments = DB::table('payments')->select('customer_id','currency_id',
                DB::raw('CAST(accrual_balance AS DECIMAL(10,2)) as accrual_balance')
            )
            ->where('customer_id', $this->invoice->customer_id)
            ->where('currency_id', $this->invoice->currency_id)
            ->whereNotNull('invoice_id'); // Ensure the payment is linked to an invoice

        $invoices = DB::table('invoices')
            ->select(
                'customer_id',
                'customer_id',
                DB::raw('CAST(accrual_balance AS DECIMAL(10,2)) as accrual_balance')
            )
            ->where('customer_id', $this->invoice->customer_id)
            ->where('currency_id', $this->invoice->currency_id);

        $highestBalance = DB::table(DB::raw("({$payments->toSql()} UNION ALL {$invoices->toSql()}) as combined_data"))
            ->mergeBindings($payments)
            ->mergeBindings($invoices)
            ->select(DB::raw('MAX(accrual_balance) as highest_accrual_balance'))
            ->first();



            if((isset($highestBalance) && is_numeric($highestBalance->highest_accrual_balance)) && is_numeric($this->amount)){
                $payment->accrual_balance = $highestBalance->highest_accrual_balance - $this->amount;
            }
        
        $payment->date = $this->date;
        $payment->save();

        $invoice_payment = new InvoicePayment;
        $invoice_payment->customer_id = $this->invoice->customer_id;
        $invoice_payment->invoice_id = $this->invoice->id;
        $invoice_payment->payment_id = $payment->id;
        $invoice_payment->currency_id = $this->invoice->currency_id;
        $invoice_payment->amount = $this->amount;
        $invoice_payment->save();  
        

        if(isset($this->pop)){
            $file = $this->pop;
            // get file with ext
            $fileNameWithExt = $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore = $filename.'_'.time().'.'.$extention;
            $file->storeAs('/documents', $fileNameToStore, 'my_files');

                    $document = new Document;
        $document->payment_id = $payment->id;
        $document->category = 'payment';
        $document->title = "Proof Of Payment";
        if (isset( $fileNameToStore)) {
             $document->filename = $fileNameToStore;
        }
        if(isset($this->expires_at)){
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


        $invoice = Invoice::find($this->invoice->id);
        $invoice->balance = $this->current_balance;
     
        if ($this->current_balance <= 0) {
            $invoice->status = "Paid";
        }else {
            $invoice->status = "Partial";
        }
        $invoice->update();

        if ($this->denomination) {
            foreach ($this->denomination as $key => $value) {
                $denomination = new Denomination;
                $denomination->payment_id = $payment->id;
                if (isset($this->denomination[$key])) {
                    $denomination->denomination = $this->denomination[$key];
                }
              if (isset($this->denomination_qty[$key])) {
                $denomination->quantity =  $this->denomination_qty[$key];
              }
               
                $denomination->save();
            }
        }

        if (isset($this->account_id)) {
            $account = Account::find($this->account_id);
            $current_balance = $account->balance;
            $account->balance = $current_balance + $this->amount;
            $account->update();
        }
      
        $receipt =  new Receipt;
        $receipt->payment_id = $payment->id;
        $receipt->company_id = $payment->company_id;
        $receipt->invoice_id = $payment->invoice_id;
        $receipt->vendor_id = $payment->vendor_id;
        $receipt->transporter_id = $payment->transporter_id;
        $receipt->currency_id = $payment->currency_id;
        $receipt->receipt_number = $this->receiptNumber(); ;
        $receipt->user_id = Auth::user()->id;
        $receipt->amount = $this->amount;
        $receipt->balance = $this->current_balance;
        $receipt->date = $this->date;
        $receipt->save();

       
        $this->dispatchBrowserEvent('hide-paymentModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
        return redirect()->route('payments.index');
       
    }

   
    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {
        if (isset($this->selectedCustomer) && isset($this->selectedCurrency)) {
        
            $this->last_payment = Payment::where('customer_id',$this->selectedCustomer)->where('currency_id',$this->selectedCurrency)->where('transaction_category', "Customer Deposits")->orderBy('created_at','desc')->first();
            if(isset($this->last_payment)){
                $this->drawdown_amount = $this->last_payment->drawdown_balance;
                $this->payment_drawdown_balance = $this->last_payment->drawdown_balance;
            }
        

            $this->unpaid_invoices = Invoice::where('customer_id',$this->selectedCustomer)
                                        ->where('currency_id',$this->selectedCurrency)
                                        ->where('authorization','approved')
                                        ->where('status','!=','Paid')
                                        ->orderBy($this->invoice_filter,'desc')->get();
        }
      

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }
       
        $this->tax_status;

        if (isset($this->from) && isset($this->to) && isset($this->trip_filter)) {
            $this->uninvoiced_trips = Trip::where('trip_status','!=', 'Cancelled')->whereRaw('freight REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->where('authorization','approved')->whereBetween($this->trip_filter,[$this->from, $this->to] )->doesntHave('invoice_items')->get();
        }else{
            $this->uninvoiced_trips = Trip::where('trip_status','!=', 'Cancelled')->whereRaw('freight REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->where('authorization','approved')->doesntHave('invoice_items')->get();
        }

        $this->amount;
        $this->invoice_balance;

        if ((is_numeric($this->invoice_balance) && $this->invoice_balance > 0) && (is_numeric($this->amount))) {
            $this->current_balance = $this->invoice_balance - $this->amount;
        }

  
            if (isset($this->from) && isset($this->to)) {

                if (isset($this->search)) {

                    return view('livewire.invoices.index',[
                        'invoices' => Invoice::query()->with(['customer:id,name','currency'])->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                        ->where('invoice_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('expiry','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->invoice_filter,'desc')->paginate(10),
                        'current_balance' => $this->current_balance
                    ]);

                }else {
                    if($this->tax_status == "taxed"){

                        return view('livewire.invoices.index',[
                            'invoices' => Invoice::query()->with(['customer:id,name','currency'])
                            ->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                            ->where('tax_amount','!=', Null)
                            ->where('tax_amount','!=', 0)
                            ->where('tax_amount','!=', "")
                            ->orderBy($this->invoice_filter,'desc')->paginate(10),
                            'current_balance' => $this->current_balance
                        ]);

                    }elseif($this->tax_status == "non-taxed"){

                        return view('livewire.invoices.index',[
                            'invoices' => Invoice::query()->with(['customer:id,name','currency'])
                            ->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                            ->where('tax_amount', Null)
                            ->orWhere('tax_amount', "")
                            ->orWhere('tax_amount', 0)
                            ->orderBy($this->invoice_filter,'desc')->paginate(10),
                            'current_balance' => $this->current_balance
                        ]);

                    }elseif($this->tax_status == "all"){
                        return view('livewire.invoices.index',[
                            'invoices' => Invoice::query()->with(['customer:id,name','currency'])
                            ->whereBetween($this->invoice_filter,[$this->from, $this->to] )
                            ->orderBy($this->invoice_filter,'desc')->paginate(10),
                            'current_balance' => $this->current_balance
                        ]);
                    }
                
                }
               
            }
            elseif (isset($this->search)) {
               
                return view('livewire.invoices.index',[
                    'invoices' => Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
                    ->whereYear($this->invoice_filter, date('Y'))
                    ->where('invoice_number','like', '%'.$this->search.'%')
                        ->orWhere('status','like', '%'.$this->search.'%')
                        ->orWhere('date','like', '%'.$this->search.'%')
                        ->orWhere('expiry','like', '%'.$this->search.'%')
                        ->orWhere('authorization','like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('currency', function ($query) {
                            return $query->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orderBy($this->invoice_filter,'desc')->paginate(10),
                       
                        'current_balance' => $this->current_balance
                ]);

            }
            else {
                if ($this->tax_status == "all") {
                    return view('livewire.invoices.index',[
                        'invoices' => Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
                        ->whereYear($this->invoice_filter, date('Y'))->orderBy($this->invoice_filter,'desc')->paginate(10),
                        'current_balance' => $this->current_balance
                    ]);
                }elseif($this->tax_status == "taxed"){
                    return view('livewire.invoices.index',[
                        'invoices' => Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
                        ->whereYear($this->invoice_filter, date('Y'))
                        ->where('tax_amount','!=', Null)
                        ->where('tax_amount','!=', 0)
                        ->where('tax_amount','!=', "")
                        ->orderBy($this->invoice_filter,'desc')->paginate(10),
                        'current_balance' => $this->current_balance
                    ]);
                }elseif($this->tax_status == "non-taxed"){
                    return view('livewire.invoices.index',[
                        'invoices' => Invoice::query()->with(['customer:id,name','currency'])->whereMonth($this->invoice_filter, date('m'))
                        ->whereYear($this->invoice_filter, date('Y'))
                        ->where('tax_amount', Null)
                        ->orWhere('tax_amount', "")
                        ->orWhere('tax_amount', 0)
                        ->orderBy($this->invoice_filter,'desc')->paginate(10),
                        'current_balance' => $this->current_balance
                    ]);
                }
              
            }
        

    }
}
