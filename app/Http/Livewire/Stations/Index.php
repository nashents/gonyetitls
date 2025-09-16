<?php

namespace App\Http\Livewire\Stations;

use App\Models\Station;
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


    private $stations;
    public $station_id;
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
        'name' => 'required|unique:stations,name,NULL,id,deleted_at,NULL|string|min:2',
        'country' => 'required',
        'city' => 'required',
        'suburb' => 'required',
        'street_address' => 'required',
    ];

    public function station(){
        $station = new Station;
        $station->user_id = Auth::user()->id;
        $station->name = $this->name;
        $station->country = $this->country;
        $station->city = $this->city;
        $station->suburb = $this->suburb;
        $station->street_address = $this->street_address;
        $station->status = '1';
        $station->save();
        $this->dispatchBrowserEvent('hide-stationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Station Created Successfully!!"
        ]);
    }

    public function edit($id){
    $station = Station::find($id);
    $this->user_id = $station->user_id;
    $this->name = $station->name;
    $this->country = $station->country;
    $this->city = $station->city;
    $this->suburb = $station->suburb;
    $this->street_address = $station->street_address;
    $this->status = $station->status;
    $this->station_id = $station->id;
    $this->dispatchBrowserEvent('show-stationEditModal');

    }

    public function update()
    {
        if ($this->station_id) {

            $station = Station::find($this->station_id);
            $station->name = $this->name;
            $station->country = $this->country;
            $station->city = $this->city;
            $station->suburb = $this->suburb;
            $station->street_address = $this->street_address;
            $station->status = $this->status;
            $station->update();

            $this->dispatchBrowserEvent('hide-stationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Station Updated Successfully!!"
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
            return view('livewire.stations.index',[
                'stations' => Station::where('name','like', '%'.$this->search.'%')
                ->orWhere('country','like', '%'.$this->search.'%')
                ->orWhere('city','like', '%'.$this->search.'%')
                ->orWhere('street_address','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
            ]);
        }else{
            return view('livewire.stations.index',[
                'stations' => Station::orderBy('name','asc')->paginate(10)
            ]);
        }
       
        
    }
}
