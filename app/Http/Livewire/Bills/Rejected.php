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
