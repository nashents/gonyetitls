<?php

namespace App\Http\Livewire\Bills;


use App\Exports\BillsExport;
use App\Imports\BillsImport;
use App\Models\Account;
use App\Models\Asset;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Denomination;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Trailer;
use App\Models\TransactionType;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class Index extends Component
{

    use WithFileUploads;

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $tax_status = "all";
    public $bank_accounts;
    public $bank_account_id;
    public $currencies;
    public $currency_id;
    public $selectedCurrency;
    public $payment_type;
    public $vehicles;
    public $vehicle_id;
    public $drivers;
    public $driver_id;
    public $horses;
    public $horse_id;
    public $trailers;
    public $trailer_id;
    public $customers;
    public $customer_id;
    public $assets;
    public $asset_id;
    public $transporters;
    public $transporter_id;
    public $trips;
    public $trip;
    public $trip_id;
    public $drawdown_bill_balance;
    public $amount_paid;
    public $drawdown_amount;
    public $bill_drawdown_current_balance;
    public $bill_drawdown_balance;
    public $payment_drawdown_balance;
    public $selectedVendorAccount;
    public $selectedVendor;
    public $importFile;
    private $bills;
    public $bill;
    public $selectedBill;
    public $unpaid_bills;
    public $account_payments;
    public $bill_id;
    public $bill_balance;
    public $bill_currency;
    public $bill_filter;
    public $selected_currency;
    public $selected_vendor;
    public $vendors;
    public $last_payment;
    public $pop;
    public $reference_code;
    public $transaction_types;
    public $transaction_type_id;
    public $transaction_category;
    public $name;
    public $denomination;
    public $denomination_qty;
    public $surname;
    public $notes;
    public $user_id;
    public $vendor_id;
    public $amount;
    public $current_balance;
    public $mode_of_payment;
    public $accounts;
    public $account_id;
    public $company;
    public $filters;
    public $accrual_balance;
    public $date;
    public $inputs = [];
    public $i = 1;
    public $n = 1;


    public $exchange_amount;
    public $exchange_rate;

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
   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'account_id' => 'required',
    ];

    private function resetInputFields(){
        $this->payment_type = '';
         
    }

    private function recalculateBills(){
        $bills = Bill::where('trip_id',"!=", Null)
                        ->where('transporter_id','!=', Null)
                        ->where('status','!=','Paid')->get();

        if ((isset($bills) && $bills->count()>0)) {
                foreach ($bills as $bill) {
                    if (Auth::user()->employee->company->offloading_details == TRUE) {
                        $delivery_note = $bill->trip->delivery_note;
                        $bill->total = $delivery_note->transporter_offloaded_freight; 
                    }
                }
        }
    }


    public function exportBillsCSV(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->filters, $this->tax_status,  $this->search), 'bills_' .time().'.csv', Excel::CSV);
    }
    public function exportBillsPDF(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->filters, $this->search), 'bills_' .time().'.pdf', Excel::DOMPDF);
    }
    public function exportBillsExcel(Excel $excel){
        return $excel->download(new BillsExport($this->from, $this->to, $this->filters, $this->search), 'bills_' .time().'.xlsx');
    }

   // In your Livewire component

  

    public function importBills()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new BillsImport($this->company);
            ExcelFacade::import($import, $this->importFile);

            $this->importFile = null;
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Bills imported successfully!',
            ]);

        } catch (ExcelValidationException $e) {
            foreach ($e->failures() as $failure) {
                $this->addError('importFile', "Row {$failure->row()}: " . implode(', ', $failure->errors()));
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Import failed: ' . $e->getMessage());
        }

        return redirect(request()->header('Referer'));
    }

    public function mount(){
        $this->company = Auth::user()->employee->company;
        $this->resetPage();
        $this->recalculateBills();
        $this->bill_filter = "created_at";
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->drivers = Driver::query()
        ->join('employees', 'employees.id', '=', 'drivers.employee_id')
        ->select('drivers.*')          // important so you still get Driver models
        ->with('employee')             // eager load relationship
        ->orderBy('employees.name', 'asc')
        ->orderBy('employees.surname', 'asc')
        ->get();
        $this->assets = Asset::query()
        ->join('products', 'products.id', '=', 'assets.product_id')
        ->select('assets.*')
        ->with(['product.brand'])
        ->orderBy('products.name', 'asc')
        ->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->trips = Trip::with('customer','horse','trailers','vehicle','loading_point','offloading_point')->whereYear('start_date',date('Y'))->orderBy('start_date','desc')->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        $this->transaction_type_id = TransactionType::where('name','Withdrawal')->first()->id;
        $this->bank_accounts = BankAccount::orderBy('name','asc')->get()->sortBy('account_name');
        $this->accounts = Account::where('account_type_id',1)->orderBy('name','asc')->get();
        $this->transaction_category = "Bills";
        $this->tax_status = "all";
        $this->vendors = Vendor::orderBy('name','asc')->get();
    
    }
 

      public function updatedSelectedVendor($id){
        if (!is_null($id)) {
            $this->selectedVendor = $id;
            $this->selected_vendor = Vendor::find($id);
        } 
    }
    public function updatedSelectedCurrency($id){
        if (!is_null($id)) {
         
            $this->selected_currency = Currency::find($id);
        } 
    }

    public function updatedSelectedVendorAccount($id){
        if (!is_null($id)) {
            $this->selectedVendorAccount = $id;
            $account = Account::find($id);
            $currency_id = $account->currency_id;
           

            $this->account_payments = Payment::where('vendor_account_id',$id)
            ->where('drawdown_balance','>',0)
            ->orderBy('created_at','desc')->get();
        } 
    }


    public function receiptNumber(){

        $initials = "";
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

     $initials = "";

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

        public function updatedSelectedBill($id){
            if (!is_null($id)) {
                $this->selectedBill = $id;
                $this->bill = Bill::find($id);
                $this->bill_drawdown_balance = $this->bill->balance;
            }
        }

   
    public function drawdownPayments(){
       

         DB::transaction(function () {

        $this->amount_paid = 0;
        $this->payment_drawdown_balance = 0;

        if (isset($this->drawdown_amount) && isset($this->bill_drawdown_balance) && ($this->drawdown_amount >= $this->bill_drawdown_balance)) {
            $this->payment_drawdown_balance = $this->drawdown_amount - $this->bill_drawdown_balance;
            $this->amount_paid = $this->bill_drawdown_balance;
            $this->bill_drawdown_balance = 0;
        }elseif(isset($this->drawdown_amount) && isset($this->bill_drawdown_balance) && ($this->drawdown_amount <= $this->bill_drawdown_balance)){
            $this->bill_drawdown_balance = $this->bill_drawdown_balance - $this->drawdown_amount;
            $this->amount_paid = $this->drawdown_amount;
            $this->payment_drawdown_balance = 0;
        }else{
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Invalid Drawdown Amount!!"
            ]);
            return;
        }

       

        $bill = Bill::find($this->selectedBill); 

        $payments = DB::table('payments')
            ->select([
                'vendor_id',
                'currency_id',
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                'payments.date',
                'payments.created_at',
                DB::raw("'payment' AS source"),
                'id',
            ])
            ->where('vendor_id', $bill->vendor_id)
            ->where('currency_id', $bill->currency_id)
             ->whereNull('deleted_at') // exclude soft-deleted payments
             ->whereNotNull('accrual_balance'); // Ensure accrual balance exists
            // ->whereNotNull('bill_id') // Ensure the payment is linked to an bill
        

        // 2) Build bills subquery
        $bills = DB::table('bills')
            ->select([
                'vendor_id',
                'currency_id',
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                DB::raw('bills.bill_date AS date'), // ✅ alias to "date"
                'bills.created_at',
                DB::raw("'bill' AS source"),
                'id',
            ])
            ->where('authorization','approved')
            ->where('vendor_id', $bill->vendor_id)
            ->where('currency_id', $bill->currency_id)
             ->whereNull('deleted_at') // exclude soft-deleted bills
            ->whereNotNull('accrual_balance'); // Ensure accrual balance exists

        // 3) Union and pick the most recent row
        $latest = DB::query()
            ->fromSub($payments->unionAll($bills), 'u')
            ->orderByDesc('date')        // ✅ business date first
            ->orderByDesc('created_at')  // ✅ system timestamp next
            ->orderByRaw("CASE WHEN source = 'payment' THEN 1 ELSE 0 END DESC") // ✅ prefer payments over bills
            ->first();
        
        $payment = Payment::find($this->last_payment->id);
        $payment->drawdown_balance = $this->payment_drawdown_balance;
        
        if ($latest && is_numeric($latest->accrual_balance) && is_numeric($this->amount_paid)) {
            // Use bc math if you care about money precision
            $payment->accrual_balance = (float) bcsub(
                (string) $latest->accrual_balance,
                (string) $this->amount_paid,
                2
            );
        }else{

        }

        $payment->update();

    
        
        $bill->balance = $this->bill_drawdown_balance;
        if ($this->bill_drawdown_balance <= 0) {
            $bill->status = "Paid";
        }else {
            $bill->status = "Partial";
        }
        $bill->update();

        $bill_payment = new BillPayment;
        $bill_payment->vendor_id = $bill->vendor_id;
        $bill_payment->bill_id = $bill->id;
        $bill_payment->payment_id = $payment->id;
        $bill_payment->currency_id = $bill->currency_id;
        $bill_payment->amount = $this->amount_paid;
        $bill_payment->save();  
 
        $this->dispatchBrowserEvent('hide-paymentDrawdownModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Drawdown Effected Successfully!!"
        ]);

        });
       
    }

    public function showBillUpdate(){
        $this->dispatchBrowserEvent('show-updateBillsModal');
    }

    public function updateAllBills(){

         DB::transaction(function () {

        $bills = Bill::whereNotNull('accrual_balance')->orderBy('id','asc')->orderBy($this->bill_filter,'asc')->get();
        if($bills){
            foreach($bills as $bill){
                if((isset($bill->vendor_id) && isset($bill->currency_id))){
                    if (!is_null($bill->accrual_balance)) {
                     $last_payment = Payment::where('created_at', '<', $bill->created_at)
                                            ->where('vendor_id', $bill->vendor_id)
                                            ->where('currency_id', $bill->currency_id)
                                            ->whereNotNull('bill_id') // Ensure payment is linked to an bill
                                            ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                                            ->orderByDesc('date') // Prioritize latest transaction date
                                            ->orderByDesc('created_at') // If same date, get most recently recorded
                                            ->orderByDesc('id') // If same creation time, get latest ID
                                            ->first();
    
                                        // If no valid payment exists, retrieve the last bill with the highest accrual balance
                    $last_bill = null;
                    if (!$last_payment) {
                        $last_bill = Bill::where('authorization', 'approved')
                            ->where('vendor_id', $bill->vendor_id)
                            ->where('currency_id', $bill->currency_id)
                            ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                            ->orderByDesc('accrual_balance') // Prioritize highest balance
                            ->orderByDesc('bill_date') // If tie, use latest bill date
                            ->orderByDesc('id') // If tie, use latest ID
                            ->first();
                    }
    
                  
                    // Determine the last accrual balance, prioritizing payments over bills
                    $previous_balance = $last_payment && is_numeric($last_payment->accrual_balance) 
                        ? $last_payment->accrual_balance 
                        : ($last_bill && is_numeric($last_bill->accrual_balance) ? $last_bill->accrual_balance : 0);
    
                    // Compute and set the new accrual balance
                    $bill->accrual_balance = $previous_balance + $bill->total;
                    $bill->update(); // Save the updated bill
                       
                    }
                }
            }
        }

        $this->dispatchBrowserEvent('hide-updateBillsModal');

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"All Bills Updated Successfully!!"
        ]);

    });
    }
    
    public function showPayment($id){
        $this->bill_id = $id ;
        $this->bill = Bill::find($id);
        $this->name = Auth::user()->name;
        $this->surname = Auth::user()->surname;
        $this->selectedCurrency = $this->bill->currency_id;
        $this->bill_currency = $this->bill->currency;
        $this->bill_balance = $this->bill->balance;
        $this->current_balance = $this->bill_balance - $this->amount;
        $this->dispatchBrowserEvent('show-paymentModal');
    }

    public function recordPayment(){

        DB::transaction(function () {

            $account = Account::find($this->account_id);
            $current_balance = $account->balance;

            if (isset($current_balance)) {
           
                if ($current_balance >= $this->amount ) {
                    $payment = new Payment;
                    $payment->company_id = Auth::user()->employee->company ? Auth::user()->employee->company->id : "";
                    $payment->vendor_id = $this->bill->vendor_id;
                    $payment->transporter_id = $this->bill->transporter_id;
                    $payment->container_id = $this->bill->container_id;
                    $payment->top_up_id = $this->bill->top_up_id;
                    $payment->trip_id = $this->bill->trip_id;
                    $payment->bill_id = $this->bill->id;
                    $payment->transaction_type_id = $this->transaction_type_id;
                    $payment->movement = "Dbt";
                    $payment->description =  $this->bill->notes;
                    $payment->user_id = Auth::user()->id;
                    $payment->currency_id = $this->bill->currency_id;
                    $payment->payment_number = $this->paymentNumber();   
                    $payment->name = $this->name;
                    $payment->surname = $this->surname;
                    $payment->notes = $this->notes;
                    $payment->category = "Bill";
                    $payment->mode_of_payment = $this->mode_of_payment;
                    $payment->reference_code = $this->reference_code;
                    $payment->bank_account_id = $this->bank_account_id;
                    $payment->account_id = $this->account_id;
                    $payment->amount = $this->amount;
                    $payment->exchange_rate = $this->exchange_rate;
                    $payment->exchange_amount = $this->exchange_amount;
                    if (is_numeric($this->bill->balance) && is_numeric($this->amount)) {
                        $this->current_balance = $this->bill->balance - $this->amount;
                    }
                    $payment->balance = $this->current_balance;

                    // 1) Build payments subquery
                    $payments = DB::table('payments')
                        ->select([
                            'vendor_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            'payments.date',
                            'payments.created_at',
                            DB::raw("'payment' AS source"),
                            'id',
                        ])
                        ->whereNull('deleted_at') // exclude soft-deleted payments
                        ->where('vendor_id', $this->bill->vendor_id)
                        ->where('currency_id', $this->bill->currency_id)
                    ;

        // 2) Build bills subquery
        $bills = DB::table('bills')
            ->select([
                'vendor_id',
                'currency_id',
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                DB::raw('bills.bill_date AS date'), // alias to date
                'bills.created_at',
                DB::raw("'bill' AS source"),
                'id',
            ])
            ->where('authorization','approved')
            ->where('vendor_id', $this->bill->vendor_id)
             ->whereNull('deleted_at') // exclude soft-deleted bill
            ->where('currency_id', $this->bill->currency_id);

        // 3) Union and pick the most recent row
        $latest = DB::query()
            ->fromSub($payments->unionAll($bills), 'u')
            ->orderByDesc('date')        // ✅ business date first
            ->orderByDesc('created_at')  // ✅ system timestamp next
            ->orderByRaw("CASE WHEN source = 'payment' THEN 1 ELSE 0 END DESC") // ✅ prefer payments over bills
            ->first();
          

        if ($latest && is_numeric($latest->accrual_balance) && is_numeric($this->amount)) {
            // Use bc math if you care about money precision
            $payment->accrual_balance = (float) bcsub(
                (string) $latest->accrual_balance,
                (string) $this->amount,
                2
            );
        }
        
        $payment->date = $this->date;
        $payment->save();

        $account->balance = $current_balance - $this->amount;
        $account->update();

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
            $document->expires_at = Carbon::create($this->expires_at)->toDateTimeString();
            $today = now()->toDateTimeString();
            $expire = Carbon::create($this->expires_at)->toDateTimeString();
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

        $bill = Bill::find($this->bill->id);
        $bill->balance = $this->current_balance;
        if ($this->current_balance <= 0) {
            $bill->status = "Paid";
        }else {
            $bill->status = "Partial";
        }
        $bill->update();

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



        $this->dispatchBrowserEvent('hide-paymentModal');
        
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Recorded Successfully!!"
        ]);
        return redirect()->route('payments.index');
              # code...
            } else {

         $this->dispatchBrowserEvent('hide-paymentModal');
         $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Transaction failed, amount to pay exceeds account floating balance!!"
        ]);
                # code...
            }
        }

    });
    }

    public function showAccrual($id){
        $bill = Bill::find($id);
        $this->bill_id = $id ; 
        $this->accrual_balance = $bill->accrual_balance;
        $this->dispatchBrowserEvent('show-accrualModal');
    }

    public function updateAccrualBalance(){
        $bill = Bill::find($this->bill_id);
        $bill->accrual_balance = $this->accrual_balance;
        $bill->update();

        $this->dispatchBrowserEvent('hide-accrualModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Accrual Balance Updated Successfully!!"
        ]);
    }

    
   
    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render()
    {

        $this->filters = [
            'customer_id' => $this->customer_id,
            'transporter_id' => $this->transporter_id,
            'bill_filter' => $this->bill_filter,
            'tax_status' => $this->tax_status,
            'currency_id' => $this->currency_id,
            'horse_id' => $this->horse_id,
            'trailer_id' => $this->trailer_id,
            'asset_id' => $this->asset_id,
            'vehicle_id' => $this->vehicle_id,
            'trip_id' => $this->trip_id,
        ];

        if ((isset($this->exchange_rate) && $this->exchange_rate > 0)  &&  ( isset($this->amount) && $this->amount > 0 )) {

            $this->exchange_amount = $this->exchange_rate * $this->amount;

        }

        if (isset($this->selectedVendor) && isset($this->selectedCurrency)) {
        
            $this->last_payment = Payment::where('vendor_id',$this->selectedVendor)->where('currency_id',$this->selectedCurrency)->where('transaction_category', "Vendor Payments")->orderBy('created_at','desc')->first();
            
            if(isset($this->last_payment)){
                $this->drawdown_amount = $this->last_payment->drawdown_balance;
            }
        

            $this->unpaid_bills = Bill::where('vendor_id',$this->selectedVendor)
                                        ->where('currency_id',$this->selectedCurrency)
                                        ->where('authorization','approved')
                                        ->where('status','!=','Paid')
                                        ->orderBy($this->bill_filter,'desc')->get();
        }

       
          if ((is_numeric($this->bill_balance) && $this->bill_balance > 0) && (is_numeric($this->amount))) {
            $this->current_balance = $this->bill_balance - $this->amount;
        }
        
         

            $base = Bill::query()
                ->with([
                    'invoice','transporter','container','top_up','trip',
                    'horse','driver','purchase','currency','payments',
                    // add these if the relationships exist and you use them in search:
                    'vehicle','trailer','ticket','vendor',
                ])
                ->where('to_be_paid', true);

            // Date filter: use provided range, else current month
            $base->when(
                    filled($this->from) && filled($this->to),
                    fn ($q) => $q->whereBetween($this->bill_filter, [
                        Carbon::parse($this->from)->startOfDay(),
                        Carbon::parse($this->to)->endOfDay(),
                    ]),
                    fn ($q) => $q->whereMonth($this->bill_filter, now()->month)
                                ->whereYear($this->bill_filter, now()->year)
                );

            // Search filter (grouped to keep AND/OR logic correct)
            $base->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';

                $q->where(function ($qq) use ($term) {
                    $qq->where('bill_number', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('bill_date', 'like', $term)
                    ->orWhereHas('horse', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('trailer', function ($sub) use ($term) {
                        $sub->where('registration_number', 'like', $term);
                    })
                    ->orWhereHas('driver', function ($sub) use ($term) {
                        $sub->whereHas('employee', function ($emp) use ($term) {
                            $emp->where(DB::raw("concat(name, ' ', surname)"), 'like', $term);
                        });
                    })
                    ->orWhereHas('ticket', function ($sub) use ($term) {
                        $sub->where('ticket_number', 'like', $term);
                    })
                    ->orWhereHas('trip', function ($sub) use ($term) {
                        $sub->where('trip_number', 'like', $term);
                    })
                    ->orWhereHas('currency', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('invoice', function ($sub) use ($term) {
                        $sub->where('invoice_number', 'like', $term);
                    })
                    ->orWhereHas('transporter', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('container', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    })
                    ->orWhereHas('purchase', function ($sub) use ($term) {
                        $sub->where('purchase_number', 'like', $term);
                    })
                    ->orWhereHas('vendor', function ($sub) use ($term) {
                        $sub->where('name', 'like', $term);
                    });
                });
            });


            if ($this->customer_id != "") {
                $base->where('customer_id', $this->customer_id);
            }
            if ($this->transporter_id != "") {
                $base->where('transporter_id', $this->transporter_id);
            }
            if ($this->trip_id != "") {
                $base->where('trip_id', $this->trip_id);
            }
            if ($this->currency_id != "") {
                $base->where('currency_id', $this->currency_id);
            }
            if ($this->tax_status === 'taxed') {
                $base->whereNotNull('tax_amount')
                    ->where('tax_amount', '!=', 0)
                    ->where('tax_amount', '!=', '');
            }
            if ($this->tax_status === 'non-taxed') {
                $base->where(function ($q) {
                    $q->whereNull('tax_amount')
                    ->orWhere('tax_amount', 0)
                    ->orWhere('tax_amount', '');
                });
            }
            
            if ($this->horse_id != "") {
                $base->where('horse_id', $this->horse_id);
            }
            if ($this->trailer_id != "") {
                $base->where('trailer_id', $this->trailer_id);
            }
            if ($this->vehicle_id != "") {
                $base->where('vehicle_id', $this->vehicle_id);
            }
            if ($this->asset_id != "") {
                $base->where('asset_id', $this->asset_id);
            }
           

            $bills = $base
                ->orderByDesc($this->bill_filter)
                ->paginate(10);

            return view('livewire.bills.index', compact('bills'));
        
    }
}
