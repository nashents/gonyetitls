<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use App\Models\User;
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
            

        $selected_bills = Bills::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_bills)) {
             foreach($selected_bills as $bill){
                 
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = $this->authorize;
                $bill->comments = $this->comments;
                $bill->update();
                if ($this->authorize == "approved") {
                    if((isset($bill->vendor_id) && isset($bill->currency_id))){
                        if ($bill->accrual_balance == Null) {
                            $accrual_balance = Bill::where('authorization','approved')->where('vendor_id',$bill->vendor_id)->where('id','!=',$bill->id)->where('currency_id', $bill->currency_id)->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('balance');
                            if (is_numeric($accrual_balance) && is_numeric($bill->total)) {
                                $accrual_balance = $accrual_balance + $bill->total;
                                $bill->accrual_balance =   $accrual_balance;
                                $bill->update();
                            }
                           
                        }
                    }
                    $accrual_balance = Null;
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
        $invoice = Invoice::find($id);
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->dispatchBrowserEvent('show-invoiceAuthorizationModal');
      }

      public function update(){

          DB::transaction(function () {
      try{
            $invoice = Invoice::find($this->invoice_id);
            $invoice->authorized_by_id = Auth::user()->id;
            $invoice->authorization = $this->authorize;
            $invoice->comments = $this->comments;
            $invoice->update();

        if ($this->authorize == "approved") {

            if((isset($bill->vendor_id) && isset($bill->currency_id))){
                if ($bill->accrual_balance == Null) {
                    $accrual_balance = Bill::where('authorization','approved')->where('vendor_id',$bill->vendor_id)->where('id','!=',$bill->id)->where('currency_id', $bill->currency_id)->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('balance');
                    if (is_numeric($accrual_balance) && is_numeric($bill->total)) {
                        $accrual_balance = $accrual_balance + $bill->total;
                        $bill->accrual_balance =   $accrual_balance;
                        $bill->update();
                    }
                   
                }
            }
            $accrual_balance = Null;
            
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Approved Successfully"
            ]);
            return redirect()->route('invoices.approved');
        }else {
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Rejected Successfully"
            ]);
            return redirect()->route('invoices.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-invoiceEditModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize an invoice!!"
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
