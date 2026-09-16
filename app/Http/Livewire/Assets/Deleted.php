<?php

namespace App\Http\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $asset_id;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showRestore($id){
        $this->asset_id = $id;
        $this->dispatchBrowserEvent('show-assetRestoreModal');
    }

    public function restore(){
        $asset = Asset::onlyTrashed()->find($this->asset_id);

        if ($asset) {
            $asset->restore();
        }

        $this->dispatchBrowserEvent('hide-assetRestoreModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset Restored Successfully!!"
        ]);
    }

    public function render()
    {
        $query = Asset::onlyTrashed()
            ->with([
                'product', 'product.brand', 'product.category', 'product.category_value',
                'bin', 'rack', 'store', 'vendor', 'currency',
            ]);

        if (filled($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
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

        return view('livewire.assets.deleted', [
            'assets' => $query->orderByDesc('deleted_at')->paginate(10),
        ]);
    }
}
