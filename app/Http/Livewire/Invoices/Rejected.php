<?php

namespace App\Http\Livewire\Invoices;


use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\AuthorizationNotificationMail;

class Rejected extends Component
{

    use WithPagination;
    // public $invoices;

    protected $paginationTheme = 'bootstrap';
    public $selectedRows = [];
    public $selectPageRows = false;


    // public $invoices;
    public $invoice_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $invoice;

    public function mount(){
       

    }

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->invoices->pluck('id')->map(function ($id){
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

           $selected_invoices = Invoice::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_invoices)) {
                foreach($selected_invoices as $invoice){
                    
                    $invoice->authorized_by_id = Auth::user()->id;
                    $invoice->authorization = $this->authorize;
                    $invoice->authorization_date = now();
                    $invoice->comments = $this->comments;
                    $invoice->update();

                    $company =  Auth::user()->employee->company;
                    $user = $invoice->user;
                    $email = $user?->email ?? null;
                    $notification = "Invoice Authorization";
                    if($email){
                        Mail::to($email)->send(new AuthorizationNotificationMail($company, $notification, $user, $invoice));
                    }

                }
                if ($this->authorize == "approved") {

                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Invoice(s) Approved Successfully"
                ]);
                return redirect()->route('invoices.approved');
            }else {
                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Invoice(s) Rejected Successfully"
                ]);
                return redirect()->route('invoices.rejected');
            }

        

            $this->reset(['selectedRows','selectPageRows']);

            
            
           }

        });

      }

      public function getInvoicesProperty(){
        return Invoice::query()->where('authorization', 'rejected')->latest()->paginate(10);
      }

    public function authorize($id){
        $invoice = Invoice::find($id);
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->dispatchBrowserEvent('show-invoiceAuthorizationModal');
      }

    public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

        DB::transaction(function () {
      try{
            $invoice = Invoice::find($this->invoice_id);
            $invoice->authorized_by_id = Auth::user()->id;
            $invoice->authorization = $this->authorize;
            $invoice->comments = $this->comments;
            $invoice->update();

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Approved Successfully"
            ]);
            return redirect()->route('invoices.approved');
        }else {
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Rejected Successfully"
            ]);
            return redirect()->route('invoices.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-invoiceEditModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize an invoice!!"
    ]);
    }

    });

      }
    public function render()
    {
        return view('livewire.invoices.rejected',[
            'invoices' => $this->invoices
        ]);
    }
}
