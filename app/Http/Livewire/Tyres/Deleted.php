<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $tyre_id;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showRestore($id){
        $this->tyre_id = $id;
        $this->dispatchBrowserEvent('show-tyreRestoreModal');
    }

    public function restore(){
        $tyre = Tyre::onlyTrashed()->find($this->tyre_id);

        if ($tyre) {
            $tyre->tyre_assignments()->onlyTrashed()->restore();
            $tyre->restore();
        }

        $this->dispatchBrowserEvent('hide-tyreRestoreModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Tyre Restored Successfully!!"
        ]);
    }

    public function render()
    {
        $query = Tyre::onlyTrashed()
            ->with(['product', 'product.brand', 'store', 'vendor', 'currency']);

        if (filled($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('tyre_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
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

        return view('livewire.tyres.deleted', [
            'tyres' => $query->orderByDesc('deleted_at')->paginate(10),
        ]);
    }
}
