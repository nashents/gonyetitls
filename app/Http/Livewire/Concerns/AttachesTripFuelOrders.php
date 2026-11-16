<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Fuel;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Services\Accounting\FuelJournalService;
use App\Services\Fuel\FuelTripAllocationService;
use Illuminate\Validation\ValidationException;

/**
 * Trips > Edit "Attach Existing Fuel Order(s)": the trip's fuel allocations
 * shown as ticked rows (litres editable, untick to take the order off the
 * trip) next to the orders that still have unallocated litres. Mirrors
 * Trips\Create's attach mode, but aware of what is already on the trip.
 *
 * Host component provides: $trip_id, $fuel_order, $fuel_category,
 * $mode_of_transport, $selectedHorse, $selectedVehicle, $user.
 */
trait AttachesTripFuelOrders
{
    public $fuel_order_mode = 'new';
    public $attach_fuel_ids = [];
    // fuel id => litres going to this trip
    public $attach_fuel_quantities = [];

    /**
     * Litres of a fuel order already allocated to trips. A legacy allocation
     * with no quantity carries the whole order, as does an order on a trip
     * with no allocation rows at all (its trip expense was deleted).
     */
    protected function allocatedFuelLitresSql(): string
    {
        return 'COALESCE((SELECT SUM(COALESCE(te.quantity, fuels.quantity)) FROM trip_expenses te'
            . ' WHERE te.fuel_id = fuels.id AND te.trip_id IS NOT NULL AND te.deleted_at IS NULL),'
            . ' CASE WHEN fuels.trip_id IS NULL THEN 0 ELSE fuels.quantity END)';
    }

    /**
     * Orders already allocated to this trip, plus the ones that can still go
     * to it: not rejected, for the selected horse/vehicle, and either on no
     * trip yet or with litres left (customer-supplied fuel goes to one trip
     * only, so it is offered only while unattached).
     */
    protected function attachableFuelsQuery()
    {
        $allocated = $this->allocatedFuelLitresSql();
        $tripId = (int) $this->trip_id;
        $onTrip = "FROM trip_expenses tt WHERE tt.fuel_id = fuels.id AND tt.trip_id = {$tripId} AND tt.deleted_at IS NULL";

        return Fuel::query()
            ->select('fuels.*')
            ->selectRaw("{$allocated} as allocated_litres")
            ->selectRaw("(SELECT COALESCE(tt.quantity, fuels.quantity) {$onTrip} ORDER BY tt.id LIMIT 1) as this_trip_litres")
            ->where(fn ($q) => $q->whereRaw("EXISTS (SELECT 1 {$onTrip})")
                ->orWhere(fn ($q) => $q
                    ->where(fn ($q) => $q->whereNull('authorization')->orWhere('authorization', '!=', 'rejected'))
                    ->where(fn ($q) => $q->whereNull('trip_id')
                        ->orWhere(fn ($q) => $q->where(fn ($q) => $q->whereNull('supplied_by_customer')->orWhere('supplied_by_customer', false))
                            ->whereRaw("fuels.quantity - {$allocated} > 0.005")))
                    ->when($this->mode_of_transport === 'Horse' && $this->selectedHorse, fn ($q) => $q->where('horse_id', $this->selectedHorse))
                    ->when($this->mode_of_transport === 'Vehicle' && $this->selectedVehicle, fn ($q) => $q->where('vehicle_id', $this->selectedVehicle))));
    }

    /** Litres of $fuel (loaded by attachableFuelsQuery) this trip can take: the unallocated balance plus its own share. */
    protected function availableFuelLitres(Fuel $fuel): float
    {
        return max(0, round((float) $fuel->quantity - (float) $fuel->allocated_litres + (float) $fuel->this_trip_litres, 2));
    }

    /**
     * Customer-supplied fuel already on this trip is a part-payment of it -
     * its allocation is only changed from Fuel Orders.
     */
    protected function attachedFuelLocked(Fuel $fuel): bool
    {
        return (bool) $fuel->supplied_by_customer && $fuel->this_trip_litres !== null;
    }

