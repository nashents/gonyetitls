<?php

namespace App\Http\Livewire\Products;

use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tyre;
use Livewire\Component;

class Items extends Component
{
    public $product;
    public $department;
    public $items;

    public function mount($id, $department){

        $this->product = Product::find($id);
        $this->department = $department;



    }

    public function deleteShow(){
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function deleteItems(){

        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                $item->delete();
            }
        }

        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"All Inventory Items Deleted Successfully!!"
        ]);
    }
    public function render()
    {
        $map = [
            'tyre' => Tyre::class,
            'inventory' => Inventory::class,
            'asset' => Asset::class,
        ];

        $model = $map[$this->department] ?? null;

        // Tyre has no rack/bin relation — only eager-load what the model actually has.
        $with = [
            'product.brand', 'product.category', 'product.category_value',
            'store', 'currency',
            'goods_received',
            'dispatch_items.dispatch',
        ];
        if ($this->department !== 'tyre') {
            $with[] = 'rack';
            $with[] = 'bin';
        }

        // Every instance regardless of stock/disposed status — this list exists to
        // trace a product's full history (received via which GRV, dispatched where),
        // so hiding out-of-stock/disposed rows would defeat the point.
        $this->items = $model
            ? $model::where('product_id', $this->product->id)
                ->with($with)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('livewire.products.items',[
            'items' => $this->items
        ]);
    }
}
