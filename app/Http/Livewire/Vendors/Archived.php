<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore a vendor back to the active list. */
    public function restore($id)
    {
        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->archive = 0;
            $vendor->status  = 1;
            $vendor->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Vendor restored.']);
        }
    }

    public function render()
    {
        $query = Vendor::where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('livewire.vendors.archived', [
            'vendors' => $query->paginate(10),
        ]);
    }
}
