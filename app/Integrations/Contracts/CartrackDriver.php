<?php

namespace App\Integrations\Contracts;

/**
 * Cartrack Fleet API operations, on top of the base IntegrationDriver.
 *
 * The Fleet API (https://fleetapi-{region}.cartrack.com/rest) is plain HTTP
 * Basic Auth on every call — there is no separate token/login endpoint.
 * All methods return the normalised ['success','status','data','error'] shape.
 */
interface CartrackDriver extends IntegrationDriver
{
    /**
     * Vehicle master data via GET /vehicles (the endpoint used in Cartrack's
     * own auth guide example, not /manufacturer/customers — that one lives
     * under their separate OEM "Car Manufacturers" product and 401s for a
     * normal Fleet API subscriber). `data` is a list of associative rows;
     * exact field names / supported $filters need confirming against a live
     * response once account access is sorted out.
     */
    public function listVehicles(array $filters = []): array;

    /**
     * ISO 15143-3 (AEMP) snapshot for every vehicle, paginated (max 100/page).
     * `data` is a list of per-vehicle snapshots (position, odometer, engine hours, fuel).
     */
    public function getFleetSnapshot(int $page = 1, int $limit = 100): array;

    /**
     * ISO 15143-3 (AEMP) snapshot for a single vehicle, identified by its
     * Cartrack registration or equipment id. `data` is one snapshot row.
     */
    public function getVehicleSnapshot(string $registrationOrId): array;
}
