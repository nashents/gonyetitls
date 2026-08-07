<?php

namespace App\Services\Sage;

use App\Models\CompanyIntegration;
use App\Models\Horse;
use App\Models\IntegrationLog;
use App\Models\Trailer;
use App\Models\Trip;
use App\Services\Sage\Concerns\ResolvesSageIntegration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Public entry point for fleet/trip → Sage sync (Classes & Projects).
 *
 * Called inline by the Livewire components for single records and by the queue
 * jobs for bulk. Resolves the company's active integration + driver, delegates
 * to SageClassService / SageProjectService, then writes an integration_logs
 * audit row. Company scope: trips use trip.company_id; horses/trailers use
 * their transporter's company, falling back to the acting user's company.
 */
class SageSyncService
{
    use ResolvesSageIntegration;

    public function syncHorse(Horse $horse): array
    {
        return $this->run($horse, 'horse');
    }

    public function syncTrailer(Trailer $trailer): array
    {
        return $this->run($trailer, 'trailer');
    }

    public function syncTrip(Trip $trip): array
    {
        return $this->run($trip, 'trip');
    }

    /** Driver → Sage Employee (via its Employee + Contact). */
    public function syncDriver(\App\Models\Driver $driver): array
    {
        return $this->run($driver, 'driver');
    }

    /** Product (products & services) → Sage ITEM. */
    public function syncProduct(\App\Models\Product $product): array
    {
        return $this->run($product, 'product');
    }

    /** Store → Sage WAREHOUSE. */
    public function syncStore(\App\Models\Store $store): array
    {
        return $this->run($store, 'store');
    }

    /** Fuel order → Sage "PR - Diesel" (supplier = fuelling station). */
    public function syncFuel(\App\Models\Fuel $fuel): array
    {
        return $this->run($fuel, 'fuel');
    }

    /** Purchase → Sage "Purchase order". */
    public function syncPurchase(\App\Models\Purchase $purchase): array
    {
        return $this->run($purchase, 'purchase');
    }

    /** Goods received → Sage "Receipt" (converts the PO). */
    public function syncGoodsReceived(\App\Models\GoodsReceived $goodsReceived): array
    {
        return $this->run($goodsReceived, 'goods_received');
    }

    /** Workshop Ticket → Sage job card (Internal Job Card / Job Card Invoice). */
    public function syncJobCard(\App\Models\Ticket $ticket): array
    {
        return $this->run($ticket, 'job_card');
    }

    /** Close off a job card (on ticket close) — converts the Sage job card. */
    public function closeOffJobCard(\App\Models\Ticket $ticket): array
    {
        return $this->run($ticket, 'job_card_closeoff');
    }

    /** Reverse an internal job card (on booking rejection/cancellation). */
    public function reverseJobCard(\App\Models\Ticket $ticket): array
    {
        return $this->run($ticket, 'job_card_reversal');
    }

    /** Invoice → Sage OE sales invoice (customer) / Job Card Invoice (transporter). */
    public function syncInvoice(\App\Models\Invoice $invoice): array
    {
        return $this->run($invoice, 'invoice');
    }

    /** Retry a previously-attempted record (idempotent — de-dup handles it). */
    public function retry(Model $model): array
    {
        if ($model instanceof Trip) {
            return $this->syncTrip($model);
        }
        if ($model instanceof Trailer) {
            return $this->syncTrailer($model);
        }
        if ($model instanceof \App\Models\Driver) {
            return $this->syncDriver($model);
        }
        return $this->syncHorse($model);
    }

    // ─────────────────────────────────────────────────────────────
    // CORE
    // ─────────────────────────────────────────────────────────────

