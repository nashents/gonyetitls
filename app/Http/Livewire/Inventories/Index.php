<?php

namespace App\Http\Livewire\Inventories;

use App\Models\Dispose;
use Livewire\Component;
use App\Models\Inventory;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\InventoriesImport;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $inventories;
    public $inventory;
    public $inventory_id;
    public $dispose;
    public $comments;
    public $date;
    public $importFile;

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
        $this->inventory_id = $id;
        $this->inventory = Inventory::find($id);
        $this->dispatchBrowserEvent('show-disposeModal');

    }

    public function dispose(){
        $dispose = new Dispose;
        $dispose->user_id = Auth::user()->id;
        $dispose->inventory_id = $this->inventory_id;
        $dispose->comments = $this->comments;
        $dispose->date = $this->date;
        $dispose->save();

        $inventory = Inventory::find($this->inventory_id);
        $inventory->disposed = $this->dispose;
        $inventory->status = 0;
        $inventory->update();

        $this->dispatchBrowserEvent('hide-disposeModal');

        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inventory Item Disposed Successfully!!"
        ]);
        return redirect()->route('disposes.index');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function importInventory(){
        
        $file = $this->importFile;
        $import = new InventoriesImport;
        $import->import($file);

        $this->dispatchBrowserEvent('hide-inventoryImportModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Inventory Items(s) Imported Successfully!!"
        ]);

        return redirect(request()->header('Referer'));
      
    }

    public function render()
    {
        if (isset($this->search)) {
            return view('livewire.inventories.index',[
                'inventories' => Inventory::query()->with('product','product.brand','currency')
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
            return view('livewire.inventories.index',[
                'inventories' => Inventory::query()->select('inventories.*') // Select all inventory columns
                ->join('products', 'products.id', '=', 'inventories.product_id') // Join with products table
                ->with(['product.brand', 'currency']) // Eager load relationships
                ->where('inventories.disposed', 0)
                ->where('inventories.status', 1)
                ->orderBy('products.name', 'asc') // Order by product name
                ->paginate(10)
            ]);
        }
       
    }
}
