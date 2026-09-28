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
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;
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
    public $container_id;

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

        return DB::transaction(function () {

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

                   // Tank balance: added by TopUpObserver on the update() above.

                   if (app(\App\Services\Accounting\CustomerFuelSupplyService::class)->appliesToTopUp($top_up)
                       || (isset($top_up->amount) && $top_up->amount > 0)
                       || (isset($top_up->account_amount) && $top_up->account_amount)) {
                       // Customer-supplied: no supplier Bill, DR Fuel Inventory / CR Accounts
                       // Receivable. Vendor-purchased: a genuine new payable, DR Fuel
                       // Inventory / CR Accounts Payable. FuelJournalService branches on
                       // which this is internally.
                       app(\App\Services\Accounting\FuelJournalService::class)->postTopUp($top_up->fresh());
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
                return $this->redirectOrStay('top_ups.approved');
            }else {

                $this->dispatchBrowserEvent('hide-fuelAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Fuel Top Up(s) Rejected Successfully"
                ]);
                return $this->redirectOrStay('top_ups.rejected');
            }
     

          

        }

        });

    }

    public function authorize($id){
        $top_up = TopUp::find($id);
        $this->top_up_id = $top_up->id;
        $this->top_up = $top_up;
        $this->container_id = $top_up->container_id;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){


      return DB::transaction(function () {
   
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
                // Tank balance: added by TopUpObserver on the update() above.

                if (app(\App\Services\Accounting\CustomerFuelSupplyService::class)->appliesToTopUp($top_up)
                    || (isset($top_up->amount) && $top_up->amount > 0)
                    || (isset($top_up->account_amount) && $top_up->account_amount)) {
                    // Customer-supplied: DR Fuel Inventory / CR Accounts Receivable. Vendor-
                    // purchased: DR Fuel Inventory / CR Accounts Payable - same call as the
                    // bulk approval above. (This used to build its own Bill booked to
                    // Fuel - Ops, expensing the fuel before it was ever used.)
                    app(\App\Services\Accounting\FuelJournalService::class)->postTopUp($top_up->fresh());
                }

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Fuel Top Up Approved Successfully"
                ]);
                return $this->redirectOrStay('top_ups.approved');

            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Fuel Top Rejected Successfully"
                ]);
                return $this->redirectOrStay('top_ups.rejected');
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
