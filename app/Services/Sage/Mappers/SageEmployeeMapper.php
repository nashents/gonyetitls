<?php

namespace App\Services\Sage\Mappers;

use App\Models\Employee;
use App\Services\Sage\Support\SageFormat;

/**
 * Gonyeti Employee (a Driver's employee) → Sage EMPLOYEE (+ its required Contact).
 * Requisition lines reference the employee (the trip's driver).
 */
class SageEmployeeMapper
{
    /** Stable EMPLOYEEID: employee_number, else EMP-{id}. */
    public static function employeeId(Employee $e): string
    {
        $prefix = (string) config('sageintacct.employee.id_prefix', 'EMP-');
        $max    = (int) config('sageintacct.class.id_max_length', 20);
        $ref    = $e->employee_number ?: ($prefix . $e->id);

        return SageFormat::id($ref, $max);
    }

    /** Human full name. */
    public static function fullName(Employee $e): string
    {
        $full = trim(($e->name ?? '') . ' ' . ($e->surname ?? ''));

        return $full !== '' ? $full : ('Employee ' . $e->id);
    }

    /**
     * Contact name — must be UNIQUE in Sage, so it is suffixed with the
     * employee id. This is the CONTACTNAME the employee references.
     */
    public static function contactName(Employee $e): string
    {
        return mb_substr(self::fullName($e) . ' (' . self::employeeId($e) . ')', 0, 100);
    }

    /** Payload for creating the prerequisite Contact. */
    public static function contactPayload(Employee $e): array
    {
        return [
            'name'    => self::contactName($e),   // CONTACTNAME (unique)
            'printas' => self::fullName($e),      // PRINTAS (display)
        ];
    }

    /** Payload for creating/updating the EMPLOYEE. */
    public static function map(Employee $e): array
    {
        return [
            'id'           => self::employeeId($e),
            'contactname'  => self::contactName($e),
            'departmentid' => config('sageintacct.project.department_id'),
            'locationid'   => config('sageintacct.project.location_id'),
            'status'       => SageFormat::boolStatus($e->status),
        ];
    }
}
