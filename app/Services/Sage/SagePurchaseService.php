<?php

namespace App\Services\Sage;

use App\Integrations\Contracts\SageDriver;
use App\Models\CompanyIntegration;
use App\Models\GoodsReceived;
use App\Models\IntegrationMapping;
use App\Models\Purchase;
use App\Services\Sage\Concerns\ManagesMappings;
use App\Services\SageIntacctService;

/**
 * Purchasing documents:
 *   • Gonyeti Purchase (PO)     → Sage "Purchase order"
 *   • Gonyeti GoodsReceived     → Sage "Receipt" (Shipping Receipt), created by
 *                                 CONVERTING the PO — each receipt line references
 *                                 its PO line via <sourcelinekey>.
 *
 * Both are entity-scoped. Idempotent per record (mapping entity_type
 * purchase_order / goods_receipt, local_id = the model id).
 *
 * NOTE: the Receipt definition affects GL, so a received ITEM must have AP GL
 * accounts configured in Sage — items without them are flagged for attention.
 */
class SagePurchaseService
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

    // ── Purchase → Sage Purchase Order ───────────────────────────

    public function syncPurchase(Purchase $po): array
    {
        $entity  = 'purchase_order';
        $mapping = $this->mappingFor($this->integration, $entity, $po);
        $mapping->local_model     = get_class($po);
        $mapping->local_reference = $this->purchaseRef($po);

        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $po);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $vendor = $po->vendor;
        if (! $vendor) {
            return $this->fail($mapping, $entity, $po, 'Purchase has no vendor.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        $vendorSageId = $this->ensureVendor($vendor, $po->company_id ?? optional($po->user)->company_id);
        if (! $vendorSageId) {
            return $this->fail($mapping, $entity, $po, "Vendor '{$vendor->name}' is not synced to Sage yet.", IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }
        $vendorContact = $this->vendorContactName($vendorSageId) ?: $vendor->name;

        $lines = [];
        foreach ($po->purchase_products as $pp) {
            if (! $pp->product) {
                continue;
            }
            $item = $this->itemService->ensureProduct($pp->product);
            if (empty($item['success']) || empty($item['external_id'])) {
                return $this->fail($mapping, $entity, $po, 'Item sync failed: ' . ($item['error'] ?? 'unknown'), IntegrationMapping::STATUS_FAILED);
            }
            $lines[] = [
                'itemid'       => $item['external_id'],
                'itemdesc'     => optional($pp->product)->name ?: 'Purchase item',
                'quantity'     => $this->qty($pp->qty),
                'unit'         => (string) config('sageintacct.purchasing.line_unit', 'Each'),
                'price'        => $this->unitPrice($pp->subtotal ?: $pp->amount, $pp->qty),
                'locationid'   => config('sageintacct.project.location_id'),
                'departmentid' => config('sageintacct.project.department_id'),
            ];
        }

        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity, $po);
        }

        $header = $this->header((string) config('sageintacct.purchasing.purchase_order_type', 'Purchase order'), $po->date, $vendorSageId, $this->purchaseRef($po), $vendorContact, optional($po->currency)->code);
        $res    = $this->driver->createRequisition($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $po);
    }

    // ── GoodsReceived → Sage Receipt (converts the PO) ───────────

    public function syncGoodsReceipt(GoodsReceived $gr): array
    {
        $entity  = 'goods_receipt';
        $mapping = $this->mappingFor($this->integration, $entity, $gr);
        $mapping->local_model     = get_class($gr);
        $mapping->local_reference = $this->receiptRef($gr);

        if ($mapping->exists && $mapping->external_id) {
            return $this->result(true, 'skipped', $mapping->external_id, null, $entity, $gr);
        }

        $mapping->last_attempted_at = now();
        if (! $mapping->exists) {
            $mapping->save();
        }

        $purchase = $gr->purchase;
        if (! $purchase) {
            return $this->fail($mapping, $entity, $gr, 'Goods received is not linked to a purchase order.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        // The PO must exist in Sage first — sync it once if needed.
        $poDocId = $this->mappingExternalId('purchase_order', $purchase->id);
        if (! $poDocId) {
            $this->syncPurchase($purchase);
            $poDocId = $this->mappingExternalId('purchase_order', $purchase->id);
        }
        if (! $poDocId) {
            return $this->fail($mapping, $entity, $gr, 'The linked purchase order is not synced to Sage yet.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }

        // Map each PO line's Sage ITEM → its line record key (the <sourcelinekey>).
        $poLines = $this->poLineKeysByItem($poDocId);

        $vendor       = $gr->vendor ?: $purchase->vendor;
        $vendorSageId = $vendor ? $this->ensureVendor($vendor, $gr->company_id ?? optional($gr->user)->company_id) : null;
        if (! $vendorSageId) {
            return $this->fail($mapping, $entity, $gr, 'Could not resolve the vendor for the goods receipt.', IntegrationMapping::STATUS_REQUIRES_ATTENTION);
        }
        $vendorContact = $this->vendorContactName($vendorSageId) ?: optional($vendor)->name;

        // Received lines = the inventories linked to this goods received.
        $lines = [];
        foreach ($gr->inventories as $inv) {
            if (! $inv->product) {
                continue;
            }
            $item = $this->itemService->ensureProduct($inv->product);
            if (empty($item['success']) || empty($item['external_id'])) {
                return $this->fail($mapping, $entity, $gr, 'Item sync failed: ' . ($item['error'] ?? 'unknown'), IntegrationMapping::STATUS_FAILED);
            }
            $itemId  = $item['external_id'];
            $lineKey = $this->takeLineKey($poLines, $itemId);
            if (! $lineKey) {
                // No matching open PO line to receive against — needs a human.
                return $this->fail($mapping, $entity, $gr, "No matching purchase-order line for item '{$itemId}' to receive against.", IntegrationMapping::STATUS_REQUIRES_ATTENTION);
            }

            $lines[] = [
                'itemid'        => $itemId,
                'itemdesc'      => optional($inv->product)->name ?: 'Received item',
                'quantity'      => $this->qty($inv->qty),
                'unit'          => (string) config('sageintacct.purchasing.line_unit', 'Each'),
                'price'         => $this->unitPrice($inv->amount, $inv->qty),
                'sourcelinekey' => $lineKey,
                'locationid'    => config('sageintacct.project.location_id'),
                'departmentid'  => config('sageintacct.project.department_id'),
            ];
        }

        if (empty($lines)) {
            return $this->result(true, 'skipped', null, null, $entity, $gr);
        }

        $header = $this->header((string) config('sageintacct.purchasing.receipt_type', 'Receipt'), $gr->delivery_date ?: $gr->date, $vendorSageId, $this->receiptRef($gr), $vendorContact, optional($purchase->currency)->code);
        $res    = $this->driver->createRequisition($header, $lines);

        return $this->finishSync($mapping, $res, $header['referenceno'], 'create', $entity, $gr);
    }

    // ── Shared helpers ───────────────────────────────────────────

    /** Common entity-scoped create_potransaction header. */
    protected function header(string $type, $date, string $vendorSageId, string $ref, string $contact, ?string $currency): array
    {
        return [
            'transactiontype' => $type,
            'datecreated'     => $date ?: now()->toDateString(),
            'datedue'         => $date ?: now()->toDateString(),
            'vendorid'        => $vendorSageId,
            'referenceno'     => $ref,
            'contactname'     => $contact,
            'currency'        => $currency ?: null,
            'exchratetype'    => config('sageintacct.purchasing.exchange_rate_type') ?: null,
            'entityid'        => config('sageintacct.purchasing.entity_id') ?: null,
        ];
    }

    /** Ensure the vendor is in Sage; returns its VENDORID. */
    protected function ensureVendor($vendor, $companyId): ?string
    {
        $sageId = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        if (! $sageId) {
            // Fall back to this integration's company so syncVendor resolves it.
            if (! $vendor->company_id) {
                $vendor->company_id = $companyId ?: $this->integration->company_id;
            }
            app(SageIntacctService::class)->syncVendor($vendor);
            $vendor->refresh();
            $sageId = $vendor->sage_intacct_id ?: $vendor->custom_ref;
        }

        return $sageId ?: null;
    }

    /** Read a PO's lines → [ITEMID => [lineRecordNo, …]] (in line order). */
    protected function poLineKeysByItem(string $poDocId): array
    {
        $safe = str_replace("'", '', $poDocId);
        $res  = $this->driver->readByQuery('PODOCUMENTENTRY', ['ITEMID', 'RECORDNO'], "DOCID = '{$safe}'", 200);

        $map = [];
        foreach ($res['data'] ?? [] as $row) {
            $itemId = $row['ITEMID'] ?? null;
            $rec    = $row['RECORDNO'] ?? null;
            if ($itemId && $rec) {
                $map[$itemId][] = $rec;
            }
        }

        return $map;
    }

    /** Pop the next open PO line key for an item (handles repeated items). */
    protected function takeLineKey(array &$poLines, string $itemId): ?string
    {
        if (empty($poLines[$itemId])) {
            return null;
        }

        return array_shift($poLines[$itemId]);
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

    protected function vendorContactName(string $vendorSageId): ?string
    {
        $safe = str_replace("'", '', $vendorSageId);
        $res  = $this->driver->readByQuery('VENDOR', ['VENDORID', 'DISPLAYCONTACT.CONTACTNAME'], "VENDORID = '{$safe}'", 1);

        return (! empty($res['success']) && ! empty($res['data'][0]['DISPLAYCONTACT.CONTACTNAME']))
            ? $res['data'][0]['DISPLAYCONTACT.CONTACTNAME']
            : null;
    }

    protected function purchaseRef(Purchase $po): string
    {
        return mb_substr('PO-' . ($po->purchase_number ?: $po->id), 0, 100);
    }

    protected function receiptRef(GoodsReceived $gr): string
    {
        return mb_substr('GRV-' . ($gr->goods_received_number ?: $gr->id), 0, 100);
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
