<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Services\BankReconciliation\ReconciliationService;
use App\Services\BankStatement\StatementImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BankReconciliationController extends Controller
{
    public function index()
    {
        return view('bank-reconciliations.index');
    }

    public function importStatement(Request $request, StatementImportService $importer)
    {
        $this->authorize('create', BankReconciliation::class);

        $this->validate($request, [
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'file'            => 'required|file|mimes:csv,txt,ofx,sta',
        ]);

        $bankAccount = BankAccount::findOrFail($request->bank_account_id);
        $file = $request->file('file');

        try {
            $statement = $importer->import($bankAccount, $file->getRealPath(), $file->getClientOriginalName());
            Session::flash('success', "Imported {$statement->imported_count} line(s), skipped {$statement->duplicate_count} duplicate(s).");
        } catch (\Throwable $e) {
            Session::flash('error', 'Statement import failed: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function start(Request $request, ReconciliationService $service)
    {
        $this->authorize('create', BankReconciliation::class);

        $this->validate($request, [
            'bank_account_id'           => 'required|exists:bank_accounts,id',
            'period_start'              => 'required|date',
            'period_end'                => 'required|date|after_or_equal:period_start',
            'statement_closing_balance' => 'required|numeric',
        ]);

        $bankAccount = BankAccount::findOrFail($request->bank_account_id);

        try {
            $reconciliation = $service->start(
                $bankAccount,
                $request->period_start,
                $request->period_end,
                (float) $request->statement_closing_balance
            );
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            return redirect()->back();
        }

        return redirect()->route('bank-reconciliations.workspace', $reconciliation->id);
    }

    public function workspace(BankReconciliation $bankReconciliation)
    {
        $this->authorize('view', $bankReconciliation);

        return view('bank-reconciliations.workspace', ['reconciliation' => $bankReconciliation]);
    }

    public function statement(BankReconciliation $bankReconciliation)
    {
        $this->authorize('view', $bankReconciliation);

        return view('reports.bank_reconciliations.print', $this->buildStatementData($bankReconciliation));
    }

    public function statementPdf(BankReconciliation $bankReconciliation)
    {
        $this->authorize('view', $bankReconciliation);

        $pdf = Pdf::loadView('reports.bank_reconciliations.pdf', $this->buildStatementData($bankReconciliation, isPdf: true));

        return $pdf->download("bank-reconciliation-{$bankReconciliation->id}.pdf");
    }

    /**
     * Data shared by the on-screen print view and the PDF export, so the
     * numbers can never drift apart between the two.
     */
    private function buildStatementData(BankReconciliation $reconciliation, bool $isPdf = false): array
    {
        $reconciliation->loadMissing(['company', 'bank_account.currency', 'account', 'items.journal_entry_line']);
        $bankAccount = $reconciliation->bank_account;
        $company = $reconciliation->company;

        $outstandingItems = $reconciliation->items
            ->where('item_type', 'outstanding_book_item')
            ->map(fn ($item) => ['label' => $item->description ?: ($item->journal_entry_line?->description ?: 'Outstanding item'), 'amount' => (float) $item->amount])
            ->values()->all();

        $adjustmentItems = $reconciliation->items
            ->where('item_type', 'adjustment')
            ->map(fn ($item) => ['label' => $item->description ?: 'Adjustment', 'amount' => (float) $item->amount])
            ->values()->all();

        $outstandingTotal = array_sum(array_column($outstandingItems, 'amount'));
        $adjustmentTotal = array_sum(array_column($adjustmentItems, 'amount'));
        $bookBalanceBeforeAdjustments = (float) $reconciliation->book_closing_balance - $adjustmentTotal;

        return [
            'company'                        => $company,
            'currencyCode'                   => $bankAccount?->currency?->name ?? '',
            'reconciliation'                 => $reconciliation,
            'bank_account'                   => $bankAccount,
            'from'                           => $reconciliation->period_start,
            'to'                             => $reconciliation->period_end,
            'statement_balance'              => (float) $reconciliation->statement_closing_balance,
            'outstanding_items'              => $outstandingItems,
            'outstanding_total'              => $outstandingTotal,
            'adjusted_bank_balance'          => (float) $reconciliation->adjusted_bank_balance,
            'book_balance_before_adjustments' => $bookBalanceBeforeAdjustments,
            'adjustment_items'               => $adjustmentItems,
            'adjustment_total'               => $adjustmentTotal,
            'adjusted_book_balance'          => (float) $reconciliation->adjusted_book_balance,
            'difference'                     => (float) $reconciliation->difference,
            'is_balanced'                    => abs((float) $reconciliation->difference) < 0.01,
            'isPdf'                          => $isPdf,
        ];
    }
}
