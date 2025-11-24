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
        $query = Asset::query()
        ->with([
            'product',
            'product.brand',
            'product.category',
            'product.category_value',
            'bin',
            'rack',
            'store',
            'vendor',
            'currency',
        ])
        ->where('disposed', 0)
        ->where('status', 1);

        if (filled($this->search)) {
            $search = $this->search;

            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('purchase_date', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('subtotal', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.brand', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.category', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product.category_value', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('bin', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('bin_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('rack', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('rack_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('store', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vendor', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('currency', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        } else {
            // No search: default sort
            $query->orderByDesc('created_at');
        }

        return view('livewire.assets.index', [
            'assets' => $query->paginate(10),
        ]);
    }
}
