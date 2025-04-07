<?php

namespace App\Http\Livewire\Tyres;

use App\Models\Tyre;
use App\Models\Dispose;
use App\Models\Product;
use Livewire\Component;
use App\Models\TyreDetail;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $tyres;
    public $tyre_id;
    public $tyre;
    public $tyre_number;
    public $serial_number;
    public $product_id;
    public $products;
    public $width;
    public $aspect_ratio;
    public $diameter;
    public $qty;
    public $rate;
    public $user_id;
    public $dispose;
    public $comments;
    public $date;

    public function mount(){
        $this->products = Product::where('department','tyre')->get();
        $this->resetPage();
    }
    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'product_id' => 'required',
        'rate' => 'required',
        'tyre_number' => 'required',
        'serial_number' => 'required',
        'width' => 'required',
        'aspect_ratio' => 'required',
        'diameter' => 'required',

        'date' => 'required',
        'comments' => 'required',
        'dispose' => 'required',
    ];



        public function update(){
            $tyre = Tyre::find($this->tyre_id);
            $tyre->tyre_id = $this->tyre_id;
            $tyre->tyre_number = $this->tyre_number;
            $tyre->serial_number = $this->serial_number;
            $tyre->product_id = $this->product_id;
            $tyre->width = $this->width;
            $tyre->aspect_ratio = $this->aspect_ratio;
            $tyre->diameter = $this->diameter;
            $tyre->rate = $this->rate;
            $tyre->update();

            $this->dispatchBrowserEvent('hide-tyreEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Tyre Updated Successfully!!"
            ]);
        }


   
    
        private function resetInputFields(){
            $this->dispose = '';
            $this->comments = '';
            $this->date = '';
    
        }
    
        public function showDispose($id){
            $this->tyre_id = $id;
            $this->tyre = Tyre::find($id);
            $this->dispatchBrowserEvent('show-disposeModal');
    
        }
    
        public function dispose(){
            $dispose = new Dispose;
            $dispose->user_id = Auth::user()->id;
            $dispose->tyre_id = $this->tyre_id;
            $dispose->comments = $this->comments;
            $dispose->date = $this->date;
            $dispose->save();
    
            $tyre = Tyre::find($this->tyre_id);
            $tyre->disposed = $this->dispose;
            $tyre->status = 0;
            $tyre->update();
    
            $this->dispatchBrowserEvent('hide-disposeModal');
    
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Tyre Disposed Successfully!!"
            ]);

            return redirect()->route('disposes.index');
        }


        public function updatingSearch()
        {
            $this->resetPage();
        }

    public function render()
    {
        if(isset($this->search)){
            return view('livewire.tyres.index',[
                'tyres' => Tyre::with('product.brand')
                ->where('disposed',0)
                ->where('tyre_number','like', '%'.$this->search.'%')
                ->orWhere('serial_number','like', '%'.$this->search.'%')
                ->orWhere('purchase_date','like', '%'.$this->search.'%')
                ->orWhere('type','like', '%'.$this->search.'%')
                ->orWhereHas('product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('store', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('created_at','desc')
                ->paginate(10)
            ]);
        }else{
            return view('livewire.tyres.index',[
                'tyres' => Tyre::with('product.brand')
                ->where('disposed',0)
                ->orderBy('created_at','desc')
                ->paginate(10)
            ]);
        }
      
    }
}
