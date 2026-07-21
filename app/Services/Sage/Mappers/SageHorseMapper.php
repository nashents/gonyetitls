<?php

namespace App\Services\Sage\Mappers;

use App\Models\Horse;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Horse → Sage CLASS (child of its Transporter).
 * CLASSID = horse_number (e.g. FHH00001); NAME = registration (Sage convention).
 */
class SageHorseMapper
{
    /** Stable CLASSID: horse_number, else HORSE-{id}. */
    public static function classId(Horse $h): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);
        $ref = $h->horse_number ?: 'HORSE-' . $h->id;

        return SageFormat::id($ref, $max);
    }

    /** The registration used as NAME and as the de-dup match key against Sage. */
    public static function registration(Horse $h): ?string
    {
        return $h->registration_number ?: null;
    }

    /**
     * Generic CLASS payload.
     * @param  string  $parentClassId  the Transporter CLASSID (PARENTID).
     */
    public static function map(Horse $h, string $parentClassId): array
    {
        // NAME must be present: registration, else horse_number, else a label.
        $name = $h->registration_number ?: ($h->horse_number ?: ('Horse ' . $h->id));

        // DESCRIPTION: readable make/model/year (only if present).
        $desc = trim(implode(' ', array_filter([
            $h->manufacturer,
            $h->model,
            $h->year,
        ])));

        return [
            'id'          => self::classId($h),
            'name'        => $name,
            'parentid'    => $parentClassId,
            'description' => $desc !== '' ? $desc : null,
            'status'      => SageFormat::boolStatus($h->status),
        ];
    }
}
