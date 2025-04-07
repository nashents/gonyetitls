<?php

namespace App\Http\Livewire\Purchases;

use Livewire\Component;
use App\Models\Purchase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;
    public $purchase_filter;

    private $purchases;
    public $purchase;
    public $department;
    public $purchase_id;

    public function mount($category){
        $this->department = $category;
      
    }
    public function authorize($id){
        $purchase = Purchase::find($id);
        $this->purchase_id = $purchase->id;
        $this->purchase = $purchase;
        $this->dispatchBrowserEvent('show-purchaseAuthorizationModal');
      }

      public function update(){
        $purchase = Purchase::find($this->purchase_id);
        if ($purchase->authorization == "approved") {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Purchase Order Approved Already"
            ]);
        }else {
            $purchase->authorized_by_id = Auth::user()->id;
            $purchase->authorization = $this->authorize;
            $purchase->authorization_comments = $this->comments;
            $purchase->update();
        if ($this->authorize == "approved") {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_purchases.approved');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_purchases.approved');
            }elseif($this->department == "asset"){
                return redirect()->route('purchases.approved');
            }
        }else {
            $this->dispatchBrowserEvent('hide-purchaseAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Purchase Order Approved Successfully"
            ]);
            if ($this->department == "tyre") {
                return redirect()->route('tyre_purchases.rejected');
            }elseif($this->department == "inventory"){
                return redirect()->route('inventory_purchases.rejected');
            }elseif($this->department == "asset"){
                return redirect()->route('purchases.rejected');
            }
        }
        }
      }

    public function render()
    {
        return view('livewire.purchases.approved',[
            'purchases' => Purchase::where('authorization', 'approved')
            ->where('department',$this->department)->latest()->paginate(10)
        ]);
    }
}
