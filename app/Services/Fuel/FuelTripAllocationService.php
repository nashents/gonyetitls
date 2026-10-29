<?php

namespace App\Services\Fuel;

use App\Models\Expense;
use App\Models\Fuel;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Services\Accounting\TransporterPaymentBillService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * A fuel order's litres can be split across trips (e.g. 800 L: 350 L to trip
 * A, 450 L to trip B). Each fuel-linked TripExpense is one trip's allocation:
 * trip_expenses.quantity holds its litres and amount/exchange_amount its
 * pro-rata share of the order. Allocations may total less than the order -
 * the balance is unallocated and posts to Fuel - Ops (FuelJournalService) -
 * but never more. fuels.trip_id stays as the first allocated trip, so the
 * single-trip screens and relations keep working.
 */
class FuelTripAllocationService
{
    /**
     * Why $rows can't be saved on an order of $quantity litres, or null.
     * $rows: [['trip_id' => .., 'quantity' => ..], ...].
     */
    public function validationError(array $rows, $quantity, bool $customerSupplied = false, $horseId = null, $vehicleId = null): ?string
    {
        $rows = $this->normalise($rows);

        if (count($rows) !== count(array_unique(array_column($rows, 'trip_id')))) {
            return 'Each trip can only be allocated once.';
        }

        // The litres went into one truck, so every trip must be that truck's
        // (which also keeps them under one transporter).
        if (count($rows) > 1) {
            $trips = Trip::whereIn('id', array_column($rows, 'trip_id'))->get(['id', 'trip_number', 'horse_id', 'vehicle_id']);
            $horseId = $horseId ?: $trips->firstWhere('id', $rows[0]['trip_id'] ?? null)?->horse_id;
            $vehicleId = $vehicleId ?: ($horseId ? null : $trips->firstWhere('id', $rows[0]['trip_id'] ?? null)?->vehicle_id);
            $other = $trips->first(fn ($trip) => $horseId
                ? (int) $trip->horse_id !== (int) $horseId
                : ($vehicleId && (int) $trip->vehicle_id !== (int) $vehicleId));
            if ($other) {
                return "Trip {$other->trip_number} is for a different " . ($horseId ? 'horse' : 'vehicle') . ' - a fuel order can only be split across trips of the same horse/vehicle.';
            }
        }
        foreach ($rows as $row) {
            if ($row['quantity'] <= 0) {
                return 'Enter the litres going to each selected trip.';
            }
        }
        if ($customerSupplied && count($rows) > 1) {
            return 'Customer-supplied fuel can only go to one trip.';
        }

        $allocated = round(array_sum(array_column($rows, 'quantity')), 2);
        if ($allocated - (float) $quantity > 0.005) {
            return "Trip allocations ({$allocated} L) are more than the fuel order quantity (" . (float) $quantity . ' L).';
        }

        return null;
    }

    /**
     * Save $rows as the order's trip allocations: create/update one trip
     * expense per trip, delete those for trips no longer listed, and refresh
     * every affected trip's cost of sales (and, for trips that lost fuel, the
     * transporter payment bill deductions). Returns the affected trip ids.
     */
    public function sync(Fuel $fuel, array $rows, ?string $category): array
    {
        $rows = $this->normalise($rows);
        $category = $category ?: 'Self';

        $existing = TripExpense::where('fuel_id', $fuel->id)->get();
        $keep = array_column($rows, 'trip_id');
        $removedTripIds = $existing->whereNotIn('trip_id', $keep)->pluck('trip_id')->filter()->unique()->values()->all();

        foreach ($existing->whereNotIn('trip_id', $keep) as $stale) {
            $stale->delete();
        }

        $fuelExpenseId = Expense::where('name', Expense::FUEL_TOPUP)->value('id');
        foreach ($rows as $row) {
            $tripExpense = $existing->firstWhere('trip_id', $row['trip_id']) ?? new TripExpense;
            $tripExpense->user_id = $tripExpense->user_id ?? $fuel->user_id ?? Auth::id();
            $tripExpense->trip_id = $row['trip_id'];
            $tripExpense->fuel_id = $fuel->id;
            $tripExpense->quantity = $row['quantity'];
            $tripExpense->expense_id = $fuelExpenseId ?? $tripExpense->expense_id;
            $tripExpense->category = $category;
            $tripExpense->save();
        }

        $fuel->trip_id = $rows[0]['trip_id'] ?? null;
        $fuel->category = $fuel->trip_id ? $category : $fuel->category;
        $fuel->save();

        $this->reprice($fuel);

        $affected = array_values(array_unique(array_merge($keep, $removedTripIds)));
        $this->refreshTrips($affected, $removedTripIds);

        return $affected;
    }

