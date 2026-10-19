<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BillDeletionService
{
    public function __construct(protected JournalReversalService $journalReversal)
    {
    }

    /**
     * Delete a bill, reversing every account/wallet balance and journal entry
     * its payments posted, and recording who deleted it.
     *
     * A bill raised from another document (fuel order, trip expense, GRV,
     * workshop ticket, ...) can't be deleted on its own - deleting just the
     * bill left the source approved with no cost behind it (FB00099 /
     * CEFO00017). The source has to be reversed/deleted instead, and that
     * removes the bill. Those source flows pass $fromSource = true.
     */
    public function delete(Bill $bill, ?int $deletedById = null, ?string $reason = null, bool $fromSource = false): void
    {
        if (! $fromSource && ($blocked = $this->sourceBlockReason($bill))) {
            throw new \RuntimeException($blocked);
        }

        DB::transaction(function () use ($bill, $deletedById, $reason) {
            $bill = Bill::lockForUpdate()->findOrFail($bill->id);
            $reasonText = $reason ?? "Bill {$bill->bill_number} deleted";

            $this->cascade($bill, $reasonText, $deletedById);

            $bill->deleted_by_id = $deletedById;
            $bill->save();
            $bill->delete();
        });
    }

    /**
     * Why this bill can't be deleted on its own, or null if it can: it was
     * raised from a document that still exists. Manual bills (only vendor /
     * transporter / horse / vehicle / trailer / driver / asset set) return null.
     */
    public function sourceBlockReason(Bill $bill): ?string
    {
        $source = $this->sourceDocument($bill);

        return $source
            ? "Bill {$bill->bill_number} was raised from {$source[0]}. Deleting only the bill would leave that document "
                . "in place with no cost behind it - {$source[1]} instead; that removes this bill and reverses everything under it."
            : null;
    }

    /** [what it was raised from, what to do instead] for a live source document, else null. */
    public function sourceDocument(Bill $bill): ?array
    {
        $live = fn ($related) => $related && ! (method_exists($related, 'trashed') && $related->trashed());

        if ($bill->fuel_id && $live($fuel = $bill->fuel)) {
            return ["fuel order {$fuel->order_number}", 'reject or delete the fuel order (Fuel Orders)'];
        }
        if ($bill->top_up_id && $live($topUp = $bill->top_up)) {
            return ["station top-up {$topUp->order_number}", 'reverse or delete the top-up (Fuel Stations)'];
        }
        if ($bill->goods_received_id && $live($grv = $bill->goods_received)) {
            return ["GRV {$grv->goods_received_number}", 'reverse the GRV (Goods Received)'];
        }
        if ($bill->purchase_id && $live($purchase = $bill->purchase)) {
            return ["purchase order {$purchase->purchase_number}", 'reject or delete the purchase order'];
        }
        if ($bill->retread_id && $live($retread = $bill->retread)) {
            return ["retread {$retread->retread_number}", 'reject or delete the retread'];
        }
        if ($bill->workshop_service_id && $live($service = $bill->workshop_service)) {
            return ["workshop service {$service->workshop_service_number}", 'delete the workshop service'];
        }
        if ($bill->dispatch_id && $live($dispatch = $bill->dispatch)) {
            return ["dispatch {$dispatch->dispatch_number}", 'reverse the dispatch'];
        }
        if ($bill->ticket_expense_id && $live($ticketExpense = $bill->ticket_expense)) {
            return ['an expense on workshop ticket ' . (optional($ticketExpense->ticket)->ticket_number ?? "#{$ticketExpense->ticket_id}"), 'remove the expense from the ticket'];
        }
        if ($bill->ticket_inventory_id && $live($ticketInventory = $bill->ticket_inventory)) {
            return ['a part on workshop ticket ' . (optional($ticketInventory->ticket)->ticket_number ?? "#{$ticketInventory->ticket_id}"), 'remove the part from the ticket'];
        }
        if ($bill->requisition_id && $live($requisition = $bill->requisition)) {
            return ["requisition {$requisition->requisition_number}", 'reject or delete the requisition'];
        }
        if ($bill->trip_expense_id && $live($tripExpense = $bill->trip_expense)) {
            $trip = optional($tripExpense->trip)->trip_number ?? "#{$tripExpense->trip_id}";

            return $bill->category === TransporterPaymentBillService::CATEGORY
                ? ["the transporter payment on trip {$trip}", "change or clear the transporter freight on trip {$trip}"]
                : ["a trip expense on trip {$trip}", "remove the expense from trip {$trip}'s Expenses"];
        }
        if ($bill->invoice_id && $live($invoice = $bill->invoice)) {
            return ["invoice {$invoice->invoice_number}", 'delete the invoice'];
        }

        return null;
    }

    /**
     * Everything under a bill: allocations against it (drawdowns go back to
     * their prepayment), payments made against it (cash/bank balance and
     * ledger reversed), its own posting and its line items. Idempotent, and
     * also run by BillObserver::deleted() so a bill removed by any path -
     * including a source document's own deletion - leaves nothing behind.
     */
    public function cascade(Bill $bill, string $reasonText, ?int $deletedById = null): void
    {
        DB::transaction(function () use ($bill, $reasonText, $deletedById) {

            // 1) Allocations recorded against this bill (direct + drawdown)
            foreach ($bill->bill_payments()->get() as $bill_payment) {

                if ($bill_payment->source === 'drawdown' && $bill_payment->payment_id) {
                    // Give the money back to the wallet it was drawn down from
                    $funding_payment = Payment::where('id', $bill_payment->payment_id)->lockForUpdate()->first();
                    if ($funding_payment) {
                        $funding_payment->drawdown_balance = (float) ($funding_payment->drawdown_balance ?? 0) + (float) $bill_payment->amount;
                        $funding_payment->save();
                    }
                }

                // BillPaymentObserver reverses a drawdown's own posting
                $bill_payment->delete();
            }

            // 2) Payments recorded directly against this bill - PaymentDeletionService
            //    puts the cash back on the account and reverses its posting.
            foreach ($bill->payments()->get() as $payment) {
                app(PaymentDeletionService::class)->delete($payment, $deletedById, $reasonText);
            }

            // 3) The bill's own posting and its line items
            $this->journalReversal->reverseLiveEffects(JournalEntry::where('bill_id', $bill->id), $reasonText);

            foreach ($bill->bill_expenses()->get() as $bill_expense) {
                $bill_expense->delete();
            }
        });
    }
}
