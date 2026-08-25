<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\TrialBalanceCalculator;
use App\Services\Accounting\LedgerImbalanceDiagnosticService;

class TrialBalance extends Component
{
    public string $date_from = '';
    public string $date_to = '';
    public string $search = '';
    public bool $hide_zero_balances = true;

    public ?array $imbalanceDiagnosis = null;
    public ?array $repairSummary = null;

    public function mount(): void
    {
        $this->date_from = now()->startOfYear()->toDateString();
        $this->date_to   = now()->toDateString();
    }

    /**
     * Bound to the filter form's wire:submit so pressing Enter doesn't
     * error out looking for a missing action; the bound properties
     * already trigger a recalculation on change.
     */
    public function generateStatement()
    {
        //
    }

    private function calculator(): TrialBalanceCalculator
    {
        return new TrialBalanceCalculator($this->companyId(), $this->date_from, $this->date_to, $this->search, $this->hide_zero_balances);
    }

    private function companyId(): int
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;

        return $company->id ?? 0;
    }

    /**
     * Read-only scan: finds journal entries in the current date range whose
     * own lines don't balance (the root cause of an out-of-balance Trial
     * Balance), and whether each is auto-fixable (has a source document to
     * regenerate from) or needs manual review.
     */
    public function diagnoseImbalance(): void
    {
        $this->repairSummary = null;
        $this->refreshDiagnosis();
    }

    private function refreshDiagnosis(): void
    {
        $entries = app(LedgerImbalanceDiagnosticService::class)
            ->findImbalancedEntries($this->companyId(), $this->date_from, $this->date_to);

        $this->imbalanceDiagnosis = $entries->map(fn ($e) => [
            'journal_number' => $e->journal_number,
            'reference' => $e->reference,
            'date' => $e->date,
            'diff' => $e->diff,
            'source_type' => $e->source['type'],
            'fixable' => $e->source['fixable'],
        ])->all();
    }

    /**
     * Reverses+reposts every auto-fixable imbalanced entry in the current
     * date range (same mechanism as the "Resync to Ledger" buttons), then
     * re-scans so the diagnosis list reflects what's left, if anything.
     */
    public function repairImbalance(): void
    {
        $summary = app(LedgerImbalanceDiagnosticService::class)->repair(
            $this->companyId(),
            $this->date_from,
            $this->date_to,
            'Trial Balance correction (' . Auth::user()->name . ' ' . Auth::user()->surname . ', ' . now()->format('Y-m-d H:i') . ')'
        );

        $this->repairSummary = $summary;

        $this->dispatchBrowserEvent('alert', [
            'type' => empty($summary['skipped']) ? 'success' : 'error',
            'message' => count($summary['fixed']) . ' entr' . (count($summary['fixed']) === 1 ? 'y' : 'ies') . ' fixed.'
                . (empty($summary['skipped']) ? '' : ' ' . count($summary['skipped']) . ' need manual review.'),
        ]);

        $this->refreshDiagnosis();
    }

    public function getTrialBalanceProperty(): \Illuminate\Support\Collection
    {
        return $this->calculator()->lines();
    }

    public function getTotalsProperty(): array
    {
        return $this->calculator()->totals($this->trialBalance);
    }

    public function getIsBalancedProperty(): bool
    {
        return $this->calculator()->isBalanced($this->totals);
    }

    public function render()
    {
        return view('livewire.reports.trial-balance', [
            'trialBalance'  => $this->trialBalance,
            'totals'        => $this->totals,
            'isBalanced'    => $this->isBalanced,
            'groupedLines'  => $this->trialBalance->groupBy('account_type_group_name'),
        ]);
    }
}
