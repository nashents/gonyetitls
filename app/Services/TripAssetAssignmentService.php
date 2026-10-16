<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Mileage;
use App\Models\TrailerAssignment;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

/**
 * "Assign driver & trailers to this horse" on the trip form: makes the
 * trip's horse/driver/trailers the active Horse-Driver and Horse-Trailer
 * assignments, the same records the Assignments screens create, so the next
 * trip for this horse pre-fills them.
 *
 * - Horse-Driver: one active per horse and per driver. Other active
 *   assignments of this horse or this driver are ended.
 * - Horse-Trailer: the horse's active trailers become exactly the trip's
 *   trailers. Trailers dropped from the horse, and the trip's trailers'
 *   assignments to other horses, are ended.
 *
 * Ended assignments get status 0, end_date = trip start and ending_odometer =
 * the trip's starting mileage. New ones mirror Assignments\Index::store(),
 * including the starting Mileage row. Matching active assignments are kept.
 */
class TripAssetAssignmentService
{
    /** @return array{driver: bool, trailers_added: int, ended: int} */
    public function assign(Trip $trip, array $trailerIds): array
    {
        $result = ['driver' => false, 'trailers_added' => 0, 'ended' => 0];
        $horseId = $trip->horse_id;
        if (!$horseId) {
            return $result;
        }

        $date = $trip->start_date ?: now()->toDateString();
        $odometer = $trip->starting_mileage;

        if ($trip->driver_id) {
            $current = Assignment::where('status', 1)->where('horse_id', $horseId)->where('driver_id', $trip->driver_id)->first();
            if (!$current) {
                $result['ended'] += $this->end(
                    Assignment::where('status', 1)->where(function ($q) use ($horseId, $trip) {
                        $q->where('horse_id', $horseId)->orWhere('driver_id', $trip->driver_id);
                    })->get(),
                    $date, $odometer
                );

                $assignment = new Assignment;
                $assignment->user_id = Auth::id();
                $assignment->transporter_id = $trip->transporter_id;
                $assignment->driver_id = $trip->driver_id;
                $assignment->horse_id = $horseId;
                $assignment->starting_odometer = $odometer;
                $assignment->start_date = $date;
                $assignment->comments = "Assigned from trip {$trip->trip_number}";
                $assignment->status = 1;
                $assignment->save();

                $this->startingMileage(['assignment_id' => $assignment->id, 'horse_id' => $horseId], $odometer, $date, 'Horse Assignment');
                $result['driver'] = true;
            }
        }

        $trailerIds = array_values(array_unique(array_filter(array_map('intval', $trailerIds))));
        if (!empty($trailerIds)) {
            // Trailers no longer on this horse, and the trip's trailers still on another horse
            $result['ended'] += $this->end(
                TrailerAssignment::where('status', 1)->where(function ($q) use ($horseId, $trailerIds) {
                    $q->where(fn ($h) => $h->where('horse_id', $horseId)->whereNotIn('trailer_id', $trailerIds))
                      ->orWhere(fn ($t) => $t->whereIn('trailer_id', $trailerIds)->where('horse_id', '!=', $horseId));
                })->get(),
                $date, $odometer
            );

            $alreadyOnHorse = TrailerAssignment::where('status', 1)->where('horse_id', $horseId)
                ->whereIn('trailer_id', $trailerIds)->pluck('trailer_id')->map(fn ($id) => (int) $id)->all();

            foreach (array_diff($trailerIds, $alreadyOnHorse) as $trailerId) {
                $assignment = new TrailerAssignment;
                $assignment->user_id = Auth::id();
                $assignment->transporter_id = $trip->transporter_id;
                $assignment->trailer_id = $trailerId;
                $assignment->horse_id = $horseId;
                $assignment->starting_odometer = $odometer;
                $assignment->start_date = $date;
                $assignment->comments = "Assigned from trip {$trip->trip_number}";
                $assignment->status = 1;
                $assignment->save();

                $this->startingMileage(['trailer_assignment_id' => $assignment->id, 'trailer_id' => $trailerId], $odometer, $date, 'Trailer Assignment');
                $result['trailers_added']++;
            }
        }

        return $result;
    }

    private function end($assignments, $date, $odometer): int
    {
        foreach ($assignments as $assignment) {
            $assignment->status = 0;
            $assignment->end_date = $date;
            $assignment->ending_odometer = $odometer;
            $assignment->update();
        }

        return $assignments->count();
    }

    private function startingMileage(array $links, $odometer, $date, string $category): void
    {
        $mileage = new Mileage;
        $mileage->user_id = Auth::id();
        foreach ($links as $column => $value) {
            $mileage->{$column} = $value;
        }
        $mileage->mileage = $odometer;
        $mileage->date = $date;
        $mileage->category = $category;
        $mileage->save();
    }
}
