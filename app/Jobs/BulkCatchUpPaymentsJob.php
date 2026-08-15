<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\BulkCatchUpCompleted;
use App\Services\Accounting\BulkCatchUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BulkCatchUpPaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(
        protected array $params,
        protected int $actorUserId
    ) {
    }

    public function handle(BulkCatchUpService $service): void
    {
        $report = $service->run($this->params, $this->actorUserId);

        $user = User::find($this->actorUserId);
        if ($user) {
            $user->notify(new BulkCatchUpCompleted($this->params, $report));
        }
    }
}
