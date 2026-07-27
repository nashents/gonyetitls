<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageHorseMapper;
use App\Services\Sage\Mappers\SageTrailerMapper;
use App\Services\Sage\Mappers\SageTransporterMapper;
use Illuminate\Database\Eloquent\Model;

/**
 * Sage CLASS sync.
 *  - Horse class: top-level (NO parent) → renders orange. Referenced by trips.
 *  - Trailer class: child of the Transporter class → renders green.
 *  - Transporter class: kept only as the green parent for trailer classes.
 * Horse/Trailer classes de-dup against existing Sage classes by NAME (registration).
 */
class SageClassService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
    }

    /** Horse → CLASS, flat (orange). */
    public function syncHorseClass(Horse $horse): array
    {
        return $this->syncClass(
            'horse_class',
            $horse,
            SageHorseMapper::classId($horse),
            SageHorseMapper::registration($horse),
            SageHorseMapper::map($horse)          // deliberately no parentid
        );
    }

    /** Trailer → CLASS, under the Transporter class (green). */
    public function syncTrailer(Trailer $trailer): array
    {
        $parent   = $this->ensureTransporterClass($trailer->transporter);
        $parentId = $parent['external_id'] ?? null;

        $payload = SageTrailerMapper::map($trailer, (string) $parentId);
        if (! $parentId) {
            unset($payload['parentid']);
        }

        return $this->syncClass(
            'trailer_class',
            $trailer,
            SageTrailerMapper::classId($trailer),
            SageTrailerMapper::registration($trailer),
            $payload
        );
    }

    /**
     * Ensure the Transporter CLASS exists (the green parent for trailers).
     * Returns ['success'=>bool,'external_id'=>?string].
     */
    public function ensureTransporterClass(?Transporter $transporter): array
    {
        if (! $transporter) {
            return ['success' => false, 'external_id' => null];
        }

        $mapping = $this->mappingFor($this->integration, 'transporter_class', $transporter);
        if ($mapping->exists && $mapping->external_id) {
            return ['success' => true, 'external_id' => $mapping->external_id];
        }

        return $this->syncClass(
            'transporter_class',
            $transporter,
            SageTransporterMapper::classId($transporter),
            null,
            SageTransporterMapper::map($transporter)
        );
    }

    /** Update if already linked, else de-dup by NAME (registration), else create. */
    protected function syncClass(string $entityType, Model $model, string $classId, ?string $registration, array $payload): array
    {
        $mapping = $this->mappingFor($this->integration, $entityType, $model);
        $mapping->local_model       = get_class($model);
        $mapping->local_reference   = $registration ?: $classId;
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->sync_status = IntegrationMapping::STATUS_PENDING;
            $mapping->save();
        }

        if ($mapping->external_id) {
            $res = $this->driver->updateClass($mapping->external_id, $payload);
            return $this->finishSync($mapping, $res, $mapping->external_id, 'update', $entityType, $model);
        }

        if ($registration && ($existingId = $this->findClassIdByName($registration))) {
            $res = $this->driver->updateClass($existingId, $payload);
            return $this->finishSync($mapping, $res, $existingId, 'link', $entityType, $model);
        }

        $res = $this->driver->createClass($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res = $this->driver->updateClass($classId, $payload);
            return $this->finishSync($mapping, $res, $classId, 'update', $entityType, $model);
        }

        return $this->finishSync($mapping, $res, $classId, 'create', $entityType, $model);
    }

    /** Look up an existing Sage CLASS by exact NAME; returns its CLASSID or null. */
    protected function findClassIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('CLASS', ['RECORDNO', 'CLASSID', 'NAME'], "NAME = '{$safe}'", 1);

        if (! empty($res['success']) && ! empty($res['data'][0]['CLASSID'])) {
            return $res['data'][0]['CLASSID'];
        }

        return null;
    }
}
