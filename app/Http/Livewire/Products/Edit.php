<?php

namespace App\Http\Livewire\Products;

use App\Models\Account;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryValue;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Store;
use App\Models\Tax;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $brands;
    public $categories;
    public $stores;
    public $store_id;
    public $attributes;
    public $attribute_values;
    public $selectedCategory = NULL;
    public $selectedCategoryValue = NULL;
    public $category_values;
    public $brand_id;
    public $status;
    public $name;
    public $min;
    public $max;
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
    public $units_of_measure;
    public $type;

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

    public $is_trackable;
    public $is_serialized;
    public $requires_position;
    public $requires_fitment;
    public $fitment_mode;


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
        $this->brands = Brand::orderBy('name','asc')->get();
        $this->categories = Category::orderBy('name','asc')->get();
        $this->stores = Store::orderBy('name','asc')->get();
        $this->store_id = $product->store_id;
        $this->units_of_measure = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();
        $this->selectedCategory = $product->category_id;
        $this->selectedCategoryValue = $product->category_value_id;
        $this->name = $product->name;
        $this->identification_number = $product->identification_number;
        $this->department = $product->department;
        $this->brand_id = $product->brand_id;
        $this->type = $product->type;
        $this->min = $product->min;
        $this->max = $product->max;
        $this->manufacturer = $product->manufacturer;
        $this->description = $product->description;
        $this->previous_image = $product->filename;
        $this->unit_of_measure = $product->unit_of_measure;
        $this->buy = $product->buy;
        $this->sell = $product->sell;
        $this->sell_price = $product->sell_price;
        $this->requires_position = $product->requires_position;
        $this->requires_fitment = $product->requires_fitment;
        $this->fitment_mode = $product->fitment_mode;
        $this->is_serialized = $product->is_serialized;
        $this->is_trackable = $product->is_trackable;
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

          $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
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
    protected function rules(){
        // Name uniqueness (active AND deleted matches, same/other department) is
        // handled by checkForDuplicateName() instead of a plain unique rule — a
        // blunt "taken" error can't tell the user WHY or point them at a fix,
        // and (unlike a raw unique rule) it correctly excludes this product's
        // own row from the check.
        return [
            'unit_of_measure' => 'required',
            'image' => 'nullable|image',
            'name' => 'required',
        ];
    }

    public $duplicateProductId;
    public $duplicateProductDepartment;
    public $duplicateProductEditRoute;

    public function updatedName(){
        $this->checkForDuplicateName();
    }

    /**
     * Blocks a name collision with either another active product (same or a
     * different department — cross-department is very likely the same
     * physical thing filed under the wrong department, not a real duplicate)
     * or a soft-deleted one (silently reusing a deleted product's name is how
     * "two products, one name, different #" happens — see Deleted Products).
     * Returns true (and sets a field error + duplicateProduct* properties for
     * the "edit that product instead" link) when the save should be blocked.
     */
    private function checkForDuplicateName(): bool
    {
        $this->resetErrorBag('name');
        $this->duplicateProductId = null;
        $this->duplicateProductDepartment = null;
        $this->duplicateProductEditRoute = null;

        if (blank($this->name)) {
            return false;
        }

        $editRoutes = [
            'asset' => 'products.edit',
            'inventory' => 'inventory_products.edit',
            'tyre' => 'tyre_products.edit',
        ];

        $activeMatch = Product::where('name', $this->name)
            ->where('id', '!=', $this->product_id)
            ->first();
        if ($activeMatch) {
            $this->duplicateProductId = $activeMatch->id;
            $this->duplicateProductDepartment = $activeMatch->department;
            $this->duplicateProductEditRoute = $editRoutes[$activeMatch->department] ?? null;

            if ($activeMatch->department === $this->department) {
                $this->addError('name', "A product with this name already exists in this department (Product#: {$activeMatch->product_number}). Please use a different name, or edit the existing product below.");
            } else {
                $this->addError('name', "A product named \"{$this->name}\" already exists, but in the " . ucfirst($activeMatch->department) . " department (Product#: {$activeMatch->product_number}). If it should really be " . ucfirst($this->department) . ", edit it below and change its department instead of creating a duplicate.");
            }
            return true;
        }

        $deletedMatch = Product::onlyTrashed()->where('name', $this->name)->first();
        if ($deletedMatch) {
            $this->addError('name', "A deleted product with this name already exists (Product#: {$deletedMatch->product_number}, deleted " . optional($deletedMatch->deleted_at)->format('Y-m-d') . "). Restore it from Deleted Products instead of creating a duplicate, or use a different name.");
            return true;
        }

        return false;
    }

    public function update(){

        $this->validate();

        if ($this->checkForDuplicateName()) {
            return;
        }

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
        $product->min = $this->min;
        $product->max = $this->max;
        $product->type = $this->type;
        $product->price = $this->buy_price;
        $product->sell_price = $this->sell_price;
        $product->fitment_mode = $this->fitment_mode;
        $product->is_serialized = $this->is_serialized;
        $product->is_trackable = $this->is_trackable;
        $product->requires_position = $this->requires_position;
        $product->requires_fitment = $this->requires_fitment;
        $product->sell = $this->sell;
        $product->unit_of_measure = $this->unit_of_measure;
        $product->buy = $this->buy;
        $product->account_id = $this->income_account_id;
        $product->expense_account_id = $this->expense_account_id;
        $product->tax_id = $this->selectedTax;
        $product->identification_number = $this->identification_number;
        $product->department = $this->department;
        // Store/warehouse — default to the first store when none is chosen.
        $product->store_id = $this->store_id ?: optional(Store::defaultStore())->id;
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
               $this->tax_accounts = Tax::whereHas('account', function ($query) {
                    return $query->where('name','Value Added Tax');
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

         $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
    

        return view('livewire.products.edit');
    }
}
