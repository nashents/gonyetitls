<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use App\Models\User;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Rejected extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $bill_filter;
    // private $bills;
    public $bill_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $bill;
    public $selectedRows;


    public function mount(){
        $this->resetPage();
        $this->bill_filter = "created_at";
    }

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->bills->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      
      public function authorizeSelectedRows(){

         $this->validate([
            'authorize' => 'required',
        ]);
          DB::transaction(function () {
            

        $selected_bills = Bill::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_bills)) {
             foreach($selected_bills as $bill){
                 
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = $this->authorize;
                $bill->comments = $this->comments;
                $bill->update();
                if ($this->authorize == "approved") {

                         if (isset($bill->vendor_id, $bill->currency_id)) {

                if ($bill->accrual_balance === null) {

                    $vendorId = $bill->vendor_id;
                    $currencyId = $bill->currency_id;

                    // Payments subquery
                    $payments = DB::table('payments')
                        ->select([
                            'vendor_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`date`) AS txn_date'),
                            'created_at',
                            DB::raw("'payment' AS source"),
                            DB::raw('0 AS source_priority'),
                            'id',
                        ])
                         ->whereNull('deleted_at') // exclude soft-deleted payments
                        ->where('vendor_id', $vendorId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance');

                    // bills subquery (exclude the current bill)
                    $bills = DB::table('bills')
                        ->select([
                            'vendor_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`bill_date`) AS txn_date'),
                            'created_at',
                            DB::raw("'bill' AS source"),
                            DB::raw('1 AS source_priority'),
                            'id',
                        ])
                        ->where('authorization', 'approved')
                        ->where('vendor_id', $vendorId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance')
                        ->whereNull('deleted_at') // exclude soft-deleted bill
                        ->when(isset($bill->id), function ($q) use ($bill) {
                            $q->where('id', '<>', $bill->id);
                        });

                    // Union and pick the most recent by our deterministic ordering
                    $last = DB::query()
                        ->fromSub($payments->unionAll($bills), 't')
                        // prefer real transaction date; if it's null, fall back to created_at
                        ->orderByRaw('COALESCE(t.txn_date, DATE(t.created_at)) DESC')
                        ->orderByDesc('t.created_at')
                        ->orderBy('t.source_priority')   // payments (0) before bills (1) on ties
                        ->orderByDesc('t.id')
                        ->first();

                    $previous_balance = ($last && is_numeric($last->accrual_balance))
                        ? (float) $last->accrual_balance
                        : 0.0;

                    $bill->accrual_balance = $previous_balance + (float) $bill->total;
                    $bill->save();
                }
            }
                }
    
             }
             if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Bill(s) Approved Successfully"
                ]);
                return redirect()->route('bills.approved');
         }else {
            $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bulk Bills Rejected Successfully"
            ]);
            return redirect()->route('bills.rejected');
         }

     

         $this->reset(['selectedRows','selectPageRows']);
         
        }
    });

   }

   public function getBillsProperty(){


        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                ->whereDate($this->bill_filter, '>=', $this->from)
                ->whereDate($this->bill_filter, '<=', $this->to)
                ->where('to_be_paid', True)
                ->where('authorization','rejected')
                ->where('bill_number','like', '%'.$this->search.'%')
                ->orWhere('status','like', '%'.$this->search.'%')
                ->orWhere('bill_date','like', '%'.$this->search.'%')
                ->orWhereHas('horse', function ($query) {
                    return $query->where('registration_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('trip', function ($query) {
                    return $query->where('trip_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('ticket', function ($query) {
                    return $query->where('ticket_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('invoice', function ($query) {
                    return $query->where('invoice_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('transporter', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('container', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('purchase', function ($query) {
                    return $query->where('purchase_number', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('bill_number','desc')->paginate(10);
            }else {
                return  Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->where('authorization','rejected')
                ->whereDate($this->bill_filter, '>=', $this->from)
                ->whereDate($this->bill_filter, '<=', $this->to)
                ->where('to_be_paid', True)
                ->orderBy('bill_number','desc')->paginate(10);
            }
           
        }
        elseif (isset($this->search)) {
           
            return Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->whereMonth('created_at', date('m'))
            ->where('authorization','rejected')
            ->whereYear('created_at', date('Y'))
            ->where('bill_number','like', '%'.$this->search.'%')
            ->where('to_be_paid', True)
            ->orWhere('status','like', '%'.$this->search.'%')
            ->orWhere('bill_date','like', '%'.$this->search.'%')
            ->orWhereHas('horse', function ($query) {
                return $query->where('registration_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('ticket', function ($query) {
                return $query->where('ticket_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('trip', function ($query) {
                return $query->where('trip_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('invoice', function ($query) {
                return $query->where('invoice_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('transporter', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('currency', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('container', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('purchase', function ($query) {
                return $query->where('purchase_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('vendor', function ($query) {
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('bill_number','desc')->paginate(10);
        }
        else {
            return Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->whereMonth('created_at', date('m'))
            ->where('authorization','rejected')->where('to_be_paid', True)
            ->whereYear($this->bill_filter, date('Y'))->orderBy('bill_number','desc')->paginate(10); 
        }
    
    

   }
  
    public function authorize($id){
        $bill = Bill::find($id);
        $this->bill_id = $bill->id;
        $this->bill = $bill;
        $this->dispatchBrowserEvent('show-billAuthorizationModal');
    }

    public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

    DB::transaction(function () {

      try{
            $bill = Bill::find($this->bill_id);
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = $this->authorize;
            $bill->comments = $this->comments;
            $bill->update();

        if ($this->authorize == "approved") {

                if (isset($bill->vendor_id, $bill->currency_id)) {

                if ($bill->accrual_balance === null) {

                    $vendorId = $bill->vendor_id;
                    $currencyId = $bill->currency_id;

                    // Payments subquery
                    $payments = DB::table('payments')
                        ->select([
                            'vendor_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`date`) AS txn_date'),
                            'created_at',
                            DB::raw("'payment' AS source"),
                            DB::raw('0 AS source_priority'),
                            'id',
                        ])
                         ->whereNull('deleted_at') // exclude soft-deleted payments
                        ->where('vendor_id', $vendorId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance');

                    // bills subquery (exclude the current bill)
                    $bills = DB::table('bills')
                        ->select([
                            'vendor_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`bill_date`) AS txn_date'),
                            'created_at',
                            DB::raw("'bill' AS source"),
                            DB::raw('1 AS source_priority'),
                            'id',
                        ])
                        ->where('authorization', 'approved')
                        ->where('vendor_id', $vendorId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance')
                        ->whereNull('deleted_at') // exclude soft-deleted bill
                        ->when(isset($bill->id), function ($q) use ($bill) {
                            $q->where('id', '<>', $bill->id);
                        });

                    // Union and pick the most recent by our deterministic ordering
                    $last = DB::query()
                        ->fromSub($payments->unionAll($bills), 't')
                        // prefer real transaction date; if it's null, fall back to created_at
                        ->orderByRaw('COALESCE(t.txn_date, DATE(t.created_at)) DESC')
                        ->orderByDesc('t.created_at')
                        ->orderBy('t.source_priority')   // payments (0) before bills (1) on ties
                        ->orderByDesc('t.id')
                        ->first();

                    $previous_balance = ($last && is_numeric($last->accrual_balance))
                        ? (float) $last->accrual_balance
                        : 0.0;

                    $bill->accrual_balance = $previous_balance + (float) $bill->total;
                    $bill->save();
                }
            }
            
            $this->dispatchBrowserEvent('hide-billAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bill Approved Successfully"
            ]);
            return redirect()->route('bills.approved');
        }else {
            $this->dispatchBrowserEvent('hide-billAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bill Rejected Successfully"
            ]);
            return redirect()->route('bills.rejected');
        }
    }
    catch(\Exception $e){

        $this->dispatchBrowserEvent('hide-billEditModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while trying to authorize an bill!!"
        ]);

    }
    
    });

    }

         
    public function dateRange(){
 
        // $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
       
        return view('livewire.bills.rejected',[
            'bills' => $this->bills
        ]);
    }
}
