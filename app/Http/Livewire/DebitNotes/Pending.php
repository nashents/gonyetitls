<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use Livewire\Component;
use App\Models\DebitNote;
use Illuminate\Support\Facades\Auth;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;

class Pending extends Component
{
    use HasStayOnPageAuthorization;

    public $bills;
    public $bill;
    public $bill_total;
    public $bill_balance;
    public $bill_id;
    public $debit_notes;
    public $debit_note_id;
    public $authorize;
    public $comments;
    public $debit_note;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->debit_notes = DebitNote::where('authorization', 'pending')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->debit_notes = DebitNote::where('authorization', 'pending')->latest()->get();
            }
        }

    }
    public function authorize($id){
        $debit_note = DebitNote::find($id);
        $this->debit_note_id = $debit_note->id;
        $this->debit_note = $debit_note;
        $this->bill = $debit_note->bill;
        if ($this->bill) {
            $this->bill_total = $this->bill->total;
            $this->bill_balance = $this->bill->balance;
        }
        $this->dispatchBrowserEvent('show-debit_noteAuthorizationModal');
      }

      public function update(){
            $debit_note = DebitNote::find($this->debit_note_id);
            $debit_note->authorized_by_id = Auth::user()->id;
            $debit_note->authorization = $this->authorize;
            $debit_note->reason = $this->comments;
            $debit_note->update();

        if ($this->authorize == "approved") {
            if ($this->bill) {
                $bill = Bill::find($this->bill->id);
                $bill->balance = 0;
                $bill->paid = $this->debit_note->total;
                $bill->status = "Paid";
                $bill->update();
            }

            $this->dispatchBrowserEvent('hide-debit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Debit Note Approved Successfully"
            ]);
            return $this->redirectOrStay('debit_notes.approved');
        }else {
            $this->dispatchBrowserEvent('hide-debit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Debit Note Rejected Successfully"
            ]);
            return $this->redirectOrStay('debit_notes.rejected');
        }

      }
    public function render()
    {
        $this->debit_notes = DebitNote::where('authorization', 'pending')->latest()->get();
        return view('livewire.debit-notes.pending',[
            'debit_notes' => $this->debit_notes
        ]);
    }
}
