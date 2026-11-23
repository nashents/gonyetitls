<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Services\Maps\RouteDistanceService;

/**
 * Fills $distance / $duration on trip-style forms (Trips Create/Edit,
 * Transport Orders) from Google Distance Matrix.
 *
 * Prefers loading point → offloading point; falls back to departure →
 * destination when the points are missing or have no coordinates. If neither
 * pair can be measured the user is told why and any typed distance is kept.
 *
 * Expects public $selectedFrom, $selectedTo, $loading_point_id,
 * $offloading_point_id, $distance, $duration on the component.
 */
trait CalculatesTripDistance
{
    /** Kept for existing callers; the arguments are re-read from the form. */
    public function calculateDistance($from = null, $to = null, $category = null)
    {
        $pairs = [];

        if (filled($this->loading_point_id) && filled($this->offloading_point_id)) {
            $pairs[] = [LoadingPoint::find($this->loading_point_id), OffloadingPoint::find($this->offloading_point_id)];
        }
        if (filled($this->selectedFrom) && filled($this->selectedTo)) {
            $pairs[] = [Destination::find($this->selectedFrom), Destination::find($this->selectedTo)];
        }

        $error = null;
        foreach ($pairs as [$origin, $destination]) {
            if (!$origin || !$destination) {
                continue;
            }

            if ($result = RouteDistanceService::between($origin, $destination)) {
                $this->distance = $result['km'];
                $this->duration = $result['duration'];

                return;
            }

            $error = $error ?? RouteDistanceService::$lastError;
        }

        if ($error) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'Distance not calculated: ' . $error . ' You can type the distance manually.',
            ]);
        }
    }
}
