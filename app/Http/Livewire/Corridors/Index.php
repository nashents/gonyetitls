<?php

namespace App\Http\Livewire\Corridors;

use App\Models\Country;
use Livewire\Component;
use App\Models\Corridor;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{


    public $countries;
    public $country_id;
    public $corridors;
    public $from;
    public $to;
    public $rank;
    public $name;
    public $description;

    public $corridor_id;
    public $user_id;

    public function mount(){
        $this->corridors = Corridor::latest()->get();
        $this->countries = Country::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'to' => 'required',
        'from' => 'required',
        'rank' => 'required',
        'description' => 'required',
        'name' => 'required|unique:corridors,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->to = '';
        $this->from = '';
        $this->name = '';
        $this->rank = '';
        $this->description = '';
    }

    public function store(){
        try{
        $corridor = new Corridor;
        $corridor->user_id = Auth::user()->id;
        $corridor->name = $this->name;
        $corridor->from = $this->from;
        $corridor->to = $this->to;
        $corridor->save();

        $this->dispatchBrowserEvent('hide-corridorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Corridor Created Successfully!!"
        ]);

        // return redirect()->corridor('corridors.index');

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while creating Corridor!!"
        ]);
    }
    }

    public function edit($id){
    $corridor = Corridor::find($id);
    $this->user_id = $corridor->user_id;
    $this->name = $corridor->name;
    $this->from = $corridor->from;
    $this->to = $corridor->to;
    $this->corridor_id = $corridor->id;
    $this->dispatchBrowserEvent('show-corridorEditModal');

    }


    public function update()
    {
        if ($this->corridor_id) {
            try{
            $corridor = Corridor::find($this->corridor_id);
            $corridor->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'from' => $this->from,
                'to' => $this->to,
            ]);

            $this->dispatchBrowserEvent('hide-corridorEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Corridor Updated Successfully!!"
            ]);


            // return redirect()->corridor('corridors.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-corridorEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating Corridor!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->corridors = Corridor::latest()->get();
        return view('livewire.corridors.index',[
            'corridors'=>   $this->corridors
        ]);
    }
}
