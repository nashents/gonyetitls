<?php

namespace App\Http\Livewire\Transporters;

use Livewire\Component;
use App\Models\Corridor;
use App\Models\Transporter;
use App\Models\TransporterCorridor;

class Corridors extends Component
{
    public $transporter;
    public $transporter_id;
    public $transporter_corridors;
    public $transporter_corridor_id;
    public $corridors;
    public $corridor;
    public $corridor_id;
    public $name;
    public $from;
    public $to;

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
        $this->title = "" ;
        $this->file = "";
    }

    public function mount($id){
        $this->transporter = Transporter::find($id);
        $this->transporter_id = $id;
        $this->corridors = Corridor::latest()->get();
        $this->transporter_corridors =  $this->transporter->corridors;
    }

    public function removeShow($transporter_id, $corridor_id){
        $this->transporter = Transporter::find($transporter_id);
        $this->corridor = Corridor::find($corridor_id);
        $this->dispatchBrowserEvent('show-removeCorridorModal');
    }

    public function removeCorridor(){ 
        $this->transporter->corridors()->detach($this->corridor->id);
        $this->dispatchBrowserEvent('hide-removeCorridorModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Corridor(s) Removed Successfully!!"
        ]);
    }

    public function store(){
        try{

            if (isset($this->corridor_id)) {
                $this->transporter->corridors()->attach($this->corridor_id);
                }
            

            $this->dispatchBrowserEvent('hide-addCorridorModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Corridor(s) Added Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading corridor(s)!!"
            ]);
        }
    }

    public function render()
    {
        $this->transporter_corridors =  $this->transporter->corridors;
        $this->transporter = Transporter::find( $this->transporter_id);
        return view('livewire.transporters.corridors',[
            'transporter_corridors' => $this->transporter_corridors,
            'transporter' => $this->transporter
        ]);
    }
}
