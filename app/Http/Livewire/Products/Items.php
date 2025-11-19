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
        if ($this->department == "tyre") {
            $this->items = $this->product->tyres->where('status',1);
        }elseif ($this->department == "inventory") {
            $this->items = $this->product->inventories->where('status',1)->where('balance','>',0);
        }elseif ($this->department == "asset") {
            $this->items = $this->product->assets->where('status',1)->where('balance','>',0);
        }
        return view('livewire.products.items',[
            'items' => $this->items
        ]);
    }
}
