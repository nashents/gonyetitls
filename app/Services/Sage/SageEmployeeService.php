<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Driver;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\Sage\Mappers\SageEmployeeMapper;

/**
 * Ensures a Gonyeti Driver's Employee exists as a Sage EMPLOYEE. Sage employees
 * require a Contact, so we create the contact first (idempotent), then the
 * employee. Keyed on the underlying Employee record.
 */
class SageEmployeeService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
    }

    /** Returns ['success'=>bool,'external_id'=>?string,'error'=>?string]. */
    public function ensureForDriver(?Driver $driver): array
    {
        $employee = $driver ? $driver->employee : null;
        if (! $employee) {
            return ['success' => false, 'external_id' => null, 'error' => 'Trip driver has no employee record.'];
        }

        $mapping = $this->mappingFor($this->integration, 'driver_employee', $employee);
        if ($mapping->exists && $mapping->external_id) {
            $this->storeCustomRef($driver, $employee, $mapping->external_id);
            return ['success' => true, 'external_id' => $mapping->external_id];
        }

        $mapping->local_model       = get_class($employee);
        $mapping->local_reference   = SageEmployeeMapper::fullName($employee);
        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $employeeId = SageEmployeeMapper::employeeId($employee);
        $payload    = SageEmployeeMapper::map($employee);

        // 1) Ensure the contact exists (duplicate contact name is fine — link).
        $contact = $this->driver->createContact(SageEmployeeMapper::contactPayload($employee));
        if (empty($contact['success']) && ! $this->isDuplicate($contact['error'] ?? null)) {
            return $this->finishSync($mapping, $contact, $employeeId, 'create', 'driver_employee', $employee);
        }

        // 2) Create the employee (fall back to update if it already exists).
        $action = 'create';
        $res    = $this->driver->createEmployee($payload);
        if (empty($res['success']) && $this->isDuplicate($res['error'] ?? null)) {
            $res    = $this->driver->updateEmployee($employeeId, $payload);
            $action = 'update';
        }

        $result = $this->finishSync($mapping, $res, $employeeId, $action, 'driver_employee', $employee);

        // Store the Sage EMPLOYEEID in custom_ref (Sage-id convention) so the
        // Gonyeti employee_number/driver_number sequences stay untouched.
        if (! empty($result['success'])) {
            $this->storeCustomRef($driver, $employee, $result['external_id'] ?? $employeeId);
        }

        return $result;
    }

    /** Persist the Sage EMPLOYEEID onto the employee and driver custom_ref. */
    protected function storeCustomRef(?\App\Models\Driver $driver, \App\Models\Employee $employee, string $externalId): void
    {
        if (empty($employee->custom_ref)) {
            $employee->forceFill(['custom_ref' => $externalId])->saveQuietly();
        }
        if ($driver && empty($driver->custom_ref)) {
            $driver->forceFill(['custom_ref' => $externalId])->saveQuietly();
        }
    }
}
