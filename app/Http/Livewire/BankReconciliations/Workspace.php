<?php

namespace App\Http\Livewire\BankReconciliations;

use App\Models\Account;
use App\Models\BankReconciliation;
use App\Models\BankStatementLine;
use App\Models\JournalEntryLine;
use App\Services\BankReconciliation\AutoMatchService;
use App\Services\BankReconciliation\ReconciliationService;
use Livewire\Component;

class Workspace extends Component
{
    public BankReconciliation $reconciliation;

    public $selectedStatementLineId;
    public $selectedBookLineId;

    public $adjustmentStatementLineId;
    public $adjustmentContraAccountId;
    public $adjustmentDescription;

    public $contraAccounts = [];

    public function mount(BankReconciliation $reconciliation)
    {
        $this->authorize('view', $reconciliation);
        $this->reconciliation = $reconciliation;
        $this->contraAccounts = Account::orderBy('name')->get(['id', 'name']);
    }

    public function autoMatch()
    {
        $this->authorize('update', $this->reconciliation);

        $result = app(AutoMatchService::class)->match($this->reconciliation->bank_account_id, $this->reconciliation->account_id);

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Auto-matched {$result['matched']} of {$result['statement_lines']} unmatched line(s).",
        ]);
    }

    public function selectStatementLine($id)
    {
        $this->selectedStatementLineId = $this->selectedStatementLineId == $id ? null : $id;
    }

    public function selectBookLine($id)
    {
        $this->selectedBookLineId = $this->selectedBookLineId == $id ? null : $id;
    }

    public function matchSelected()
    {
        $this->authorize('update', $this->reconciliation);

        if (!$this->selectedStatementLineId || !$this->selectedBookLineId) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Select one statement line and one book line to match.']);
            return;
        }

        try {
            app(ReconciliationService::class)->matchLine(
                $this->reconciliation,
                BankStatementLine::findOrFail($this->selectedStatementLineId),
                JournalEntryLine::findOrFail($this->selectedBookLineId)
            );
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Matched.']);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }

        $this->selectedStatementLineId = null;
        $this->selectedBookLineId = null;
    }

    public function unmatch($statementLineId)
    {
        $this->authorize('update', $this->reconciliation);

        try {
            app(ReconciliationService::class)->unmatch(BankStatementLine::findOrFail($statementLineId));
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Unmatched.']);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openAdjustmentModal($statementLineId)
    {
        $this->adjustmentStatementLineId = $statementLineId;
        $this->adjustmentContraAccountId = null;
        $this->adjustmentDescription = null;
        $this->dispatchBrowserEvent('show-adjustmentModal');
    }

    public function createAdjustment()
    {
        $this->authorize('update', $this->reconciliation);

        $this->validate([
            'adjustmentContraAccountId' => 'required|exists:accounts,id',
        ]);

        try {
            app(ReconciliationService::class)->createAdjustmentEntry(
                $this->reconciliation,
                BankStatementLine::findOrFail($this->adjustmentStatementLineId),
                $this->adjustmentContraAccountId,
                $this->adjustmentDescription
            );
            $this->dispatchBrowserEvent('hide-adjustmentModal');
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Adjustment entry recorded and matched.']);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function complete()
    {
        $this->authorize('update', $this->reconciliation);

        try {
            $this->reconciliation = app(ReconciliationService::class)->complete($this->reconciliation);
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Reconciliation completed.']);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function reopen()
    {
        $this->authorize('reopen', $this->reconciliation);

        app(ReconciliationService::class)->reopen($this->reconciliation);
        $this->reconciliation->refresh();
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Reconciliation reopened.']);
    }

    public function render()
    {
        $statementLines = BankStatementLine::where('bank_account_id', $this->reconciliation->bank_account_id)
            ->where('transaction_date', '<=', $this->reconciliation->period_end)
            ->where('status', '!=', 'ignored')
            ->orderBy('transaction_date')
            ->get();

        $bookLines = app(ReconciliationService::class)->outstandingBookItems($this->reconciliation);

        $matchedBookLineIds = $statementLines->pluck('matched_journal_entry_line_id')->filter();
        if ($matchedBookLineIds->isNotEmpty()) {
            $matchedBookLines = JournalEntryLine::whereIn('id', $matchedBookLineIds)->get();
            $bookLines = $bookLines->concat($matchedBookLines)->unique('id')->sortBy('id')->values();
        }

        return view('livewire.bank-reconciliations.workspace', [
            'statementLines' => $statementLines,
            'bookLines' => $bookLines,
        ]);
    }
}
