<?php

namespace App\Http\Livewire\Stores;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore a store back to the active list. */
    public function restore($id)
    {
        $store = Store::find($id);
        if ($store) {
            $store->archive = 0;
            $store->status  = 1;
            $store->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Store restored.']);
        }
    }

    public function render()
    {
        $query = Store::where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        return view('livewire.stores.archived', [
            'stores' => $query->paginate(10),
        ]);
    }
}
