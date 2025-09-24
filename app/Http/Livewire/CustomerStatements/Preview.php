<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{


    public $selectedCustomer;
    public $customer;
    public $selectedType;
    public $from;
    public $to;
    public $company;
    protected $invoices;
    public $invoice;
    public $results;
    public $result;

    public function mount($selectedCustomer, $selectedType, $from, $to){
        $this->selectedCustomer = $selectedCustomer;
        $this->customer = Customer::find($this->selectedCustomer);
        $this->selectedType = $selectedType;
        $this->from = $from;
        $this->to = $to;
        $this->company = Auth::user()->employee->company;
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
                $this->invoices = DB::table('invoices')
                ->select(
                    DB::raw("'invoice' as transaction_type"),
                    'invoice_number as number',
                    'currency_id',
                    'created_at',
                    'date as transaction_date',
                    'total as amount',
                    'total as balance',
                    'accrual_balance',
                    'created_at')
                ->where('authorization', 'approved')
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] );
                // ->orderBy('created_at','asc');
                $this->results = DB::table('payments')
                ->select(
                    DB::raw("'payment' as transaction_type"),
                    'payment_number as number',
                    'currency_id',
                    'created_at',
                    'date as transaction_date',
                    'amount',
                    'balance',
                    'accrual_balance',
                    'created_at'
                    )
                ->where('customer_id', $this->selectedCustomer)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
                // ->orderBy('created_at','asc')
                ->union($this->invoices)
                ->get();
                // $this->results = $this->invoices->union($this->payments);
            }
          
            
        }
        return view('livewire.customer-statements.preview',[
            'invoices' => $this->invoices,
            // 'payments' => $this->payments,
            'results' => $this->results,
        ]);
    }
}
