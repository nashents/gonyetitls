<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\IntegrationMapping;
use App\Models\Ticket;
use App\Services\Sage\Concerns\ManagesMappings;

/**
 * Syncs a Gonyeti workshop Ticket (job card) to a Sage Order-Entry job card.
 *
 * The ticket's booking.transaction_type chooses the Sage definition:
 *   • expense → "Internal Job Card"
 *   • income  → "Job Card Invoice" (the standard, customer-billed job card)
 *
 * Customer = the serviced unit's transporter matched to a Sage CUSTOMER by name,
 * else the configured default for the type. Lines = the ticket's parts
 * (ticket_inventories); if none, a single labour line for the booking total.
 *
 * Idempotent: mapping entity_type="job_card", local_id=ticket_id.
 */
class SageJobCardService
{
    use ManagesMappings;

    protected SageDriver $driver;
    protected CompanyIntegration $integration;
    protected SageItemService $itemService;

    public function __construct(SageDriver $driver, CompanyIntegration $integration)
    {
        $this->driver      = $driver;
        $this->integration = $integration;
        $this->itemService = new SageItemService($driver, $integration);
    }

    public function syncJobCard(Ticket $ticket): array
    {
        $entity  = 'job_card';
        $mapping = $this->mappingFor($this->integration, $entity, $ticket);
        $mapping->local_model     = get_class($ticket);
        $mapping->local_reference = $this->referenceNo($ticket);

        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $ticket);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $isIncome = strcasecmp((string) optional($ticket->booking)->transaction_type, 'income') === 0;
        $type     = $isIncome
            ? (string) config('sageintacct.jobcard.standard_type', 'Job Card Invoice')
            : (string) config('sageintacct.jobcard.internal_type', 'Internal Job Card');

        $customerId = $this->resolveCustomer($ticket, $isIncome);
        if (! $customerId) {
            return $this->fail($mapping, $entity, $ticket, 'No Sage customer could be resolved for the job card.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $lines = $this->buildLines($ticket);
        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity, $ticket);
        }

        $currency = optional($ticket->booking)->currency_id ? optional(optional($ticket->booking)->currency)->code : null;

        $header = [
            'transactiontype' => $type,
            'datecreated'     => optional($ticket->created_at)->toDateString() ?: now()->toDateString(),
            'datedue'         => optional($ticket->created_at)->toDateString() ?: now()->toDateString(),
            'customerid'      => $customerId,
            'referenceno'     => $this->referenceNo($ticket),
            'currency'        => $currency,
            'exchratetype'    => $currency ? (config('sageintacct.purchasing.exchange_rate_type') ?: null) : null,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
        ];

