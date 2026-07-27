<?php

namespace App\Services\Sage\Mappers;

use App\Models\Horse;
use App\Services\Sage\Support\SageFormat;
use App\Services\Sage\Support\SageProjectDefaults;

/**
 * Gonyeti Horse → Sage PROJECT (child of the Transporter project, SUB - TRUCKS)
 * AND Sage CLASS (top-level, NO parent → renders orange), both named after the
 * registration number. The class is what a Trip project references.
 */
class SageHorseMapper
{
    /** Stable CLASSID / horse ref: horse_number, else HORSE-{id}. */
    public static function classId(Horse $h): string
    {
        $max = (int) config('sageintacct.class.id_max_length', 20);

        return SageFormat::id($h->horse_number ?: 'HORSE-' . $h->id, $max);
    }

    public static function projectId(Horse $h): string
    {
        return self::classId($h);
    }

    /** The registration used as NAME and as the de-dup match key against Sage. */
    public static function registration(Horse $h): ?string
    {
        return $h->registration_number ?: null;
    }

    /** Human name: registration, else horse_number, else a label. */
    protected static function name(Horse $h): string
    {
        return $h->registration_number ?: ($h->horse_number ?: ('Horse ' . $h->id));
    }

    /**
     * CLASS payload. Deliberately NO parentid → top-level class → orange,
     * matching the client's existing horse classes.
     */
    public static function map(Horse $h): array
    {
        $desc = trim(implode(' ', array_filter([$h->manufacturer, $h->model, $h->year])));

        return [
            'id'          => self::classId($h),
            'name'        => self::name($h),
            'description' => $desc !== '' ? $desc : null,
            // Explicit empty parent → forces the class top-level (orange), and
            // clears any parent a previously-synced class may have had.
            'parentid'    => '',
            'status'      => SageFormat::boolStatus($h->status),
        ];
    }

    /**
     * PROJECT payload (SUB - TRUCKS, child of the Transporter project).
     * @param  string  $parentProjectId  the Transporter PROJECTID (PARENTID).
     */
    public static function mapProject(Horse $h, string $parentProjectId): array
    {
        return array_merge(SageProjectDefaults::forEntity('horse'), [
            'id'       => self::projectId($h),
            'name'     => self::name($h),
            'parentid' => $parentProjectId,
            'status'   => SageFormat::boolStatus($h->status),
        ]);
    }
}
