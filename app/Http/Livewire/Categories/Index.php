<?php

namespace App\Http\Livewire\Categories;

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
    
    private $categories;
    public $category_id = Null;
    public $status;
    public $value_name;
    public $category_name;
    public $category = Null;
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
       
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'value_name' => 'unique:values,name,NULL,id,deleted_at,NULL|string|min:2',
        'category_name' => 'unique:categories,name,NULL,id,deleted_at,NULL|string|min:2',
    ];
    private function resetInputFields(){
        $this->category_name = '';
        $this->category_id = '';
        $this->value_name = '';
    }

    public function store(){

        if(isset($this->category_id) && $this->category_id != "" ){
            if (isset($this->value_name)) {
                  foreach ($this->value_name as $key => $model) {
                $value = new CategoryValue;
                $value->user_id = Auth::user()->id;
                $value->category_id = $this->category_id;
                $value->name = $this->value_name[$key];
                $value->status = 1;
                $value->save();
              }
            }
        
           
        }else {
            $category = new Category;
            $category->user_id = Auth::user()->id;
            $category->name = $this->category_name;
            $category->status = '1';
            $category->save();

            if (isset($this->value_name)) {
                foreach ($this->value_name as $key => $model) {
                    $value = new CategoryValue;
                    $value->user_id = Auth::user()->id;
                    $value->category_id = $category->id;
                    $value->name = $this->value_name[$key];
                    $value->status = 1;
                    $value->save();
                  }
            }
          
        }

        $this->dispatchBrowserEvent('hide-categoryModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Category Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

        public function showValue($id){
            $category = Category::find($id);
            $this->category_id = $category->id;
            $this->dispatchBrowserEvent('show-categoryValueModal');

        }

        public function storeValue(){
            $value = new CategoryValue;
            $value->user_id = Auth::user()->id;
            $value->category_id = $this->category_id;
            $value->name = $this->value_name;
            $value->status = '1';
            $value->save();

            $this->dispatchBrowserEvent('hide-categoryValueModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Value Created Successfully!!"
            ]);
        }
    public function editValue($id){

        $value = CategoryValue::find($id);
        $this->user_id = $value->user_id;
        $this->value_name = $value->name;
        $this->status = $value->status;
        $this->value_id = $value->id;
        $this->dispatchBrowserEvent('show-categoryValueEditModal');

        }

        public function updateValue()
        {
            if ($this->value_id) {
                $value = CategoryValue::find($this->value_id);
                $value->update([
                    'user_id' => Auth::user()->id,
                    'name' => $this->value_name,
                    'status' => $this->status,
                ]);

            $this->dispatchBrowserEvent('hide-categoryValueEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Value Updated Successfully!!"
            ]);

            }
        }

    public function edit($id){

    $category = Category::find($id);
    $this->user_id = $category->user_id;
    $this->category_name= $category->name;
    $this->status = $category->status;
    $this->category_id = $category->id;
    $this->dispatchBrowserEvent('show-categoryEditModal');

    }

    public function update()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            $category->update([
                'user_id' => Auth::user()->id,
                'name' => $this->category_name,
                'status' => $this->status,
            ]);

        $this->dispatchBrowserEvent('hide-categoryEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Category Updated Successfully!!"
        ]);

        }
    }
    public function render()
    {

        if (isset($this->search)) {
            return view('livewire.categories.index',[
                'categories' => Category::query()->with('category_values')
                ->where('name','like', '%'.$this->search.'%')
                ->orWhereHas('category_values', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('name','asc')->paginate(10),
                'values' => CategoryValue::orderBy('name','asc')->paginate(10),
            ]);
        }else {
            return view('livewire.categories.index',[
                'categories' => Category::orderBy('name','asc')->paginate(10),
                'values' => CategoryValue::orderBy('name','asc')->paginate(10),
            ]);
        }
       
       
    }
}
