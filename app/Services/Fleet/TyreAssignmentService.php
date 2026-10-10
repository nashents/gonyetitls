<?php

namespace App\Services\Fleet;

use App\Models\Tyre;
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
     * @param array  $data     tyre_id, axle, position, starting_odometer, date_fitted, description
     */
    public function assign(string $type, int $assetId, array $data): TyreAssignment
    {
        if ($active = TyreAssignment::activeForTyre($data['tyre_id'])) {
            $tyre = Tyre::find($data['tyre_id']);
            throw ValidationException::withMessages([
                'tyre_id' => TyreAssignment::alreadyAssignedMessage($active, $tyre ? ($tyre->serial_number ?: $tyre->tyre_number) : null),
            ]);
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
            $assignment->status = 1;
            $assignment->save();

            $movement = Movement::firstOrNew(['tyre_assignment_id' => $assignment->id]);
            $movement->user_id = $assignment->user_id;
            $movement->tyre_id = $assignment->tyre_id;
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

            Tyre::whereKey($assignment->tyre_id)->update(['status' => 0]);

            return $assignment;
        });
    }

    /**
     * Ends an active assignment and adds the distance covered on this fitting
     * to the tyre's running total (tyres.mileage). Returns the km added.
     */
    public function unassign(TyreAssignment $assignment, $endingOdometer, $date, $reason): float
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

        return DB::transaction(function () use ($assignment, $endingOdometer, $date, $reason) {
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
                $tyre->status = 1;
                $tyre->update();
            }

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
