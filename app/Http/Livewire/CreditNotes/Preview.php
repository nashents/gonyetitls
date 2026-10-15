<?php

namespace App\Http\Livewire\CreditNotes;

use App\Http\Livewire\Concerns\HasFiscalDocument;
use App\Models\CreditNote;
use App\Models\FiscalDocument;
use App\Services\FiscalisationService;
use Livewire\Component;

class Preview extends Component
{
    use HasFiscalDocument;

    public $invoice;
    public $invoice_items;
    public $credit_note;
    public $credit_note_items;
    public $company;

    public function mount($credit_note, $credit_note_items, $company){
        $this->credit_note = $credit_note;
        $this->invoice = $credit_note->invoice;
        $this->invoice_items = $this->invoice ? $this->invoice->invoice_items : collect();
        $this->credit_note_items = $credit_note_items;
        $this->company = $company;
        $this->loadFiscalState();
    }

    protected function fiscalDocument(): ?FiscalDocument
    {
        return FiscalDocument::forCreditNote($this->credit_note->id)->latest()->first();
    }

    public function fiscalizeCreditNote()
    {
        $this->fiscalLoading = true;
        $this->fiscalMessage = '';

        try {
            $creditNote = CreditNote::findOrFail($this->credit_note->id);

            if ($creditNote->authorization !== 'approved') {
                $this->setFiscalMessage('Only approved credit notes can be fiscalised.', 'error');
                return;
            }

            $this->applyFiscalResult(app(FiscalisationService::class)->fiscaliseCreditNote($creditNote));
        } catch (\Exception $e) {
            $this->setFiscalMessage('Error: ' . $e->getMessage(), 'error');
        } finally {
            $this->fiscalLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.credit-notes.preview');
    }
}
