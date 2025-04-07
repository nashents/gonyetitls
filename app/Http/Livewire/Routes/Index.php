<?php

namespace App\Http\Livewire\Routes;

use App\Models\Route;
use App\Models\Border;
use Livewire\Component;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $destinations;
    public $routes;
    public $from;
    public $to;
    public $rank;
    public $distance;
    public $status;
    public $tollgates;
    public $expiry_date;
    public $borders;
    public $border_id;
    public $name;
    public $description;

    public $route_id;
    public $user_id;

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

    public function mount(){
        $this->routes = Route::latest()->get();
        $this->borders = Border::latest()->get();
        $this->destinations = Destination::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to' => 'required',
        'from' => 'required',
        'rank' => 'required',
        'description' => 'required',
        'name' => 'required|unique:routes,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->to = '';
        $this->from = '';
        $this->name = '';
        $this->rank = '';
        $this->description = '';
        $this->expiry_date = '';
        $this->tollgates = '';
        $this->distance = '';
        $this->status = '';
        $this->border_id = Null;
    }

    public function store(){
        try{
        $route = new Route;
        $route->user_id = Auth::user()->id;
        $route->name = $this->name;
        $route->distance = $this->distance;
        $route->tollgates = $this->tollgates;
        $route->description = $this->description;
        $route->from = $this->from;
        $route->expiry_date = $this->expiry_date;
        $route->to = $this->to;
        $route->rank = $this->rank;
        $route->status = 1;
        $route->save();
        $route->borders()->sync($this->border_id);

        $this->dispatchBrowserEvent('hide-routeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Route Created Successfully!!"
        ]);

        // return redirect()->route('routes.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating route!!"
        ]);
    }
    }

    public function edit($id){
    $route = Route::find($id);
    $this->user_id = $route->user_id;
    $this->name = $route->name;
    $this->description = $route->description;
    $this->tollgates = $route->tollgates;
    $this->distance = $route->distance;
    $this->status = $route->status;
    foreach ($route->borders as $border) {
        $this->border_id[] = $border->id;
    }
    $this->expiry_date = $route->expiry_date;
    $this->from = $route->from;
    $this->to = $route->to;
    $this->rank = $route->rank;
    $this->route_id = $route->id;
    $this->dispatchBrowserEvent('show-routeEditModal');

    }


    public function update()
    {
        if ($this->route_id) {
            try{
            $route = Route::find($this->route_id);
            $route->user_id = Auth::user()->id;
            $route->name = $this->name;
            $route->description = $this->description;
            $route->tollgates = $this->tollgates;
            $route->distance = $this->distance;
            $route->status = $this->status;
            $route->expiry_date = $this->expiry_date;
            $route->from = $this->from;
            $route->to = $this->to;
            $route->rank = $this->rank;
            $route->update();
            $route->borders()->detach();
            $route->borders()->sync($this->border_id);

            $this->dispatchBrowserEvent('hide-routeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Route Updated Successfully!!"
            ]);


            // return redirect()->route('routes.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-routeEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating route!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->routes = Route::latest()->get();
        return view('livewire.routes.index',[
            'routes'=>   $this->routes
        ]);
    }
}
