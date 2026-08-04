<?php

namespace App\Jobs\Sage;

use App\Services\Sage\Concerns\ResolvesSageIntegration;
use App\Services\Sage\SagePullService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queued pull of one entity type (customer|vendor|horse|trailer|transporter|
 * driver|tax|product) from Sage into Gonyeti for a company. Runs with no Auth,
 * so company + creator are carried explicitly.
 */
class PullFromSageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ResolvesSageIntegration;

    public int $tries = 2;
    public array $backoff = [60, 300];
    public int $timeout = 600;

    protected string $entity;
    protected int $companyId;
    protected int $creatorId;
    protected array $options;

    public function __construct(string $entity, int $companyId, int $creatorId, array $options = [])
    {
        $this->entity    = $entity;
        $this->companyId = $companyId;
        $this->creatorId = $creatorId;
        $this->options   = $options;
    }

    public function handle(): void
    {
        $integration = $this->activeSageIntegration($this->companyId);
        if (! $integration) {
            return; // no active Sage integration for this company → no-op
        }

        $driver = $this->sageDriverFor($integration);

        (new SagePullService($driver, $integration, $this->companyId, $this->creatorId))
            ->pull($this->entity, $this->options);
    }
}
