<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use Livewire\Component;
use App\Models\Vendor;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\VendorStatementExport;

class Index extends Component
{
    public $from;
    public $to;
    public $payments;
    public $payment_id;
    public $results;
    protected $bills;
    public $bill_id;
    public $vendors;
    public $vendor;
    public $vendor_id;
    public $selectedVendor;
    public $selectedType;

    public function mount(){
       $this->vendors = Vendor::orderBy('name','asc')->where('status',True)->get(); 
    }

    public function exportVendorStatementExcel(Excel $excel){
        return $excel->download(new VendorStatementExport($this->selectedType,$this->selectedVendor,$this->from,$this->to), 'vendor_statement.xlsx');
    }

    
    public function updatedSelectedVendor($id){
        if (!is_null($id)) {
            $this->selectedVendor = $id;
            $this->vendor = Vendor::find($this->selectedVendor);

            if ( isset($id) && $this->selectedType == "Outstanding Bills") {
                $this->bills = Bill::where('vendor_id', $this->selectedVendor)
                ->where('status', 'Unpaid')
                ->where('authorization', 'approved')
                ->orWhere('vendor_id', $this->selectedVendor)
                ->where('authorization','approved')
                ->where('status', 'Partial')
                ->get();
        
            }elseif ( isset($this->selectedVendor) && $this->selectedType == "Account Activity") {
                if (isset($this->from) && isset($this->to)) {
                    $this->bills = Bill::where('vendor_id', $this->selectedVendor)->where('authorization', 'approved')->orderBy('date','desc')
                    ->whereBetween('date',[$this->from, $this->to] )->get();
                }
              
            }
        }
    }
    public function updatedSelectedType($type){
        if (!is_null($type)) {
            $this->selectedType = $type;
            
            if ( isset($this->selectedVendor) && $this->selectedType == "Outstanding Bills") {
                $this->bills = Bill::where('vendor_id', $this->selectedVendor)
                ->where('status', 'Unpaid')
                ->where('authorization', 'approved')
                ->orWhere('vendor_id', $this->selectedVendor)
                ->where('authorization','approved')
                ->where('status', 'Partial')
                ->get();
        
            }elseif ( isset($this->selectedVendor) && $this->selectedType == "Account Activity") {
                if (isset($this->from) && isset($this->to)) {
                    $this->bills = Bill::where('vendor_id', $this->selectedVendor)->where('authorization', 'approved')->orderBy('date','desc')
                    ->whereBetween('date',[$this->from, $this->to] )->get();
                }
              
            }
        }
      
    }

    public function vendorStatementPreview($selectedType = NULL, $selectedVendor = NULL, $from = NULL, $to = NULL){
        $this->emit('showVendorStatement',['selectedType' => $selectedType]);
    }

    public function generateStatement(){

        if ( isset($this->selectedVendor) && $this->selectedType == "Outstanding Bills") {
            $this->bills = Bill::where('vendor_id', $this->selectedVendor)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')->orWhere('status', 'Partial')->get();
    
        }elseif ( isset($this->selectedVendor) && $this->selectedType == "Account Activity") {
            if (isset($this->from) && isset($this->to)) {
                $this->bills = DB::table('bills')->select('bill_number as number','currency_id','date as transaction_date','total as amount','balance','accrual_balance','created_at')
                ->where('authorization', 'approved')
                ->where('vendor_id', $this->selectedVendor)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] );
              
                $this->results = DB::table('payments')->select('payment_number as number','currency_id','date as transaction_date','amount','balance','accrual_balance','created_at')
                ->where('vendor_id', $this->selectedVendor)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
               
                ->union($this->bills)
                ->get()->
                sortBy([
                    ['transaction_date', 'desc'],
                    ['accrual_balance', 'asc']
                ]);

                // $this->results = $this->bills->union($this->payments);
            }
          
        }
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
                $this->bills = DB::table('bills')->select('bill_number as number','currency_id','date as transaction_date','total as amount','balance','accrual_balance','created_at')
                ->where('authorization', 'approved')
                ->where('vendor_id', $this->selectedVendor)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] );
             
                $this->results = DB::table('payments')->select('payment_number as number','currency_id','date as transaction_date','amount','balance','accrual_balance','created_at')
                ->where('vendor_id', $this->selectedVendor)
                ->where('deleted_at', NULL)
                ->whereBetween('date',[$this->from, $this->to] )
             
                ->union($this->bills)
                ->get()->
                sortBy([
                    ['transaction_date', 'desc'],
                    ['accrual_balance', 'asc']
                ]);

                // $this->results = $this->bills->union($this->payments);
            }
          
        }
        return view('livewire.vendor-statements.index',[
            'bills' => $this->bills
        ]);
    }
}
