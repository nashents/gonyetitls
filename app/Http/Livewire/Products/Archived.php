<?php

namespace App\Http\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Archived extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    /** Restore a product back to the active list. */
    public function restore($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->archive = 0;
            $product->status  = 1;
            $product->save();
            $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => 'Product restored.']);
        }
    }

    public function render()
    {
        $query = Product::with(['brand', 'category'])->where('archive', 1)->orderBy('name', 'asc');

        if (filled($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('identification_number', 'like', "%{$search}%");
            });
        }

        return view('livewire.products.archived', [
            'products' => $query->paginate(10),
        ]);
    }
}
