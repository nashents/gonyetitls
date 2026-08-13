<?php

namespace App\Integrations\Contracts;

/**
 * FanTracker (a white-labelled Navixy platform deployment) operations, on top
 * of the base IntegrationDriver.
 *
 * Auth is a session hash: POST {base_url}/user/auth {login,password} -> {hash},
 * then every other call sends {"hash": "..."} in its JSON body. The hash is
 * cached and transparently refreshed by the concrete driver — callers never
 * see it. All methods return the normalised ['success','status','data','error'] shape.
 */
interface FanTrackerDriver extends IntegrationDriver
{
    /** POST /tracker/list — every tracker on the account (id, label, group_id, source{...}). No plate/registration field exists; vehicles are matched by label. */
    public function listTrackers(): array;

    /** POST /tracker/get_states — batched current GPS state (location/speed/last_update) for the given tracker ids. */
    public function getStates(array $trackerIds): array;

    /** POST /tracker/get_counters — a single tracker's counters (odometer in km, engine_hours). tracker_id is required by the API; there is no fleet-wide batch form. */
    public function getCounters(int $trackerId): array;
}
