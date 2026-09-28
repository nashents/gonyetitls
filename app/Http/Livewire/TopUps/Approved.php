<?php

namespace App\Http\Livewire\TopUps;

use App\Models\Bill;
use App\Models\TopUp;
use App\Models\Account;
use Livewire\Component;
use App\Models\Container;
use App\Models\BillExpense;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    private $top_ups;
    public $top_up_id;
    public $authorize;
    public $comments;
    public $top_up;
    public $container;

    public function mount(){
        
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
        $top_up = TopUp::find($id);
        $this->top_up_id = $top_up->id;
        $this->top_up = $top_up;
        $this->container = $top_up->container;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
   
        $top_up = TopUp::find($this->top_up_id);
        $top_up->authorized_by_id = Auth::user()->id;
        $top_up->authorization = $this->authorize;
        $top_up->reason = $this->comments;
        $top_up->update();

    if ($this->authorize == "approved") {

    $container = Container::find($this->container->id);
        if($container){
            if(($container && is_numeric($container->balance)) && ($this->top_up && is_numeric($this->top_up->quantity))){
                $container->balance = $container->balance + $this->top_up->quantity;
            }
            if(($container && is_numeric($container->account_balance)) && ($this->top_up && is_numeric($this->top_up->amount))){
                $container->account_balance = $container->account_balance + $this->top_up->amount;
            }
            $container->update();
        }

    if (app(\App\Services\Accounting\CustomerFuelSupplyService::class)->appliesToTopUp($top_up)
        || (isset($top_up->amount) && $top_up->amount > 0)) {
        // Customer-supplied: no supplier Bill, DR Fuel Inventory / CR Accounts
        // Receivable. Vendor-purchased: a genuine new payable, DR Fuel
        // Inventory / CR Accounts Payable. FuelJournalService branches on
        // which this is internally.
        app(\App\Services\Accounting\FuelJournalService::class)->postTopUp($top_up->fresh());
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

  }

    public function render()
    {
        $this->top_ups = TopUp::where('authorization','approved')->orderBy('created_at','desc')->paginate(10);
        return view('livewire.top-ups.approved',[
            'top_ups' => $this->top_ups
        ]);
       
    }
}