    /** Tick the orders already allocated to this trip, with its litres. */
    protected function loadAttachedFuelOrders(): void
    {
        $this->attach_fuel_ids = [];
        $this->attach_fuel_quantities = [];

        $shares = TripExpense::where('trip_id', $this->trip_id)->whereNotNull('fuel_id')->with('fuel')->orderBy('id')->get();
        foreach ($shares as $share) {
            if (!$share->fuel || isset($this->attach_fuel_quantities[$share->fuel_id])) {
                continue;
            }
            $this->attach_fuel_ids[] = (string) $share->fuel_id;
            $this->attach_fuel_quantities[$share->fuel_id] = $share->quantity !== null ? (float) $share->quantity : (float) $share->fuel->quantity;
        }
    }

    public function updatedFuelOrderMode($value)
    {
        // Back to what is saved on the trip.
        $this->loadAttachedFuelOrders();
        if ($value == 'attach' && !$this->fuel_category) {
            $this->fuel_category = 'Self';
        }
    }

    /** A newly ticked order defaults to all the litres this trip can take. */
    public function updatedAttachFuelIds()
    {
        $ids = array_map('intval', array_filter((array) $this->attach_fuel_ids));
        $quantities = array_intersect_key((array) $this->attach_fuel_quantities, array_flip($ids));

        $missing = array_diff($ids, array_keys(array_filter($quantities, fn ($qty) => is_numeric($qty))));
        if ($missing) {
            foreach ($this->attachableFuelsQuery()->whereIn('fuels.id', $missing)->get() as $fuel) {
                $quantities[$fuel->id] = $this->availableFuelLitres($fuel);
            }
        }
        $this->attach_fuel_quantities = $quantities;
    }

    public function getAttachableFuelsProperty()
    {
        if (!$this->fuel_order || $this->fuel_order_mode != 'attach') {
            return collect();
        }

        return $this->attachableFuelsQuery()
            ->with(['container', 'currency', 'horse', 'vehicle', 'source_horse', 'trip'])
            ->orderByRaw('this_trip_litres IS NULL')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->each(function (Fuel $fuel) {
                $fuel->available_litres = $this->availableFuelLitres($fuel);
                $fuel->attach_locked = $this->attachedFuelLocked($fuel);
            });
    }

    protected function tickedFuelIds(): array
    {
        return array_values(array_unique(array_map('intval', array_filter((array) $this->attach_fuel_ids))));
    }

    /**
     * Why the ticked orders/litres can't go to this trip, keyed by field, or
     * null. $fuels: the ticked orders loaded by attachableFuelsQuery.
     */
    protected function attachFuelAllocationErrors($fuels): ?array
    {
        if ($fuels->count() != count($this->tickedFuelIds())) {
            return ['attach_fuel_ids' => 'One or more of the selected fuel orders is no longer available - it may have just been fully allocated to another trip, or belongs to a different horse/vehicle. Re-select and try again.'];
        }

        foreach ($fuels as $fuel) {
            if ($this->attachedFuelLocked($fuel)) {
                continue;
            }
            $qty = $this->attach_fuel_quantities[$fuel->id] ?? null;
            $available = $this->availableFuelLitres($fuel);
            if (!is_numeric($qty) || (float) $qty <= 0) {
                return ["attach_fuel_quantities.{$fuel->id}" => "Enter the litres of order {$fuel->order_number} going to this trip."];
            }
            if ((float) $qty - $available > 0.005) {
                return ["attach_fuel_quantities.{$fuel->id}" => "Only {$available} L of order {$fuel->order_number} is available to this trip."];
            }
        }

        return null;
    }

    /** Attach mode's checks, run before anything on the trip is saved. */
    protected function validateAttachedFuelOrders(): void
    {
        $ticked = $this->tickedFuelIds();
        $onTrip = TripExpense::where('trip_id', $this->trip_id)->whereNotNull('fuel_id')->exists();

        if (!$ticked && !$onTrip) {
            throw ValidationException::withMessages(['attach_fuel_ids' => 'Select at least one fuel order to attach.']);
        }
        if (!$ticked) {
            return;
        }

        $this->validate(['fuel_category' => 'required|in:Self,Customer,Transporter']);

        $fuels = $this->attachableFuelsQuery()->whereIn('fuels.id', $ticked)->get();
        if ($errors = $this->attachFuelAllocationErrors($fuels)) {
            throw ValidationException::withMessages($errors);
        }

        // A newly attached customer-supplied order is unattached, so it takes the form's category.
        if ($this->fuel_category == 'Customer' && $fuels->contains(fn ($fuel) => $fuel->supplied_by_customer && $fuel->this_trip_litres === null)) {
            throw ValidationException::withMessages([
                'fuel_category' => 'Customer funded fuel can\'t also be recharged to the customer - use Self or Transporter.',
            ]);
        }
    }

