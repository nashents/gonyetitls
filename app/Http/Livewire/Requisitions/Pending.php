<?php

namespace App\Http\Livewire\Requisitions;

use App\Mail\AuthorizationNotificationMail;
use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Requisition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class Pending extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $notificationsOnly = false;
    public bool $paymentNotificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false], 'paymentNotificationsOnly' => ['as' => 'payment_notifications', 'except' => false]];
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
        $this->paymentNotificationsOnly = request()->boolean('payment_notifications', false);
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



    public function update()

    {
        DB::transaction(function () {

            $requisition = Requisition::with(['user', 'requisition_items'])
                ->findOrFail($this->requisition_id);

            $company = Auth::user()->employee->company;

            $twoStepEnabled = ($company->enable_requisition_two_step_authorization ?? false)
                && $requisition->type == "payment_requisition";

            /*
            |--------------------------------------------------------------------------
            | REJECTION
            |--------------------------------------------------------------------------
            */
            if ($this->authorize == "rejected") {

                if ($twoStepEnabled && $requisition->authorization_stage == 1) {
                    $requisition->second_authorized_by_id = Auth::id();
                    $requisition->second_authorization_date = now();
                    $requisition->second_authorization_comments = $this->comments;
                } else {
                    $requisition->authorized_by_id = Auth::id();
                    $requisition->authorization_date = now();
                    $requisition->reason = $this->comments;
                }

                $requisition->authorization = "rejected";
                $requisition->save();

                $this->sendRequisitionAuthorizationEmail($company, $requisition);

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => "Requisition Rejected Successfully"
                ]);

                return redirect()->route('requisitions.rejected');
            }

            /*
            |--------------------------------------------------------------------------
            | ONE STEP AUTHORIZATION
            |--------------------------------------------------------------------------
            */
            if (!$twoStepEnabled) {

                $requisition->authorized_by_id = Auth::id();
                $requisition->authorization = "approved";
                $requisition->authorization_date = now();
                $requisition->reason = $this->comments;
                $requisition->authorization_stage = 2;
                $requisition->save();

                $this->createBillFromRequisition($requisition);
                $this->sendRequisitionAuthorizationEmail($company, $requisition);

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => "Requisition Approved Successfully"
                ]);

                return redirect()->route('requisitions.approved');
            }

            /*
            |--------------------------------------------------------------------------
            | TWO STEP AUTHORIZATION - FIRST APPROVAL
            |--------------------------------------------------------------------------
            */
            if ($twoStepEnabled && ($requisition->authorization_stage == 0 || $requisition->authorization_stage == null)) {

                $requisition->authorized_by_id = Auth::id();
                $requisition->authorization = "pending";
                $requisition->authorization_date = now();
                $requisition->reason = $this->comments;
                $requisition->authorization_stage = 1;
                $requisition->save();

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => "First Authorization Captured. Awaiting Second Authorization."
                ]);

                return redirect()->route('requisitions.pending');
            }

            /*
            |--------------------------------------------------------------------------
            | TWO STEP AUTHORIZATION - SECOND APPROVAL GUARD
            |--------------------------------------------------------------------------
            */
            if (
                $twoStepEnabled &&
                $requisition->authorization_stage == 1 &&
                $requisition->authorized_by_id == Auth::id()
            ) {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => "Dual authorization requires two different users."
                ]);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | TWO STEP AUTHORIZATION - SECOND APPROVAL
            |--------------------------------------------------------------------------
            */
            if ($twoStepEnabled && $requisition->authorization_stage == 1) {

                $requisition->second_authorized_by_id = Auth::id();
                $requisition->second_authorization_date = now();
                $requisition->second_authorization_comments = $this->comments;
                $requisition->authorization = "approved";
                $requisition->authorization_stage = 2;
                $requisition->save();

                $this->createBillFromRequisition($requisition);
                $this->sendRequisitionAuthorizationEmail($company, $requisition);

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => "Requisition Fully Approved Successfully"
                ]);

                return redirect()->route('requisitions.approved');
            }

        });
    }

    private function sendRequisitionAuthorizationEmail($company, $requisition)
    {
        $user = $requisition->user;
        $email = $user?->email ?? null;

        if ($email) {
            Mail::to($email)->send(
                new AuthorizationNotificationMail(
                    $company,
                    "Requisition Authorization",
                    $user,
                    $requisition
                )
            );
        }
    }

    private function createBillFromRequisition($requisition)
    {
        if (
            $requisition->type != "payment_requisition" ||
            $requisition->trip_id != null ||
            $requisition->booking_id != null ||
            $requisition->purchase_id != null
        ) {
            return;
        }

        if (!$requisition->requisition_items || $requisition->requisition_items->count() == 0) {
            return;
        }

        foreach ($requisition->requisition_items as $requisition_item) {

            $account = Account::find($requisition->account_id);
            $account_type = $account ? $account->account_type : null;

            $bill = new Bill;
            $bill->user_id = Auth::id();
            $bill->bill_number = $this->billNumber();
            $bill->requisition_id = $requisition->id;
            $bill->category = "Requisition";
            $bill->bill_date = $requisition->date;
            $bill->notes = $requisition->description;
            $bill->account_id = $requisition->account_id ?? null;

            if ($account_type) {
                $bill->account_type_id = $account_type->id;
            }

            $bill->currency_id = $requisition_item->currency_id;
            $bill->authorized_by_id = Auth::id();
            $bill->authorization = "approved";
            $bill->comments = $this->comments;
            $bill->total = $requisition_item->subtotal;
            $bill->exchange_rate = $requisition_item->exchange_rate;
            $bill->exchange_amount = $requisition_item->exchange_amount;
            $bill->balance = $requisition_item->subtotal;
            $bill->to_be_paid = true;
            $bill->save();

            $bill_expense = new BillExpense;
            $bill_expense->bill_id = $bill->id;
            $bill_expense->currency_id = $bill->currency_id;
            $bill_expense->account_id = $requisition->account_id ?? null;

            if ($account_type) {
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


      public function updatingSearch()
      {
          $this->resetPage();
      }

       public function findUser($id){
        $user = User::find($id);
        $name = $user?->name;
        $surname = $user?->surname;
        return $name ." ". $surname;
    }
    
    public function render()
    {
        $query = Requisition::query()
        ->with('employee', 'department', 'trip', 'currency', 'payments')
        ->where('authorization', 'pending');
        
        if ($this->notificationsOnly) {
             $query->where('type','po_requisition')->whereYear('created_at', now()->year); 
        }elseif($this->paymentNotificationsOnly){
            $query->where('type','payment_requisition')->whereYear('created_at', now()->year);
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
