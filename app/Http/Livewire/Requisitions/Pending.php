<?php

namespace App\Http\Livewire\Requisitions;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Account;
use Livewire\Component;
use App\Models\BillExpense;
use App\Models\Requisition;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthorizationNotificationMail;

class Pending extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
    public $from;
    public $to;
    public $requisition_filter;
    
    private $requisitions;
    public $requisition_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $requisition;
    public $company;

    public function mount(){

        $this->notificationsOnly = request()->boolean('notifications', false);
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = 'created_at';
        $this->resetPage();
    }

      public function billNumber(){

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

        $bill = Bill::latest()->orderBy('id','desc')->first();

        if (!$bill) {
            $bill_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $bill->id + 1;
            $bill_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $bill_number;


    }

    public function authorize($id){
        $requisition = Requisition::find($id);
        $this->requisition_id = $requisition->id;
        $this->requisition = $requisition;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){

          DB::transaction(function () {
          
            $requisition = Requisition::find($this->requisition_id);
            $requisition->authorized_by_id = Auth::user()->id;
            $requisition->authorization = $this->authorize;
            $requisition->authorization_date = now();
            $requisition->reason = $this->comments;
            $requisition->update();

            $company =  Auth::user()->employee->company;
            $user = $requisition->user;
            $email = $user?->email ?? null;
            $notification = "Requisition Authorization";
            if($email){
                Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $requisition));
            }

        if ($this->authorize == "approved") {

            if ($requisition->trip_id == Null && $requisition->booking_id == Null && $requisition->purchase_id == Null) {
             
                $bill = new Bill;
                $bill->user_id = Auth::user()->id;
                $bill->bill_number = $this->billNumber();
                $bill->requisition_id = $requisition->id;
                $bill->category = "Requisition";
                $bill->bill_date = $requisition->date;
                $bill->notes = $requisition->description;
                $account_type = Account::find($requisition->account_id) ? Account::find($requisition->account_id)->account_type : Null;
                $bill->account_id = $requisition->account_id ?? null;
                if ($account_type) {
                    $bill->account_type_id = $account_type->id;
                }
                $bill->currency_id = $requisition->currency_id;
                $bill->authorized_by_id = Auth::user()->id;
                $bill->authorization = $this->authorize;
                $bill->comments = $this->comments;
                $bill->total = $requisition->total;
                $bill->exchange_rate = $requisition->exchange_rate;
                $bill->exchange_amount = $requisition->exchange_amount;
                $bill->balance = $requisition->total;
                $bill->to_be_paid = True;
                $bill->save();

                $requisition_items = $requisition->requisition_items;

                if(isset($requisition_items)){
                    foreach($requisition_items as $requisition_item){

                        $bill_expense = new BillExpense;
                        $bill_expense->bill_id = $bill->id;
                        $bill_expense->currency_id = $bill->currency_id;
                        $account_type = Account::find($requisition->account_id) ? Account::find($requisition->account_id)->account_type : Null;
                        $bill_expense->account_id = $requisition->account_id ?? null;
                        if (isset($account_type)) {
                            $bill_expense->account_type_id = $account_type->id;
                        }
                        $bill_expense->product_id = $requisition_item->product_id;
                        $bill_expense->qty = $requisition_item->qty;
                        $bill_expense->amount = $requisition_item->amount;
                        $bill_expense->subtotal = $requisition_item->subtotal;
                        $bill_expense->subtotal_incl = $requisition_item->subtotal;
                        $bill_expense->save();
            
                    }
                }

            }


          

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Requisition Approved Successfully"
            ]);
            return redirect()->route('requisitions.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Requisition Rejected Successfully"
            ]);
            return redirect()->route('requisitions.rejected');
        }

    });

      }


      public function updatingSearch()
      {
          $this->resetPage();
      }

    public function render()
    {
        $query = Requisition::query()
        ->with('employee', 'department', 'trip', 'currency', 'payments')
        ->where('authorization', 'pending');
        
        if ($this->notificationsOnly) {
            $query->whereYear('created_at', now()->year);
        }else{
             // 1. Date range / default period
            if (!empty($this->from) && !empty($this->to)) {
                // Use the selected column in $this->requisition_filter
                $query->whereBetween($this->requisition_filter, [$this->from, $this->to]);
            } else {
                // Default to current month & year on created_at
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }
        }
       

        // 2. Search block (applied on top of whatever date filter is active)
        if (!empty($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('requisition_number', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%")
                ->orWhere('date', 'like', "%{$search}%")
                ->orWhereHas('trip', function ($tripQ) use ($search) {
                    $tripQ->where('trip_number', 'like', "%{$search}%");
                })
                ->orWhereHas('currency', function ($currencyQ) use ($search) {
                    $currencyQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 3. Final query
        $requisitions = $query
            ->orderBy('requisition_number', 'desc')
            ->paginate(10);

        return view('livewire.requisitions.pending', [
            'requisitions'        => $requisitions,
            'requisition_filter'  => $this->requisition_filter,
        ]);
    }
}
