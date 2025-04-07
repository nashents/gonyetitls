<?php

namespace App\Http\Livewire\TyreProducts;

use App\Models\Brand;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\CategoryValue;
use Livewire\WithFileUploads;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    use WithFileUploads;

    public $brands;
    public $categories;
    public $attributes;
    public $attribute_values;
    public $selectedCategory = NULL;
    public $selectedCategoryValue = NULL;
    public $selectedAttribute = NULL;
    public $category_values;
    public $attribute_value_id;
    public $brand_id;
    public $status;
    public $name;
    public $manufacturer;
    public $description;
    public $image;
    public $department;
    public $user_id;
    public $product;
    public $product_id;
    public $previous_image;

    public $tax;
    public $tax_accounts;
    public $selectedTax = [];
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $tax_account_id;
    public $buy ;
    public $sell ;
    public $price;
    public $tax_id;
    public $account_id;
    public $buy_price;
    public $sell_price;
    public $identification_number;



    public $inputs = [];
    public $i = 1;
    public $n = 1;


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
        $this->product_id = $id;
        $this->brands = Brand::all();
        $this->categories = Category::all();
        $this->category_values = CategoryValue::all();
        $this->attributes = Attribute::all();
        $this->attribute_values = AttributeValue::all();
        $this->product = Product::find($id);
        $this->selectedCategory = $this->product->category_id;
        $this->selectedCategoryValue = $this->product->category_value_id;
        $this->department = $this->product->department;
        $this->identification_number = $this->product->identification_number;
        $this->buy_price = $this->product->price;
        $this->buy = $this->product->buy;
        $this->sell = $this->product->sell;
        $this->sell_price = $this->product->sell_price;
        $this->tax_account_id = $this->product->tax_id;
        $this->income_account_id = $this->product->account_id;
        $this->expense_account_id = $this->product->expense_account_id;
        $this->department = $this->product->department;
        $this->name = $this->product->name;
        $this->brand_id = $this->product->brand_id;
        $this->manufacturer = $this->product->manufacturer;
        $this->description = $this->product->description;
        $this->previous_image = $this->product->filename;

        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Expenses');
         })->orderBy('name','asc')->get();

         $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
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
        $this->brands = Brand::where('category_value_id', $category_value)->get();
        $this->attributes = Attribute::where('category_value_id', $category_value)->get();
        }
    }
    public function updatedSelectedAttribute($attribute)
    {
        if (!is_null($attribute) ) {
        $this->attribute_values = AttributeValue::where('attribute_id', $attribute)->get();
        }
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedCategory' => 'required',
        'selectedCategoryValue' => 'required',
        'selectedAttribute' => 'required',
        'attribute_value_id' => 'required',
        'brand_id' => 'required',
        'manufacturer' => 'required',
        'image' => 'nullable|image',
        'description' => 'nullable',
        'name' => 'required|unique:products,name,NULL,id,deleted_at,NULL',
    ];


    public function update(){

        if ($this->image) {
                $image = $this->image;
                $fileNameWithExt = $image->getClientOriginalName();
                //get filename
                $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //get extention
                $extention = $image->getClientOriginalExtension();
                //file name to store
                $fileNameToStore= $filename.'_'.time().'.'.$extention;
                $image->storeAs('/uploads', $fileNameToStore, 'path');
        }

        $product = Product::find($this->product_id);
        $product->category_id = $this->selectedCategory;
        $product->category_value_id = $this->selectedCategoryValue;
        $product->brand_id = $this->brand_id;
        $product->department = $this->department;
        $product->name = $this->name;
        $product->price = $this->buy_price;
        $product->identification_number = $this->identification_number;
        $product->sell_price = $this->sell_price;
        $product->sell = $this->sell;
        $product->buy = $this->buy;
        $product->tax_id = $this->tax_account_id;
        $product->account_id = $this->income_account_id;
        $product->expense_account_id = $this->expense_account_id;
        $product->department = $this->department;
        $product->manufacturer = $this->manufacturer;
        $product->description = $this->description;
        if (isset($fileNameToStore)) {
            $product->filename = $fileNameToStore;
        }
        $product->status = '1';

        $product->update();

        return redirect(route('tyre_products.index'));
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Product Updated Successfully!!"
        ]);
    }


    public function render()
    {
        return view('livewire.tyre-products.edit');
    }
}
