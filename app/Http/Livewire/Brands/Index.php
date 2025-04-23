<?php

namespace App\Http\Livewire\Brands;


use App\Models\Brand;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use App\Models\CategoryValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $brands;
    public $categories;
    public $selectedCategory = NULL;
    public $category_values;
    public $category_value_id;
    public $brand_id;
    public $status;
    public $name;
    public $user_id;
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


    public function mount(){
        $this->resetPage();
    }

    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->category_values = CategoryValue::where('category_id', $category)->get();
        }
    }

    private function resetInputFields(){
        $this->name = '';
        $this->selectedCategory= '';
        $this->category_value_id = '';
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedCategory' => 'nullable',
        'name' => 'required|unique:brands,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

 

    public function store(){
        $this->validate();
        if (isset($this->name)) {
            foreach ($this->name as $key => $value) {
                $brand = new Brand;
                $brand->user_id = Auth::user()->id;
                $brand->category_id = $this->selectedCategory;
                $brand->category_value_id = $this->category_value_id;
                if (isset($this->name[$key])) {
                    $brand->name = $this->name[$key];
                }
                $brand->status = 1;
                $brand->save();
            }   
        }
      
        $this->dispatchBrowserEvent('hide-brandModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Brand(s) Added Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function edit($id){
    $brand = Brand::find($id);
    $this->user_id = $brand->user_id;
    $this->name = $brand->name;
    $this->selectedCategory = $brand->category_id;
    $this->category_value_id = $brand->category_value_id;
    $this->status = $brand->status;
    $this->brand_id = $brand->id;
    $this->dispatchBrowserEvent('show-brandEditModal');

    }

    public function update()
    {
        if ($this->brand_id) {
            $brand = Brand::find($this->brand_id);
            $brand->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'category_value_id' => $this->category_value_id,
                'category_id' => $this->selectedCategory,
                'status' => $this->status,
            ]);

        $this->dispatchBrowserEvent('hide-brandEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Brand Updated Successfully!!"
        ]);

        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->categories = Category::orderBy('name','asc')->get();
        $this->category_values = CategoryValue::orderBy('name','asc')->get();

        if (isset($this->search)) {
            return view('livewire.brands.index',[
                'brands' => Brand::query()->with('category','category_value')
                ->where('name','like', '%'.$this->search.'%')
                ->orWhereHas('category', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('category_value', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10),
                'categories' => $this->categories,
                'category_values' => $this->category_values,
            ]);
        }else {
            return view('livewire.brands.index',[
                'brands' => Brand::orderBy('name','asc')->paginate(10),
                'categories' => $this->categories,
                'category_values' => $this->category_values,
            ]);
        }

      
    }
}
