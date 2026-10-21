<?php

namespace App\Services\Dispatches;

use App\Models\Dispatch;
use App\Models\DispatchItem;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Tyre;
use App\Models\TyreAssignment;
use App\Models\Vehicle;
use App\Services\Fleet\TyreAssignmentService;

/**
 * Tyre dispatches take tyres out of stores to a unit (horse / trailer / vehicle on the dispatch).
 * Stores may also say where each tyre goes (axle + position); then approval fits it there and
 * takes off whatever tyre is in that position. Without a position the tyre is out of stores but
 * not fitted, and is fitted later in Tyre Assignments, except on a ticket (job card) dispatch, where
 * it is fitted to the ticket's unit with no axle/position.
 */
class TyreDispatchFitmentService
{
    public function __construct(private TyreAssignmentService $assignments)
    {
    }

    /**
     * Units a tyre on this dispatch can be fitted to: ['Horse' => id, 'Trailer' => id, ...].
     */
    public function units($horseId, $trailerId, $vehicleId): array
    {
        return array_filter([
            'Horse' => $horseId ?: null,
            'Trailer' => $trailerId ?: null,
            'Vehicle' => $vehicleId ?: null,
        ]);
    }

    public function unitsFor(Dispatch $dispatch): array
    {
        return $this->units($dispatch->horse_id, $dispatch->trailer_id, $dispatch->vehicle_id);
    }

    public function unitLabel(string $type, $id): ?string
    {
        $model = match ($type) {
            'Horse' => Horse::find($id),
            'Trailer' => Trailer::find($id),
            'Vehicle' => Vehicle::find($id),
            default => null,
        };

        return $model ? "{$type}: {$model->identifier_label}" : null;
    }

    /**
     * @param array $rows  [key => ['tyre_id', 'fit_type', 'axle', 'position', 'serial']] (serial: entered when the tyre has none)
     * @param array $units from units()
     * @param bool  $newDispatch true when creating: a tyre already on another pending dispatch is refused
     * @return array       [errorKey => message], errorKey = "{field}.{key}"
     */
    public function validate(array $rows, array $units, bool $newDispatch = false): array
    {
        $errors = [];

        if (empty($units)) {
            $errors['fit_unit'] = 'Select the horse, trailer or vehicle the tyres are being fitted to.';
        }

        $slots = [];
        $serials = [];

        foreach ($rows as $key => $row) {
            $tyre = Tyre::find($row['tyre_id'] ?? null);

            if (!$tyre) {
                $errors["selectedTyre.{$key}"] = 'Select a tyre.';
                continue;
            }

            $label = $tyre->serial_number ?: $tyre->tyre_number;

            if ($active = TyreAssignment::activeForTyre($tyre->id)) {
                $location = $active->locationLabel();
                $errors["selectedTyre.{$key}"] = "Tyre {$label} is already fitted"
                    .($location ? " to {$location}" : '')
                    .', so it is not in stock. Reject this dispatch and dispatch an in-stock tyre instead.';
                continue;
            }

            if (!Tyre::inStock()->whereKey($tyre->id)->exists()) {
                $issued = $tyre->lastDispatchItem();
                $errors["selectedTyre.{$key}"] = "Tyre {$label} is not in stock"
                    .($issued && $issued->dispatch ? " (already issued on dispatch {$issued->dispatch->dispatch_number}). Reject this dispatch." : '.');
                continue;
            }

            if ($newDispatch && ($pending = $tyre->pendingDispatch())) {
                $errors["selectedTyre.{$key}"] = "Tyre {$label} is already on pending dispatch {$pending->dispatch_number}. Approve or reject that one first.";
                continue;
            }

            // A dispatched tyre must be identifiable: ask for the serial if the record has none.
            if (blank($tyre->serial_number)) {
                $serial = trim((string) ($row['serial'] ?? ''));
                if ($serial === '') {
                    $errors["tyre_serial.{$key}"] = "Enter the serial number for tyre {$tyre->tyre_number}.";
                } elseif (isset($serials[strtolower($serial)])) {
                    $errors["tyre_serial.{$key}"] = "Serial {$serial} is entered for two tyres on this dispatch.";
                } elseif (Tyre::where('serial_number', $serial)->whereKeyNot($tyre->id)->exists()) {
                    $errors["tyre_serial.{$key}"] = "Serial {$serial} already belongs to another tyre.";
                }
                $serials[strtolower($serial)] = true;
            }

            $type = $row['fit_type'] ?? null;
            $axle = $row['axle'] ?? null;
            $position = $row['position'] ?? null;

            // Fitment is optional: no axle and no position means "fit later in Tyre Assignments".
            if (blank($axle) && blank($position)) {
                continue;
            }

            if (!$type || !isset($units[$type])) {
                $errors["fit_type.{$key}"] = "Select the unit tyre {$label} is fitted to.";
            }
            if (!in_array($axle, TyreAssignment::AXLES, true)) {
                $errors["fit_axle.{$key}"] = "Select the axle for tyre {$label}.";
            }
            if (!array_key_exists((string) $position, TyreAssignment::POSITIONS)) {
                $errors["fit_position.{$key}"] = "Select the position for tyre {$label}.";
            }

            if ($type && $axle && $position && !TyreAssignment::isSpareSlot($axle, $position)) {
                $slot = "{$type}|{$axle}|{$position}";
                if (isset($slots[$slot])) {
                    $errors["fit_position.{$key}"] = "Two tyres on this dispatch are going to {$type} {$axle} "
                        .(TyreAssignment::POSITIONS[$position] ?? $position).'.';
                }
                $slots[$slot] = true;
            }
        }

        return $errors;
    }

