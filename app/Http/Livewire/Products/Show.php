<?php

namespace App\Http\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\CategoryValue;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;

class Show extends Component
{


    public $products;
    public $product_id;


    public function mount($id){
        $this->product = Product::find($id);
    }



    public function render()
    {

        return view('livewire.products.show');
    }
}
