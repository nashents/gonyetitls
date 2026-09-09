<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetPositionLog extends Model
{
    protected $fillable = [
        'horse_id',
        'vehicle_id',
        'trip_id',
        'company_id',
        'source',
        'latitude',
        'longitude',
        'speed',
        'odometer',
        'recorded_at',
    ];

    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'speed'       => 'float',
        'odometer'    => 'float',
        'recorded_at' => 'datetime',
    ];

    public function horse()
    {
        return $this->belongsTo(Horse::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Distance in kilometres between two lat/lng points (haversine). Used to
     * derive both dwell radius checks and 24h/48h distance travelled from a
     * point-in-time GPS log rather than an odometer.
     */
    public static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * "In area" radius: consecutive points within this many km of the latest
     * point are treated as the truck not having moved on, matching the
     * screenshots' "In area since/for" columns.
     */
    public const IN_AREA_RADIUS_KM = 0.3;

    /**
     * Dwell time + distance travelled for one horse, computed from an
     * already-fetched, descending-by-recorded_at collection of its logs
     * (caller batches this per page load to avoid N+1 queries).
     *
     * @param  \Illuminate\Support\Collection<int, self>  $logsDesc
     * @return array{in_area_since: ?\Carbon\Carbon, distance_24h: float, distance_48h: float}
     */
    public static function summarize($logsDesc): array
    {
        $latest = $logsDesc->first();

        $inAreaSince = null;
        if ($latest) {
            $inAreaSince = $latest->recorded_at;
            foreach ($logsDesc as $point) {
                if (self::haversineKm($latest->latitude, $latest->longitude, $point->latitude, $point->longitude) > self::IN_AREA_RADIUS_KM) {
                    break;
                }
                $inAreaSince = $point->recorded_at;
            }
        }

        $now = now();
        $distance24h = self::distanceSince($logsDesc, $now->copy()->subDay());
        $distance48h = self::distanceSince($logsDesc, $now->copy()->subDays(2));

        return [
            'in_area_since' => $inAreaSince,
            'distance_24h'  => $distance24h,
            'distance_48h'  => $distance48h,
        ];
    }

    private static function distanceSince($logsDesc, \Carbon\Carbon $since): float
    {
        $points = $logsDesc->filter(fn ($log) => $log->recorded_at->gte($since))->sortBy('recorded_at')->values();

        $distance = 0.0;
        for ($i = 1; $i < $points->count(); $i++) {
            $distance += self::haversineKm(
                $points[$i - 1]->latitude,
                $points[$i - 1]->longitude,
                $points[$i]->latitude,
                $points[$i]->longitude
            );
        }

        return round($distance, 1);
    }
}
