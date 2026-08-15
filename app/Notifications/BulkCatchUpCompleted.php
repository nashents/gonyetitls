<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BulkCatchUpCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $params,
        protected array $report
    ) {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title'    => 'Bulk catch-up payments finished',
            'from'     => $this->params['from'] ?? null,
            'until'    => $this->params['until'] ?? null,
            'invoices' => $this->summarize($this->report['invoices'] ?? null),
            'bills'    => $this->summarize($this->report['bills'] ?? null),
        ];
    }

    protected function summarize(?array $result): ?array
    {
        if (!$result) {
            return null;
        }

        return [
            'total'    => $result['total'],
            'settled'  => count($result['settled']),
            'approved' => count($result['approved']),
            'skipped'  => count($result['skipped']),
            'errors'   => count($result['errors']),
        ];
    }
}
