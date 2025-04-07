<?php

namespace App\Http\Livewire\Purchases;

use Livewire\Component;
use App\Models\Purchase;
use Livewire\WithPagination;

class Deleted extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    private $purchases;
    public $purchase_id;
    public $category;

    public function mount($category){
       $this->category = $category;
    }

    public function showRestore($id){
        $this->purchase_id = $id;
        $this->dispatchBrowserEvent('show-purchaseRestoreModal');
    }
    public function update(){
        Purchase::withTrashed()->find($this->purchase_id)->restore();
        
        $this->dispatchBrowserEvent('hide-purchaseRestoreModal');
       
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Purchase Order Restored Successfully!!"
        ]);
    
    }
    public function render()
    {
        $this->purchases = Purchase::onlyTrashed()->where('department',$this->category)->orderBy('deleted_at','desc')->paginate(10);
        return view('livewire.purchases.deleted',[
            'purchases' => $this->purchases
        ]);
    }
}
