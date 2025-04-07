<?php

namespace App\Http\Livewire\TyrePurchases;

use App\Models\Bill;
use Livewire\Component;
use App\Models\Purchase;
use App\Models\BillExpense;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Rejected extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $purchase_filter;
    
    private $purchases;
    public $purchase;
    public $purchase_id;
    public $department;
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

    public function mount($category){
        $this->department = $category;
        $this->purchases = Purchase::where('authorization', 'rejected')
        ->where('department',$this->department)->latest()->get();
    }
    public function authorize($id){
        $purchase = Purchase::find($id);
        $this->purchase_id = $purchase->id;
        $this->purchase = $purchase;
        $this->dispatchBrowserEvent('show-purchaseAuthorizationModal');
      }

      public function update(){
        $purchase = Purchase::find($this->purchase_id);
        if ($purchase->authorization == "approved") {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Purchase Order Approved Already"
            ]);
        }else {

            $purchase->authorized_by_id = Auth::user()->id;
            $purchase->authorization = $this->authorize;
            $purchase->authorization_comments = $this->comments;
            $purchase->update();

            $bill = new Bill;
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->purchase_id = $purchase->id;
            $bill->category = "Asset Expense";
            $bill->bill_date = $purchase->date;
            $bill->currency_id = $purchase->currency_id;
            $bill->total = $purchase->value;
            $bill->balance = $purchase->value;
            $bill->save();

            $bill_expense = new BillExpense;
            $bill_expense->user_id = Auth::user()->id;
            $bill_expense->bill_id = $bill->id;
            $bill_expense->currency_id = $bill->currency_id;
            $bill_expense->expense_id = $purchase->expense_id;
            $bill_expense->qty = 1;
            $bill_expense->amount = $purchase->value;
            $bill_expense->subtotal = $purchase->value;
            $bill_expense->save();
            
        if ($this->authorize == "approved") {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            return redirect()->route('purchases.approved');
        }else {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            return redirect()->route('purchases.rejected');
        }
        }
      }
    public function render()
    {
        return view('livewire.tyre-purchases.rejected',[
            'purchases' => Purchase::where('authorization', 'rejected')
            ->where('department',$this->department)->latest()->paginate(10)
        ]);
    }
}
