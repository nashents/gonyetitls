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
        return view('livewire.bills.deleted', [
            'bills' => Bill::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10),
        ]);
    }
}
