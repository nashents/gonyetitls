<?php

namespace App\Http\Livewire\Purchases;

use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Document;
use App\Models\Purchase;
use App\Models\CategoryValue;
use Livewire\WithFileUploads;
use App\Models\PurchaseProduct;

class Products extends Component
{
    use WithFileUploads;

    public $purchase;
    public $purchase_id;
    public $products;
    public $purchase_products;
    public $purchase_product_id;
    public $document_id;
    public $categories;
    public $category_values;
    public $selectedCategory;
    public $selectedCategoryValue;

    public $product_id;
    // public $qty = 1;
    // public $amount;
    // public $subtotal;
    public $current_subtotal;

    // public $products;
    public $selectedProduct = [];
    public $selectedCurrentProduct = [];
    public $tax_accounts;
    public $selectedTax = [];
    public $selectedCurrentTax = [];
    public $qty = [];
    public $current_qty = [];
    public $amount  = [];
    public $current_amount = [];
    public $total;
    public $subtotal;
    public $subtotal_incl;
    public $value = 0;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    private function resetInputFields(){
        $this->rate = '';
        $this->qty = '';
        $this->product_id = '';
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
    $this->purchase = Purchase::find($id);
    $this->purchase_id = $id;
    $this->categories = Category::latest()->get();
    $this->category_values = CategoryValue::all();
    $this->products = Product::with('brand')->orderBy('name','asc')->get();
    $this->purchase_products = PurchaseProduct::where('purchase_id', $this->purchase->id)->latest()->get();
    $this->tax_accounts = Account::whereHas('account_type', function ($query) {
        return $query->where('name','Sales Taxes');
    })->orderBy('name','asc')->get();
    }


    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'product_id.0' => 'required',
        'amount.0' => 'required',
        'qty.0' => 'required',
        'product_id.*' => 'required',
        'amount.*' => 'required',
        'qty.*' => 'required',
    ];
    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->products = Product::where('category_id', $category)->orderBy('name','asc')->get();
        $this->category_values = CategoryValue::where('category_id', $category)->orderBy('name','asc')->get();
        }
    }
    public function updatedSelectedCategoryValue($category_value)
    {
        if (!is_null($category_value) ) {
        $this->products = Product::where('category_value_id', $category_value)->orderBy('name','asc')->get();
        }
    }

    public function store(){
        // try{

            if (isset($this->product_id)) {
                foreach ($this->product_id as $key => $value) {
                  $product = new PurchaseProduct;
                  $product->purchase_id = $this->purchase_id;
                  if (isset($this->product_id[$key])) {
                    $product->product_id = $this->product_id[$key];
                  }
                  if (isset($this->rate[$key])) {
                    $product->rate = $this->rate[$key];
                  }
                  if (isset($this->qty[$key])) {
                    $product->qty = $this->qty[$key];
                  }
                  if ((isset($this->qty[$key]) && $this->qty[$key] > 0) && (isset($this->rate[$key]) && $this->rate[$key] > 0)) {
                    $product->value = $this->qty[$key] * $this->rate[$key];
                  }
                  $product->save();
    
                  if ((isset($this->qty[$key]) && $this->qty[$key] > 0) && (isset($this->rate[$key]) && $this->rate[$key] > 0)) {
                  $this->subtotal = $this->subtotal + ($this->rate[$key] * $this->qty[$key]);
                  }
    
                }

                $purchase = Purchase::find($this->purchase_id);
                $purchase->value = $purchase->value + $this->subtotal;
                $purchase->update();
    
              }

            $this->dispatchBrowserEvent('hide-productModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Product(s) Added Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while adding product(s)!!"
        //     ]);
        // }
    }

    public function edit($id){

        $product = PurchaseProduct::find($id);
        $this->product_id = $product->id;
        $this->purchase_id = $product->purchase_id;
        $this->selectedCategory = $product->purchase->category_id;
        $this->selectedCategoryValue = $product->purchase->category_value_id;
        $this->rate = $product->rate;
        $this->current_subtotal = $product->value;
        $this->qty = $product->qty;
        $this->product_id = $product->id;

        $this->dispatchBrowserEvent('show-productEditModal');

        }

        public function update()
        {
            if ($this->product_id) {
                try{
                    $product = PurchaseProduct::find($this->product_id);
                    $product->purchase_id = $this->purchase_id;
                    $product->product_id = $this->product_id;
                    $product->rate = $this->rate;
                    $product->qty = $this->qty;
                    if ((isset($this->qty) && $this->qty > 0) && (isset($this->rate) && $this->rate > 0)) {
                      $product->value = $this->qty * $this->rate;
                      $this->subtotal = $this->qty * $this->rate;
                    }
                    $product->update();

                    $purchase = Purchase::find($this->purchase_id);
                    $purchase->value = ($purchase->value - $this->current_subtotal) + $this->subtotal ;
                    $purchase->update();

                    $this->dispatchBrowserEvent('hide-productEditModal');
                    $this->resetInputFields();
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'success',
                        'message'=>"Product Updated Successfully!!"
                    ]);
        

                }catch(\Exception $e){
                    // Set Flash Message
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Something went wrong while updating document(s)!!"
                    ]);
                }

            }
        }


    public function render()
    {
        $this->purchase_products = PurchaseProduct::where('purchase_id', $this->purchase->id)->latest()->get();
        return view('livewire.purchases.products',[
            'purchase_products'=> $this->purchase_products
        ]);
    }
}
