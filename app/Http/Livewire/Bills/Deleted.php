<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use App\Services\Accounting\BillRestorationService;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    public $bill_id;

    public function restore($id)
    {
        $this->bill_id = $id;
        $this->dispatchBrowserEvent('show-billRestoreModal');
    }

    public function update()
    {
        $bill = Bill::withTrashed()->findOrFail($this->bill_id);

        try {
            app(BillRestorationService::class)->restore($bill);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('hide-billRestoreModal');
            $this->dispatchBrowserEvent('alert', [
                'type' => 'danger',
                'message' => $e->getMessage(),
            ]);
            return;
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => 'Bill Restored Successfully!! Its account balances and journal entries have been reapplied.',
        ]);
        $this->dispatchBrowserEvent('hide-billRestoreModal');

        return redirect()->route('bills.index');
    }

    public function render()
    {
        $query = Bill::onlyTrashed()->with(['vendor', 'transporter', 'currency', 'user', 'deleted_by']);

        $search = trim((string) ($this->search ?? ''));
        if ($search !== '') {
            $term = "%{$search}%";
            $query->where(function ($q) use ($term) {
                $q->where('bill_number', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('bill_date', 'like', $term)
                    ->orWhereHas('vendor', fn ($qq) => $qq->where('name', 'like', $term))
                    ->orWhereHas('transporter', fn ($qq) => $qq->where('name', 'like', $term))
                    ->orWhereHas('currency', fn ($qq) => $qq->where('name', 'like', $term));
            });
        }

        return view('livewire.bills.deleted', [
            'bills' => $query->orderBy('deleted_at', 'desc')->paginate(10),
        ]);
    }
}
