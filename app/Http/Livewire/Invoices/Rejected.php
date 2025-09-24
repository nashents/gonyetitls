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
use Illuminate\Support\Facades\Session;

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

          DB::transaction(function () {

           $selected_invoices = Invoice::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_invoices)) {
                foreach($selected_invoices as $invoice){
                    
                    $invoice->authorized_by_id = Auth::user()->id;
                    $invoice->authorization = $this->authorize;
                    $invoice->comments = $this->comments;
                    $invoice->update();

                    if ($this->authorize == "approved") {
                             if((isset($invoice->customer_id) && isset($invoice->currency_id))){

                if ($invoice->accrual_balance === Null) {

                 $last_payment = Payment::where('customer_id', $invoice->customer_id)
                                        ->where('currency_id', $invoice->currency_id)
                                        ->whereNotNull('invoice_id') // Ensure payment is linked to an invoice
                                        ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                                        ->orderByDesc('date') // Prioritize latest transaction date
                                        ->orderByDesc('created_at') // If same date, get most recently recorded
                                        ->orderByDesc('id') // If same creation time, get latest ID
                                        ->first();

                                    // If no valid payment exists, retrieve the last invoice with the highest accrual balance
                $last_invoice = null;

                if (!$last_payment) {
                    
                    $last_invoice = Invoice::where('authorization', 'approved')
                        ->where('customer_id', $invoice->customer_id)
                        ->where('currency_id', $invoice->currency_id)
                        ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                        ->orderByDesc('accrual_balance') // Prioritize highest balance
                        ->orderByDesc('date') // If tie, use latest invoice date
                        ->orderByDesc('id') // If tie, use latest ID
                        ->first();
                }

              
                // Determine the last accrual balance, prioritizing payments over invoices
                $previous_balance = $last_payment && is_numeric($last_payment->accrual_balance) 
                    ? $last_payment->accrual_balance 
                    : ($last_invoice && is_numeric($last_invoice->accrual_balance) ? $last_invoice->accrual_balance : 0);

                // Compute and set the new accrual balance
                $invoice->accrual_balance = $previous_balance + $invoice->total;
                $invoice->update(); // Save the updated invoice
                   
                }
            }
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

        DB::transaction(function () {
      try{
            $invoice = Invoice::find($this->invoice_id);
            $invoice->authorized_by_id = Auth::user()->id;
            $invoice->authorization = $this->authorize;
            $invoice->comments = $this->comments;
            $invoice->update();

        if ($this->authorize == "approved") {

            if((isset($invoice->customer_id) && isset($invoice->currency_id))){
                if ($invoice->accrual_balance === Null) {

                 $last_payment = Payment::where('customer_id', $invoice->customer_id)
                                        ->where('currency_id', $invoice->currency_id)
                                        ->whereNotNull('invoice_id') // Ensure payment is linked to an invoice
                                        ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                                        ->orderByDesc('date') // Prioritize latest transaction date
                                        ->orderByDesc('created_at') // If same date, get most recently recorded
                                        ->orderByDesc('id') // If same creation time, get latest ID
                                        ->first();

                                    // If no valid payment exists, retrieve the last invoice with the highest accrual balance
                $last_invoice = null;
                if (!$last_payment) {
                    $last_invoice = Invoice::where('authorization', 'approved')
                        ->where('customer_id', $invoice->customer_id)
                        ->where('currency_id', $invoice->currency_id)
                        ->whereNotNull('accrual_balance') // Ensure accrual balance exists
                        ->orderByDesc('accrual_balance') // Prioritize highest balance
                        ->orderByDesc('date') // If tie, use latest invoice date
                        ->orderByDesc('id') // If tie, use latest ID
                        ->first();
                }

              
                // Determine the last accrual balance, prioritizing payments over invoices
                $previous_balance = $last_payment && is_numeric($last_payment->accrual_balance) 
                    ? $last_payment->accrual_balance 
                    : ($last_invoice && is_numeric($last_invoice->accrual_balance) ? $last_invoice->accrual_balance : 0);

                // Compute and set the new accrual balance
                $invoice->accrual_balance = $previous_balance + $invoice->total;
                $invoice->update(); // Save the updated invoice
                   
                }
            }

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
