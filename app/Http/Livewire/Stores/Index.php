<?php

namespace App\Http\Livewire\Stores;

use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Livewire\Concerns\PullsFromSage;
use App\Services\Sage\SageIntegration;
use App\Services\Sage\SageSyncService;

class Index extends Component
{
    use WithPagination;
    use PullsFromSage;
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

    /** Sage integration gate — controls the Pull button + sync badge/button. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
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

        // Push to Sage as a WAREHOUSE (guarded — a Sage hiccup never blocks creation).
        if ($this->sageEnabled) {
            try {
                app(SageSyncService::class)->syncStore($store);
            } catch (\Throwable $e) {
                Log::warning('Sage store push failed: ' . $e->getMessage());
            }
        }

        $this->dispatchBrowserEvent('hide-storeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Store Created Successfully!!"
        ]);
    }

    /** Queue a bulk import of Sage warehouses into Gonyeti stores. */
    public function pullFromSage()
    {
        $this->dispatchSagePull('store', 'stores');
    }

    /** Manually push a single store to Sage (badge Sync / Re-sync / Retry button). */
    public function syncStoreToSage($id)
    {
        if (! $this->sageEnabled) {
            return;
        }

        $store = Store::find($id);
        if (! $store) {
            return;
        }

        try {
            $result = app(SageSyncService::class)->syncStore($store);
            $ok     = ! empty($result['success']) && ! empty($result['external_id']);
            $this->dispatchBrowserEvent('alert', [
                'type'    => $ok ? 'success' : 'warning',
                'message' => $ok
                    ? 'Store synced to Sage warehouse (' . $result['external_id'] . ').'
                    : 'Sage sync: ' . ($result['error'] ?? 'could not sync this store.'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Sage store manual push failed: ' . $e->getMessage());
            $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => 'Sage sync failed for this store.']);
        }
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
                'stores' => Store::with('sageMapping')->where('name','like', '%'.$this->search.'%')
                ->orWhere('country','like', '%'.$this->search.'%')
                ->orWhere('city','like', '%'.$this->search.'%')
                ->orWhere('street_address','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.stores.index',[
                'stores' => Store::with('sageMapping')->orderBy('name','asc')->paginate(10)
            ]);
        }
       
        
    }
}
