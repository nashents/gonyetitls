<?php

namespace App\Services\GoodsReturneds;

use App\Models\Asset;
use App\Models\Bill;
use App\Models\DebitNote;
use App\Models\GoodsReturned;
use App\Models\Inventory;
use App\Models\Tyre;
use App\Services\Accounting\DebitNoteJournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Undoes the portion of an approved GRV's stock/GL effect that a return
 * covers. Reuses the existing DebitNote / DebitNoteJournalService
 * infrastructure (the vendor-side "credit note" document) instead of
 * touching the original GRV Bill/JournalEntry directly - the original
 * posting stays intact for audit, and DebitNoteJournalService already does
 * exactly the offsetting entry needed: DR Accounts Payable, CR the same
 * account each BillExpense line was originally booked to.
 */
class GoodsReturnedReversalService
{
    public function __construct(private DebitNoteJournalService $debitNoteJournal)
    {
    }

    public function reverse(GoodsReturned $goodsReturned): DebitNote
    {
        return DB::transaction(function () use ($goodsReturned) {
            $goodsReturned->loadMissing(['goods_returned_items', 'vendor', 'goods_received']);

            $bill = Bill::where('goods_received_id', $goodsReturned->goods_received_id)->first();

            if (! $bill) {
                throw ValidationException::withMessages([
                    'goods_returned' => 'The original GRV has no posted bill to reverse against.',
                ]);
            }

            foreach ($goodsReturned->goods_returned_items as $item) {
                $line = $this->lockLine($item);

                if (! $line) {
                    throw ValidationException::withMessages([
                        'goods_returned' => 'A returned line item could not be found - it may have been deleted.',
                    ]);
                }

                // Re-validate under lock: the real concurrency guard, in case
                // another return against the same line was approved between
                // this return's submission and its approval.
                $returnable = ReturnableQuantityResolver::forLine($line, $goodsReturned->id);
                if ((float) $item->qty_returned > $returnable) {
                    throw ValidationException::withMessages([
                        'goods_returned' => "Only {$returnable} unit(s) of {$line->product?->name} remain returnable - this return requests {$item->qty_returned}.",
                    ]);
                }

                $line->balance = max(0, (float) $line->balance - (float) $item->qty_returned);
                $line->save();
            }

            $total = (float) $goodsReturned->goods_returned_items->sum('total_value');

            // authorization is set to 'approved' only after the debit note
            // items below exist - DebitNoteObserver auto-posts as soon as
            // authorization becomes 'approved' (on both created and
            // updated), and its duplicate-posting guard would otherwise
            // treat an incomplete, items-less entry from a premature post
            // as "already posted", permanently missing the expense credit
            // lines (see InventoryJournalService::postReceipt() for the
            // identical hazard with Bills).
            $debitNote = new DebitNote;
            $debitNote->user_id = Auth::id();
            $debitNote->company_id = $bill->company_id ?? (Auth::user()->employee->company_id ?? null);
            $debitNote->vendor_id = $goodsReturned->vendor_id;
            $debitNote->bill_id = $bill->id;
            $debitNote->goods_returned_id = $goodsReturned->id;
            $debitNote->department = $goodsReturned->department;
            $debitNote->currency_id = $bill->currency_id;
            $debitNote->debit_note_number = $this->nextDebitNoteNumber();
            $debitNote->date = now()->toDateString();
            $debitNote->subtotal = $total;
            $debitNote->total = $total;
            $debitNote->bill_amount = $bill->total;
            $debitNote->bill_balance = (float) $bill->total - $total;
            $debitNote->reason = $goodsReturned->reason;
            $debitNote->save();

            foreach ($goodsReturned->goods_returned_items as $item) {
                $debitNote->debit_note_items()->create([
                    'user_id' => Auth::id(),
                    'bill_expense_id' => $item->bill_expense_id,
                    'item' => $item->product?->name,
                    'description' => $item->return_reason,
                    'qty' => $item->qty_returned,
                    'amount' => $item->unit_cost,
                    'subtotal' => $item->total_value,
                ]);
            }

            $debitNote->authorized_by_id = Auth::id();
            $debitNote->authorization = 'approved';
            $debitNote->save();

            // Safety net in case the observer isn't registered for some
            // reason - post() is idempotent (guards on an existing
            // non-reversed entry), so this is a no-op when it already ran.
            $this->debitNoteJournal->post($debitNote->fresh());

            $goodsReturned->debit_note_id = $debitNote->id;
            $goodsReturned->save();

            return $debitNote;
        });
    }

    private function lockLine($item): Inventory|Asset|Tyre|null
    {
        if ($item->inventory_id) {
            return Inventory::lockForUpdate()->find($item->inventory_id);
        }
        if ($item->asset_id) {
            return Asset::lockForUpdate()->find($item->asset_id);
        }
        if ($item->tyre_id) {
            return Tyre::lockForUpdate()->find($item->tyre_id);
        }
        return null;
    }

    private function nextDebitNoteNumber(): string
    {
        $company = Auth::user()->employee->company ?? null;
        $initials = 'DN';
        if ($company) {
            $words = explode(' ', $company->name);
            $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        }

        $last = DebitNote::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'DN' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
