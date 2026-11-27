<?php

namespace App\Http\Livewire\Freight\Concerns;

/**
 * Pagination for lists embedded in a page that also holds forms/modals (job
 * Show tabs, Costing, Consolidation Show). The page number lives in component
 * state only - NOT in the URL query string - because Livewire 2 query-string
 * history replays stale payloads on Back/Forward and corrupts form components
 * (see memory: livewire-querystring-corrupt-payload).
 */
trait FreightEmbeddedPagination
{
    use FreightPagination;

    public function queryStringWithPagination()
    {
        return [];
    }

    public function resolvePage()
    {
        return $this->page;
    }
}
