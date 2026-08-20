<?php

namespace App\Http\Livewire\CreditNotes;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\CreditNote;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $invoices;
    public $invoice;
    public $invoice_total;
    public $invoice_balance;
    public $invoice_id;
    public $credit_notes;
    public $credit_note_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $credit_note;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->credit_notes = CreditNote::where('authorization', 'pending')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->credit_notes = CreditNote::where('authorization', 'pending')->latest()->get();
            }
        }

    }
    public function authorize($id){
        $credit_note = CreditNote::find($id);
        $this->credit_note_id = $credit_note->id;
        $this->credit_note = $credit_note;
        $this->invoice = $credit_note->invoice;
        $this->invoice_total = $this->invoice->total;
        $this->invoice_balance = $this->invoice->balance;
        $this->dispatchBrowserEvent('show-credit_noteAuthorizationModal');
      }

      public function update(){
    //   try{
            $credit_note = CreditNote::find($this->credit_note_id);
            $credit_note->authorized_by_id = Auth::user()->id;
            $credit_note->authorization = $this->authorize;
            $credit_note->reason = $this->comments;
            $credit_note->update();
            

        if ($this->authorize == "approved") {
            $invoice = Invoice::find($this->invoice->id);
            $invoice->balance = 0;
            $invoice->paid = $this->credit_note->total;
            $invoice->status = "Paid";
            $invoice->invoice_status = 0;
            $invoice->update();

            $this->dispatchBrowserEvent('hide-credit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credit Note Approved Successfully"
            ]);
            return redirect()->route('credit_notes.approved');
        }else {
            $this->dispatchBrowserEvent('hide-credit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Credit Note Rejected Successfully"
            ]);
            return redirect()->route('credit_notes.rejected');
        }
// }
// catch(\Exception $e){
//     $this->dispatchBrowserEvent('hide-credit_noteAuthorizationModal');
//     $this->dispatchBrowserEvent('alert',[
//         'type'=>'error',
//         'message'=>"Something went wrong while trying to authorize an Credit Note!!"
//     ]);
//     }

      }
    public function render()
    {
        return view('livewire.credit-notes.pending',[
            'credit_notes' => CreditNote::where('authorization', 'pending')->latest()->paginate(10)
        ]);
    }
}
