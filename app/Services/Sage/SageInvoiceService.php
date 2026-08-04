<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\Invoice;
use App\Models\IntegrationMapping;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\SageIntacctService;

/**
 * Gonyeti Invoice → Sage Order-Entry sales document, pushed on approval.
 *
 *   invoice to a Customer    → "OE sales invoice"  (customer = the customer)
 *   invoice to a Transporter → "Job Card Invoice"  (customer = the transporter,
 *                                                    matched/created as a Sage customer)
 *
 * Lines = the invoice's own items (product → Sage item, qty, ex-tax price).
 * Idempotent: mapping entity_type="sales_invoice", local_id=invoice_id.
 */
class SageInvoiceService
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

    public function syncInvoice(Invoice $invoice): array
    {
        $entity  = 'sales_invoice';
        $mapping = $this->mappingFor($this->integration, $entity, $invoice);
        $mapping->local_model     = get_class($invoice);
        $mapping->local_reference = $this->referenceNo($invoice);

        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $invoice);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $isTransporter = ! empty($invoice->transporter_id);
        $type          = $isTransporter
            ? (string) config('sageintacct.invoice.jobcard_type', 'Job Card Invoice')
            : (string) config('sageintacct.invoice.oe_type', 'OE sales invoice');

        $customerId = $isTransporter
            ? $this->resolveTransporterCustomer($invoice)
            : $this->resolveCustomer($invoice);
        if (! $customerId) {
            return $this->fail($mapping, $entity, $invoice, 'Could not resolve the Sage customer for the invoice.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $lines = $this->buildLines($invoice);
        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity, $invoice);
        }

        $currency = optional($invoice->currency)->code;

        $header = [
            'transactiontype' => $type,
            'datecreated'     => $this->invoiceDate($invoice),
            'datedue'         => $this->invoiceDate($invoice),
            'customerid'      => $customerId,
            'referenceno'     => $this->referenceNo($invoice),
            'currency'        => $currency,
            'exchratetype'    => $currency ? (config('sageintacct.purchasing.exchange_rate_type') ?: null) : null,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
        ];

        $res = $this->driver->createSalesTransaction($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $invoice);
    }

    /** One Sage line per invoice item (product → item, qty, ex-tax unit price). */
    protected function buildLines(Invoice $invoice): array
    {
        $lines = [];
        foreach ($invoice->invoice_items as $item) {
            $itemId = null;
            if ($item->product) {
                $res    = $this->itemService->ensureProduct($item->product);
                $itemId = $res['external_id'] ?? null;
            }
            if (! $itemId) {
                // Custom / transport-order freight line with no product → a shared
                // fallback service item.
                $res    = $this->itemService->ensureNamedItem(
                    (string) config('sageintacct.invoice.default_item_name', 'Transportation'),
                    null,
                    config('sageintacct.item.tax_group') ?: null
                );
                $itemId = $res['external_id'] ?? null;
            }
            if (! $itemId) {
                continue;
            }

            $lines[] = [
                'itemid'       => $itemId,
                'quantity'     => $this->qty($item->qty),
                'unit'         => (string) config('sageintacct.invoice.line_unit', 'Each'),
                'price'        => $this->unitPrice($item->subtotal ?: $item->amount, $item->qty),
                'locationid'   => config('sageintacct.project.location_id'),
                'departmentid' => config('sageintacct.project.department_id'),
                'memo'         => $item->description ?: optional($item->product)->name,
            ];
        }

        return $lines;
    }

    /** Ensure the invoice's Customer is in Sage; returns its CUSTOMERID. */
    protected function resolveCustomer(Invoice $invoice): ?string
    {
        $customer = $invoice->customer;
        if (! $customer) {
            return null;
        }

        $sageId = $customer->sage_intacct_id ?: $customer->custom_ref;
        if (! $sageId) {
            if (! $customer->company_id) {
                $customer->company_id = $invoice->company_id ?: $this->integration->company_id;
            }
            app(SageIntacctService::class)->syncCustomer($customer);
            $customer->refresh();
            $sageId = $customer->sage_intacct_id ?: $customer->custom_ref;
        }

        return $sageId ?: null;
    }

    /**
     * The transporter as a Sage CUSTOMER: existing customer of the same name,
     * else created. Cached per transporter in integration_mappings.
     */
    protected function resolveTransporterCustomer(Invoice $invoice): ?string
    {
        $transporter = $invoice->transporter;
        if (! $transporter) {
            return null;
        }

        $mapping = $this->mappingFor($this->integration, 'transporter_customer', $transporter);
        if ($mapping->exists && $mapping->external_id) {
            return $mapping->external_id;
        }

        $name       = trim((string) $transporter->name);
        $customerId = $name !== '' ? $this->findCustomerIdByName($name) : null;

        if (! $customerId && $name !== '') {
            $id  = (string) config('sageintacct.invoice.transporter_customer_prefix', 'TCUS-') . $transporter->id;
            $res = $this->driver->createCustomer([
                'id'       => $id,
                'name'     => $name,
                'taxgroup' => config('sageintacct.customer.tax_group') ?: null,
                'currency' => optional($invoice->currency)->code ?: null,
                'status'   => 'active',
            ]);
            if (! empty($res['success']) || str_contains(strtolower((string) ($res['error'] ?? '')), 'already exists')) {
                $customerId = ($res['data']['id'] ?? null) ?: $id;
            }
        }

        if ($customerId) {
            $mapping->local_model = get_class($transporter);
            $mapping->markSynced($customerId, $name);
        }

        return $customerId;
    }

    protected function findCustomerIdByName(string $name): ?string
    {
        $safe = str_replace("'", '', $name);
        $res  = $this->driver->readByQuery('CUSTOMER', ['CUSTOMERID', 'NAME'], "NAME = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['CUSTOMERID'])) ? $res['data'][0]['CUSTOMERID'] : null;
    }

    protected function referenceNo(Invoice $invoice): string
    {
        return mb_substr('INV-' . ($invoice->invoice_number ?: $invoice->number ?: $invoice->id), 0, 100);
    }

    protected function invoiceDate(Invoice $invoice): string
    {
        return optional($invoice->created_at)->toDateString() ?: now()->toDateString();
    }

    protected function qty($qty): float
    {
        $q = (float) ($qty ?: 0);

        return $q > 0 ? $q : 1;
    }

    /** Unit (ex-tax) price = line total / qty, to 2 dp. */
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
