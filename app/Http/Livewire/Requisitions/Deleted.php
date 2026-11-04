<?php

namespace App\Http\Livewire\Requisitions;

use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Super Admin only: soft-deleted requisitions, both PO (purchase) and
 * payment requisitions, with a Restore action. Restoring brings back the
 * requisition and the items deleted with it; it posts nothing.
 */
class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $type_filter = '';
    public $requisition_id;

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);
    }

    public function restore($id)
    {
        $this->requisition_id = $id;
        $this->dispatchBrowserEvent('show-requisitionRestoreModal');
    }

    public function update()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $requisition = Requisition::onlyTrashed()->findOrFail($this->requisition_id);

        DB::transaction(function () use ($requisition) {
            // RequisitionController::destroy deletes the items just before the
            // requisition. Only bring those back — items removed earlier by an
            // edit stay deleted.
            $deletedAt = $requisition->deleted_at;
            $requisition->requisition_items()->onlyTrashed()
                ->whereBetween('deleted_at', [$deletedAt->copy()->subMinute(), $deletedAt->copy()->addMinute()])
                ->get()
                ->each->restore();

            $requisition->restore();
        });

        $this->dispatchBrowserEvent('hide-requisitionRestoreModal');
        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Requisition {$requisition->requisition_number} Restored Successfully",
        ]);
        $this->requisition_id = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $query = Requisition::onlyTrashed()->with([
            'user', 'employee', 'department', 'currency', 'trip', 'booking',
            // Items are soft-deleted along with their requisition
            'requisition_items' => fn ($q) => $q->withTrashed(),
            'requisition_items.expense', 'requisition_items.allowance', 'requisition_items.currency', 'requisition_items.product.brand',
            'audits' => fn ($q) => $q->where('event', 'deleted')->latest()->with('user'),
        ]);

        if ($this->type_filter === 'po_requisition') {
            $query->where('type', 'po_requisition');
        } elseif ($this->type_filter === 'payment_requisition') {
            // Legacy rows have a null type and display as payment requisitions
            $query->where(fn ($q) => $q->where('type', 'payment_requisition')->orWhereNull('type'));
        }

        $search = trim((string) ($this->search ?? ''));
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like, $search) {
                $q->where('requisition_number', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('date', 'like', $like)
                    ->orWhere('total', 'like', $like)
                    ->orWhereHas('trip', fn ($qq) => $qq->where('trip_number', 'like', $like))
                    ->orWhereHas('department', fn ($qq) => $qq->where('name', 'like', $like))
                    ->orWhereHas('employee', fn ($qq) => $qq->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%"))
                    ->orWhereHas('currency', fn ($qq) => $qq->where('name', 'like', $like));
            });
        }

        return view('livewire.requisitions.deleted', [
            'requisitions' => $query->orderBy('deleted_at', 'desc')->orderBy('id', 'desc')->paginate(10),
        ]);
    }
}
