<?php

namespace App\Integrations\Contracts;

/**
 * Sage Intacct accounting operations, on top of the base IntegrationDriver.
 *
 * Implemented by the concrete XML and REST drivers and by the SageIntacctDriver
 * façade. Customer / vendor payloads are the mapped arrays produced by
 * SageIntacctService (mapCustomer / mapVendor), e.g.:
 *
 *   [
 *     'id'      => 'GONC00012',   // CUSTOMERID / VENDORID
 *     'name'    => 'Acme Ltd',
 *     'email'   => 'ap@acme.com',
 *     'phone'   => '+263...',
 *     'address1'=> '...', 'city' => '...', 'country' => '...',
 *     'taxid'   => '...', 'currency' => 'USD',
 *     'status'  => 'active'|'inactive',
 *   ]
 *
 * create* returns the assigned Sage id in ['data']['id']; update* targets an
 * existing record by its Sage id.
 */
interface SageDriver extends IntegrationDriver
{
    public function createCustomer(array $customer): array;

    public function updateCustomer(string $sageId, array $customer): array;

    public function createVendor(array $vendor): array;

    public function updateVendor(string $sageId, array $vendor): array;

    // ── Phase 2: Classes (Transporter/Horse/Trailer) & Projects (Trip) ──

    /** Generic class payload: id, name, parentid?, description?, status?. */
    public function createClass(array $class): array;

    public function updateClass(string $classId, array $class): array;

    /** Generic project payload: id, name, category, customerid?, classid?, begindate?, enddate?, currency?, status?, description?. */
    public function createProject(array $project): array;

    public function updateProject(string $projectId, array $project): array;

    /** Read records; on success `data` is a list of associative rows. */
    public function readByQuery(string $object, array $fields, string $query, int $pageSize = 200): array;

    // ── Phase 3: Items, Employees, Purchase Requisitions ──

    public function createItem(array $item): array;

    public function updateItem(string $itemId, array $item): array;

    public function createContact(array $contact): array;

    public function createEmployee(array $employee): array;

    public function updateEmployee(string $employeeId, array $employee): array;

    /** create_potransaction (Purchase Requisition): header array + line rows. */
    public function createRequisition(array $header, array $lines): array;
}
