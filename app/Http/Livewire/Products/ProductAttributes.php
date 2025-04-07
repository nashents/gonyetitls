<?php

namespace App\Http\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\CategoryValue;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;

class ProductAttributes extends Component
{
    public $attribute_values;
    public $attribute_value;
    public $attribute_value_id;
    public $category_values;
    public $selectedCategoryValue;
    public $categories;
    public $selectedCategory;
    public $product_attributes;
    public $product_attribute_id;
    public $attributes;
    public $attribute;
    public $selectedAttribute;

    public $inputs = [];
    public $i = 1;
    public $n = 1;


    private function resetInputFields(){
        $this->selectedAttribute = '';
        $this->attribute_value_id = '';
        $this->selectedCategory = '';
        $this->selectedCategoryValue = '';
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }


    public function mount($id){
        $this->product = Product::find($id);
        $this->product_attributes = $this->product->product_attributes;
        $this->product_id = $id;
        $this->categories = Category::all();
        $this->category_values = collect();
        $this->attributes = collect();
        $this->attribute_values = collect();
    }

    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->category_values = CategoryValue::where('category_id', $category)->get();
        }
    }
    public function updatedSelectedCategoryValue($category_value)
    {
        if (!is_null($category_value) ) {
        $this->attributes = Attribute::where('category_value_id', $category_value)->get();
        }
    }

    public function updatedSelectedAttribute($id){
        if (!is_null($id)) {
            $this->attribute_values = AttributeValue::where('attribute_id', $id)->get();
        }
    }

    public function showRemoveAttribute($id){
        $this->attribute_id = $id;
        $this->attribute = Attribute::find($id);
        $this->dispatchBrowserEvent('show-removeProductAttributeModal');
    }
    public function showRemoveAttributeValue($id){
        $this->attribute_value_id = $id;
        $this->attribute_value = AttributeValue::find($id);
        $this->dispatchBrowserEvent('show-removeProductAttributeValueModal');
    }
    public function removeProductAttribute(){
        $attribute_values = ProductAttribute::where('attribute_id',$this->attribute_id)
        ->where('product_id',$this->product_id)->get();
        foreach ($attribute_values as $attribute_value) {
            $attribute_value->delete();
        }
     
        $this->dispatchBrowserEvent('hide-removeProductAttributeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Attribute Removed Successfully!!"
        ]);
    }
    public function removeProductAttributeValue(){
        $product_attribute_value = ProductAttribute::where('attribute_value_id',$this->attribute_value_id)
        ->where('product_id',$this->product_id)->get()->first();
        $product_attribute_value->delete();
        $this->dispatchBrowserEvent('hide-removeProductAttributeValueModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Attribute Value Removed Successfully!!"
        ]);
    }
    public function addAttributes(){
        
        
        if (isset($this->attribute_value_id)) {
            foreach ($this->attribute_value_id as $key => $value) {
                $product_attribute = new ProductAttribute;
                $product_attribute->product_id = $this->product_id;
                $product_attribute->attribute_id = $this->selectedAttribute;
                if (isset($this->attribute_value_id[$key])) {
                    $product_attribute->attribute_value_id = $this->attribute_value_id[$key];
                }
                $product_attribute->save();
            }
        }
        $this->dispatchBrowserEvent('hide-attributeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Attribute & Value(s) Added Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }




    public function render()
    {
        $this->product_attributes = ProductAttribute::where('product_id', $this->product_id)->get();
        return view('livewire.products.product-attributes',[
            'product_attributes' => $this->product_attributes 
        ]);
    }
}