    /**
     * Re-split the order's amount over its allocations by litres - after the
     * order's quantity, price, currency or rate changed. The last allocation
     * takes the rounding remainder when the whole order is allocated.
     */
    public function reprice(Fuel $fuel): void
    {
        $allocations = TripExpense::where('fuel_id', $fuel->id)->whereNotNull('trip_id')->orderBy('id')->get();
        if ($allocations->isEmpty()) {
            return;
        }

        $orderQty = (float) $fuel->quantity;
        $orderAmount = is_numeric($fuel->amount) && (float) $fuel->amount > 0
            ? (float) $fuel->amount
            : round($orderQty * (float) $fuel->unit_price, 2);
        $orderExchange = is_numeric($fuel->exchange_amount) ? (float) $fuel->exchange_amount : null;

        $allocatedQty = 0.0;
        $allocatedAmount = 0.0;
        $allocatedExchange = 0.0;
        $lastIndex = $allocations->count() - 1;

        foreach ($allocations->values() as $index => $tripExpense) {
            $qty = $tripExpense->quantity !== null ? (float) $tripExpense->quantity : $orderQty;
            $allocatedQty += $qty;
            $isWholeRemainder = $index === $lastIndex && abs($allocatedQty - $orderQty) < 0.005;
            $share = $orderQty > 0 ? $qty / $orderQty : 1;

            $amount = $isWholeRemainder ? round($orderAmount - $allocatedAmount, 2) : round($orderAmount * $share, 2);
            $exchange = $orderExchange === null ? null
                : ($isWholeRemainder ? round($orderExchange - $allocatedExchange, 2) : round($orderExchange * $share, 2));
            $allocatedAmount += $amount;
            $allocatedExchange += (float) $exchange;

            $tripExpense->quantity = $qty;
            $tripExpense->amount = $amount;
            $tripExpense->exchange_amount = $exchange;
            $tripExpense->exchange_rate = $fuel->exchange_rate;
            $tripExpense->currency_id = $fuel->currency_id;
            $tripExpense->save();
        }
    }

    /** The order's current allocations as form rows. */
    public function rowsFor(Fuel $fuel): array
    {
        return TripExpense::where('fuel_id', $fuel->id)->whereNotNull('trip_id')->orderBy('id')->get()
            ->map(fn (TripExpense $te) => [
                'trip_id' => (string) $te->trip_id,
                'quantity' => $te->quantity !== null ? (float) $te->quantity : (float) $fuel->quantity,
            ])->values()->all();
    }

    /** Is this order split across trips, or only partly allocated? */
    public function isSplit(Fuel $fuel): bool
    {
        $allocations = TripExpense::where('fuel_id', $fuel->id)->whereNotNull('trip_id')->get();

        return $allocations->count() > 1
            || ($allocations->count() === 1 && $allocations->first()->quantity !== null
                && abs((float) $allocations->first()->quantity - (float) $fuel->quantity) > 0.005);
    }

    private function refreshTrips(array $tripIds, array $removedTripIds): void
    {
        $trips = app(BulkFuelOrderService::class);
        foreach (Trip::whereIn('id', $tripIds)->get() as $trip) {
            $trips->recalculateTrip($trip);
        }

        // Trips that lost this fuel stop deducting it from the transporter.
        foreach (Trip::whereIn('id', $removedTripIds)->get() as $trip) {
            try {
                app(TransporterPaymentBillService::class)->syncTrip($trip);
            } catch (\Throwable $e) {
                Log::warning("Transporter bill deductions for trip {$trip->trip_number} not refreshed: " . $e->getMessage());
            }
        }
    }

    private function normalise(array $rows): array
    {
        return collect($rows)
            ->filter(fn ($row) => filled($row['trip_id'] ?? null))
            ->map(fn ($row) => [
                'trip_id' => (int) $row['trip_id'],
                'quantity' => round((float) ($row['quantity'] ?? 0), 2),
            ])
            ->values()
            ->all();
    }
}
