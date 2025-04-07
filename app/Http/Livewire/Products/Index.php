<?php

namespace App\Http\Livewire\Products;

use App\Models\Brand;
use App\Models\Store;
use App\Models\Value;
use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $department;
    protected $queryString = ['search'];
    private $products;

    public function mount($category){
        $this->department = $category;
        $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.products.index',[
                'products' => Product::query()->with('brand','category','category_value')->where('department', $this->department)->where('status',1)
                ->where('product_number','like', '%'.$this->search.'%')
                ->orWhere('name','like', '%'.$this->search.'%')
                ->orWhereHas('category', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('category_value', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.products.index',[
                'products' => Product::query()->with('brand','category','category_value')->where('department', $this->department)->where('status',1)->orderBy('name','asc')->paginate(10)
            ]);
        }
    }
}
