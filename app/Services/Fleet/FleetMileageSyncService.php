<?php

namespace App\Services\Fleet;

use App\Models\CompanyIntegration;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Cartrack\CartrackSyncService;
use App\Services\FanTracker\FanTrackerSyncService;
use App\Services\Pinpoint\PinpointSyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Copies the live odometer from whichever tracking provider an asset is mapped
 * to (Cartrack, then FanTracker, then Pinpoint — the same precedence as the
 * trip/booking forms) into horses/trailers/vehicles.mileage. Mileage only ever
 * increases; lower tracker readings are ignored.
 *
 * No cron needed: App\Http\Middleware\SyncFleetMileage claims a per-company
 * cache lock on ordinary web traffic (one run per interval) and then either
 * runs the sync after the response is flushed (PHP-FPM) or launches it as a
 * detached `fleet:sync-mileage` process (Apache mod_php), so users never wait
 * on the tracker APIs.
 *
 * EzyTrack is not covered: its pushed device records carry position only.
 */
class FleetMileageSyncService
{
    /** @var array<int, class-string<Model>> */
    protected const ASSET_MODELS = [Horse::class, Trailer::class, Vehicle::class];

    protected const MAPPING_RELATIONS = ['cartrackMapping', 'fanTrackerMapping', 'pinpointMapping'];

    public static function intervalMinutes(): int
    {
        return max(1, (int) config('services.fleet_mileage_sync.interval_minutes', 10));
    }

    public static function lockKey(?int $companyId): string
    {
        return 'fleet-mileage-sync:' . ($companyId ?? 'none');
    }

    /** Claims this interval's slot; false when another request already ran (or is running) the sync. */
    public function claim(?int $companyId): bool
    {
        if (! config('services.fleet_mileage_sync.enabled', true)) {
            return false;
        }

        return Cache::add(self::lockKey($companyId), now()->toDateTimeString(), now()->addMinutes(self::intervalMinutes()));
    }

    /**
     * Starts `php artisan fleet:sync-mileage --user=` as a detached process so the
     * current request isn't held open. Returns false when processes can't be
     * spawned here (disabled functions / no PHP CLI found); callers then fall back.
     */
    public function launchInBackground(?int $userId): bool
    {
        $php = config('services.fleet_mileage_sync.php_binary') ?: (new PhpExecutableFinder)->find(false);

        // Under mod_php the finder can resolve to the web server binary itself; only a PHP CLI will do.
        if (! $php || ! preg_match('/php[\d.]*(\.exe)?$/i', basename($php))) {
            return false;
        }

        $command = escapeshellarg($php) . ' ' . escapeshellarg(base_path('artisan'))
            . ' fleet:sync-mileage' . ($userId ? ' --user=' . (int) $userId : '');

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                if (! function_exists('popen') || ! function_exists('pclose')) {
                    return false;
                }
                pclose(popen('start "" /B ' . $command . ' > NUL 2>&1', 'r'));
            } else {
                if (! function_exists('exec')) {
                    return false;
                }
                exec($command . ' > /dev/null 2>&1 &');
            }
        } catch (\Throwable $e) {
            Log::warning('FleetMileageSync: could not start background sync: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    public function hasActiveTracking(?int $companyId): bool
    {
        return CompanyIntegration::whereHas('integration_provider', fn ($q) => $q->where('type', 'tracking'))
            ->where('status', 'active')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->exists();
    }

    /**
     * @return array{checked:int, updated:int}
     */
    public function sync(): array
    {
        $checked = 0;
        $updated = 0;

        foreach (self::ASSET_MODELS as $modelClass) {
            $modelClass::query()
                ->where('archive', 0)
                ->where(function ($q) {
                    foreach (self::MAPPING_RELATIONS as $relation) {
                        $q->orWhereHas($relation);
                    }
                })
                ->with(self::MAPPING_RELATIONS)
                ->chunkById(100, function ($assets) use (&$checked, &$updated) {
                    foreach ($assets as $asset) {
                        $checked++;
                        $mileage = $this->liveMileage($asset);

                        // Only ever move forward: a lower reading (tracker reset/swapped, or never set to the
                        // truck's real odometer) is ignored rather than pulling the stored mileage back.
                        if ($mileage === null || $mileage < (float) $asset->mileage + 1) {
                            continue;
                        }

                        // Query-level update: skips model events/audits, which would otherwise log a row every interval.
                        $asset->newQuery()->whereKey($asset->getKey())->update(['mileage' => round($mileage)]);
                        $updated++;
                    }
                });
        }

        return ['checked' => $checked, 'updated' => $updated];
    }

    /** Positive numeric odometer from the first provider that returns one, else null. */
    public function liveMileage(Model $asset): ?float
    {
        $providers = [
            'cartrack'   => fn () => $asset->cartrackMapping ? app(CartrackSyncService::class)->currentSnapshot($asset) : null,
            'fantracker' => fn () => $asset->fanTrackerMapping ? app(FanTrackerSyncService::class)->currentSnapshot($asset) : null,
            'pinpoint'   => fn () => $asset->pinpointMapping ? app(PinpointSyncService::class)->currentSnapshot($asset) : null,
        ];

        foreach ($providers as $provider => $snapshot) {
            try {
                $value = data_get($snapshot(), 'mileage');
            } catch (\Throwable $e) {
                Log::warning("FleetMileageSync: {$provider} lookup failed for " . class_basename($asset) . " #{$asset->getKey()}: " . $e->getMessage());
                continue;
            }

            if (is_numeric($value) && (float) $value > 0) {
                return (float) $value;
            }
        }

        return null;
    }
}
