<?php

namespace App\Http\Livewire\Sage;

use App\Models\CompanyIntegration;
use App\Models\IntegrationMapping;
use App\Models\IntegrationProvider;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageSyncService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Sage integration reconciliation / exception queue (Finance workflow §10, §7.3).
 * A single visible view of every operational document pushed to Sage — success,
 * outstanding, requires-attention and failed — so no interface failure stays
 * silent and Finance can reconcile Gonyeti source → Sage outcome end-to-end.
 */
class Reconciliation extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $entityFilter = '';
    public $statusFilter = '';

    protected $queryString = ['search', 'entityFilter', 'statusFilter'];

    /** Human labels for the mapping entity types shown in the queue. */
    public const LABELS = [
        'trip_project'           => 'Trip / Project',
        'horse_project'          => 'Horse Project',
        'horse_class'            => 'Horse Class',
        'trailer_class'          => 'Trailer Class',
        'transporter_project'    => 'Transporter Project',
        'job_card'               => 'Job Card',
        'job_card_closeoff'      => 'Job Card Close-off',
        'job_card_reversal'      => 'Job Card Reversal',
        'sales_invoice'          => 'Sales Invoice',
        'purchase_order'         => 'Purchase Order',
        'goods_receipt'          => 'Goods Receipt (GRV)',
        'trip_requisition'       => 'Purchase Requisition',
        'trip_dispatch'          => 'Dispatch Sheet',
        'fuel_pr_diesel'         => 'Fuel (PR - Diesel)',
        'driver_employee'        => 'Driver / Employee',
        'product_item'           => 'Product / Item',
        'store_warehouse'        => 'Store / Warehouse',
    ];

    /** Sage integration gate. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEntityFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    /** The active Sage integration for the acting user's company (scopes the queue). */
    protected function integrationId(): ?int
    {
        $companyId  = SageIntegration::companyIdForUser();
        $providerId = optional(IntegrationProvider::where('key', SageIntegration::PROVIDER_KEY)->first())->id;
        if (! $companyId || ! $providerId) {
            return null;
        }

        return optional(
            CompanyIntegration::where('company_id', $companyId)
                ->where('integration_provider_id', $providerId)
                ->where('status', 'active')
                ->first()
        )->id;
    }

    /** Re-attempt a single mapping's push through the normal sync path. */
    public function retry($mappingId)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $mapping = IntegrationMapping::find($mappingId);
        if (! $mapping || ! $mapping->local_model || ! $mapping->local_id) {
            return;
        }

        try {
            $model = $mapping->local_model::find($mapping->local_id);
            if (! $model) {
                $this->dispatchBrowserEvent('alert', ['type' => 'warning', 'message' => 'The source record no longer exists.']);
                return;
            }
            $result = app(SageSyncService::class)->retry($model);
            $ok     = ! empty($result['success']) && empty($result['error']);
            $this->dispatchBrowserEvent('alert', [
                'type'    => $ok ? 'success' : 'warning',
                'message' => $ok ? 'Re-sync attempted.' : ('Sage sync: ' . ($result['error'] ?? 'see status.')),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sage reconciliation retry failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Retry failed for this record.']);
        }
    }

    public function render()
    {
        $integrationId = $this->integrationId();

        $base = IntegrationMapping::query()
            ->when($integrationId, fn ($q) => $q->where('company_integration_id', $integrationId));

        // Summary across the whole scope (not the table filters).
        $summary = (clone $base)->selectRaw('sync_status, count(*) c')
            ->groupBy('sync_status')->pluck('c', 'sync_status')->all();

        $entities = (clone $base)->select('entity_type')->distinct()
            ->orderBy('entity_type')->pluck('entity_type');

        $rows = (clone $base)
            ->when($this->entityFilter !== '', fn ($q) => $q->where('entity_type', $this->entityFilter))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('sync_status', $this->statusFilter))
            ->when(filled($this->search), function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($w) use ($s) {
                    $w->where('local_reference', 'like', $s)
                        ->orWhere('external_id', 'like', $s)
                        ->orWhere('last_error', 'like', $s)
                        ->orWhere('entity_type', 'like', $s);
                });
            })
            // Exceptions first, then most-recently attempted.
            ->orderByRaw("FIELD(sync_status,'failed','requires_attention','pending','synced')")
            ->orderByDesc('last_attempted_at')
            ->paginate(20);

        return view('livewire.sage.reconciliation', [
            'summary'  => $summary,
            'entities' => $entities,
            'rows'     => $rows,
            'labels'   => self::LABELS,
        ]);
    }
}
