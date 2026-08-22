<?php

namespace App\Services\Accounting;

use App\Models\Fuel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Repairs approved fuel orders whose linked Bill (and posted JournalEntry)
 * fell out of sync with the fuel order's own current exchange_rate/amount/
 * currency_id - e.g. a wrong exchange rate was entered, the order was
 * approved and posted to the ledger, and the exchange rate was corrected
 * on the fuel order afterward. Before FuelJournalService::postConsumption()
 * was changed to resync (see Fuels/Index::update()), editing an
 * already-approved fuel order silently never touched the Bill or the GL -
 * this repairs records that drifted while that gap existed.
 *
 * A mismatch is detected by comparing the Bill's stored total/exchange_rate/
 * currency_id against the Fuel's current values. Fixing one means re-running
 * FuelJournalService::postConsumption(), which rebuilds Bill/BillExpense
 * from the fuel order's current fields and resyncs (reverses + reposts) the
 * JournalEntry - identical to what a normal edit now triggers, so a repaired
 * record ends up indistinguishable from one that was always correct.
 */
class FuelLedgerRepairService
{
    public function __construct(private FuelJournalService $fuelJournal)
    {
    }

    /**
     * @param int[]|null $fuelIds limit the scan to these fuel order ids
     */
    public function mismatches(?array $fuelIds = null): Collection
    {
        $results = collect();

        $query = Fuel::where('authorization', 'approved')->whereHas('bill');

        if ($fuelIds) {
            $query->whereIn('id', $fuelIds);
        }

        $query->with('bill')->chunkById(200, function ($fuels) use (&$results) {
            foreach ($fuels as $fuel) {
                $bill = $fuel->bill;

                if (!$bill) {
                    continue;
                }

                $billRate = round((float) ($bill->exchange_rate ?? 1), 6);
                $fuelRate = round((float) ($fuel->exchange_rate ?? 1), 6);
                $billTotal = round((float) $bill->total, 2);
                $fuelAmount = round((float) $fuel->amount, 2);

                $rateDiff = $billRate !== $fuelRate;
                $totalDiff = $billTotal !== $fuelAmount;
                $currencyDiff = (int) $bill->currency_id !== (int) $fuel->currency_id;

                if (!$rateDiff && !$totalDiff && !$currencyDiff) {
                    continue;
                }

                $results->push([
                    'fuel_id'             => $fuel->id,
                    'order_number'        => $fuel->order_number,
                    'bill_id'             => $bill->id,
                    'bill_number'         => $bill->bill_number,
                    'bill_total'          => $billTotal,
                    'fuel_amount'         => $fuelAmount,
                    'bill_exchange_rate'  => $billRate,
                    'fuel_exchange_rate'  => $fuelRate,
                    'bill_currency_id'    => $bill->currency_id,
                    'fuel_currency_id'    => $fuel->currency_id,
                ]);
            }
        });

        return $results;
    }

    /**
     * @param int[]|null $fuelIds limit the run to these fuel order ids
     */
    public function repairAll(?array $fuelIds = null): array
    {
        $mismatches = $this->mismatches($fuelIds);

        $result = ['total' => 0, 'fixed' => [], 'errors' => []];

        foreach ($mismatches as $mismatch) {
            $result['total']++;
            $outcome = $this->repair($mismatch['fuel_id']);

            if ($outcome['status'] === 'fixed') {
                $result['fixed'][] = $outcome;
            } else {
                $result['errors'][] = $outcome;
            }
        }

        return $result;
    }

    public function repair(int $fuelId): array
    {
        $fuel = Fuel::find($fuelId);

        if (!$fuel || !$fuel->bill) {
            return ['fuel_id' => $fuelId, 'status' => 'error', 'message' => 'Fuel order or linked bill not found.'];
        }

        // postConsumption() posts under Auth::user()->employee->company_id -
        // impersonate whoever originally created the bill (or, failing
        // that, the fuel order) rather than requiring an interactive
        // session, the same pattern LedgerBackfillService uses for CLI runs.
        $userId = $fuel->bill->user_id ?: $fuel->user_id;
        $originalUser = Auth::user();

        try {
            if ($userId && ($user = User::find($userId))) {
                Auth::setUser($user);
            }

            $entry = $this->fuelJournal->postConsumption($fuel->fresh());

            return ['fuel_id' => $fuelId, 'status' => 'fixed', 'journal_number' => $entry->journal_number];
        } catch (\Throwable $e) {
            Log::error("FuelLedgerRepairService: failed to repair fuel order #{$fuelId}: " . $e->getMessage());

            return ['fuel_id' => $fuelId, 'status' => 'error', 'message' => $e->getMessage()];
        } finally {
            if ($originalUser) {
                Auth::setUser($originalUser);
            }
        }
    }
}
