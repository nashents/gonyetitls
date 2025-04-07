<?php

namespace App\Http\Livewire\Purchases;

use App\Models\Bill;
use App\Models\Account;
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
    public $authorize;
    public $comments;

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
    }
    public function authorize($id){
        $purchase = Purchase::find($id);
        $this->purchase_id = $purchase->id;
        $this->purchase = $purchase;
        $this->dispatchBrowserEvent('show-purchaseAuthorizationModal');
      }

      public function update(){
        $purchase = Purchase::find($this->purchase_id);
        $purchase->authorized_by_id = Auth::user()->id;
        $purchase->authorization = $this->authorize;
        $purchase->authorization_comments = $this->comments;
        $purchase->update();
            
        if ($this->authorize == "approved") {

            $bill = new Bill;
            $bill->user_id = Auth::user()->id;
            $bill->bill_number = $this->billNumber();
            $bill->purchase_id = $purchase->id;
            $bill->category = "Purchase Order";
            $bill->bill_date = $purchase->date;
            
            $account_type = Account::find($purchase->account_id)->account_type;
            $bill->account_id = $purchase->account_id;
            if (isset($account_type)) {
                $bill->account_type_id = $account_type->id;
            }
            $bill->currency_id = $purchase->currency_id;
            $bill->authorized_by_id = Auth::user()->id;
            $bill->authorization = $this->authorize;
            $bill->comments = $this->comments;
            $bill->tax_amount = $purchase->tax_amount;
            $bill->subtotal = $purchase->subtoal;
            $bill->total = $purchase->total;
            $bill->exchange_rate = $purchase->exchange_rate;
            $bill->exchange_amount = $purchase->exchange_amount;
            $bill->balance = $purchase->total;
            $bill->to_be_paid = True;
            $bill->save();

            $purchase_products = $purchase->purchase_products;

            if(isset($purchase_products)){
                foreach($purchase_products as $purchase_product){

                    $bill_expense = new BillExpense;
                    $bill_expense->bill_id = $bill->id;
                    $bill_expense->currency_id = $bill->currency_id;
                    $account_type = Account::find($purchase->account_id)->account_type;
                    $bill_expense->account_id = $purchase->account_id;
                    if (isset($account_type)) {
                        $bill_expense->account_type_id = $account_type->id;
                    }
                    $bill_expense->product_id = $purchase_product->product_id;
                    $bill_expense->qty = $purchase_product->qty;
                    $bill_expense->amount = $purchase_product->amount;
                    $bill_expense->subtotal = $purchase_product->subtotal;
                    $bill_expense->tax_amount = $purchase_product->tax_amount;
                    $bill_expense->subtotal_incl = $purchase_product->subtotal_incl;
                    $bill_expense->save();
        
                }
            }

           
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_purchases.approved');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_purchases.approved');
            }elseif($this->department == "asset"){
                return redirect()->route('purchases.approved');
            }
           
        }else {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_purchases.rejected');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_purchases.rejected');
            }elseif($this->department == "asset"){
                return redirect()->route('purchases.rejected');
            }
          
        }
        
      }

    public function render()
    {
        return view('livewire.purchases.rejected',[
            'purchases' => Purchase::where('authorization', 'rejected')
            ->where('department',$this->department)->latest()->paginate(10)
        ]);
    }
}
