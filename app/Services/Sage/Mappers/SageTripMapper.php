<?php

namespace App\Services\Sage\Mappers;

use App\Models\Trip;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Trip → Sage PROJECT.
 * PROJECTID = trip_number; NAME = uppercase "ORIGIN TO DESTINATION";
 * references the synced Customer (CUSTOMERID) and the Horse CLASS (CLASSID).
 */
class SageTripMapper
{
    /** Stable PROJECTID: trip_number, else TRIP-{id}. */
    public static function projectId(Trip $trip): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);
        $ref = $trip->trip_number ?: 'TRIP-' . $trip->id;

        return SageFormat::id($ref, $max);
    }

    /**
     * Project NAME as uppercase "ORIGIN TO DESTINATION".
     * `from`/`to` hold Destination IDs → resolve via fromDestination/toDestination;
     * then loading_point/offloading_point; finally fall back to trip_number.
     */
    public static function routeName(Trip $trip): string
    {
        $origin = optional($trip->fromDestination)->name
            ?: optional($trip->loading_point)->name;

        $destination = optional($trip->toDestination)->name
            ?: optional($trip->offloading_point)->name;

        if ($origin && $destination) {
            return strtoupper(trim($origin) . ' TO ' . trim($destination));
        }

        return strtoupper((string) ($trip->trip_number ?: ('TRIP ' . $trip->id)));
    }

    /**
     * Generic PROJECT payload.
     *
     * @param  string|null  $customerSageId  synced customer CUSTOMERID (dependency)
     * @param  string|null  $horseClassId    synced horse CLASSID
     * @param  string[]     $trailerRegs     trailer registrations for the description
     */
    public static function map(Trip $trip, ?string $customerSageId, ?string $horseClassId, array $trailerRegs = []): array
    {
        $category    = self::category($trip);
        $description = self::description($trip, $trailerRegs);

        return [
            'id'          => self::projectId($trip),
            'name'        => self::routeName($trip),
            'category'    => $category,
            'customerid'  => $customerSageId ?: null,
            'classid'     => $horseClassId ?: null,
            'begindate'   => SageFormat::date($trip->start_date),
            'enddate'     => SageFormat::date($trip->end_date),
            'currency'    => optional($trip->currency)->code ?: null,
            'status'      => self::status($trip),
            'description' => $description,
        ];
    }

    /** PROJECTCATEGORY: per-company override, else config default (Contract). */
    public static function category(Trip $trip): string
    {
        // ADJUST: per-company override may be read by the service and passed in;
        // default comes from config. Kept here as the single source of the value.
        return (string) config('sageintacct.project.category', 'Contract');
    }

    /** Map Gonyeti trip_status to Sage PROJECT STATUS (active/inactive). */
    protected static function status(Trip $trip): string
    {
        $s = strtolower((string) $trip->trip_status);

        return in_array($s, ['cancelled', 'canceled', 'closed', 'void'], true) ? 'inactive' : 'active';
    }

    /** Human-readable description (Sage projects can't hold these as fields). */
    protected static function description(Trip $trip, array $trailerRegs): string
    {
        $parts = array_filter([
            $trip->trip_number ? ('Trip ' . $trip->trip_number) : null,
            optional($trip->horse)->registration_number ? ('Horse ' . $trip->horse->registration_number) : null,
            $trailerRegs ? ('Trailers ' . implode(', ', $trailerRegs)) : null,
            optional($trip->driver)->name ? ('Driver ' . $trip->driver->name) : null,
            $trip->trip_ref ? ('Ref ' . $trip->trip_ref) : null,
        ]);

        return mb_substr(implode('; ', $parts), 0, 1000);
    }
}
