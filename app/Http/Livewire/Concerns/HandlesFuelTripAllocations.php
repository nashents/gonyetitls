<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Fuel;
use App\Models\Trip;
use App\Services\Fuel\FuelTripAllocationService;

/**
 * Fuel order forms: split the order's litres across trips. Picking a trip in
 * the trip list ($pickTrip) adds a row; each row gets its litres. Rows may
 * total less than the order (the balance stays unallocated) but not more.
 * $selectedTrip mirrors the first row, so the existing single-trip logic
 * (category, customer, horse/driver defaults) keeps working.
 *
 * Needs on the component: $quantity, $selectedTrip and (optional)
 * updatedSelectedTrip($id).
 */
trait HandlesFuelTripAllocations
{
    public $trip_allocations = [];
    public $pickTrip;

    public function updatedPickTrip($id)
    {
        if (!filled($id)) {
            return;
        }
        $this->pickTrip = null;

        if (collect($this->trip_allocations)->contains(fn ($row) => (string) $row['trip_id'] === (string) $id)) {
            return;
        }

        $trip = Trip::find($id);
        if (!$trip || !$this->tripIsOnOrderUnit($trip)) {
            return;
        }

        $this->trip_allocations[] = [
            'trip_id' => (string) $trip->id,
            'label' => $this->tripAllocationLabel($trip),
            'quantity' => $this->unallocatedLitres > 0 ? $this->unallocatedLitres : null,
        ];

        $this->syncPrimaryTrip();
    }

    public function removeTripAllocation($index)
    {
        unset($this->trip_allocations[$index]);
        $this->trip_allocations = array_values($this->trip_allocations);
        $this->syncPrimaryTrip();
    }

    public function getAllocatedLitresProperty(): float
    {
        return round((float) collect($this->trip_allocations)->sum(fn ($row) => (float) ($row['quantity'] ?? 0)), 2);
    }

    public function getUnallocatedLitresProperty(): float
    {
        return max(0, round((float) $this->quantity - $this->allocatedLitres, 2));
    }

    /** Rows in the shape FuelTripAllocationService expects. */
    protected function allocationRows(): array
    {
        return collect($this->trip_allocations)
            ->map(fn ($row) => ['trip_id' => $row['trip_id'], 'quantity' => $row['quantity'] ?? 0])
            ->values()
            ->all();
    }

    /** Validation message for the current rows, or null. */
    protected function tripAllocationError(bool $customerSupplied = false): ?string
    {
        return app(FuelTripAllocationService::class)->validationError(
            $this->allocationRows(),
            $this->quantity,
            $customerSupplied,
            ($this->type ?? null) === 'Horse' ? ($this->selectedHorse ?? null) : null,
            ($this->type ?? null) === 'Vehicle' ? ($this->selectedVehicle ?? null) : null
        );
    }

    protected function loadTripAllocations(Fuel $fuel): void
    {
        $rows = app(FuelTripAllocationService::class)->rowsFor($fuel);
        $trips = Trip::whereIn('id', array_column($rows, 'trip_id'))->get()->keyBy('id');
        $this->trip_allocations = collect($rows)
            ->map(fn ($row) => $row + ['label' => $trips->has((int) $row['trip_id']) ? $this->tripAllocationLabel($trips[(int) $row['trip_id']]) : "Trip #{$row['trip_id']}"])
            ->all();

        // Legacy order linked by fuels.trip_id only (no trip expense yet).
        if (empty($this->trip_allocations) && $fuel->trip_id && $fuel->trip) {
            $this->trip_allocations = [[
                'trip_id' => (string) $fuel->trip_id,
                'label' => $this->tripAllocationLabel($fuel->trip),
                'quantity' => (float) $fuel->quantity,
            ]];
        }
        $this->pickTrip = null;
    }

    /** The order's horse/vehicle changed: drop trips of any other unit. */
    protected function dropOtherUnitTripAllocations(): void
    {
        if (empty($this->trip_allocations)) {
            return;
        }

        $trips = Trip::whereIn('id', array_column($this->trip_allocations, 'trip_id'))->get()->keyBy('id');
        $kept = array_values(array_filter($this->trip_allocations, fn ($row) => $trips->has((int) $row['trip_id'])
            && $this->tripIsOnOrderUnit($trips[(int) $row['trip_id']])));

        if (count($kept) !== count($this->trip_allocations)) {
            $this->trip_allocations = $kept;
            $this->syncPrimaryTrip();
        }
    }

    /** No horse/vehicle chosen yet means any trip (its unit then fills the order). */
    private function tripIsOnOrderUnit(Trip $trip): bool
    {
        if (!empty($this->selectedHorse)) {
            return (int) $trip->horse_id === (int) $this->selectedHorse;
        }
        if (!empty($this->selectedVehicle)) {
            return (int) $trip->vehicle_id === (int) $this->selectedVehicle;
        }

        return true;
    }

    private function syncPrimaryTrip(): void
    {
        $first = $this->trip_allocations[0]['trip_id'] ?? null;
        if ((string) $first === (string) $this->selectedTrip) {
            return;
        }

        $this->selectedTrip = $first;
        if (method_exists($this, 'updatedSelectedTrip')) {
            $this->updatedSelectedTrip($first);
        }
    }

    private function tripAllocationLabel(Trip $trip): string
    {
        $unit = optional($trip->horse)->identifier_label ?? optional($trip->vehicle)->identifier_label;

        return trim($trip->trip_number . ($trip->trip_ref ? '/' . $trip->trip_ref : '') . ' | ' . $trip->start_date . ($unit ? ' | ' . $unit : ''));
    }
}
