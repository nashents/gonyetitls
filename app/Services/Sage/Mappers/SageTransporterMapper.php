<?php

namespace App\Services\Sage\Mappers;

use App\Models\Transporter;
use App\Services\Sage\Support\SageFormat;
use App\Services\Sage\Support\SageProjectDefaults;

/**
 * Gonyeti Transporter → Sage PROJECT (top-level, PROJECTTYPE = SUBCONTRACTOR)
 * AND Sage CLASS (kept as the green parent for trailer classes).
 */
class SageTransporterMapper
{
    /** Stable id used for both the PROJECT and CLASS: transport_number, else TRANS-{id}. */
    public static function ref(Transporter $t): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);

        return SageFormat::id($t->transport_number ?: 'TRANS-' . $t->id, $max);
    }

    public static function classId(Transporter $t): string
    {
        return self::ref($t);
    }

    public static function projectId(Transporter $t): string
    {
        return self::ref($t);
    }

    /** CLASS payload (parent for trailer classes). */
    public static function map(Transporter $t): array
    {
        return [
            'id'     => self::classId($t),
            'name'   => $t->name ?: ('Transporter ' . $t->id),
            'status' => SageFormat::boolStatus($t->status),
        ];
    }

    /** PROJECT payload (top-level, SUBCONTRACTOR). */
    public static function mapProject(Transporter $t): array
    {
        return array_merge(SageProjectDefaults::forEntity('transporter'), [
            'id'     => self::projectId($t),
            'name'   => $t->name ?: ('Transporter ' . $t->id),
            'status' => SageFormat::boolStatus($t->status),
            // top-level: no parentid
        ]);
    }
}
