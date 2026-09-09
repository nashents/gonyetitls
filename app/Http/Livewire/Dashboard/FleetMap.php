<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Fleet\LiveMap;

/**
 * Dashboard-embedded fleet map: same company-wide, all-provider marker
 * aggregation as App\Http\Livewire\Fleet\LiveMap (Cartrack/EzyTrack/
 * FanTracker/Pinpoint), just rendered into a slim panel that matches the
 * dashboard's own styling instead of LiveMap's full standalone page.
 */
class FleetMap extends LiveMap
{
    public function render()
    {
        $markers = array_merge(
            $this->safely('cartrack', fn () => $this->cartrackEnabled ? $this->markers() : []),
            $this->safely('ezytrack', fn () => $this->ezyTrackEnabled ? $this->ezyTrackMarkers() : []),
            $this->safely('fantracker', fn () => $this->fanTrackerEnabled ? $this->fanTrackerMarkers() : []),
            $this->safely('pinpoint', fn () => $this->pinpointEnabled ? $this->pinpointMarkers() : [])
        );

        return view('livewire.dashboard.fleet-map', [
            'markers'  => $markers,
            'apiError' => $this->apiError,
        ]);
    }
}
