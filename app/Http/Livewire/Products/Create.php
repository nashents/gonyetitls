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
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $brands;
    public $categories;
    public $department;
    public $stores;
    public $store_id;
    public $selectedCategory = NULL;
    public $selectedCategoryValue = NULL;
    public $category_values;
    public $brand_id;
    public $status;
    public $name;
    public $model;
    public $type;

    public $identification_number;
    public $manufacturer;
    public $description;
    public $image;
    public $user_id;
    public $min = 2;
    public $max = 10;

    public $category_name;
    public $sub_category_name;
    public $category_id;
    public $sub_category_id;
    public $brand_name;
    public $unit_of_measure;
    public $units_of_measure;

    public $tax;
    public $tax_accounts;
    public $income_accounts;
    public $expense_accounts;
    public $income_account_id;
    public $expense_account_id;
    public $selectedTax;
    public $buy = true;
    public $sell = false;
    public $price;
    public $tax_id;
    public $account_id;
    public $buy_price;
    public $sell_price;

    public $is_trackable = False;
    public $is_serialized = False;
    public $requires_position = False;
    public $requires_fitment = False;
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
    public function productNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $product = Product::orderBy('id','desc')->first();

        if (!$product) {
            $product_number =  $initials .'P'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $product->id + 1;
            $product_number =  $initials .'P'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $product_number;


    }

    public function mount($category){
        $this->brands = Brand::orderBy('name','asc')->get();
        $this->stores = Store::orderBy('name','asc')->get();
        $this->units_of_measure = UnitsOfMeasure::orderBy('name','asc')->get();
       
        $this->categories = Category::orderBy('name','asc')->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();
        $this->department = $category;
      

        $this->income_accounts = Account::active()->whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();

        $this->expense_accounts = Account::active()->whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Expenses');
         })->orderBy('name','asc')->get();
         $this->expense_account_id = $this->expense_accounts->where('name','Uncategorized Expense')->first()?->id;
          $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
    }

    public function updatedSelectedCategory($id)
    {
        if (!is_null($id) ) {
        $this->category_values = CategoryValue::where('category_id', $id)->orderBy('name','asc')->get();
        }
    }
    public function updatedSelectedCategoryValue($id)
    {
        if (!is_null($id) ) {
        $this->brands = Brand::where('category_value_id', $id)->orderBy('name','asc')->get();
        }
    }


    private function resetInputFields(){
        $this->category_name = Null;
        $this->sub_category_name = Null;
        $this->brand_name = Null;
        $this->category_id = Null;
        $this->sub_category_id = Null;
        $this->type = Null;
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        // Name uniqueness (active AND deleted matches, same/other department) is
        // handled by checkForDuplicateName() instead of a plain unique rule — a
        // blunt "taken" error can't tell the user WHY or point them at a fix.
        'name' => 'required',
        'unit_of_measure' => 'required',
    ];

    public $duplicateProductId;
    public $duplicateProductDepartment;
    public $duplicateProductEditRoute;

    public function updatedName(){
        $this->checkForDuplicateName();
    }

    /**
     * Blocks a name collision with either an active product (same or a
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

        $activeMatch = Product::where('name', $this->name)->first();
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

    public function storeCategory(){

        $category = new Category;
        $category->user_id = Auth::user()->id;
        $category->name = $this->category_name;
        $category->status = '1';
        $category->save();
        $this->selectedCategory = $category->id;

        $this->dispatchBrowserEvent('hide-categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Category Created Successfully!!"
        ]);
       
    }

    public function storeBrand(){
        $brand = new Brand;
        $brand->user_id = Auth::user()->id;
        $brand->category_id = $this->selectedCategory;
        $brand->category_value_id = $this->selectedCategoryValue;
        $brand->name = $this->brand_name;
        $brand->status = 1;
        $brand->save();
        $this->brand_id = $brand->id;
      
        $this->dispatchBrowserEvent('hide-brandModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Brand Added Successfully!!"
        ]);
        
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
             $this->expense_accounts = Account::active()->whereHas('account_type.account_type_group', function ($query) {
                return $query->where('name','Expenses');
            })->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Expense Accounts Refreshed Successfully!!."
            ]);
        }
        elseif($category == "income_expenses"){
              $this->income_accounts = Account::active()->whereHas('account_type.account_type_group', function($q){
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

        public function storeSubCategory(){
            $sub_category = new CategoryValue;
            $sub_category->user_id = Auth::user()->id;
            $sub_category->category_id = $this->selectedCategory;
            $sub_category->name = $this->sub_category_name;
            $sub_category->status = '1';
            $sub_category->save();
            $this->selectedCategoryValue = $sub_category->id;

            $this->dispatchBrowserEvent('hide-categoryValueModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Sub Category Created Successfully!!"
            ]);
        }



    public function store(){

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

        $product = new Product;
        $product->user_id = Auth::user()->id;
        $product->category_id = $this->selectedCategory;
        $product->category_value_id = $this->selectedCategoryValue;
        $product->brand_id = $this->brand_id;
        $product->name = $this->name;
        $product->product_number = $this->productNumber();;
        $product->identification_number = $this->identification_number;
        $product->price = $this->buy_price;
        $product->type = $this->type;
        $product->unit_of_measure = $this->unit_of_measure;
        $product->sell_price = $this->sell_price;
        $product->fitment_mode = $this->fitment_mode;
        $product->is_serialized = $this->is_serialized;
        $product->is_trackable = $this->is_trackable;
        $product->requires_position = $this->requires_position;
        $product->requires_fitment = $this->requires_fitment;
        $product->min = $this->min;
        $product->max = $this->max;
        $product->sell = $this->sell;
        $product->buy = $this->buy;
        $product->account_id = $this->income_account_id;
        $product->expense_account_id = $this->expense_account_id;
        $product->tax_id = $this->selectedTax;
        $product->department = $this->department;
        // Store/warehouse — default to the first store when none is chosen.
        $product->store_id = $this->store_id ?: optional(Store::defaultStore())->id;
        $product->manufacturer = $this->manufacturer;
        $product->description = $this->description;
        if (isset($fileNameToStore)) {
            $product->filename = $fileNameToStore;
        }
        $product->status = '1';

        $product->save();

        // Push to Sage as an ITEM (guarded — a Sage hiccup never blocks creation).
        if (\App\Services\Sage\SageIntegration::enabledForUser()) {
            try {
                app(\App\Services\Sage\SageSyncService::class)->syncProduct($product);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Sage product push failed: ' . $e->getMessage());
            }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Product Created Successfully!!"
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

    public function render()
    {
        if (isset( $this->selectedCategoryValue)) {
            $this->brands = Brand::where('category_value_id',  $this->selectedCategoryValue)->orderBy('name','asc')->get();
        }
        if (isset($this->selectedCategory)) {
            $this->category_values = CategoryValue::where('category_id', $this->selectedCategory)->orderBy('name','asc')->get();
            $this->brands = Brand::where('category_id',  $this->selectedCategory)->orderBy('name','asc')->get();
        }

        $this->categories = Category::orderBy('name','asc')->get();

        $this->income_accounts = Account::active()->whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();

        $this->expense_accounts = Account::active()->whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Expenses');
         })->orderBy('name','asc')->get();

         $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
     
        return view('livewire.products.create',[
            'brands' => $this->brands,
            'categories' => $this->categories,
            'category_values' => $this->category_values,
        ]);
    }
}
