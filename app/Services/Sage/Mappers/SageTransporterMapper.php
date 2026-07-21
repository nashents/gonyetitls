<?php

namespace App\Services\Sage\Mappers;

use App\Models\Transporter;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Transporter → Sage CLASS (top-level parent, no PARENTID).
 */
class SageTransporterMapper
{
    /** Stable CLASSID: transport_number, else TRANS-{id}. */
    public static function classId(Transporter $t): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);
        $ref = $t->transport_number ?: 'TRANS-' . $t->id;

        return SageFormat::id($ref, $max);
    }

    /** Generic CLASS payload for the driver. */
    public static function map(Transporter $t): array
    {
        return [
            'id'     => self::classId($t),
            'name'   => $t->name ?: ('Transporter ' . $t->id),
            'status' => SageFormat::boolStatus($t->status),
            // ADJUST: add DESCRIPTION here if the client wants extra detail.
        ];
    }
}
