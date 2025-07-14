<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerStatementExport;

class Index extends Component
{
    public $from;
    public $to;
    public $payments;
    public $payment_id;
    public $results;
    protected $invoices;
    public $invoice_id;
    public $customers;
    public $customer;
    public $customer_id;
    public $selectedCustomer;
    public $selectedType;

    public function mount(){
       $this->customers = Customer::orderBy('name','asc')->where('status',True)->get(); 
    }

    public function exportCustomerStatementExcel(Excel $excel){
        return $excel->download(new CustomerStatementExport($this->selectedType,$this->selectedCustomer,$this->from,$this->to), 'customer_statement.xlsx');
    }

    
    public function updatedSelectedCustomer($id){
        if (!is_null($id)) {
            $this->selectedCustomer = $id;
            $this->customer = Customer::find($this->selectedCustomer);

            if ( isset($id) && $this->selectedType == "Outstanding Invoices") {
                $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
                ->where('status', 'Unpaid')
                ->where('authorization', 'approved')
                ->orWhere('customer_id', $this->selectedCustomer)
                ->where('authorization','approved')
                ->where('status', 'Partial')
                ->get();
        
            }elseif ( isset($this->selectedCustomer) && $this->selectedType == "Account Activity") {
                if (isset($this->from) && isset($this->to)) {
                    $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)->where('authorization', 'approved')->orderBy('date','desc')
                    ->whereBetween('date',[$this->from, $this->to] )->get();
                }
              
            }
        }
    }
    public function updatedSelectedType($type){
        if (!is_null($type)) {
            $this->selectedType = $type;
            
            if ( isset($this->selectedCustomer) && $this->selectedType == "Outstanding Invoices") {
                $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
                ->where('status', 'Unpaid')
                ->where('authorization', 'approved')
                ->orWhere('customer_id', $this->selectedCustomer)
                ->where('authorization','approved')
                ->where('status', 'Partial')
                ->get();
        
            }elseif ( isset($this->selectedCustomer) && $this->selectedType == "Account Activity") {
                if (isset($this->from) && isset($this->to)) {
                    $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)->where('authorization', 'approved')->orderBy('date','desc')
                    ->whereBetween('date',[$this->from, $this->to] )->get();
                }
              
            }
        }
      
    }

    public function customerStatementPreview($selectedType = NULL, $selectedCustomer = NULL, $from = NULL, $to = NULL){
        $this->emit('showCustomerStatement',['selectedType' => $selectedType]);
    }

    public function generateStatement(){

        if ( isset($this->selectedCustomer) && $this->selectedType == "Outstanding Invoices") {
            $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')->orWhere('status', 'Partial')->get();
    
        }elseif ( isset($this->selectedCustomer) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                $this->invoices = DB::table('invoices')->select('invoice_number as number','currency_id','date as transaction_date','total as amount','balance','accrual_balance','created_at')
                ->where('authorization', 'approved')
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] );
              
                $this->results = DB::table('payments')->select('payment_number as number','currency_id','date as transaction_date','amount','balance','accrual_balance','created_at')
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
               
                ->union($this->invoices)
                ->get()->
                sortBy([
                    ['transaction_date', 'desc'],
                    ['accrual_balance', 'asc']
                ]);

                // $this->results = $this->invoices->union($this->payments);
            }
          
        }
    }

    public function render()
    {
        
        if ( isset($this->selectedCustomer) && $this->selectedType == "Outstanding Invoices") {
            $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('customer_id', $this->selectedCustomer)
            ->where('authorization','approved')
            ->where('status', 'Partial')
            ->get();
    
        }elseif ( isset($this->selectedCustomer) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                $this->invoices = DB::table('invoices')->select('invoice_number as number','currency_id','date as transaction_date','total as amount','balance','accrual_balance','created_at')
                ->where('authorization', 'approved')
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] );
             
                $this->results = DB::table('payments')->select('payment_number as number','currency_id','date as transaction_date','amount','balance','accrual_balance','created_at')
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
             
                ->union($this->invoices)
                ->get()->
                sortBy([
                    ['transaction_date', 'desc'],
                    ['accrual_balance', 'asc']
                ]);

                // $this->results = $this->invoices->union($this->payments);
            }
          
        }
        return view('livewire.customer-statements.index',[
            'invoices' => $this->invoices
        ]);
    }
}
