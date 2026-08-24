<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\GoodsReceived;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Posts the receipt (GRV) and consumption (Dispatch) legs of the spares/
 * stock lifecycle - the inventory-module sibling of FuelJournalService.
 *
 * Previously a Bill got created (inconsistently) at Purchase Order
 * authorization time, before the goods had even arrived - a PO is a
 * commitment, not a liability, since quantities/pricing can still change
 * and the goods may never arrive. Receipt (GRV) is the actual event that
 * creates a liability, so that's where the AP-crediting entry belongs:
 *
 *  - GRV approved:      debit Spares Inventory, credit Accounts Payable.
 *  - Dispatch approved: debit the destination expense account (unchanged -
 *    still resolved the way Dispatches/Pending.php already did), credit
 *    Spares Inventory - not a new payable, just moving already-recognized
 *    value out of inventory and into the P&L as it's actually consumed.
 */
class InventoryJournalService
{
    public function __construct(private BillJournalService $billJournal)
    {
    }

    /**
     * Called from GoodsReceivedAuthorizationService::authorize() once a GRV
     * is approved. Sums the value of every inventory/asset/tyre row linked
     * to this GRV and posts a single receipt bill. Returns null if nothing
     * valued was actually received (nothing to post).
     */
    public function postReceipt(GoodsReceived $goodsReceived): ?JournalEntry
    {
        return DB::transaction(function () use ($goodsReceived) {
            $goodsReceived->loadMissing(['inventories', 'assets', 'tyres', 'purchase']);

            $lines = collect()
                ->concat($goodsReceived->inventories->map(fn ($i) => ['ref' => $i, 'inventory_id' => $i->id, 'asset_id' => null, 'tyre_id' => null]))
                ->concat($goodsReceived->assets->map(fn ($a) => ['ref' => $a, 'inventory_id' => null, 'asset_id' => $a->id, 'tyre_id' => null]))
                ->concat($goodsReceived->tyres->map(fn ($t) => ['ref' => $t, 'inventory_id' => null, 'asset_id' => null, 'tyre_id' => $t->id]));

            $total = $lines->sum(fn ($l) => is_numeric($l['ref']->subtotal ?? null) ? (float) $l['ref']->subtotal : 0);

            if ($total <= 0) {
                return null;
            }

            $spareInventory = Account::where('name', 'Spares Inventory')->first();

            if (! $spareInventory) {
                throw new \RuntimeException("Required GL account 'Spares Inventory' was not found. Run AccountSeeder or create it manually before authorizing GRVs.");
            }

            $currencyId = $goodsReceived->purchase?->currency_id ?? $lines->first()['ref']->currency_id ?? null;

            $bill = Bill::where('goods_received_id', $goodsReceived->id)->first() ?: new Bill;
            $bill->user_id = $bill->user_id ?? Auth::id();
            $bill->bill_number = $bill->bill_number ?? $this->billNumber();
            $bill->goods_received_id = $goodsReceived->id;
            $bill->purchase_id = $goodsReceived->purchase_id;
            $bill->vendor_id = $goodsReceived->vendor_id;
            $bill->category = 'Goods Received';
            $bill->bill_date = $goodsReceived->date;
            $bill->currency_id = $currencyId;
            $bill->total = $total;
            $bill->subtotal = $total;
            $bill->balance = $total;
            $bill->authorized_by_id = $bill->authorized_by_id ?? Auth::id();
            // authorization/to_be_paid set only after the BillExpenses below
            // exist - see FuelJournalService for why (BillObserver would
            // otherwise auto-post an incomplete, expense-line-less entry).
            $bill->save();

            foreach ($lines as $line) {
                $billExpense = BillExpense::where('bill_id', $bill->id)
                    ->where('inventory_id', $line['inventory_id'])
                    ->where('asset_id', $line['asset_id'])
                    ->where('tyre_id', $line['tyre_id'])
                    ->first() ?? new BillExpense;

                $subtotal = is_numeric($line['ref']->subtotal ?? null) ? (float) $line['ref']->subtotal : 0;

                $billExpense->user_id = $billExpense->user_id ?? Auth::id();
                $billExpense->bill_id = $bill->id;
                $billExpense->currency_id = $bill->currency_id;
                $billExpense->account_id = $spareInventory->id;
                $billExpense->account_type_id = $spareInventory->account_type_id;
                $billExpense->inventory_id = $line['inventory_id'];
                $billExpense->asset_id = $line['asset_id'];
                $billExpense->tyre_id = $line['tyre_id'];
                $billExpense->qty = 1;
                $billExpense->amount = $subtotal;
                $billExpense->subtotal = $subtotal;
                $billExpense->save();
            }

            $bill->to_be_paid = true;
            $bill->authorization = 'approved';
            $bill->save();

            return $this->billJournal->post($bill->fresh());
        });
    }

    /**
     * Called from Dispatches/Pending.php::update() after it builds the
     * dispatch's Bill/BillExpense rows (unchanged - still keyed off
     * "Repairs & Maintenance" as the destination expense account, same as
     * before). Explicitly posts it crediting Spares Inventory instead of
     * leaving it sitting unposted (the bill was previously created with
     * to_be_paid=false specifically to prevent BillObserver from auto-
     * posting it against Accounts Payable, which would have been wrong -
     * but nothing ever posted it correctly either). Returns null if the
     * bill has no expense lines yet (e.g. an asset-only dispatch, which
     * today never gets a BillExpense at all).
     */
    public function postDispatchBill(Bill $bill): ?JournalEntry
    {
        $bill = $bill->fresh();

        if (!$bill || $bill->bill_expenses()->count() === 0) {
            return null;
        }

        $spareInventory = Account::where('name', 'Spares Inventory')->first();

        if (! $spareInventory) {
            throw new \RuntimeException("Required GL account 'Spares Inventory' was not found. Run AccountSeeder or create it manually before authorizing dispatches.");
        }

        return $this->billJournal->postWithCreditAccount($bill, $spareInventory);
    }

    private function billNumber(): string
    {
        $bill = Bill::latest()->orderBy('id', 'desc')->first();
        $number = $bill ? $bill->id + 1 : 1;
        return 'GRB' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
