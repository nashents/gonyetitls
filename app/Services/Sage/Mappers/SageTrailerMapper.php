<?php

namespace App\Services\Sage\Mappers;

use App\Models\Trailer;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Trailer → Sage CLASS (child of its Transporter — sibling of Horse).
 * CLASSID = trailer_number (e.g. FHT00001); NAME = registration.
 */
class SageTrailerMapper
{
    /** Stable CLASSID: trailer_number, else TRL-{id}. */
    public static function classId(Trailer $t): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);
        $ref = $t->trailer_number ?: 'TRL-' . $t->id;

        return SageFormat::id($ref, $max);
    }

    /** Registration used as NAME and as the de-dup match key against Sage. */
    public static function registration(Trailer $t): ?string
    {
        return $t->registration_number ?: null;
    }

    /**
     * Generic CLASS payload.
     * @param  string  $parentClassId  the Transporter CLASSID (PARENTID).
     */
    public static function map(Trailer $t, string $parentClassId): array
    {
        $name = $t->registration_number ?: ($t->trailer_number ?: ('Trailer ' . $t->id));

        $desc = trim(implode(' ', array_filter([
            $t->manufacturer ?? $t->make,
            $t->model,
            optional($t->trailer_type)->name ?? null,
        ])));

        return [
            'id'          => self::classId($t),
            'name'        => $name,
            'parentid'    => $parentClassId,
            'description' => $desc !== '' ? $desc : null,
            'status'      => SageFormat::boolStatus($t->status),
        ];
    }
}
