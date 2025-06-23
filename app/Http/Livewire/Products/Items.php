<?php

namespace App\Http\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class Items extends Component
{
    public $product;
    public $department;
    public $items;

    public function mount($id, $department){
        
        $this->product = Product::find($id);
        $this->department = $department;

        if ($department == "tyre") {
            $this->items = $this->product->tyres->where('status',1);
        }elseif ($department == "inventory") {
            $this->items = $this->product->inventories->where('status',1)->where('balance','>',0);
        }elseif ($department == "asset") {
            $this->items = $this->product->assets->where('status',1)->where('balance','>',0);
        }
        
    }
    public function render()
    {
        return view('livewire.products.items');
    }
}
