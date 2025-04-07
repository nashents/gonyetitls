<?php

namespace App\Http\Livewire\TyrePurchases;

use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\VendorType;
use App\Models\CategoryValue;
use Livewire\WithFileUploads;
use App\Models\Account;
use App\Models\PurchaseProduct;
use Illuminate\Support\Facades\Auth;

class Manage extends Component
{
    use WithFileUploads;

    public $purchase_number;
    public $purchases;
    public $purchase_id;
    public $currencies;
    public $currency_id;
    public $category_values;
    public $categories;
    public $department;
    public $selectedCategory;
    public $selectedCategoryValue;
    public $value = 0;
    public $description;
    public $date;

    public $accounts;
    public $selectedAccount;
    public $expenses;
    public $expense_id;

    public $vendor_id;
    public $vendors;
    public $vendor_types;
    public $selectedVendorType;


    public $title;
    public $vendor;
    public $file;
    public $expires_at;

    public $products;
    public $product_id;
    public $qty = 1;
    public $rate;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->title = '';
        $this->file = '';
        $this->vendor = '';
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    
    public $documentInputs = [];
    public $m = 1;
    public $o = 1;
    public function documentsAdd($m)
    {
        $m = $m + 1;
        $this->m = $m;
        array_push($this->documentInputs ,$m);
    }

    public function documentsRemove($m)
    {
        unset($this->documentInputs[$m]);
    }



    public function mount($category){
        $this->department = $category;
        $this->products = collect();
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
            }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->purchases = Purchase::latest()
            ->where('department','tyre')->get();
        } else {
            $this->purchases = Purchase::where('user_id',Auth::user()->id)->latest()
            ->where('department','tyre')->get();
        }
        $this->vendor_types = VendorType::latest()->get();
        $this->accounts = Account::orderBy('name','asc')->get();
        $this->expenses = Expense::orderBy('name','asc')->get();
        $this->vendors = Vendor::orderBy('name','asc')->get();
        $this->currencies = Currency::all();
        $this->categories = Category::latest()->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();;

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'currency_id' => 'required',
        'purchase_number' => 'required',
        'selectedCategory' => 'required',
        'value' => 'required',
        'product_id.0' => 'required',
        'rate.0' => 'required',
        'qty.0' => 'required',
        'product_id.*' => 'required',
        'rate.*' => 'required',
        'qty.*' => 'required',
    ];
    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->products = Product::where('category_id', $category)->orderBy('name','asc')->get();
        }
    }
    public function updatedSelectedAccount($category)
    {
        if (!is_null($category) ) {
        $this->expenses = Expense::where('account_id', $category)->orderBy('name','asc')->get();
        }
    }


    public function edit($id){
        $purchase = Purchase::find($id);
        $this->purchase_number = $purchase->purchase_number;
        $this->date = $purchase->date;
        $this->currency_id = $purchase->currency_id;
        $this->vendor_id = $purchase->vendor_id;
        $this->selectedVendorType = $purchase->vendor_type_id;
        $this->selectedAccount = $purchase->account_id;
        $this->expense_id = $purchase->expense_id;
        $this->selectedCategory = $purchase->category_id;
        $this->status = $purchase->status;
        $this->description = $purchase->description;
        $this->products = $purchase->purchase_products;
        $this->purchase_id = $purchase->id;
        $this->dispatchBrowserEvent('show-purchaseEditModal');
    }
    public function update(){
        if ($this->purchase_id) {
            $purchase = Purchase::find($this->purchase_id);
            $purchase->user_id = Auth::user()->id;
            $purchase->purchase_number = $this->purchase_number;
            $purchase->date = $this->date;
            $purchase->description = $this->description;
            $purchase->account_id = $this->selectedAccount;
            $purchase->expense_id = $this->expense_id;
            $purchase->vendor_id = $this->vendor_id;
            $purchase->vendor_type_id = $this->selectedVendorType;
            $purchase->category_id = $this->selectedCategory;
            $purchase->currency_id = $this->currency_id;
            $purchase->status = '1';
            $purchase->update();
    
              $this->dispatchBrowserEvent('hide-purchaseEditModal');
              $this->dispatchBrowserEvent('alert',[
                  'type'=>'success',
                  'message'=>"Purchase Order Updated Successfully!!"
              ]);
            }
    }
    public function render()
    {
        $this->purchases = Purchase::latest()
        ->where('department','tyre')->get();
        return view('livewire.tyre-purchases.manage',[
            'purchases' => $this->purchases
        ]);
    }
}
