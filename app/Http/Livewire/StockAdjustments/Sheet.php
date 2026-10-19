<?php

namespace App\Http\Livewire\StockAdjustments;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Sheet extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $adjustment_id;
    public $search;
    public $filter = 'all'; // all | not_counted | counted | variances
    public $counts = [];    // item id => counted qty ('' = not counted)
    public $comments;

    public function mount($id)
    {
        $this->adjustment_id = StockAdjustment::findOrFail($id)->id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    private function adjustment(): StockAdjustment
    {
        return StockAdjustment::with(['store', 'user', 'authorized_by', 'journal_entry'])->findOrFail($this->adjustment_id);
    }

    // Each count is saved as it's typed (lazy), so a long count survives a closed tab.
    public function updatedCounts($value, $itemId)
    {
        if (! $this->adjustment()->isEditable()) {
            return;
        }

        $value = is_string($value) ? trim($value) : $value;
        if ($value !== '' && $value !== null && (! is_numeric($value) || (float) $value < 0)) {
            $this->addError('counts.'.$itemId, 'Enter 0 or more.');
            return;
        }
        $this->resetErrorBag('counts.'.$itemId);

        StockAdjustmentItem::where('stock_adjustment_id', $this->adjustment_id)
            ->whereKey($itemId)
            ->update(['counted_qty' => ($value === '' || $value === null) ? null : (float) $value]);
    }

    public function submit(StockAdjustmentService $service)
    {
        $service->submit($this->adjustment());
        $this->alert('success', 'Stock count submitted for approval.');
    }

    public function reopen(StockAdjustmentService $service)
    {
        $service->reopen($this->adjustment());
        $this->alert('success', 'Stock count reopened for editing.');
    }

    public function approve(StockAdjustmentService $service)
    {
        $service->approve($this->adjustment(), $this->comments);
        $this->dispatchBrowserEvent('hide-saApproveModal');
        $this->alert('success', 'Stock count approved. Balances adjusted and posted to Stock Write-off.');
    }

    public function reject(StockAdjustmentService $service)
    {
        $service->reject($this->adjustment(), $this->comments);
        $this->dispatchBrowserEvent('hide-saApproveModal');
        $this->alert('success', 'Stock count rejected. No stock or ledger changes were made.');
    }

    public function delete()
    {
        $adjustment = $this->adjustment();
        if (! in_array($adjustment->status, [StockAdjustment::DRAFT, StockAdjustment::PENDING], true)) {
            $this->addError('adjustment', 'Approved or rejected counts are kept as a record and cannot be deleted.');
            return;
        }

        $adjustment->delete();

        return redirect()->route('stock_adjustments.index');
    }

    private function alert(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('alert', ['type' => $type, 'message' => $message]);
    }

    public function render(StockAdjustmentService $service)
    {
        $adjustment = $this->adjustment();

        $items = StockAdjustmentItem::where('stock_adjustment_id', $adjustment->id)
            ->with(['inventory' => fn ($q) => $q->withReservedQty(), 'product.brand', 'store'])
            ->when($this->filter === 'not_counted', fn ($q) => $q->whereNull('counted_qty'))
            ->when($this->filter === 'counted', fn ($q) => $q->whereNotNull('counted_qty'))
            ->when($this->filter === 'variances', fn ($q) => $q->withVariance())
            ->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($qq) => $qq->whereHas('inventory', fn ($i) => $i->withTrashed()->where('inventory_number', 'like', $term))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)));
            })
            ->orderBy('id')
            ->paginate(50);

        foreach ($items as $item) {
            if (! array_key_exists($item->id, $this->counts)) {
                $this->counts[$item->id] = $item->isCounted() ? (float) $item->counted_qty : '';
            }
        }

        $totals = StockAdjustmentItem::where('stock_adjustment_id', $adjustment->id)
            ->selectRaw('COUNT(*) AS items')
            ->selectRaw('SUM(counted_qty IS NOT NULL) AS counted')
            ->selectRaw('SUM(counted_qty IS NOT NULL AND counted_qty <> system_qty) AS variances')
            ->selectRaw('COALESCE(SUM(CASE WHEN counted_qty < system_qty THEN (system_qty - counted_qty) * unit_cost END), 0) AS shortage')
            ->selectRaw('COALESCE(SUM(CASE WHEN counted_qty > system_qty THEN (counted_qty - system_qty) * unit_cost END), 0) AS surplus')
            ->first();

        return view('livewire.stock-adjustments.sheet', [
            'adjustment' => $adjustment,
            'items' => $items,
            'totals' => $totals,
            'canApprove' => $service->canApprove(Auth::user(), $adjustment),
            'currency' => optional(optional(optional(Auth::user()->employee)->company)->currency)->symbol,
        ]);
    }
}
