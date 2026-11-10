<?php

namespace App\Services\Fleet;

use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Payment;
use App\Models\Tyre;
use App\Services\Accounting\BillDeletionService;
use App\Services\Accounting\LedgerResyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Opening tyres: old tyres that were already on the fleet but were captured
 * one by one on Tyres > Create, so they sit "in store" and would need a
 * dispatch before they can be fitted.
 *
 * Fitting one as an opening tyre takes it out of stores with NO dispatch and
 * treats it as having no value: every bill that carried its cost is
 * reversed - its own bill (old "add to bills" tyres) is deleted, and its line
 * on the GRV bill is removed and the GRV bill re-posted for the rest (or
 * deleted when the tyre was its only line). The tyre's value fields are set
 * to 0 (original value kept in opening_original_value) so a later GRV
 * re-post can't bring the cost back.
 *
 * Admins / Super Admins only (User::canOverrideTripLocks()).
 */
class OpeningTyreFitmentService
{
    public function __construct(
        private BillDeletionService $billDeletion,
        private LedgerResyncService $ledgerResync,
    ) {
    }

    /** Why this tyre can't be released as an opening tyre, or null when it can. */
    public function blocker(Tyre $tyre): ?string
    {
        $label = $tyre->serial_number ?: $tyre->tyre_number;

        if ($tyre->disposed) {
            return "Tyre {$label} is disposed.";
        }
        if ((float) $tyre->balance <= 0) {
            return "Tyre {$label} is not in stores.";
        }
        if (Tyre::query()->openingFittable()->whereKey($tyre->id)->doesntExist()) {
            return "Tyre {$label} is on a dispatch or already fitted - use the dispatch instead.";
        }

        foreach ($this->bills($tyre) as $bill) {
            if ($this->hasPayments($bill)) {
                return "Tyre {$label}'s cost is on bill {$bill->bill_number}, which has payments against it - remove those first.";
            }
        }

        return null;
    }

    /**
     * Takes the tyre out of stores at no value and reverses its bills.
     * Fit it afterwards with TyreAssignmentService::assign() (qty 0 makes it assignable).
     */
    public function release(Tyre $tyre, ?string $reason = null): Tyre
    {
        return DB::transaction(function () use ($tyre, $reason) {
            $tyre = Tyre::lockForUpdate()->findOrFail($tyre->id);

            if ($blocker = $this->blocker($tyre)) {
                throw ValidationException::withMessages(['tyre_id' => $blocker]);
            }

            $label = $tyre->serial_number ?: $tyre->tyre_number;
            $reasonText = "Opening tyre {$label} fitted without dispatch - no value" . (filled($reason) ? ": {$reason}" : '');

            foreach ($this->bills($tyre) as $bill) {
                $this->reverseTyreOnBill($bill, $tyre, $reasonText);
            }

            $tyre->opening_original_value = $tyre->opening_original_value ?? $tyre->total;
            $tyre->opening_fitted_at = now();
            $tyre->opening_fitted_by_id = Auth::id();
            foreach (['amount', 'cost', 'subtotal', 'tax_amount', 'subtotal_incl', 'total', 'exchange_amount'] as $field) {
                $tyre->{$field} = 0;
            }
            // qty 0 = "was never stock", the same as an opening tyre imported with quantity 0.
            $tyre->qty = 0;
            $tyre->balance = 0;
            $tyre->status = 0;
            $tyre->save();

            return $tyre;
        });
    }

    /** Every live bill that carries this tyre's cost: its own bill, or a line on a (GRV) bill. Dispatch bills excluded. */
    protected function bills(Tyre $tyre)
    {
        $lineBillIds = BillExpense::where('tyre_id', $tyre->id)->pluck('bill_id');

        return Bill::where(fn ($q) => $q->where('tyre_id', $tyre->id)->orWhereIn('id', $lineBillIds))
            ->whereNull('dispatch_id')
            ->get();
    }

    protected function hasPayments(Bill $bill): bool
    {
        return $bill->bill_payments()->exists()
            || Payment::where('bill_id', $bill->id)->exists()
            || (is_numeric($bill->balance) && is_numeric($bill->total) && (float) $bill->balance < (float) $bill->total - 0.009);
    }

    protected function reverseTyreOnBill(Bill $bill, Tyre $tyre, string $reasonText): void
    {
        $lines = $bill->bill_expenses()->get();
        $tyreLines = $lines->where('tyre_id', $tyre->id);
        $otherLines = $lines->reject(fn ($line) => (int) $line->tyre_id === (int) $tyre->id);

        // The tyre's own bill, or a GRV bill with nothing else on it: delete it (journal reversed).
        if ((int) $bill->tyre_id === (int) $tyre->id || $otherLines->isEmpty()) {
            $this->billDeletion->delete($bill, Auth::id(), $reasonText, true);
            return;
        }

        // Shared GRV bill: drop the tyre's line, shrink the bill, re-post what is left.
        $removed = $tyreLines->sum(fn ($line) => is_numeric($line->subtotal) ? (float) $line->subtotal : 0);
        foreach ($tyreLines as $line) {
            $line->delete();
        }

        $bill->total = round(max(0, (float) $bill->total - $removed), 2);
        if (is_numeric($bill->subtotal)) {
            $bill->subtotal = round(max(0, (float) $bill->subtotal - $removed), 2);
        }
        $bill->balance = round(max(0, (float) $bill->balance - $removed), 2);
        $bill->save();

        if ($bill->authorization === 'approved' && $bill->to_be_paid) {
            $this->ledgerResync->resyncBill($bill->fresh(), $reasonText, true);
        }
    }
}
