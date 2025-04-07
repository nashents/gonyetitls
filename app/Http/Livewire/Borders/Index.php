<?php

namespace App\Http\Livewire\Borders;

use App\Models\Border;
use App\Models\Country;
use Livewire\Component;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $countries;
    public $borders;
    public $country_a;
    public $country_b;
    public $name;
    public $description;

    public $border_id;
    public $user_id;

    public function mount(){
        $this->borders = Border::latest()->get();
        $this->countries = Country::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'country_a' => 'required',
        'country_b' => 'required',
        'name' => 'required|unique:borders,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->country_a = '';
        $this->country_b = '';
        $this->name = '';
    }

    public function store(){
        try{
        $border = new Border;
        $border->user_id = Auth::user()->id;
        $border->name = $this->name;
        $border->country_a = $this->country_a;
        $border->country_b = $this->country_b;
        $border->save();

        $this->dispatchBrowserEvent('hide-borderModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Border Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating border!!"
        ]);
    }
    }

    public function edit($id){
    $border = Border::find($id);
    $this->user_id = $border->user_id;
    $this->name = $border->name;
    $this->country_a = $border->country_a;
    $this->country_b = $border->country_b;
    $this->border_id = $border->id;
    $this->dispatchBrowserEvent('show-borderEditModal');

    }


    public function update()
    {
        if ($this->border_id) {
            try{
            $border = Border::find($this->border_id);
            $border->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'country_a' => $this->country_a,
                'country_b' => $this->country_b,
            ]);

            $this->dispatchBrowserEvent('hide-borderEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Border Updated Successfully!!"
            ]);


            // return redirect()->border('border.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-borderEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating border!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->borders = Border::latest()->get();
        return view('livewire.borders.index',[
            'borders'=>   $this->borders
        ]);
    }
}
