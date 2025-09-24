<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use Livewire\Component;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{


    public $selectedVendor;
    public $vendor;
    public $selectedType;
    public $from;
    public $to;
    public $company;
    protected $bills;
    public $bill;
    public $results;
    public $result;

    public function mount($selectedVendor, $selectedType, $from, $to){
        $this->selectedVendor = $selectedVendor;
        $this->vendor = Vendor::find($this->selectedVendor);
        $this->selectedType = $selectedType;
        $this->from = $from;
        $this->to = $to;
        $this->company = Auth::user()->employee->company;
    }


    public function render()
    {
        if ( isset($this->selectedVendor) && $this->selectedType == "Outstanding Bills") {
            $this->bills = Bill::where('vendor_id', $this->selectedVendor)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('vendor_id', $this->selectedVendor)
            ->where('authorization','approved')
            ->where('status', 'Partial')
            ->get();
    
        }elseif ( isset($this->selectedVendor) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                $this->bills = DB::table('bills')
                ->select(
                    DB::raw("'bill' as transaction_type"),
                    'bill_number as number',
                    'currency_id',
                    'created_at',
                    'date as transaction_date',
                    'total as amount',
                    'total as balance',
                    'accrual_balance',
                    'created_at')
                ->where('authorization', 'approved')
                ->where('vendor_id', $this->selectedVendor)
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
                ->where('vendor_id', $this->selectedVendor)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
                // ->orderBy('created_at','asc')
                ->union($this->bills)
                ->get();
                // $this->results = $this->bills->union($this->payments);
            }
          
            
        }
        return view('livewire.vendor-statements.preview',[
            'bills' => $this->bills,
            // 'payments' => $this->payments,
            'results' => $this->results,
        ]);
    }
}
