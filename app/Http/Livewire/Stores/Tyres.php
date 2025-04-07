<?php

namespace App\Http\Livewire\Stores;

use App\Models\Tyre;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;

class Tyres extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $store;
    public $store_id;
    private $tyres;
    public $from;
    public $to;
    public $date;
    public $comments;

    public function mount($id){
        $this->store_id = $id;
        $this->store = Store::find($id);
        $this->stores = Store::orderBy('name','asc')->get();
    }

    public function showTransfer($id){

        $this->tyre_id = $id;
        $this->tyre = Tyre::find($id);
        $this->dispatchBrowserEvent('show-transferModal');

        
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to' => 'required',
        'date' => 'required',
    ];

    private function resetInputFields(){
        $this->to = '';
        $this->date = '';
        $this->comments = '';
    }

    public function transfer(){

        $transfer = new Transfer;
        $transfer->user_id = Auth::user()->id;
        $transfer->tyre_id = $this->tyre_id;
        $transfer->from = $this->store_id;
        $transfer->to = $this->to;
        $transfer->date = $this->date;
        $transfer->comments = $this->comments;
        $transfer->save();

        $tyre = Tyre::find($this->tyre_id);
        $tyre->store_id = $this->to;
        $tyre->update();

        $this->dispatchBrowserEvent('hide-transferModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Tyre Transfered Successfully!!"
        ]);
        return redirect()->route('transfers.index');
    }

    public function render()
    {
        return view('livewire.stores.tyres',[
            'tyres' => Tyre::where('store_id',$this->store_id)->where('disposed',0)->where('status',1)->paginate(10),
        ]);
    }
}
