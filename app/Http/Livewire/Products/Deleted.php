<?php

namespace App\Http\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $department;
    public $product_id;

    public function mount($category){
        $this->department = $category;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showRestore($id){
        $this->product_id = $id;
        $this->dispatchBrowserEvent('show-productRestoreModal');
    }

    public function restore(){
        $product = Product::onlyTrashed()->find($this->product_id);

        if ($product) {
            $product->restore();
        }

        $this->dispatchBrowserEvent('hide-productRestoreModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Product Restored Successfully!!"
        ]);
    }

    public function render()
    {
        $query = Product::onlyTrashed()
            ->with(['brand', 'category', 'category_value'])
            ->where('department', $this->department);

        if (filled($this->search)) {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($q) use ($search) {
                $q->where('product_number', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('identification_number', 'like', $search)
                    ->orWhereHas('brand', function ($b) use ($search) {
                        $b->where('name', 'like', $search);
                    });
            });
        }

        $products = $query->orderByDesc('deleted_at')->paginate(10);

        // For each deleted product, surface any CURRENT (active, non-deleted) product
        // sharing the same name — confirms/denies "was this recreated under a new #?".
        $activeMatches = Product::whereIn('name', $products->pluck('name')->unique())
            ->get(['id', 'name', 'product_number'])
            ->groupBy('name');

        return view('livewire.products.deleted', [
            'products' => $products,
            'activeMatches' => $activeMatches,
        ]);
    }
}
