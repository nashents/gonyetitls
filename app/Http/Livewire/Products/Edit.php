<?php

namespace App\Http\Livewire\Products;

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
use Illuminate\Support\Facades\DB;
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
    public $category_values;
    public $brand_id;
    public $status;
    public $name;
    public $department;
    public $model;
    public $serial_number;
    public $identification_number;
    public $manufacturer;
    public $description;
    public $image;
    public $user_id;
    public $product;
    public $product_id;
    public $previous_image;
    public $unit_of_measure;

    public $tax;
    public $tax_accounts;
    public $selectedTax = [];
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $tax_account_id;
    public $buy ;
    public $sell;
    public $price;
    public $tax_id;
    public $account_id;
    public $buy_price;
    public $sell_price;


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

    public function mount($product){
        $product_attribute = $product->product_attributes->first();
        $this->brands = Brand::all();
        $this->categories = Category::all();
        $this->category_values = CategoryValue::all();
        $this->selectedCategory = $product->category_id;
        $this->selectedCategoryValue = $product->category_value_id;
        $this->name = $product->name;
        $this->identification_number = $product->identification_number;
        $this->department = $product->department;
        $this->brand_id = $product->brand_id;
        $this->manufacturer = $product->manufacturer;
        $this->description = $product->description;
        $this->previous_image = $product->filename;
        $this->unit_of_measure = $product->unit_of_measure;
        $this->buy = $product->buy;
        $this->sell = $product->sell;
        $this->sell_price = $product->sell_price;
        $this->buy_price = $product->price;
        $this->selectedTax = $product->tax_id;
        $this->expense_account_id = $product->expense_account_id;
        $this->income_account_id = $product->account_id;
        $this->status = $product->status;
        $this->product_id = $product->id;

        $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
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
    

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'unit_of_measure' => 'required',
        'image' => 'nullable|image',
        'name' => 'required|unique:products,name,NULL,id,deleted_at,NULL',
    ];
    public function update(){

        DB::transaction(function () {

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

        $product =  Product::find($this->product_id);
        $product->user_id = Auth::user()->id;
        $product->category_id = $this->selectedCategory;
        $product->category_value_id = $this->selectedCategoryValue;
        $product->brand_id = $this->brand_id;
        $product->name = $this->name;
        $product->price = $this->buy_price;
        $product->sell_price = $this->sell_price;
        $product->sell = $this->sell;
        $product->unit_of_measure = $this->unit_of_measure;
        $product->buy = $this->buy;
        $product->account_id = $this->income_account_id;
        $product->expense_account_id = $this->expense_account_id;
        $product->tax_id = $this->selectedTax;
        $product->identification_number = $this->identification_number;
        $product->department = $this->department;
        $product->manufacturer = $this->manufacturer;
        $product->description = $this->description;
        if (isset($fileNameToStore)) {
            $product->filename = $fileNameToStore;
        }
        $product->status = $this->status;
        $product->update();

    
      
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Product Updated Successfully!!"
        ]);

        if ($this->department == "asset") {
            return redirect(route('products.index'));
        }elseif($this->department == "tyre"){
            return redirect(route('tyre_products.index'));
        }elseif($this->department == "inventory"){
            return redirect(route('inventory_products.index'));
        }

        });
    }

     public function refresh($category){

        if($category == "brands"){
            $this->brands = Brand::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Brands Refreshed Successfully!!."
            ]);
        }
        elseif($category == "categories"){
            $this->categories = Category::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Categories Refreshed Successfully!!."
            ]);
        }
        elseif($category == "subcategories"){
            if(isset($this->selectedCategory)){
                 $this->category_values = CategoryValue::where('category_id', $this->selectedCategory)->orderBy('name','asc')->get();
            }else{
                 $this->category_values = CategoryValue::orderBy('name','asc')->get();
            }
          
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Sub Categories Refreshed Successfully!!."
            ]);
        }
        elseif($category == "expense_accounts"){
             $this->expense_accounts = Account::whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expense Accounts Refreshed Successfully!!."
            ]);
        }
        elseif($category == "income_expenses"){
              $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
                    $q->where('name', 'Income');
                })->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Income Accounts Refreshed Successfully!!."
            ]);
        }
         elseif($category == 'taxes'){
             $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
            })->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Sales Taxes Refreshed Successfully!!."
            ]);
        }
    }


    public function render()
    {
        $this->income_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Expenses');
         })->orderBy('name','asc')->get();

         $this->tax_accounts = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
    

        return view('livewire.products.edit');
    }
}
