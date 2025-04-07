<?php

namespace App\Http\Livewire\Routes;

use App\Models\Route;
use Livewire\Component;

class Show extends Component
{
    public $route;
     
    public function mount($id){
        $this->route = Route::find($id);
    }
    public function render()
    {
        return view('livewire.routes.show');
    }
}
