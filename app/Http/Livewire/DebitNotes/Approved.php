<?php

namespace App\Http\Livewire\DebitNotes;

use App\Models\Bill;
use Livewire\Component;
use App\Models\DebitNote;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{
    public $bills;
    public $authorization;
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
                $this->debit_notes = DebitNote::where('authorization', 'approved')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->debit_notes = DebitNote::where('authorization', 'approved')->latest()->get();
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
      try{
            $debit_note = DebitNote::find($this->debit_note_id);
            $this->debit_note = $debit_note;
            $debit_note->authorization = $this->authorize;
            $debit_note->authorized_by_id = Auth::user()->id;
            $this->authorization = $debit_note->authorization;
            $debit_note->reason = $this->comments;
            $debit_note->update();

        if ($this->authorize == "approved") {

            $this->dispatchBrowserEvent('hide-debit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Debit Note Approved Already"
            ]);
            return redirect()->route('debit_notes.approved');
        }else {

            if ($this->bill) {
                $bill = Bill::find($this->bill->id);
                $bill->total = $this->bill_total + $this->debit_note->total;
                $bill->balance = $this->bill_balance + $this->debit_note->total;
                $bill->update();
            }

            $this->dispatchBrowserEvent('hide-debit_noteAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Debit Note Rejected Successfully"
            ]);
            return redirect()->route('debit_notes.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-debit_noteAuthorizationModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize a Debit Note!!"
    ]);
    }

      }
    public function render()
    {
        $this->debit_notes = DebitNote::where('authorization', 'approved')->latest()->get();
        return view('livewire.debit-notes.approved',[
            'debit_notes' => $this->debit_notes
        ]);
    }
}
