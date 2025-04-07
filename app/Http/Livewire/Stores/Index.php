<?php

namespace App\Http\Livewire\Stores;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];


    private $stores;
    public $store_id;
    public $status;
    public $name;
    public $country;
    public $city;
    public $suburb;
    public $street_address;
    public $user_id;

    private function resetInputFields(){
        $this->name = '';
        $this->country = '';
        $this->city = '';
        $this->suburb = '';
        $this->street_address = '';
        $this->status = '';
    }
    public function mount(){
        $this->resetPage();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:stores,name,NULL,id,deleted_at,NULL|string|min:2',
        'country' => 'required',
        'city' => 'required',
        'suburb' => 'required',
        'street_address' => 'required',
    ];

    public function store(){
        $store = new Store;
        $store->user_id = Auth::user()->id;
        $store->name = $this->name;
        $store->country = $this->country;
        $store->city = $this->city;
        $store->suburb = $this->suburb;
        $store->street_address = $this->street_address;
        $store->status = '1';
        $store->save();
        $this->dispatchBrowserEvent('hide-storeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Store Created Successfully!!"
        ]);
    }

    public function edit($id){
    $store = Store::find($id);
    $this->user_id = $store->user_id;
    $this->name = $store->name;
    $this->country = $store->country;
    $this->city = $store->city;
    $this->suburb = $store->suburb;
    $this->street_address = $store->street_address;
    $this->status = $store->status;
    $this->store_id = $store->id;
    $this->dispatchBrowserEvent('show-storeEditModal');

    }

    public function update()
    {
        if ($this->store_id) {

            $store = Store::find($this->store_id);
            $store->name = $this->name;
            $store->country = $this->country;
            $store->city = $this->city;
            $store->suburb = $this->suburb;
            $store->street_address = $this->street_address;
            $store->status = $this->status;
            $store->update();

            $this->dispatchBrowserEvent('hide-storeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Store Updated Successfully!!"
            ]);

        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if (isset($this->search)) {
            return view('livewire.stores.index',[
                'stores' => Store::where('name','like', '%'.$this->search.'%')
                ->orWhere('country','like', '%'.$this->search.'%')
                ->orWhere('city','like', '%'.$this->search.'%')
                ->orWhere('street_address','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.stores.index',[
                'stores' => Store::orderBy('name','asc')->paginate(10)
            ]);
        }
       
        
    }
}
