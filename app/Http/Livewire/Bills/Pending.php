<?php

namespace App\Http\Livewire\Bills;

use id;
use App\Models\Bill;
use App\Models\User;
use App\Models\Expense;
use App\Models\Payment;
use Livewire\Component;
use App\Models\CashFlow;
use App\Models\Container;
use App\Models\BillExpense;
use Livewire\WithPagination;
use App\Mail\invoiceOrderMail;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\AuthorizationNotificationMail;

class Pending extends Component
{

    use WithPagination;

    public $selectedRows = [];
    public $selectPageRows = false;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
    public $from;
    public $to;
    public $bill_filter;
    // private $bills;
    public $bill_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $bill;
    public $accrual_balance;


    public function mount(){
        $this->notificationsOnly = request()->boolean('notifications', false);
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
                $bill->authorization_date = now();
                $bill->comments = $this->comments;
                $bill->update();

                $company =  Auth::user()->employee->company;
                $user = $bill->user;
                $email = $user?->email ?? null;
                $notification = "Bill Authorization";
                if($email){
                    Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $bill));
                }

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
                'message'=>"Bulk Bill(s) Rejected Successfully"
            ]);
            return redirect()->route('bills.rejected');
         }

     

         $this->reset(['selectedRows','selectPageRows']);
         
        }

    });
   }

    public function getBillsProperty()
    {
        $query = Bill::query()
            ->with([
                'invoice',
                'transporter',
                'container',
                'top_up',
                'trip',
                'horse',
                'driver',
                'purchase',
                'currency',
                'payments',
                'ticket',
                'vendor',
            ])
            ->where('authorization', 'pending')
            ->where('to_be_paid', true);

        if ($this->notificationsOnly) {
            $query->whereYear($this->bill_filter, now()->year);
        }else{
            if (!empty($this->from) && !empty($this->to)) {
                $query->whereDate($this->bill_filter, '>=', $this->from)
                    ->whereDate($this->bill_filter, '<=', $this->to);
            } else {
                $query->whereMonth($this->bill_filter, now()->month)
                    ->whereYear($this->bill_filter, now()->year);
            }
        }
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';

            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhere('bill_date', 'like', $search)
                    ->orWhereHas('horse', function ($query) use ($search) {
                        $query->where('registration_number', 'like', $search);
                    })
                    ->orWhereHas('trip', function ($query) use ($search) {
                        $query->where('trip_number', 'like', $search);
                    })
                    ->orWhereHas('ticket', function ($query) use ($search) {
                        $query->where('ticket_number', 'like', $search);
                    })
                    ->orWhereHas('currency', function ($query) use ($search) {
                        $query->where('name', 'like', $search);
                    })
                    ->orWhereHas('invoice', function ($query) use ($search) {
                        $query->where('invoice_number', 'like', $search);
                    })
                    ->orWhereHas('transporter', function ($query) use ($search) {
                        $query->where('name', 'like', $search);
                    })
                    ->orWhereHas('container', function ($query) use ($search) {
                        $query->where('name', 'like', $search);
                    })
                    ->orWhereHas('purchase', function ($query) use ($search) {
                        $query->where('purchase_number', 'like', $search);
                    })
                    ->orWhereHas('vendor', function ($query) use ($search) {
                        $query->where('name', 'like', $search);
                    });
            });
        }

        return $query
            ->orderBy('bill_number', 'desc')
            ->paginate(10);
    }


    public function authorize($id){
        $bill = Bill::find($id);
        $this->bill_id = $bill->id;
        $this->bill = $bill;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

          DB::transaction(function () {
    
            $bill = Bill::find($this->bill_id);
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = $this->authorize;
            $bill->authorization_date = now();
            $bill->comments = $this->comments;
            $bill->update();

            $company =  Auth::user()->employee->company;
            $user = $bill->user;
            $email = $user?->email ?? null;
            $notification = "Bill Authorization";
            if($email){
                Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $bill));
            }

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
            
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bill Approved Successfully"
            ]);
            return redirect()->route('bills.approved');
            
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Bill Rejected Successfully"
            ]);
            return redirect()->route('bills.rejected');
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
        
        return view('livewire.bills.pending',[
            'bills' => $this->bills,
            'bill_filter' => $this->bill_filter,

           
        ]);
   
    }
}
