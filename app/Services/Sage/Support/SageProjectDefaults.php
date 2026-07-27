<?php

namespace App\Services\Sage\Support;

/**
 * Shared PROJECT defaults (category, type, location, department) applied to
 * every Gonyeti→Sage project. Values come from config and may be overridden
 * per company on the integration `config` (see SageProjectService).
 */
class SageProjectDefaults
{
    /**
     * @param  string  $entity  transporter|horse|trip (selects the PROJECTTYPE)
     * @return array{category:string,projecttype:?string,locationid:?string,departmentid:?string}
     */
    public static function forEntity(string $entity): array
    {
        return [
            'category'     => (string) config('sageintacct.project.category', 'Contract'),
            'projecttype'  => config("sageintacct.project.types.{$entity}"),
            'locationid'   => config('sageintacct.project.location_id'),
            'departmentid' => config('sageintacct.project.department_id'),
        ];
    }
}
