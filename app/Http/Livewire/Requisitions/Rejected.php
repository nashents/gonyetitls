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

class Rejected extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
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
    public $department_ids;

    public function mount(){

        $employee_departments = Auth::user()->employee->departments;
        foreach ($employee_departments as $department) {
            $this->department_ids[] = $department->id;
        }
        $this->company = Auth::user()->employee->company;
        $this->requisition_filter = 'created_at';
        $this->resetPage();
    }
    public function authorize($id){
        $requisition = Requisition::find($id);
        $this->requisition_id = $requisition->id;
        $this->requisition = $requisition;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

       public function findUser($id){
        $user = User::find($id);
        $name = $user?->name;
        $surname = $user?->surname;
        return $name ." ". $surname;
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
                $bill->bill_for = $requisition->attach_to;
                $bill->asset_id = $requisition->asset_id;
                $bill->driver_id = $requisition->driver_id;
                $bill->horse_id = $requisition->horse_id;
                $bill->trailer_id = $requisition->trailer_id;
                $bill->transporter_id = $requisition->transporter_id;
                $bill->vehicle_id = $requisition->vehicle_id;
                $bill->employee_id = $requisition->attached_employee_id;
                $bill->bill_date = $requisition->date;
                $bill->notes = $requisition->description;
                $account_type = Account::find($requisition->account_id)->account_type;
                $bill->account_id = $requisition->account_id;
                if (isset($account_type)) {
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
                        $account_type = Account::find($requisition->account_id)->account_type;
                        $bill_expense->account_id = $requisition->account_id;
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
                'message'=>"Requisition Rejected Already"
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
            // ✅ Force an Eloquent user model instance
        $user = User::query()
            ->with(['employee.departments', 'roles', 'employee.ranks'])
            ->findOrFail(Auth::id());

        $employee        = $user->employee;

        $departmentNames = $employee?->departments?->pluck('name')->all() ?? [];
        $roleNames       = $user->roles->pluck('name')->all();

        $isFinanceOrSuper = in_array('Finance', $departmentNames, true)
            || in_array('Super Admin', $roleNames, true);

        $base = Requisition::query()
            ->with(['employee', 'department', 'trip', 'currency', 'payments'])
            ->where('authorization','approved')
            ->orderBy($this->requisition_filter, 'desc');

        // Non-finance/non-super: restrict to their departments
        if (! $isFinanceOrSuper) {
            $base->whereIn('department_id', (array) $this->department_ids);
        }

        $canViewPaymentRequisitions =
        (
            in_array('Finance', $departmentNames)
            && in_array('Admin', $roleNames)
        )
        || in_array('Super Admin', $roleNames);

        if (!$canViewPaymentRequisitions) {
            $base->where('type', '!=', 'payment_requisition');
        }


        // 1. Date range / default period
        if (!empty($this->from) && !empty($this->to)) {
            // Use the selected column in $this->requisition_filter
            $base->whereBetween($this->requisition_filter, [$this->from, $this->to]);
        } else {
            // Default to current month & year on created_at
            $base->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }
        // Search (GROUPED OR CONDITIONS) — critical fix
        if (filled($this->search)) {
            $term = trim($this->search);

            $base->where(function ($q) use ($term) {
                $like = "%{$term}%";

                $q->where('requisition_number', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('status', 'like', $like)
                ->orWhere('date', 'like', $like)
                ->orWhere('total', 'like', $like)

                ->orWhereHas('requisition_items.expense', function ($qq) use ($like) {
                    $qq->where('name', 'like', $like);
                })

                ->orWhereHas('trip', function ($qq) use ($like) {
                    $qq->where('trip_number', 'like', $like)
                        ->orWhereHas('horse', function ($hhh) use ($like) {
                            $hhh->where('registration_number', 'like', $like);
                        });
                })

                ->orWhereHas('employee', function ($qq) use ($term) {
                    $qq->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%{$term}%");
                })

                ->orWhereHas('currency', function ($qq) use ($like) {
                    $qq->where('name', 'like', $like);
                });
            });
        }

        return view('livewire.requisitions.approved', [
            'requisitions'        => $base->paginate(10),
            'requisition_filter'  => $this->requisition_filter,
        ]);
    }
}
