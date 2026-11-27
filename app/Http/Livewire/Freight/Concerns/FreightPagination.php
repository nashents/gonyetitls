<?php

namespace App\Http\Livewire\Freight\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

/**
 * Freight Forwarding lists: 10 rows per page, Bootstrap links wrapped in the
 * system-standard markup (see resources/views/livewire/freight/partials/pagination.blade.php).
 *
 * - Query lists:  ->paginate(self::FREIGHT_PER_PAGE, ['*'], 'somePage')
 * - Array/collection rows (reports, computed lists): $this->paginateRows($rows, 'somePage')
 *
 * A component with more than one list gives each its own page name and
 * declares it as a public property (e.g. public $costsPage = 1;) so Livewire
 * never creates a dynamic property.
 */
trait FreightPagination
{
    use WithPagination;

    protected static int $freightPerPage = 10;

    public function paginationView()
    {
        return 'livewire::bootstrap';
    }

    public function paginationSimpleView()
    {
        return 'livewire::simple-bootstrap';
    }

    /**
     * Paginate an in-memory list (array or collection) - totals elsewhere stay
     * computed from the full list. Clamps to the last page when rows shrink.
     */
    protected function paginateRows($rows, string $pageName = 'page'): LengthAwarePaginator
    {
        $items = collect($rows)->values();
        $perPage = static::$freightPerPage;
        $lastPage = max(1, (int) ceil($items->count() / $perPage));
        $page = min(max(1, (int) ($this->paginators[$pageName] ?? 1)), $lastPage);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['pageName' => $pageName, 'path' => url()->current()]
        );
    }
}
