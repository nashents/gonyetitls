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
 * Syncs Transporters, Horses and Trailers to Sage Classes.
 *
 * Hierarchy: Transporter (parent) → Horse / Trailer (children). Horses and
 * trailers de-dup against the 587 existing Sage classes by matching NAME
 * (= registration); a match links to the existing CLASSID instead of creating.
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

    public function syncHorse(Horse $horse): array
    {
        $parentId = $this->ensureTransporterClassId($horse->transporter);
        $payload  = SageHorseMapper::map($horse, (string) $parentId);
        if ($parentId === null) {
            unset($payload['parentid']);
        }

        return $this->syncClass('horse', $horse, SageHorseMapper::classId($horse), SageHorseMapper::registration($horse), $payload);
    }

    public function syncTrailer(Trailer $trailer): array
    {
        $parentId = $this->ensureTransporterClassId($trailer->transporter);
        $payload  = SageTrailerMapper::map($trailer, (string) $parentId);
        if ($parentId === null) {
            unset($payload['parentid']);
        }

        return $this->syncClass('trailer', $trailer, SageTrailerMapper::classId($trailer), SageTrailerMapper::registration($trailer), $payload);
    }

    /**
     * Ensure the Transporter parent class exists; returns its CLASSID (or null
     * if there's no transporter / it couldn't be created — the child is then
     * created top-level rather than blocking the whole sync).
     */
    public function ensureTransporterClassId(?Transporter $transporter): ?string
    {
        if (! $transporter) {
            return null;
        }

        $mapping = $this->mappingFor($this->integration, 'transporter', $transporter);
        if ($mapping->exists && $mapping->external_id) {
            return $mapping->external_id;
        }

        $payload = SageTransporterMapper::map($transporter);
        $classId = $payload['id'];

        $mapping->local_model      = get_class($transporter);
        $mapping->local_reference  = $transporter->name;
        $mapping->last_attempted_at = now();

        $res = $this->driver->createClass($payload);

        if (! empty($res['success']) || $this->isDuplicate($res['error'] ?? null)) {
            // Created, or already present in Sage under this CLASSID → link.
            $mapping->markSynced($classId, $transporter->name, $res['request'] ?? null, $res['response'] ?? null);
            return $classId;
        }

        $mapping->markFailed($res['error'] ?? 'Failed to create transporter class', $res['request'] ?? null, $res['response'] ?? null);
        return null;
    }

    /**
     * Core class sync: update if already linked, else de-dup by NAME, else create.
     */
    protected function syncClass(string $entityType, Model $model, string $classId, ?string $registration, array $payload): array
    {
        $mapping = $this->mappingFor($this->integration, $entityType, $model);
        $mapping->local_model      = get_class($model);
        $mapping->local_reference  = $registration ?: $classId;
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->sync_status = IntegrationMapping::STATUS_PENDING;
            $mapping->save();
        }

        // Already linked → update in place.
        if ($mapping->external_id) {
            $res = $this->driver->updateClass($mapping->external_id, $payload);
            return $this->finishSync($mapping, $res, $mapping->external_id, 'update', $entityType, $model);
        }

        // De-dup: link to an existing Sage class with the same NAME (registration).
        if ($registration && ($existingId = $this->findClassIdByName($registration))) {
            $res = $this->driver->updateClass($existingId, $payload);
            return $this->finishSync($mapping, $res, $existingId, 'link', $entityType, $model);
        }

        // Create; if the CLASSID happens to exist already, fall back to update.
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
        $safe = str_replace("'", '', $name); // strings are single-quoted in the query
        $res  = $this->driver->readByQuery('CLASS', ['RECORDNO', 'CLASSID', 'NAME'], "NAME = '{$safe}'", 1);

        if (! empty($res['success']) && ! empty($res['data'][0]['CLASSID'])) {
            return $res['data'][0]['CLASSID'];
        }

        return null;
    }
}