        $res = $this->driver->createSalesTransaction($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $ticket);
    }

    /** Close off the job card (converts it) when the ticket is closed. */
    public function closeOffJobCard(Ticket $ticket): array
    {
        return $this->convertJobCard(
            $ticket,
            'job_card_closeoff',
            (string) config('sageintacct.jobcard.closeoff_type', 'Job-Card-Close Off'),
            'CO-'
        );
    }

    /** Reverse an internal job card when its booking is rejected/cancelled. */
    public function reverseJobCard(Ticket $ticket): array
    {
        $entity  = 'job_card_reversal';
        $mapping = $this->mappingFor($this->integration, $entity, $ticket);

        // Reversal only applies to internal (expense) job cards — the standard
        // (income) card has no reversal definition in Sage.
        $isIncome = strcasecmp((string) optional($ticket->booking)->transaction_type, 'income') === 0;
        if ($isIncome) {
            $mapping->local_model = get_class($ticket);
            return $this->fail($mapping, $entity, $ticket, 'Standard job cards have no reversal definition in Sage; reverse via a credit note instead.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        return $this->convertJobCard(
            $ticket,
            $entity,
            (string) config('sageintacct.jobcard.reversal_internal_type', 'Reversal_Internal Job Card'),
            'REV-'
        );
    }

    /**
     * Shared close-off / reversal: converts the source job card by referencing
     * each of its Sage lines via <sourcelinekey>. Requires the job card to exist
     * in Sage (mapping job_card).
     */
    protected function convertJobCard(Ticket $ticket, string $entity, string $transactionType, string $refPrefix): array
    {
        $mapping = $this->mappingFor($this->integration, $entity, $ticket);
        $mapping->local_model     = get_class($ticket);
        $mapping->local_reference = $refPrefix . ($ticket->ticket_number ?: $ticket->id);

        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $ticket);
        }

        $sourceDocId = $this->mappingExternalId('job_card', $ticket->id);
        if (! $sourceDocId) {
            return $this->fail($mapping, $entity, $ticket, 'The job card is not synced to Sage yet — nothing to ' . trim($refPrefix, '-') . '.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $sourceLines = $this->sourceJobCardLines($sourceDocId);
        if (empty($sourceLines)) {
            return $this->result(true, 'skipped', null, null, $entity, $ticket);
        }

        $isIncome   = strcasecmp((string) optional($ticket->booking)->transaction_type, 'income') === 0;
        $customerId = $this->resolveCustomer($ticket, $isIncome);
        if (! $customerId) {
            return $this->fail($mapping, $entity, $ticket, 'No Sage customer could be resolved for the job-card conversion.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $lines = [];
        foreach ($sourceLines as $sl) {
            $lines[] = [
                'itemid'        => $sl['ITEMID'],
                'quantity'      => $this->qty($sl['QUANTITY'] ?? 1),
                'unit'          => (string) config('sageintacct.jobcard.line_unit', 'Each'),
                'price'         => number_format((float) ($sl['PRICE'] ?? 0), 2, '.', ''),
                'sourcelinekey' => $sl['RECORDNO'],
                'locationid'    => config('sageintacct.project.location_id'),
                'departmentid'  => config('sageintacct.project.department_id'),
            ];
        }

        $header = [
            'transactiontype' => $transactionType,
            'datecreated'     => now()->toDateString(),
            'datedue'         => now()->toDateString(),
            'customerid'      => $customerId,
            'referenceno'     => $mapping->local_reference,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
        ];

        $res = $this->driver->createSalesTransaction($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $ticket);
    }

    /** Read the source job card's Sage lines → [ITEMID, RECORDNO, QUANTITY, PRICE]. */
    protected function sourceJobCardLines(string $docId): array
    {
        $safe = str_replace("'", '', $docId);
        $res  = $this->driver->readByQuery('SODOCUMENTENTRY', ['ITEMID', 'RECORDNO', 'QUANTITY', 'PRICE'], "DOCID = '{$safe}'", 200);

        $lines = [];
        foreach ($res['data'] ?? [] as $row) {
            if (! empty($row['ITEMID']) && ! empty($row['RECORDNO'])) {
                $lines[] = $row;
            }
        }

        return $lines;
    }

    protected function mappingExternalId(string $entityType, $localId): ?string
    {
        $m = IntegrationMapping::where([
            'company_integration_id' => $this->integration->id,
            'entity_type'            => $entityType,
            'local_id'               => $localId,
        ])->first();

        return $m && $m->external_id ? $m->external_id : null;
    }

    /** Parts lines from ticket_inventories; else one labour line for the total. */
    protected function buildLines(Ticket $ticket): array
    {
        $lines = [];

        foreach ($ticket->ticket_inventories as $ti) {
            if (! $ti->product) {
                continue;
            }
            $item = $this->itemService->ensureProduct($ti->product);
            if (empty($item['success']) || empty($item['external_id'])) {
                continue;
            }
            $lines[] = [
                'itemid'       => $item['external_id'],
                'quantity'     => $this->qty($ti->qty),
                'unit'         => (string) config('sageintacct.jobcard.line_unit', 'Each'),
                'price'        => $this->unitPrice($ti->amount, $ti->qty),
                'locationid'   => config('sageintacct.project.location_id'),
                'departmentid' => config('sageintacct.project.department_id'),
                'memo'         => optional($ti->product)->name,
            ];
        }

        // No part lines → a single labour line carrying the booking total.
        if (empty($lines)) {
            $total = (float) (optional($ticket->booking)->total ?: 0);
            if ($total <= 0) {
                return [];
            }
            $labour = $this->itemService->ensureNamedItem(
                (string) config('sageintacct.jobcard.labour_item_name', 'Workshop Labour'),
                null,
                config('sageintacct.item.tax_group') ?: null
            );
            if (empty($labour['success'])) {
                return [];
            }
            $lines[] = [
                'itemid'       => $labour['external_id'],
                'quantity'     => 1,
                'unit'         => (string) config('sageintacct.jobcard.line_unit', 'Each'),
                'price'        => number_format($total, 2, '.', ''),
                'locationid'   => config('sageintacct.project.location_id'),
                'departmentid' => config('sageintacct.project.department_id'),
                'memo'         => 'Workshop job card ' . ($ticket->ticket_number ?: $ticket->id),
            ];
        }

        return $lines;
    }

    /**
     * Sage CUSTOMERID: the serviced unit's transporter matched to a Sage customer
     * by name, else the configured default for the type.
     */
    protected function resolveCustomer(Ticket $ticket, bool $isIncome): ?string
    {
        $transporter = optional($ticket->horse)->transporter
            ?? optional($ticket->trailer)->transporter
            ?? optional($ticket->vehicle)->transporter;

        if ($transporter && $transporter->name && ($cid = $this->findCustomerIdByName($transporter->name))) {
            return $cid;
        }

        return $isIncome
            ? ((string) config('sageintacct.jobcard.standard_customer', '') ?: null)
            : ((string) config('sageintacct.jobcard.internal_customer', '') ?: null);
    }

    protected function findCustomerIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('CUSTOMER', ['CUSTOMERID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['CUSTOMERID'])) ? $res['data'][0]['CUSTOMERID'] : null;
    }

    protected function referenceNo(Ticket $ticket): string
    {
        return mb_substr('JC-' . ($ticket->ticket_number ?: $ticket->id), 0, 100);
    }

    protected function qty($qty): float
    {
        $q = (float) ($qty ?: 0);

        return $q > 0 ? $q : 1;
    }

    protected function unitPrice($total, $qty): string
    {
        $q = (float) ($qty ?: 0);
        $u = $q > 0 ? ((float) ($total ?: 0) / $q) : (float) ($total ?: 0);

        return number_format($u, 2, '.', '');
    }

    protected function fail(IntegrationMapping $mapping, string $entity, $model, string $message, string $status): array
    {
        $status === IntegrationMapping::STATUS_REQUIRES_ATTENTION
            ? $mapping->markRequiresAttention($message)
            : $mapping->markFailed($message);

        return $this->result(false, $status, null, $message, $entity, $model);
    }

    protected function result(bool $success, string $status, ?string $externalId, ?string $error, string $entity, $model): array
    {
        return [
            'success'     => $success,
            'status'      => $status,
            'action'      => 'create',
            'entity'      => $entity,
            'model'       => $model,
            'external_id' => $externalId,
            'error'       => $error,
        ];
    }
}
