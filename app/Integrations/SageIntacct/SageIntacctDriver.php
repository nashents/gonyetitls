<?php

namespace App\Integrations\SageIntacct;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use InvalidArgumentException;

/**
 * Sage Intacct façade driver.
 *
 * This is the class registered on the `sage_intacct` integration provider, so
 * it is what the generic Integrations screen (and SageIntacctService) resolve.
 * It reads the company integration's `config.driver` (xml|rest), instantiates
 * the matching concrete driver, and delegates every call to it.
 *
 * Switching a company from XML to REST is therefore a pure data change:
 * set config.driver = "rest" and add the REST credentials — no code changes.
 */
class SageIntacctDriver implements SageDriver
{
    protected SageDriver $driver;

    public function __construct(CompanyIntegration $integration)
    {
        $name = $integration->config['driver']
            ?? config('sageintacct.default_driver', 'xml');

        $this->driver = $this->resolve($name, $integration);
    }

    protected function resolve(string $name, CompanyIntegration $integration): SageDriver
    {
        switch (strtolower($name)) {
            case 'xml':
                return new SageXmlDriver($integration);
            case 'rest':
                return new SageRestDriver($integration);
            default:
                throw new InvalidArgumentException("Unknown Sage Intacct driver [{$name}].");
        }
    }

    public function testConnection(): array
    {
        return $this->driver->testConnection();
    }

    public function createCustomer(array $customer): array
    {
        return $this->driver->createCustomer($customer);
    }

    public function updateCustomer(string $sageId, array $customer): array
    {
        return $this->driver->updateCustomer($sageId, $customer);
    }

    public function createVendor(array $vendor): array
    {
        return $this->driver->createVendor($vendor);
    }

    public function updateVendor(string $sageId, array $vendor): array
    {
        return $this->driver->updateVendor($sageId, $vendor);
    }

    public function createClass(array $class): array
    {
        return $this->driver->createClass($class);
    }

    public function updateClass(string $classId, array $class): array
    {
        return $this->driver->updateClass($classId, $class);
    }

    public function createProject(array $project): array
    {
        return $this->driver->createProject($project);
    }

    public function updateProject(string $projectId, array $project): array
    {
        return $this->driver->updateProject($projectId, $project);
    }

    public function readByQuery(string $object, array $fields, string $query, int $pageSize = 200): array
    {
        return $this->driver->readByQuery($object, $fields, $query, $pageSize);
    }

    public function readMore(string $resultId): array
    {
        return $this->driver->readMore($resultId);
    }

    public function createItem(array $item): array
    {
        return $this->driver->createItem($item);
    }

    public function updateItem(string $itemId, array $item): array
    {
        return $this->driver->updateItem($itemId, $item);
    }

    public function createContact(array $contact): array
    {
        return $this->driver->createContact($contact);
    }

    public function createEmployee(array $employee): array
    {
        return $this->driver->createEmployee($employee);
    }

    public function updateEmployee(string $employeeId, array $employee): array
    {
        return $this->driver->updateEmployee($employeeId, $employee);
    }

    public function createRequisition(array $header, array $lines): array
    {
        return $this->driver->createRequisition($header, $lines);
    }

    public function createSalesTransaction(array $header, array $lines): array
    {
        return $this->driver->createSalesTransaction($header, $lines);
    }
}
