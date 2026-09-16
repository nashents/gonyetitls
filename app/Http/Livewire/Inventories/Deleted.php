<?php

namespace App\Http\Livewire\Inventories;

use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $inventory_id;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showRestore($id){
        $this->inventory_id = $id;
        $this->dispatchBrowserEvent('show-inventoryRestoreModal');
    }

    public function restore(){
        $inventory = Inventory::onlyTrashed()->find($this->inventory_id);

        if ($inventory) {
            $inventory->restore();
        }

        $this->dispatchBrowserEvent('hide-inventoryRestoreModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inventory Item Restored Successfully!!"
        ]);
    }

    public function render()
    {
        $query = Inventory::onlyTrashed()
            ->with([
                'product', 'product.brand', 'product.category', 'product.category_value',
                'bin', 'rack', 'store', 'vendor', 'currency',
            ]);

        if (filled($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('inventory_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('product_number', 'like', "%{$search}%")
                            ->orWhere('identification_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.brand', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return view('livewire.inventories.deleted', [
            'inventories' => $query->orderByDesc('deleted_at')->paginate(10),
        ]);
    }
}