    /**
     * Saves serials entered for tyres that had none (rows already validated).
     */
    public function applySerials(array $rows): void
    {
        foreach ($rows as $row) {
            $serial = trim((string) ($row['serial'] ?? ''));
            if ($serial === '') {
                continue;
            }
            Tyre::whereKey($row['tyre_id'])
                ->where(fn ($q) => $q->whereNull('serial_number')->orWhere('serial_number', ''))
                ->update(['serial_number' => $serial]);
        }
    }

    /**
     * The tyre currently in the slot, which approval will take off.
     */
    public function occupant(array $units, $type, $axle, $position): ?TyreAssignment
    {
        if (!$type || !isset($units[$type])) {
            return null;
        }

        return TyreAssignment::activeAtPosition($this->assignments->assetColumn($type), $units[$type], $axle, $position);
    }

    /**
     * Fits an approved dispatch item. Call after the dispatch has taken the tyre's balance,
     * so the assignment records nothing to give back to stores on unassign.
     */
    public function fit(Dispatch $dispatch, DispatchItem $item): TyreAssignment
    {
        $units = $this->unitsFor($dispatch);
        $withPosition = filled($item->axle) && filled($item->position);
        // No position (ticket dispatch): the picked unit, else the truck (horse) first.
        $type = isset($units[$item->fit_type]) ? $item->fit_type : array_key_first($units);
        $assetId = $units[$type] ?? null;

        if (!$assetId || (!$withPosition && !$dispatch->ticket_id)) {
            throw new \RuntimeException('Every tyre on a tyre dispatch needs a unit, axle and position.');
        }
        if (!$item->fit_type) {
            $item->fit_type = $type;
            $item->save();
        }

        $odometer = $this->fittingOdometer($dispatch, $type, $assetId);
        $date = $dispatch->date ?: date('Y-m-d');
        $tyre = $item->tyre;
        $label = $tyre ? ($tyre->serial_number ?: $tyre->tyre_number) : $item->tyre_id;

        if ($withPosition && ($occupant = $this->occupant($units, $type, $item->axle, $item->position))) {
            $ending = max((float) $odometer, (float) $occupant->starting_odometer);
            $this->assignments->unassign($occupant, $ending, $date, "Replaced by tyre {$label} on dispatch {$dispatch->dispatch_number}");
        }

        return $this->assignments->assign($type, $assetId, [
            'tyre_id' => $item->tyre_id,
            'axle' => $item->axle,
            'position' => $item->position,
            'starting_odometer' => $odometer,
            'date_fitted' => $date,
            'description' => "Fitted from dispatch {$dispatch->dispatch_number}".($withPosition ? '' : ' (position not set)'),
            'ticket_id' => $dispatch->ticket_id,
            'dispatch_id' => $dispatch->id,
            'dispatch_item_id' => $item->id,
        ]);
    }

    private function fittingOdometer(Dispatch $dispatch, string $type, $assetId)
    {
        $ticket = $dispatch->ticket;
        if ($ticket && filled($ticket->odometer)) {
            return $ticket->odometer;
        }

        $asset = match ($type) {
            'Horse' => Horse::find($assetId),
            'Trailer' => Trailer::find($assetId),
            'Vehicle' => Vehicle::find($assetId),
        };

        return $asset?->mileage;
    }
}
