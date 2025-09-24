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

class Pending extends Component
{

    use WithPagination;

    public $selectedRows = [];
    public $selectPageRows = false;

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
    public $accrual_balance;


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

          DB::transaction(function () {

        $selected_bills = Bill::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_bills)) {
             foreach($selected_bills as $bill){
                 
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = $this->authorize;
                $bill->comments = $this->comments;
                $bill->update();

                if ($this->authorize == "approved") {

                    // if((isset($bill->vendor_id) && isset($bill->currency_id))){

                    //     if ($bill->accrual_balance == Null) {

                    //         $accrual_balance = Bill::where('authorization','approved')->where('vendor_id',$bill->vendor_id)->where('id','!=',$bill->id)->where('currency_id', $bill->currency_id)->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('balance');
                    //         if (is_numeric($accrual_balance) && is_numeric($bill->total)) {
                    //             $accrual_balance = $accrual_balance + $bill->total;
                    //             $bill->accrual_balance =   $accrual_balance;
                    //             $bill->update();
                    //         }
                           
                    //     }
                    // }
                    // $accrual_balance = Null;

                if((isset($bill->vendor_id) && isset($bill->currency_id))){

                if ($bill->accrual_balance === Null) {

                 $last_payment = Payment::where('vendor_id', $bill->vendor_id)
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

   public function getBillsProperty(){

        if (isset($this->from) && isset($this->to)) {
            if (isset($this->search)) {
                return Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')
                ->whereDate($this->bill_filter, '>=', $this->from)
                ->whereDate($this->bill_filter, '<=', $this->to)
                ->where('authorization','pending')
                ->where('to_be_paid', True)
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
                return  Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->where('authorization','pending')
                ->whereDate($this->bill_filter, '>=', $this->from)
                ->whereDate($this->bill_filter, '<=', $this->to)
                ->where('to_be_paid', True)
                ->orderBy('bill_number','desc')->paginate(10);
            }
           
        }
        elseif (isset($this->search)) {
           
            return Bill::query()->with('invoice','transporter','container','top_up','trip','horse','driver','purchase','currency','payments')->whereMonth('created_at', date('m'))
            ->where('authorization','pending')
            ->whereYear('created_at', date('Y'))
            ->where('to_be_paid', True)
            ->where('bill_number','like', '%'.$this->search.'%')
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
            ->where('authorization','pending')->where('to_be_paid', True)
            ->whereYear($this->bill_filter, date('Y'))->orderBy('bill_number','desc')->paginate(10);
          
        }
   
   }


    public function authorize($id){
        $bill = Bill::find($id);
        $this->bill_id = $bill->id;
        $this->bill = $bill;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){

          DB::transaction(function () {
    //   try{
            $bill = Bill::find($this->bill_id);
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = $this->authorize;
            $bill->comments = $this->comments;
            $bill->update();

        if ($this->authorize == "approved") {

                if((isset($bill->vendor_id) && isset($bill->currency_id))){

                if ($bill->accrual_balance === Null) {

                 $last_payment = Payment::where('vendor_id', $bill->vendor_id)
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
                        ->orderByDesc('date') // If tie, use latest bill date
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
// }
// catch(\Exception $e){
//     $this->dispatchBrowserEvent('hide-billEditModal');
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something went wrong while trying to authorize an bill!!"
//     ]);
//     }

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
