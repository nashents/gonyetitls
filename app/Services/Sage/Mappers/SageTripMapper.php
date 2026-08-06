<?php

namespace App\Services\Sage\Mappers;

use App\Models\Trip;
use App\Services\Sage\Support\SageFormat;
use App\Services\Sage\Support\SageProjectDefaults;

/**
 * Gonyeti Trip → Sage PROJECT (PROJECTTYPE = TRIPS).
 *  - PROJECTID  = manifest_number
 *  - NAME       = uppercase "ORIGIN TO DESTINATION"
 *  - PARENTID   = the Horse project
 *  - CLASSID    = the Horse class (orange)
 *  - DESCRIPTION= "Manifest: … Driver: …; Truck: … Trailers: … From: … Destination: …"
 */
class SageTripMapper
{
    /** Stable PROJECTID: manifest_number, else trip_number, else TRIP-{id}. */
    public static function projectId(Trip $trip): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);
        $ref = $trip->manifest_number ?: ($trip->trip_number ?: 'TRIP-' . $trip->id);

        return SageFormat::id($ref, $max);
    }

    /** Origin location name (from-Destination, else loading point). */
    public static function origin(Trip $trip): ?string
    {
        return optional($trip->fromDestination)->name ?: optional($trip->loading_point)->name;
    }

    /** Destination location name (to-Destination, else offloading point). */
    public static function destination(Trip $trip): ?string
    {
        return optional($trip->toDestination)->name ?: optional($trip->offloading_point)->name;
    }

    /** Project NAME as uppercase "ORIGIN TO DESTINATION" (falls back to trip id). */
    public static function routeName(Trip $trip): string
    {
        $origin      = self::origin($trip);
        $destination = self::destination($trip);

        if ($origin && $destination) {
            return strtoupper(trim($origin) . ' TO ' . trim($destination));
        }

        return strtoupper((string) ($trip->manifest_number ?: ($trip->trip_number ?: ('TRIP ' . $trip->id))));
    }

    /**
     * Full PROJECT payload.
     *
     * @param  string|null  $horseProjectId  parent Horse project (PARENTID)
     * @param  string|null  $horseClassId    Horse class (CLASSID)
     * @param  string[]     $trailerRegs     trailer registrations for the description
     * @param  string|null  $customerSageId  optional CUSTOMERID if the customer is synced
     * @param  string|null  $managerId       project manager = the driver's Sage EMPLOYEEID
     */
    public static function map(Trip $trip, ?string $horseProjectId, ?string $horseClassId, array $trailerRegs = [], ?string $customerSageId = null, ?string $managerId = null): array
    {
        return array_merge(SageProjectDefaults::forEntity('trip'), [
            'id'            => self::projectId($trip),
            'name'          => self::routeName($trip),
            'parentid'      => $horseProjectId ?: null,
            'classid'       => $horseClassId ?: null,
            'customerid'    => $customerSageId ?: null,
            'managerid'     => $managerId ?: null,
            'begindate'     => SageFormat::date($trip->start_date),
            'enddate'       => SageFormat::date($trip->end_date),
            'currency'      => optional($trip->currency)->code ?: null,
            'status'        => self::status($trip),
            'projectstatus' => self::projectStatus($trip),
            'description'   => self::description($trip, $trailerRegs),
        ]);
    }

    /** Map Gonyeti trip_status to Sage PROJECT STATUS (active/inactive). */
    protected static function status(Trip $trip): string
    {
        $s = strtolower((string) $trip->trip_status);

        return in_array($s, ['cancelled', 'canceled', 'closed', 'void'], true) ? 'inactive' : 'active';
    }

    /**
     * Sage PROJECTSTATUS (workflow): offloaded trips are Completed, everything
     * else In Progress. Only offloaded trips actually sync, so this is Completed.
     */
    protected static function projectStatus(Trip $trip): string
    {
        $offloaded = strcasecmp((string) $trip->trip_status, 'Offloaded') === 0;

        return $offloaded
            ? (string) config('sageintacct.project.status_completed', 'Completed')
            : (string) config('sageintacct.project.status_in_progress', 'In Progress');
    }

    /**
     * Description in the client's exact format, e.g.:
     * "Manifest: 001/01032025 Driver: JAISON MARINDE; Truck: DBF457L
     *  Trailers: DKZ631L -FST623L From: DURBAN Destination: LUBUMBASHI"
     */
    protected static function description(Trip $trip, array $trailerRegs): string
    {
        $manifest = $trip->manifest_number ?: $trip->trip_number;
        $driver   = self::driverName($trip);
        $truck    = optional($trip->horse)->registration_number;
        $trailers = implode(' -', array_filter($trailerRegs));
        $from     = self::origin($trip);
        $to       = self::destination($trip);

        $desc = 'Manifest: ' . $manifest
            . ' Driver: ' . strtoupper((string) $driver)
            . '; Truck: ' . $truck
            . ' Trailers: ' . $trailers
            . ' From: ' . strtoupper((string) $from)
            . ' Destination: ' . strtoupper((string) $to);

        return mb_substr($desc, 0, 1000);
    }

    /** Driver full name: employee name+surname if present, else driver name. */
    protected static function driverName(Trip $trip): ?string
    {
        $driver = $trip->driver;
        if (! $driver) {
            return null;
        }

        $employee = $driver->employee ?? null;
        if ($employee) {
            $full = trim(($employee->name ?? '') . ' ' . ($employee->surname ?? ''));
            if ($full !== '') {
                return $full;
            }
        }

        return $driver->name ?? null;
    }
}
