<?php

namespace App\Services\Fleet;

use App\Models\Tyre;
use App\Models\Dispose;
use App\Models\Mileage;
use App\Models\Movement;
use App\Models\TyreAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fits tyres to a horse / trailer / vehicle and takes them off again.
 * A tyre may only have one active (status = 1) assignment at a time.
 */
class TyreAssignmentService
{
    /**
     * @param string $type     Horse | Trailer | Vehicle
     * @param array  $data     tyre_id, axle, position, starting_odometer, date_fitted, description,
     *                         optional ticket_id, dispatch_id, dispatch_item_id (dispatch fitments)
     */
    public function assign(string $type, int $assetId, array $data): TyreAssignment
    {
        $tyre = Tyre::find($data['tyre_id']);
        if (!$tyre) {
            throw ValidationException::withMessages(['tyre_id' => 'Select a tyre.']);
        }

        // Only tyres that have left stores (approved dispatch, taken off a unit, or imported with qty 0).
        if ($blocker = $tyre->assignmentBlocker()) {
            throw ValidationException::withMessages(['tyre_id' => $blocker]);
        }

        $column = $this->assetColumn($type);

        if ($taken = TyreAssignment::activeAtPosition($column, $assetId, $data['axle'] ?? null, $data['position'] ?? null)) {
            throw ValidationException::withMessages([
                'position' => TyreAssignment::positionTakenMessage($taken),
            ]);
        }

        return DB::transaction(function () use ($type, $assetId, $data, $column) {
            $assignment = new TyreAssignment;
            $assignment->user_id = Auth::id();
            $assignment->tyre_id = $data['tyre_id'];
            $assignment->type = $type;
            $assignment->horse_id = null;
            $assignment->trailer_id = null;
            $assignment->vehicle_id = null;
            $assignment->{$column} = $assetId;
            $assignment->starting_odometer = $data['starting_odometer'] ?? null;
            $assignment->current_mileage = $data['starting_odometer'] ?? null;
            $assignment->date_fitted = $data['date_fitted'] ?? date('Y-m-d');
            $assignment->axle = $data['axle'] ?? null;
            $assignment->position = $data['position'] ?? null;
            $assignment->description = $data['description'] ?? null;
            $assignment->ticket_id = $data['ticket_id'] ?? null;
            $assignment->status = 1;
            $assignment->save();

            $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
            $movement->user_id = $assignment->user_id;
            $movement->tyre_id = $assignment->tyre_id;
            $movement->dispatch_id = $data['dispatch_id'] ?? null;
            $movement->dispatch_item_id = $data['dispatch_item_id'] ?? null;
            $movement->ticket_id = $data['ticket_id'] ?? null;
            $movement->position = $assignment->position;
            $movement->location = $type;
            $movement->{$column} = $assetId;
            $movement->current_mileage = $assignment->current_mileage;
            $movement->mileage_moved = $assignment->starting_odometer;
            $movement->date = $assignment->date_fitted;
            $movement->save();

            $mileage = new Mileage;
            $mileage->user_id = $assignment->user_id;
            $mileage->tyre_assignment_id = $assignment->id;
            $mileage->{$column} = $assetId;
            $mileage->mileage = $assignment->starting_odometer;
            $mileage->date = date('Y-m-d');
            $mileage->category = "Tyre Assignment";
            $mileage->save();

            $assignment->markTyreOutOfStore();

            return $assignment;
        });
    }

    /**
     * Ends an active assignment and adds the distance covered on this fitting
     * to the tyre's running total (tyres.mileage). Returns the km added.
     * The tyre stays out of stores (it can be fitted again) unless $dispose marks it disposed.
     * No journal: the tyre's cost stays with the unit it was dispatched to.
     */
    public function unassign(TyreAssignment $assignment, $endingOdometer, $date, $reason, bool $dispose = false): float
    {
        if ($assignment->status != 1) {
            throw ValidationException::withMessages([
                'tyre_assignment_id' => 'This tyre assignment is no longer active.',
            ]);
        }

        if (is_numeric($assignment->starting_odometer) && (float) $endingOdometer < (float) $assignment->starting_odometer) {
            throw ValidationException::withMessages([
                'ending_odometer' => 'Unassignment mileage cannot be less than the fitting mileage ('.$assignment->starting_odometer.').',
            ]);
        }

        return DB::transaction(function () use ($assignment, $endingOdometer, $date, $reason, $dispose) {
            $assignment->ending_odometer = $endingOdometer;
            $assignment->unassigned_date = $date;
            $assignment->unassignment_reason = $reason;
            $assignment->unassigned_by = Auth::id();
            $assignment->status = 0;
            $assignment->update();

            $distance = is_numeric($assignment->starting_odometer)
                ? max(0, (float) $endingOdometer - (float) $assignment->starting_odometer)
                : 0;

            $tyre = Tyre::find($assignment->tyre_id);
            if ($tyre) {
                $tyre->mileage = (float) $tyre->mileage + $distance;
                $tyre->status = 0;
                if ($dispose) {
                    $tyre->disposed = 1;
                }
                $tyre->update();
            }

            if ($tyre && $dispose) {
                Dispose::firstOrCreate(
                    ['tyre_id' => $tyre->id],
                    ['user_id' => Auth::id(), 'comments' => $reason, 'date' => $date]
                );
            }

            $movement = new Movement;
            $movement->user_id = Auth::id();
            $movement->tyre_id = $assignment->tyre_id;
            $movement->location = $dispose ? 'Disposed' : 'Removed';
            $movement->horse_id = $assignment->horse_id;
            $movement->trailer_id = $assignment->trailer_id;
            $movement->vehicle_id = $assignment->vehicle_id;
            $movement->position = $assignment->position;
            $movement->current_mileage = $endingOdometer;
            $movement->mileage_moved = $endingOdometer;
            $movement->date = $date;
            $movement->save();

            return $distance;
        });
    }

    public function assetColumn(string $type): string
    {
        return match ($type) {
            'Horse' => 'horse_id',
            'Trailer' => 'trailer_id',
            'Vehicle' => 'vehicle_id',
            default => throw new \InvalidArgumentException("Unknown tyre assignment type [{$type}]."),
        };
    }
}
