<?php

namespace App\Http\Livewire\TopUps;

use App\Models\Bill;
use App\Models\Fuel;
use App\Models\TopUp;
use App\Models\Account;
use Livewire\Component;
use App\Models\Container;
use App\Models\BillExpense;
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
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $top_up_id;
    public $authorize;
    public $comments;
    public $top_up;
    public $container;

    public $selectedRows = [];
    public $selectPageRows = false;

   

    
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

      public function getTopUpsProperty(){
 
        return TopUp::query()->with(['container','vendor','currency'
            ])->where('authorization','pending')->orderBy('created_at','desc')->paginate(10);

    }

        public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->top_ups->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

        public function authorizeSelectedRows(){

        DB::transaction(function () {

        $selected_top_ups = TopUP::WhereIn('id',$this->selectedRows)->get();
        
        if (isset($selected_top_ups)) {

            foreach($selected_top_ups as $top_up){

                $top_up->authorized_by_id = Auth::user()->id;
                $top_up->authorization = $this->authorize;
                $top_up->authorization_date = now();
                $top_up->reason = $this->comments;
                $top_up->update();

                $company =  Auth::user()->employee->company;
                $user = $top_up->user;
                $email = $user?->email ?? null;
                $notification = "Fuel Top Up Authorization";
                if($email){
                    Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $top_up));
                }

                if ($this->authorize == "approved") {

                    $container = Container::find($this->container->id);
                    if(($container && is_numeric($container->balance)) && ($this->top_up && is_numeric($this->top_up->quantity))){
                        $container->balance = $container->balance + $this->top_up->quantity;
                    }
                    if(($container && is_numeric($container->account_balance)) && ($this->top_up && is_numeric($this->top_up->amount))){
                        $container->account_balance = $container->account_balance + $this->top_up->amount;
                    }
                   
                    $container->update();

                    if (isset($top_up->amount) && $top_up->amount > 0) {

                        $account = Account::where('name','Fuel')->get()->first();

                        $bill = new Bill;
                        $bill->user_id = Auth::user()->id;
                        $bill->bill_number = $this->billNumber();
                        $bill->container_id = $container->id;
                        $bill->top_up_id = $top_up->id;
                        $bill->vendor_id = $top_up->vendor_id;
                        if (isset($account)) {
                            $bill->account_id = $account->id;
                            $bill->account_type_id = $account->account_type->id;
                        }
                        $bill->category = "Fuel Station Fuel Topup";
                        $bill->bill_date = date("Y-m-d");
                        $bill->currency_id =  $top_up->currency_id;
                        $bill->subtotal =  $top_up->amount;
                        $bill->total =  $top_up->amount;
                        $bill->balance =  $top_up->amount;
                        $bill->exchange_rate = $top_up->exchange_rate;
                        $bill->exchange_amount = $top_up->exchange_amount;
                        $bill->authorized_by_id = $top_up->authorized_by_id;
                        $bill->authorization = $top_up->authorization;
                        $bill->comments = $top_up->reason;
                        $bill->save();

                        // $expense = Expense::where('name','Fuel Topup')->get()->first();
                
                        $bill_expense = new BillExpense;
                        $bill_expense->user_id = Auth::user()->id;
                        $bill_expense->bill_id = $bill->id;
                        $bill_expense->currency_id = $bill->currency_id;
                        if (isset($expense)) {
                            $bill_expense->expense_id = $expense->id;
                        }
                        if (isset($account)) {
                            $bill_expense->account_id = $account->id;
                            $bill_expense->account_type_id = $account->account_type->id;
                        }
                        $bill_expense->qty = 1;
                        $bill_expense->amount = $top_up->amount;
                        $bill_expense->subtotal = $top_up->amount;
                        $bill_expense->subtotal_incl = $top_up->amount;
                        $bill_expense->save();

                    }

                }
        
             
            }

            $this->reset(['selectedRows','selectPageRows']);

            if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Fuel Top Up(s) Approved Successfully !!"
                ]);
                return redirect()->route('top_ups.approved');
            }else {
    
                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Fuel Top Up(s) Rejected Successfully"
                ]);
                return redirect()->route('top_ups.rejected');
            }
     

          

        }

        });

    }

    public function authorize($id){
        $top_up = TopUp::find($id);
        $this->top_up_id = $top_up->id;
        $this->top_up = $top_up;
        $this->container = $top_up->container;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){

      DB::transaction(function () {
   
            $top_up = TopUp::find($this->top_up_id);
            $top_up->authorized_by_id = Auth::user()->id;
            $top_up->authorization = $this->authorize;
            $top_up->authorization_date = now();
            $top_up->reason = $this->comments;
            $top_up->update();

            $company =  Auth::user()->employee->company;
            $user = $top_up->user;
            $email = $user?->email ?? null;
            $notification = "Fuel Top Up Authorization";
            if($email){
                Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $top_up));
            }

        if ($this->authorize == "approved") {

        $container = Container::find($this->container->id);
        if(($container && is_numeric($container->balance)) && ($this->top_up && is_numeric($this->top_up->quantity))){
            $container->balance = $container->balance + $this->top_up->quantity;
        }
        if(($container && is_numeric($container->account_balance)) && ($this->top_up && is_numeric($this->top_up->amount))){
            $container->account_balance = $container->account_balance + $this->top_up->amount;
        }
        $container->update();

        if (isset($top_up->amount) && $top_up->amount > 0) {

            $account = Account::where('name','Fuel')->get()->first();

            $bill = new Bill;
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->container_id = $container->id;
            $bill->top_up_id = $top_up->id;
            $bill->vendor_id = $top_up->vendor_id;
            if (isset($account)) {
                $bill->account_id = $account->id;
                $bill->account_type_id = $account->account_type->id;
            }
            $bill->category = "Fuel Station Fuel Topup";
            $bill->bill_date = date("Y-m-d");
            $bill->currency_id =  $top_up->currency_id;
            $bill->subtotal =  $top_up->amount;
            $bill->total =  $top_up->amount;
            $bill->balance =  $top_up->amount;
            $bill->exchange_rate = $top_up->exchange_rate;
            $bill->exchange_amount = $top_up->exchange_amount;
            $bill->authorized_by_id = $top_up->authorized_by_id;
            $bill->authorization = $top_up->authorization;
            $bill->comments = $top_up->reason;
            $bill->save();

            // $expense = Expense::where('name','Fuel Topup')->get()->first();
    
            $bill_expense = new BillExpense;
            $bill_expense->user_id = Auth::user()->id;
            $bill_expense->bill_id = $bill->id;
            $bill_expense->currency_id = $bill->currency_id;
            if (isset($expense)) {
                $bill_expense->expense_id = $expense->id;
            }
            if (isset($account)) {
                $bill_expense->account_id = $account->id;
                $bill_expense->account_type_id = $account->account_type->id;
            }
            $bill_expense->qty = 1;
            $bill_expense->amount = $top_up->amount;
            $bill_expense->subtotal = $top_up->amount;
            $bill_expense->subtotal_incl = $top_up->amount;
            $bill_expense->save();

        }

            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Top Up Approved Successfully"
            ]);
            return redirect()->route('top_ups.approved');
            
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Top Rejected Successfully"
            ]);
            return redirect()->route('top_ups.rejected');
        }

      });

      }

    public function render()
    {
        return view('livewire.top-ups.pending',[
            'top_ups' => $this->top_ups
        ]);
    }
}
