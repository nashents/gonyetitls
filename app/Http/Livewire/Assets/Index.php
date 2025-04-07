<?php

namespace App\Http\Livewire\Assets;

use App\Models\Asset;
use App\Models\Dispose;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public $department;
    protected $queryString = ['search'];
    
    private $assets;
    public $asset_id;
    public $asset;
    public $dispose;
    public $comments;
    public $date;

    public function mount(){
        $this->resetPage();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'comments' => 'required',
        'dispose' => 'required',
    ];

    private function resetInputFields(){
        $this->dispose = '';
        $this->comments = '';
        $this->date = '';

    }

    public function showDispose($id){
        $this->asset_id = $id;
        $this->asset = Asset::find($id);
        $this->dispatchBrowserEvent('show-disposeModal');

    }

    public function dispose(){
        $dispose = new Dispose;
        $dispose->user_id = Auth::user()->id;
        $dispose->asset_id = $this->asset_id;
        $dispose->comments = $this->comments;
        $dispose->date = $this->date;
        $dispose->save();

        $asset = Asset::find($this->asset_id);
        $asset->disposed = $this->dispose;
        $asset->status = 0;
        $asset->update();

        $this->dispatchBrowserEvent('hide-disposeModal');

        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Asset Disposed Successfully!!"
        ]);
        return redirect()->route('disposes.index');
    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.assets.index',[
                'assets' => Asset::query()->with('product','product.brand','currency')
                ->where('disposed',0)
                ->where('status',1)
                ->where('inventory_number','like', '%'.$this->search.'%')
                ->orWhere('serial_number','like', '%'.$this->search.'%')
                ->orWhere('part_number','like', '%'.$this->search.'%')
                ->orWhere('purchase_date','like', '%'.$this->search.'%')
                ->orWhereHas('product', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('product.brand', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle_make', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('currency', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vehicle_model', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse_make', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('horse_model', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('store', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })
                ->orWhereHas('vendor', function ($query) {
                    return $query->where('name', 'like', '%'.$this->search.'%');
                })->paginate(10)
            ]);
        }else {
            return view('livewire.assets.index',[
                'assets' => Asset::query()->select('assets.*') // Select all inventory columns
                ->join('products', 'products.id', '=', 'assets.product_id') // Join with products table
                ->with(['product.brand', 'currency']) // Eager load relationships
                ->where('assets.disposed', 0)
                ->where('assets.status', 1)
                ->orderBy('products.name', 'asc') // Order by product name
                ->paginate(10)
            ]);
        }
    }
}
