<?php

namespace App\Http\Livewire\StockAdjustments;

use App\Models\StockAdjustment;
use App\Models\Store;
use App\Services\Inventory\StockAdjustmentService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $status_filter = '';

    public $date;
    public $store_id;
    public $description;

    protected $rules = [
        'date' => 'required|date',
        'store_id' => 'nullable|exists:stores,id',
        'description' => 'nullable|string|max:1000',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function showCreate()
    {
        $this->resetErrorBag();
        $this->date = now()->toDateString();
        $this->store_id = null;
        $this->description = null;
        $this->dispatchBrowserEvent('show-saCreateModal');
    }

    public function store(StockAdjustmentService $service)
    {
        $this->validate();

        $adjustment = $service->createCount($this->date, $this->store_id, $this->description);

        return redirect()->route('stock_adjustments.show', $adjustment->id);
    }

    public function render()
    {
        $adjustments = StockAdjustment::with(['store', 'user', 'authorized_by', 'journal_entry'])
            ->withCount([
                'items',
                'items as counted_count' => fn ($q) => $q->whereNotNull('counted_qty'),
                'items as variance_count' => fn ($q) => $q->withVariance(),
            ])
            ->when($this->status_filter, fn ($q) => $q->where('status', $this->status_filter))
            ->when(filled($this->search), function ($q) {
                $term = '%'.$this->search.'%';
                $q->where(fn ($qq) => $qq->where('adjustment_number', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhereHas('store', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.stock-adjustments.index', [
            'adjustments' => $adjustments,
            'stores' => Store::orderBy('name')->get(),
        ]);
    }
}