    /**
     * Makes the trip's fuel allocations match the ticked orders/litres through
     * FuelTripAllocationService (the same split as Fuels\Edit): new orders are
     * added, changed litres updated, unticked orders taken off the trip. Other
     * trips' shares are left as they are. An unattached order takes the form's
     * category; one already on a trip keeps its own (one category per order).
     * An approved order is re-posted so the cost follows the allocation
     * (Fuel - Ops <-> Fuel - COGS / transporter recoverable).
     */
    protected function syncAttachedFuelOrders(Trip $trip): void
    {
        $ticked = $this->tickedFuelIds();
        $current = TripExpense::where('trip_id', $trip->id)->whereNotNull('fuel_id')->pluck('fuel_id')->map(fn ($id) => (int) $id)->all();

        // Lock, then re-check the litres are still there.
        $fuels = Fuel::whereIn('id', array_unique(array_merge($ticked, $current)))
            ->orderBy('date')->orderBy('id')->lockForUpdate()->get();
        if ($ticked && ($errors = $this->attachFuelAllocationErrors($this->attachableFuelsQuery()->whereIn('fuels.id', $ticked)->get()))) {
            throw ValidationException::withMessages($errors);
        }

        $allocations = app(FuelTripAllocationService::class);
        $hasFill = Fuel::where('trip_id', $trip->id)->where('fillup', 1)->exists();

        foreach ($fuels as $fuel) {
            $rows = $allocations->rowsFor($fuel);
            $mine = collect($rows)->first(fn ($row) => (int) $row['trip_id'] === (int) $trip->id);
            $isTicked = in_array((int) $fuel->id, $ticked, true);

            if ($mine && $fuel->supplied_by_customer) {
                continue; // changed from Fuel Orders only
            }
            if (!$mine && !$isTicked) {
                continue;
            }
            $qty = $isTicked ? round((float) $this->attach_fuel_quantities[$fuel->id], 2) : null;
            if ($mine && $isTicked && abs((float) $mine['quantity'] - $qty) < 0.005) {
                continue; // unchanged
            }

            $newRows = [];
            foreach ($rows as $row) {
                if ((int) $row['trip_id'] !== (int) $trip->id) {
                    $newRows[] = $row;
                } elseif ($isTicked) {
                    $newRows[] = ['trip_id' => $trip->id, 'quantity' => $qty];
                }
            }
            if (!$mine) {
                $newRows[] = ['trip_id' => $trip->id, 'quantity' => $qty];
            }

            if ($error = $allocations->validationError($newRows, $fuel->quantity, (bool) $fuel->supplied_by_customer, $fuel->horse_id, $fuel->vehicle_id)) {
                throw ValidationException::withMessages([
                    "attach_fuel_quantities.{$fuel->id}" => "Order {$fuel->order_number}: {$error}",
                ]);
            }

            $unattached = !$fuel->trip_id;
            if ($unattached) {
                $fuel->driver_id = $trip->driver_id ?: $fuel->driver_id;
                // The trip's first fill is its initial fill, the rest are top-ups.
                $fuel->fillup = $hasFill ? 0 : 1;
                $hasFill = true;
            }

            $allocations->sync($fuel, $newRows, $unattached ? $this->fuel_category : ($fuel->category ?: 'Self'));

            if (!$mine) {
                TripExpense::where('fuel_id', $fuel->id)->where('trip_id', $trip->id)
                    ->update(['user_id' => $this->user->id, 'date' => $trip->start_date ?? $fuel->date]);
            }

            if ($fuel->authorization == 'approved') {
                app(FuelJournalService::class)->postConsumptionSafely($fuel->fresh());
            }
        }
    }
}
