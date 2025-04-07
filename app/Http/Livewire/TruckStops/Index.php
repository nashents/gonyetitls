<?php

namespace App\Http\Livewire\TruckStops;

use App\Models\Route;
use Livewire\Component;
use App\Models\TruckStop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{



    public $truck_stops;
    public $routes;
    public $route_id;
    public $status;
    public $name;
    public $rating;
    public $expiry_date;
    public $description;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }


    public $truck_stop_id;
    public $user_id;

    public function mount(){
        $this->truck_stops = TruckStop::latest()->get();
        $this->routes = Route::latest()->get();
    }
    private function resetInputFields(){
        $this->name = '';
        $this->route_id = '';
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'route_id' => 'required',
        'name' => 'required|unique:truck_stops,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    public function store(){
        try{
            if(isset($this->name)){
                foreach ($this->name as $key => $model) {
                    $truck_stop = new TruckStop;
                    $truck_stop->user_id = Auth::user()->id;
                    $truck_stop->route_id = $this->route_id;
                    if (isset($this->name[$key])) {
                        $truck_stop->name = $this->name[$key];
                    }
                    if (isset($this->rating[$key])) {
                        $truck_stop->rating = $this->rating[$key];
                    }
                    if (isset($this->expiry_date[$key])) {
                        $truck_stop->expiry_date = $this->expiry_date[$key];
                    }
                    if (isset($this->description[$key])) {
                        $truck_stop->description = $this->description[$key];
                    }
                    $truck_stop->status = 1;
                    $truck_stop->save();
                }
            }
       
        $this->dispatchBrowserEvent('hide-truck_stopModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Truck Stop(s) Created Successfully!!"
        ]);
        }
        catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-truck_stopModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating truck stops!!"
        ]);
    }

    }

    public function edit($id){
    $truck_stop = TruckStop::find($id);
    $this->user_id = $truck_stop->user_id;
    $this->name = $truck_stop->name;
    $this->rating = $truck_stop->rating;
    $this->expiry_date = $truck_stop->expiry_date;
    $this->description = $truck_stop->description;
    $this->status = $truck_stop->status;
    $this->route_id = $truck_stop->route_id;
    $this->truck_stop_id = $truck_stop->id;
    $this->dispatchBrowserEvent('show-truck_stopEditModal');

    }

    public function update()
    {
        if ($this->truck_stop_id) {
            try{
            $truck_stop = TruckStop::find($this->truck_stop_id);
            $truck_stop->user_id = Auth::user()->id;
            $truck_stop->name = $this->name;
            $truck_stop->rating = $this->rating;
            $truck_stop->expiry_date = $this->expiry_date;
            $truck_stop->description = $this->description;
            $truck_stop->route_id = $this->route_id;
            $truck_stop->status = $this->status;
            $truck_stop->update();

            $this->dispatchBrowserEvent('hide-truck_stopEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Truck Stop Updated Successfully!!"
            ]);
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-truck_stopEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while updating truck stop!!"
            ]);
        }
        }
    }


    public function render()
    {
        $this->truck_stops = TruckStop::latest()->get();
        return view('livewire.truck-stops.index',[
            'truck_stops' => $this->truck_stops
        ]);
    }
}
