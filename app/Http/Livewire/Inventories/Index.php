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

        $query = Inventory::query()
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
                $q->where('inventory_number', 'like', "%{$search}%")
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

        return view('livewire.inventories.index', [
            'inventories' => $query->paginate(10),
        ]);
       
    }
}
