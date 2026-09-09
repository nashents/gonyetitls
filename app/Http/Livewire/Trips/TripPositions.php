<?php

namespace App\Http\Livewire\Trips;

use App\Models\AssetPositionLog;
use App\Models\Trip;
use Livewire\Component;

/**
 * One trip's logged GPS trail — asset_position_logs rows stamped with this
 * trip's id by fleet:log-asset-positions while it was in an active tracking
 * status (Trip::ACTIVE_TRACKING_STATUSES). The list below the map is shown
 * newest-first (LIFO); the map's route line is drawn chronologically.
 *
 * Also surfaces the truck/trip detail panels above the map (driver,
 * trailers, trip summary, documents, GPS status, driver distance ranking) —
 * all sourced from real Gonyeti data. "Last SMS Sent" has no backing data
 * source in this app (no SMS system) so it's shown as an honest empty state
 * rather than invented.
 */
class TripPositions extends Component
{
    public Trip $trip;

    public $from;
    public $to;

    public function mount(Trip $trip)
    {
        $this->trip = $trip->load([
            'driver.employee',
            'trailers.trailer_type',
            'loading_point',
            'offloading_point',
            'customer',
            'consignee',
            'cargo',
            'transport_orders',
            'delivery_note',
            'horse.horse_documents',
            'vehicle.vehicle_documents',
            'tripDocuments',
        ]);
    }

    /** The asset_position_logs query for whichever asset (horse/vehicle) pulled this trip — not trip-scoped, so it reflects the truck's overall GPS history. */
    protected function assetLogsQuery()
    {
        return AssetPositionLog::query()
            ->when($this->trip->horse_id, fn ($q) => $q->where('horse_id', $this->trip->horse_id))
            ->when(! $this->trip->horse_id && $this->trip->vehicle_id, fn ($q) => $q->where('vehicle_id', $this->trip->vehicle_id));
    }

    /** Distance covered over 24h/48h/7d/30d windows, from the truck's logged GPS points (will read sparse/zero until fleet:log-asset-positions has been running long enough to have that much history). */
    protected function driverRanking(): array
    {
        $windows = ['24 hours' => 1, '48 hours' => 2, '7 days' => 7, '30 days' => 30];

        $logs = $this->assetLogsQuery()
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'recorded_at']);

        $ranking = [];
        foreach ($windows as $label => $days) {
            $since = now()->subDays($days);
            $points = $logs->filter(fn ($l) => $l->recorded_at->gte($since))->values();

            $distance = 0.0;
            for ($i = 1; $i < $points->count(); $i++) {
                $distance += AssetPositionLog::haversineKm(
                    $points[$i - 1]->latitude, $points[$i - 1]->longitude,
                    $points[$i]->latitude, $points[$i]->longitude
                );
            }

            $ranking[$label] = round($distance, 2);
        }

        return $ranking;
    }

    protected function gpsStatus(): array
    {
        return [
            'sat_report' => $this->assetLogsQuery()->latest('recorded_at')->value('recorded_at'),
            'moved_at'   => $this->assetLogsQuery()->where('speed', '>', 0)->latest('recorded_at')->value('recorded_at'),
        ];
    }

    public function render()
    {
        $query = $this->trip->positions()->orderByDesc('recorded_at');

        if ($this->from) {
            $query->where('recorded_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->where('recorded_at', '<=', $this->to);
        }

        $pointsDesc = $query->get();

        $mapRoute = $pointsDesc->sortBy('recorded_at')->values()->map(fn ($p) => [
            'lat'         => $p->latitude,
            'lng'         => $p->longitude,
            'speed'       => $p->speed,
            'source'      => $p->source,
            'recorded_at' => $p->recorded_at->format('d M Y H:i'),
        ]);

        return view('livewire.trips.trip-positions', [
            'points'        => $pointsDesc,
            'mapRoute'      => $mapRoute,
            'driverRanking' => $this->driverRanking(),
            'gpsStatus'     => $this->gpsStatus(),
        ]);
    }
}
