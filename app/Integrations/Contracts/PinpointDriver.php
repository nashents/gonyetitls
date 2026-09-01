<?php

namespace App\Integrations\Contracts;

/**
 * Pinpoint (trackingpinpoint.com) tracking API operations, on top of the base
 * IntegrationDriver. Auth is a single static API token sent as a `token` HTTP
 * header on every call — there is no session/hash to obtain or refresh.
 * All methods return the normalised ['success','status','data','error'] shape.
 */
interface PinpointDriver extends IntegrationDriver
{
    /**
     * Tracker/vehicle master data via GET /api2/trackers (uin, name, plate,
     * model, belong, ...). `data` is a list of associative rows.
     */
    public function listTrackers(array $filters = []): array;

    /**
     * Latest position/telemetry for every tracker belonging to $ownerUserId,
     * via GET /api2/last?user=$ownerUserId (the docs' __all_sys_ "all
     * trackers in system" shortcut is admin-only and 403s a normal token —
     * confirmed live). `data` is a map keyed by tracker uin (lat, lng, speed,
     * date, io.7 = odometer km, offline, ...).
     */
    public function getFleetLastPositions(string $ownerUserId): array;
}
