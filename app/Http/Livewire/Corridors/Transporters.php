<?php

namespace App\Http\Livewire\Corridors;

use Livewire\Component;
use App\Models\Corridor;
use App\Models\Transporter;

class Transporters extends Component
{
    public $corridor_transporters;
    public $transporters;
    public $transporter;
    public $transporter_id;
    public $corridors;
    public $corridor_id;

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

    private function resetInputFields(){
        $this->transporter_id = "" ;
    }

    public function mount($id){
        $this->corridor = Corridor::find($id);
        $this->corridor_id = $this->corridor->id;
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->corridor_transporters = $this->corridor->transporters;
    }

    public function store(){
        try{

            if (isset($this->transporter_id)) {
                $this->corridor->transporters()->attach($this->transporter_id);
                }
            

            $this->dispatchBrowserEvent('hide-corridor_transporterModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while adding transporter(s)!!"
            ]);
        }
    }

    public function removeShow($id){
        $this->transporter = Transporter::find($id);
        $this->dispatchBrowserEvent('show-corridorDeleteModal');
    }

    public function removeTransporter(){
        $this->corridor->transporters()->detach($this->transporter->id);
        $this->dispatchBrowserEvent('hide-corridorDeleteModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transporter Removed Successfully!!"
        ]);
    }

    public function render()
    {
        $corridor = Corridor::find($this->corridor_id);
        $this->corridor_transporters = $corridor->transporters;
        return view('livewire.corridors.transporters',[
            'corridor_transporters' =>  $this->corridor_transporters
        ]);
    }
}
