<?php

namespace App\Http\Livewire\Purchases;

use App\Models\Bill;
use App\Models\Account;
use Livewire\Component;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BillExpense;
use App\Models\Notification;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SendPurchaseOrderUpdateMail;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;
    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
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
        $this->purchase_filter = "created_at";
        $this->notificationsOnly = request()->boolean('notifications', false);
    }
    public function authorize($id){
        $purchase = Purchase::find($id);
        $this->purchase_id = $purchase->id;
        $this->purchase = $purchase;
        $this->dispatchBrowserEvent('show-purchaseAuthorizationModal');
      }

    

      public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

      return DB::transaction(function () {


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
            $purchase->authorization_date = date('Y-m-d');
            $purchase->update();
            
        if ($this->authorize == "approved") {

            // Inventory-department POs no longer post a Bill here - a PO is
            // a commitment, not a liability (quantities/pricing can still
            // change, and the goods may never arrive). The GL posting now
            // happens at GRV approval instead, once the spares are actually
            // received - see InventoryJournalService::postReceipt(), wired
            // through GoodsReceivedAuthorizationService. Posting here too
            // would double-count the same stock once at PO, again at GRV.
            // Asset/tyre POs are unchanged pending a similar GRV-based
            // review for those departments.
            if ($this->department !== 'inventory') {

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
            $bill->subtotal = $purchase->subtotal;
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

            }

            $notifications = Notification::where('when','after')->where('category','Purchase Order Authorization')->where('status',1)->get();

            if ($notifications) {
                $company = Auth::user()->employee->company;
                $purchase_products = $purchase->purchase_products;
                $data = [
                    'purchase' => $purchase,
                    'company' => $company,
                    'purchase_products' => $purchase_products,
                ];

                $pdf = PDF::loadView('purchases.purchase', $data);

                $fileName = 'purchase_order' . time() . '.pdf';
                $filePath = 'myfiles/documents/' . $fileName;
                Storage::disk('local')->put($filePath, $pdf->output());

                $notification =  $notifications->first();
                if ($notification) {
                    $email =  $notification->email ?  $notification->email :  $notification->employee->email;
                    $employee =  $notification->employee;
                    if ($email) {
                        Mail::to($email)->send(new SendPurchaseOrderUpdateMail($data, $filePath, $employee, $notifications));
                    }
                }
               
               
            }
            

           
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return $this->redirectOrStay('tyre_purchases.approved');
            }elseif($this->department == "inventory"){
                return $this->redirectOrStay('inventory_purchases.approved');
            }elseif($this->department == "asset"){
                return $this->redirectOrStay('purchases.approved');
            }
           
        }else {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return $this->redirectOrStay('tyre_purchases.rejected');
            }elseif($this->department == "inventory"){
                return $this->redirectOrStay('inventory_purchases.rejected');
            }elseif($this->department == "asset"){
                return $this->redirectOrStay('purchases.rejected');
            }
          
        }
        }
        });
    }

    public function render()
    {
        $query = Purchase::query()
            ->where('authorization', 'pending')
            ->where('department', $this->department);

            if ($this->notificationsOnly) {
                $query->whereYear($this->purchase_filter, now()->year);
            }else{
                if (!empty($this->from) && !empty($this->to)) {

                    $query->whereBetween($this->purchase_filter, [
                        $this->from,
                        $this->to
                    ]);

                } else {

                    $query->whereMonth($this->purchase_filter, now()->month)
                        ->whereYear($this->purchase_filter, now()->year);
                }
            }
        $purchases = $query->latest()->paginate(10);

        return view('livewire.purchases.pending', [
            'purchases' => $purchases ,
        ]);
    }
}
