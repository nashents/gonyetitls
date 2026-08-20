<?php

namespace App\Http\Livewire\CreditNotes;

use App\Models\Cargo;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Receipt;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\Destination;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $credit_note;
    public $credit_note_id;
    public $invoices;
    public $invoice_products;
    public $invoice;
    public $invoice_id;
    public $customers;
    public $currencies;
    public $currency_id;
    public $receipt_number;
    public $invoice_number;
    public $date;
    public $amount;
    public $receipt;
    public $balance;

    public function mount(){

        $this->customers = Customer::all();
        $this->currencies = Currency::all();
        $this->invoices = Invoice::latest()->get();

    }

    public function getUnpostedCreditNotesCountProperty()
    {
        return app(\App\Services\Accounting\LedgerBackfillService::class)->missingCreditNotesQuery()->count();
    }

    /**
     * Posts every approved credit note with no JournalEntry yet - the same
     * query/logic as `php artisan ledger:backfill`, just triggered from
     * the list page instead of the CLI.
     */
    public function bulkPostToLedger()
    {
        $result = app(\App\Services\Accounting\LedgerBackfillService::class)->runCreditNotes();
        $posted = count($result['posted']);
        $errors = count($result['errors']);

        $this->dispatchBrowserEvent('alert', [
            'type'    => $errors > 0 ? 'warning' : 'success',
            'message' => "Posted {$posted} of {$result['total']} credit note(s) to the ledger."
                . ($errors > 0 ? " {$errors} failed - see logs." : ''),
        ]);
    }

    /**
     * Manually push an approved credit note to the general ledger. Covers
     * credit notes approved without ever triggering CreditNoteObserver
     * (e.g. created already-approved in one save, which never fires an
     * isDirty('authorization') transition) - the same gap fixed for
     * bills/invoices via the ledger backfill.
     */
    public function postToLedger($id)
    {
        $creditNote = CreditNote::find($id);
        if (! $creditNote) {
            return;
        }

        if (strcasecmp((string) $creditNote->authorization, 'approved') !== 0) {
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'warning',
                'message' => 'Only authorized (approved) credit notes can be posted to the ledger.',
            ]);
            return;
        }

        try {
            app(\App\Services\Accounting\CreditNoteJournalService::class)->post($creditNote);
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Credit note posted to the general ledger.',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Manual credit note ledger post failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Could not post this credit note to the ledger: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * For a credit note that was already posted, then edited afterward -
     * CreditNoteJournalService posts once and never again, and editing a
     * credit note never touches the ledger at all, so the original (now
     * wrong) journal entry otherwise sits in the Trial Balance forever.
     * Reverses it and posts a fresh one from the credit note's current
     * figures.
     */
    public function resyncLedger($id)
    {
        $creditNote = CreditNote::find($id);
        if (! $creditNote) {
            return;
        }

        try {
            app(\App\Services\Accounting\LedgerResyncService::class)->resyncCreditNote($creditNote);
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Credit note resynced to the general ledger.',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Credit note ledger resync failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', [
                'type'    => 'error',
                'message' => 'Could not resync this credit note to the ledger: ' . $e->getMessage(),
            ]);
        }
    }

    public function credit_noteNumber(){
       
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

            $credit_note = CreditNote::orderBy('id', 'desc')->first();

        if (!$credit_note) {
            $credit_note_number =  $initials .'FS'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $credit_note->id + 1;
            $credit_note_number =  $initials .'FS'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $credit_note_number;


    }
    private function resetInputFields(){
        $this->invoice_number = '';
        $this->receipt_number = '';
        $this->amount = '';
        $this->currency_id = '';
        $this->receipt = '';
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'invoice_number' => 'required',
        'receipt_number' => 'required',
        'currency_id' => 'required',
        'receipt' => 'nullable|file',
    ];

    public function addReceipt($id){
        $this->invoice_id = $id;
        $this->invoice = Invoice::find($id);
        $this->invoice_number = $this->invoice->invoice_number;
        $this->receipt_number = $this->receiptNumber();
        $this->dispatchBrowserEvent('show-receiptModal');
    }
    public function storeReceipt(){

        if(isset($this->receipt)){
            $file = $this->receipt;
            // get file with ext
            $fileNameWithExt =  $file->getClientOriginalName();
            //get filename
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            //get extention
            $extention =  $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore= $filename.'_'.time().'.'.$extention;
            $file->storeAs('/receipts', $fileNameToStore, 'my_files');

        }
        $invoice = $this->invoice;
        $receipts = Receipt::where('invoice_id',$invoice->id )->get();
        $old_receipt = Receipt::where('invoice_id',$invoice->id )->latest()->first();

        $receipt = new Receipt;
        $receipt->user_id = Auth::user()->id;
        $receipt->invoice_id = $this->invoice_id;
        $receipt->invoice_number = $this->invoice_number;
        $receipt->receipt_number = $this->receiptNumber();
        $receipt->currency_id = $this->currency_id;
        $receipt->amount = $this->amount;
        $receipt->date = $this->date;

        if (isset($old_receipt)) {
        $receipt->balance = $old_receipt->balance - $this->amount ;
        }else{
        $receipt->balance = $invoice->total - $this->amount;
        }

        if (isset($fileNameToStore)) {
        $receipt->filename = $fileNameToStore;
        }
        $receipt->save();

        $this->dispatchBrowserEvent('hide-receiptModal');
        $this->resetInputFields();
        return redirect(route('receipts.index'));
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Receipt Added Successfully!!"
        ]);


    }

    public function render()
    {
        return view('livewire.credit-notes.index', [
            'credit_notes' => CreditNote::with('journal_entry')->latest()->paginate(10),
        ]);
    }
}
