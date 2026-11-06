<?php

namespace App\Http\Livewire\Requisitions;

use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Soft-deleted requisitions, both PO (purchase) and payment requisitions.
 * Who sees what: Requisition::scopeDeletedVisibleTo (own / headed
 * departments / Super Admin all). Restore is Super Admin only; it brings back
 * the requisition and the items deleted with it, and posts nothing.
 */
class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    public $type_filter = '';
    public $requisition_id;

    public function restore($id)
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

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
        $query = Requisition::onlyTrashed()->deletedVisibleTo(Auth::user())->with([
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
            'canRestore'   => Auth::user()->isSuperAdmin(),
        ]);
    }
}