    protected function run(Model $model, string $type): array
    {
        $integration = $this->activeSageIntegration($this->companyIdFor($model, $type));

        if (! $integration) {
            return ['success' => false, 'skipped' => true, 'error' => 'Sage Intacct integration is not active for this company.'];
        }

        try {
            $driver          = $this->sageDriverFor($integration);
            $classService    = new SageClassService($driver, $integration);
            $projectService  = new SageProjectService($driver, $integration, $classService);

            if ($type === 'trip') {
                $result = $projectService->syncTrip($model);
            } elseif ($type === 'trailer') {
                $result = $classService->syncTrailer($model);   // stays a green CLASS
            } elseif ($type === 'driver') {
                $result = (new SageEmployeeService($driver, $integration))->ensureForDriver($model);
            } elseif ($type === 'product') {
                $result = (new SageItemService($driver, $integration))->ensureProduct($model);
            } elseif ($type === 'store') {
                $result = (new SageWarehouseService($driver, $integration))->ensureStore($model);
            } elseif ($type === 'fuel') {
                $result = (new SageFuelDieselService($driver, $integration))->syncFuel($model);
            } elseif ($type === 'purchase') {
                $result = (new SagePurchaseService($driver, $integration))->syncPurchase($model);
            } elseif ($type === 'goods_received') {
                $result = (new SagePurchaseService($driver, $integration))->syncGoodsReceipt($model);
            } elseif ($type === 'job_card') {
                $result = (new SageJobCardService($driver, $integration))->syncJobCard($model);
            } elseif ($type === 'job_card_closeoff') {
                $result = (new SageJobCardService($driver, $integration))->closeOffJobCard($model);
            } elseif ($type === 'job_card_reversal') {
                $result = (new SageJobCardService($driver, $integration))->reverseJobCard($model);
            } elseif ($type === 'invoice') {
                $result = (new SageInvoiceService($driver, $integration))->syncInvoice($model);
            } else {
                // Horse → Transporter project + Horse project + Horse class (orange).
                $result = $projectService->ensureHorse($model);
            }
        } catch (Throwable $e) {
            Log::error("Sage sync {$type} #{$model->getKey()} threw: " . $e->getMessage());
            $result = [
                'success' => false, 'status' => 'failed', 'action' => 'sync', 'entity' => $type,
                'model' => $model, 'external_id' => null, 'request_reference' => null,
                'response_status' => null, 'error' => 'Internal error during Sage sync.',
            ];
        }

        $this->audit($integration, $result);

        return $result;
    }

    /** Company that owns the record → which Sage integration to push to. */
    protected function companyIdFor(Model $model, string $type): ?int
    {
        // Trips and invoices carry their own company_id.
        if ($type === 'trip' || $type === 'invoice') {
            return $model->company_id;
        }

        // Products and stores have no company_id — push to the acting user's company.
        if ($type === 'product' || $type === 'store') {
            $user = auth()->user();
            return optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
        }

        // Fuel has no company_id — use its creator's company, else the horse's
        // transporter, else the acting user's company.
        if ($type === 'fuel') {
            $companyId = optional(optional($model->user)->employee)->company_id
                ?? optional(optional($model->horse)->transporter)->company_id;
            if (! $companyId) {
                $user      = auth()->user();
                $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
            }
            return $companyId;
        }

        // Purchases / goods received / job cards have no company_id — use the
        // creator's company, else the acting user's.
        if (in_array($type, ['purchase', 'goods_received', 'job_card', 'job_card_closeoff', 'job_card_reversal'], true)) {
            $companyId = optional(optional($model->user)->employee)->company_id;
            if (! $companyId) {
                $user      = auth()->user();
                $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
            }
            return $companyId;
        }

        if ($type === 'driver') {
            // A driver has no company_id: use its Employee's company, else its
            // transporter's, else the acting user's.
            $companyId = optional($model->employee)->company_id
                ?? optional($model->transporter)->company_id;
        } else {
            // Horses/trailers have no company_id: use the transporter's company.
            $companyId = optional($model->transporter)->company_id;
        }

        if (! $companyId) {
            $user = auth()->user();
            $companyId = optional(optional($user)->employee)->company_id ?? optional($user)->company_id;
        }

        return $companyId;
    }

    /**
     * Write one audit row (no secrets) + update the integration's sync markers.
     * A failing audit never undoes the Sage call or the mapping already saved.
     */
    protected function audit(CompanyIntegration $integration, array $r): void
    {
        $model = $r['model'] ?? null;

        IntegrationLog::create([
            'company_integration_id' => $integration->id,
            'direction'              => 'outbound',
            'action'                 => $r['action'] ?? 'sync',
            'status'                 => ! empty($r['success']) ? 'ok' : 'fail',
            'message'                => $r['error'] ?? null,
            'meta'                   => [
                'entity'            => $r['entity'] ?? null,
                'model_type'        => $model ? get_class($model) : null,
                'model_id'          => $model ? $model->getKey() : null,
                'request_reference' => $r['request_reference'] ?? null,
                'sync_status'       => $r['status'] ?? null,
                'response_status'   => $r['response_status'] ?? null,
                'synced_at'         => now()->toIso8601String(),
            ],
        ]);

        $integration->forceFill(
            ! empty($r['success'])
                ? ['last_sync_at' => now(), 'last_success_at' => now(), 'last_error' => null]
                : ['last_error' => $r['error'] ?? null]
        )->save();
    }
}
